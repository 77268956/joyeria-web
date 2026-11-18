const modal = document.getElementById('supervisor-authorization-modal');

if (modal) {
    const actionLabel = document.getElementById('supervisor-authorization-action');
    const pinInput = document.getElementById('supervisor-authorization-pin');
    const errorBox = document.getElementById('supervisor-authorization-error');
    const submitButton = modal.querySelector('[data-supervisor-submit]');
    const cancelButtons = modal.querySelectorAll('[data-supervisor-cancel]');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    const authorizedUntilMeta = document.querySelector('meta[name="supervisor-authorized-until"]')?.content || '';
    const validateUrl = modal.dataset.validateUrl;
    const storageKey = 'joyeria.supervisor.unlockedUntil';
    const nativeFetch = window.fetch.bind(window);
    const serverUnlockedUntil = Date.parse(authorizedUntilMeta);

    let pendingResolve = null;
    let busy = false;
    let modalOpen = false;
    let unlockedUntil = 0;

    const storage = {
        get() {
            try {
                return window.sessionStorage.getItem(storageKey);
            } catch {
                return null;
            }
        },
        set(value) {
            try {
                window.sessionStorage.setItem(storageKey, value);
            } catch {
                //
            }
        },
        clear() {
            try {
                window.sessionStorage.removeItem(storageKey);
            } catch {
                //
            }
        },
    };

    if (Number.isFinite(serverUnlockedUntil) && serverUnlockedUntil > Date.now()) {
        unlockedUntil = serverUnlockedUntil;
        storage.set(String(serverUnlockedUntil));
    } else {
        storage.clear();
    }

    function isUnlocked() {
        return unlockedUntil > Date.now() + 5000;
    }

    function setBusy(value) {
        busy = value;

        if (submitButton) {
            submitButton.disabled = value;
            submitButton.textContent = value ? 'Validando...' : 'Autorizar y continuar';
        }

        if (pinInput) {
            pinInput.disabled = value;
        }

        cancelButtons.forEach((button) => {
            button.disabled = value;
        });
    }

    function setError(message) {
        if (!errorBox) {
            return;
        }

        errorBox.textContent = message || '';
        errorBox.style.display = message ? 'flex' : 'none';
    }

    function showModal(action) {
        modalOpen = true;
        setError('');
        setBusy(false);

        if (pinInput) {
            pinInput.value = '';
        }

        if (actionLabel) {
            actionLabel.textContent = action || 'esta acción';
        }

        modal.style.display = 'flex';
        modal.classList.remove('is-closing');
        modal.classList.add('is-active');

        window.setTimeout(() => pinInput?.focus(), 80);
    }

    function hideModal() {
        modalOpen = false;
        modal.classList.add('is-closing');
        modal.classList.remove('is-active');

        window.setTimeout(() => {
            modal.style.display = 'none';
            modal.classList.remove('is-closing');
        }, 280);
    }

    function resolvePending(value) {
        if (!pendingResolve) {
            return;
        }

        const resolve = pendingResolve;
        pendingResolve = null;
        resolve(value);
    }

    function waitForPin() {
        return new Promise((resolve) => {
            pendingResolve = resolve;
        });
    }

    function submitPin() {
        if (busy) {
            return;
        }

        const pin = pinInput?.value.trim() || '';

        if (!/^\d{4}$/.test(pin)) {
            setError('El PIN debe tener exactamente 4 dígitos.');
            pinInput?.focus();

            return;
        }

        setError('');
        resolvePending(pin);
    }

    async function validatePin(pin) {
        const response = await nativeFetch(validateUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
            },
            body: JSON.stringify({ pin }),
        });

        let data = {};

        try {
            data = await response.json();
        } catch {
            //
        }

        if (!response.ok || !data.valid) {
            throw new Error(data.message || 'PIN incorrecto o supervisor no autorizado.');
        }

        return data;
    }

    async function ensureSupervisor(action) {
        if (isUnlocked()) {
            return true;
        }

        showModal(action);

        while (true) {
            const pin = await waitForPin();

            if (pin === null) {
                setBusy(false);
                hideModal();

                return false;
            }

            setBusy(true);

            try {
                const data = await validatePin(pin);
                const expiresAt = Date.parse(data.expires_at || '');

                unlockedUntil = Number.isFinite(expiresAt) ? expiresAt : Date.now() + 10 * 60 * 1000;
                storage.set(String(unlockedUntil));
                setBusy(false);
                hideModal();

                return true;
            } catch (error) {
                setBusy(false);
                setError(error instanceof Error ? error.message : 'No se pudo validar el PIN.');

                if (pinInput) {
                    pinInput.value = '';
                    pinInput.focus();
                }
            }
        }
    }

    submitButton?.addEventListener('click', submitPin);

    pinInput?.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
            event.preventDefault();
            submitPin();
        }
    });

    cancelButtons.forEach((button) => {
        button.addEventListener('click', () => {
            if (!busy) {
                resolvePending(null);
            }
        });
    });

    modal.addEventListener('click', (event) => {
        if (event.target === modal && !busy) {
            resolvePending(null);
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && modal.classList.contains('is-active') && !busy) {
            resolvePending(null);
        }
    });

    document.addEventListener('submit', async (event) => {
        const form = event.target;

        if (!(form instanceof HTMLFormElement)) {
            return;
        }

        if (form.dataset.skipSupervisor !== undefined || event.defaultPrevented) {
            return;
        }

        if (busy || modalOpen) {
            event.preventDefault();

            return;
        }

        const method = (form.getAttribute('method') || 'GET').toUpperCase();

        if (method === 'GET') {
            return;
        }

        event.preventDefault();

        const submitter = event.submitter;
        const rawAction = form.dataset.supervisorAction
            || submitter?.textContent
            || submitter?.value
            || 'esta acción';
        const action = String(rawAction).replace(/\s+/g, ' ').trim().slice(0, 90);
        const authorized = await ensureSupervisor(action);

        if (authorized) {
            HTMLFormElement.prototype.submit.call(form);
        }
    }, false);

    window.fetch = async function (input, init) {
        const retryInput = input instanceof Request ? input.clone() : input;
        const response = await nativeFetch(input, init);

        if (response.status !== 423) {
            return response;
        }

        let data = {};

        try {
            data = await response.clone().json();
        } catch {
            //
        }

        const authorized = await ensureSupervisor(data.action || 'esta acción');

        if (!authorized) {
            throw new Error(data.message || 'Autorización cancelada.');
        }

        return nativeFetch(retryInput, init);
    };

    window.SupervisorConfirm = {
        ensure: ensureSupervisor,
        isUnlocked,
        lock: () => {
            unlockedUntil = 0;
            storage.clear();
        },
    };
}
