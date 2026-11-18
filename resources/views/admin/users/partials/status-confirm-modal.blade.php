<style>
    .user-confirm-hero {
        display: flex;
        align-items: flex-start;
        gap: 0.9rem;
    }

    .user-confirm-icon {
        display: grid;
        place-items: center;
        flex-shrink: 0;
        width: 46px;
        height: 46px;
        border-radius: 50%;
        background: #FDEDED;
        color: var(--color-error);
    }

    .user-confirm-icon.is-success {
        background: #EDF7EE;
        color: var(--color-exito);
    }

    .user-confirm-icon[hidden] {
        display: none;
    }

    .user-confirm-hero h3 {
        margin: 0;
        color: var(--color-noche);
        font-size: 1rem;
        font-weight: 600;
    }

    .user-confirm-hero p {
        margin: 0.25rem 0 0;
        color: var(--color-piedra);
        font-size: 0.84rem;
        line-height: 1.45;
    }

    .user-confirm-list {
        display: grid;
        gap: 0.55rem;
        margin: 1rem 0 0;
        padding: 0.85rem 1rem;
        list-style: none;
        border: 1px solid var(--color-linea);
        border-radius: 5px;
        background: var(--color-crema);
    }

    .user-confirm-list li {
        display: flex;
        align-items: flex-start;
        gap: 0.5rem;
        color: var(--color-noche);
        font-size: 0.84rem;
        line-height: 1.4;
    }

    .user-confirm-list svg {
        flex-shrink: 0;
        margin-top: 0.15rem;
        color: var(--color-dorado);
    }

    .user-confirm-accept.is-danger {
        background-color: var(--color-error);
        color: var(--color-blanco);
    }

    .user-confirm-accept.is-danger:hover {
        background-color: #A31832;
    }
</style>

<div class="modal-backdrop" id="user-confirm-modal">
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="user-confirm-title" style="max-width: 500px;">
        <div class="modal-header">
            <div>
                <h2 id="user-confirm-title" style="font-size: 1.05rem; margin: 0;">Cambiar estado de la cuenta</h2>
                <p class="muted" id="user-confirm-subtitle" style="font-size: 0.78rem; margin: 0.2rem 0 0;">Colaborador</p>
            </div>
            <button type="button" class="modal-close" data-user-confirm-cancel aria-label="Cerrar">&times;</button>
        </div>

        <div class="modal-body">
            <div class="user-confirm-hero">
                <span class="user-confirm-icon" data-user-confirm-icon="deactivate" aria-hidden="true">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                        <circle cx="9" cy="7" r="4" />
                        <line x1="17" y1="8" x2="23" y2="14" />
                        <line x1="23" y1="8" x2="17" y2="14" />
                    </svg>
                </span>
                <span class="user-confirm-icon is-success" data-user-confirm-icon="activate" aria-hidden="true" hidden>
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                        <circle cx="9" cy="7" r="4" />
                        <path d="m16 11 2 2 4-4" />
                    </svg>
                </span>
                <span class="user-confirm-icon" data-user-confirm-icon="delete" aria-hidden="true" hidden>
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="3 6 5 6 21 6" />
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                    </svg>
                </span>
                <div>
                    <h3>Revisa antes de continuar</h3>
                    <p id="user-confirm-description">Confirma la acción para continuar.</p>
                </div>
            </div>

            <ul class="user-confirm-list">
                <li>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M20 6 9 17l-5-5" />
                    </svg>
                    <span>La acción quedará registrada en la bitácora con tu usuario y la fecha.</span>
                </li>
                <li>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M20 6 9 17l-5-5" />
                    </svg>
                    <span id="user-confirm-impact">La cuenta conservará su historial y sus registros.</span>
                </li>
            </ul>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-outline" data-user-confirm-cancel>Cancelar</button>
            <button type="button" class="btn btn-dark user-confirm-accept" data-user-confirm-accept>Continuar</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('user-confirm-modal');

    if (!modal) {
        return;
    }

    const title = document.getElementById('user-confirm-title');
    const subtitle = document.getElementById('user-confirm-subtitle');
    const description = document.getElementById('user-confirm-description');
    const impact = document.getElementById('user-confirm-impact');
    const acceptButton = modal.querySelector('[data-user-confirm-accept]');
    const icons = {
        deactivate: modal.querySelector('[data-user-confirm-icon="deactivate"]'),
        activate: modal.querySelector('[data-user-confirm-icon="activate"]'),
        delete: modal.querySelector('[data-user-confirm-icon="delete"]'),
    };
    const openButtons = document.querySelectorAll('[data-user-confirm-open]');
    const cancelButtons = modal.querySelectorAll('[data-user-confirm-cancel]');

    let activeForm = null;
    let submitting = false;
    let closeTimer = null;

    function setIcon(mode) {
        Object.entries(icons).forEach(([name, icon]) => {
            icon.hidden = name !== mode;
        });
    }

    function openModal(button) {
        const form = button.closest('form[data-user-confirm-form]');

        if (!form) {
            return;
        }

        if (closeTimer) {
            window.clearTimeout(closeTimer);
            closeTimer = null;
        }

        activeForm = form;

        const name = form.dataset.userName || 'este colaborador';
        const isDelete = form.dataset.action === 'delete';
        const willBeActive = form.dataset.willBeActive === 'true';
        const mode = isDelete ? 'delete' : (willBeActive ? 'activate' : 'deactivate');

        if (mode === 'delete') {
            title.textContent = 'Eliminar colaborador';
            subtitle.textContent = 'Colaborador: ' + name;
            description.textContent = `El colaborador "${name}" será eliminado del directorio. Esta acción no se puede deshacer desde la interfaz.`;
            impact.textContent = 'La cuenta se marcará como eliminada y dejará de aparecer en el directorio.';
            acceptButton.textContent = 'Eliminar colaborador';
        } else if (mode === 'activate') {
            title.textContent = 'Activar cuenta';
            subtitle.textContent = 'Colaborador: ' + name;
            description.textContent = `La cuenta de "${name}" volverá a estar activa y podrá iniciar sesión con sus permisos actuales.`;
            impact.textContent = 'La cuenta podrá iniciar sesión nuevamente con sus permisos actuales.';
            acceptButton.textContent = 'Activar cuenta';
        } else {
            title.textContent = 'Desactivar cuenta';
            subtitle.textContent = 'Colaborador: ' + name;
            description.textContent = `La cuenta de "${name}" se desactivará. No podrá iniciar sesión hasta que la actives de nuevo.`;
            impact.textContent = 'La cuenta dejará de poder iniciar sesión, pero conservará su historial y sus registros.';
            acceptButton.textContent = 'Desactivar cuenta';
        }

        acceptButton.classList.toggle('is-danger', mode !== 'activate');
        setIcon(mode);

        submitting = false;
        modal.style.display = 'flex';
        modal.classList.remove('is-closing');
        modal.classList.add('is-active');

        window.setTimeout(() => acceptButton.focus(), 80);
    }

    function closeModal() {
        if (closeTimer) {
            window.clearTimeout(closeTimer);
        }

        modal.classList.add('is-closing');
        modal.classList.remove('is-active');

        closeTimer = window.setTimeout(() => {
            modal.style.display = 'none';
            modal.classList.remove('is-closing');
            closeTimer = null;
        }, 280);
    }

    openButtons.forEach((button) => {
        button.addEventListener('click', () => openModal(button));
    });

    cancelButtons.forEach((button) => {
        button.addEventListener('click', closeModal);
    });

    modal.addEventListener('click', (event) => {
        if (event.target === modal) {
            closeModal();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && modal.classList.contains('is-active')) {
            closeModal();
        }
    });

    acceptButton.addEventListener('click', () => {
        if (submitting || !activeForm) {
            return;
        }

        submitting = true;
        closeModal();

        window.setTimeout(() => {
            activeForm.requestSubmit();
        }, 200);
    });
});
</script>
@endpush
