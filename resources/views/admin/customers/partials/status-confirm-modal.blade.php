<style>
    .cust-confirm-hero {
        display: flex;
        align-items: flex-start;
        gap: 0.9rem;
    }

    .cust-confirm-icon {
        display: grid;
        place-items: center;
        flex-shrink: 0;
        width: 46px;
        height: 46px;
        border-radius: 50%;
        background: #FDEDED;
        color: var(--color-error);
    }

    .cust-confirm-icon.is-reactivate {
        background: #EDF7EE;
        color: var(--color-exito);
    }

    .cust-confirm-icon[hidden] {
        display: none;
    }

    .cust-confirm-hero h3 {
        margin: 0;
        color: var(--color-noche);
        font-size: 1rem;
        font-weight: 600;
    }

    .cust-confirm-hero p {
        margin: 0.25rem 0 0;
        color: var(--color-piedra);
        font-size: 0.84rem;
        line-height: 1.45;
    }

    .cust-confirm-list {
        display: grid;
        gap: 0.55rem;
        margin: 1rem 0 0;
        padding: 0.85rem 1rem;
        list-style: none;
        border: 1px solid var(--color-linea);
        border-radius: 5px;
        background: var(--color-crema);
    }

    .cust-confirm-list li {
        display: flex;
        align-items: flex-start;
        gap: 0.5rem;
        color: var(--color-noche);
        font-size: 0.84rem;
        line-height: 1.4;
    }

    .cust-confirm-list svg {
        flex-shrink: 0;
        margin-top: 0.15rem;
        color: var(--color-dorado);
    }
</style>

<div class="modal-backdrop" id="customer-status-modal">
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="customer-status-title" style="max-width: 500px;">
        <div class="modal-header">
            <div>
                <h2 id="customer-status-title" style="font-size: 1.05rem; margin: 0;">Cambiar estado del cliente</h2>
                <p class="muted" id="customer-status-subtitle" style="font-size: 0.78rem; margin: 0.2rem 0 0;">Cliente</p>
            </div>
            <button type="button" class="modal-close" data-customer-status-cancel aria-label="Cerrar">&times;</button>
        </div>

        <div class="modal-body">
            <div class="cust-confirm-hero">
                <span class="cust-confirm-icon" data-customer-status-icon="deactivate" aria-hidden="true">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                        <circle cx="9" cy="7" r="4" />
                        <line x1="17" y1="8" x2="23" y2="14" />
                        <line x1="23" y1="8" x2="17" y2="14" />
                    </svg>
                </span>
                <span class="cust-confirm-icon is-reactivate" data-customer-status-icon="reactivate" aria-hidden="true" hidden>
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                        <circle cx="9" cy="7" r="4" />
                        <path d="m16 11 2 2 4-4" />
                    </svg>
                </span>
                <div>
                    <h3>Revisa antes de continuar</h3>
                    <p id="customer-status-description">Confirma el cambio de estado para continuar.</p>
                </div>
            </div>

            <ul class="cust-confirm-list">
                <li>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M20 6 9 17l-5-5" />
                    </svg>
                    <span>El cambio de estado quedará registrado en la bitácora de auditoría.</span>
                </li>
                <li>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M20 6 9 17l-5-5" />
                    </svg>
                    <span>Si tienes dudas, cancela y revisa la ficha del cliente antes de continuar.</span>
                </li>
            </ul>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-outline" data-customer-status-cancel>Cancelar</button>
            <button type="button" class="btn btn-dark" data-customer-status-confirm>Continuar</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('customer-status-modal');

    if (!modal) {
        return;
    }

    const title = document.getElementById('customer-status-title');
    const subtitle = document.getElementById('customer-status-subtitle');
    const description = document.getElementById('customer-status-description');
    const confirmButton = modal.querySelector('[data-customer-status-confirm]');
    const deactivateIcon = modal.querySelector('[data-customer-status-icon="deactivate"]');
    const reactivateIcon = modal.querySelector('[data-customer-status-icon="reactivate"]');
    const openButtons = document.querySelectorAll('[data-customer-status-open]');
    const cancelButtons = modal.querySelectorAll('[data-customer-status-cancel]');

    let activeForm = null;
    let submitting = false;
    let closeTimer = null;

    function openModal(button) {
        const form = button.closest('form[data-customer-status-form]');

        if (!form) {
            return;
        }

        if (closeTimer) {
            window.clearTimeout(closeTimer);
            closeTimer = null;
        }

        activeForm = form;

        const name = form.dataset.customerName || 'este cliente';
        const isDeactivate = form.dataset.action === 'deactivate';

        title.textContent = isDeactivate ? 'Desactivar cliente' : 'Reactivar cliente';
        subtitle.textContent = 'Cliente: ' + name;
        description.textContent = isDeactivate
            ? `El cliente "${name}" dejará de aparecer en el directorio activo, pero podrás reactivarlo cuando lo necesites.`
            : `El cliente "${name}" volverá al directorio activo y podrá recibir nuevas ventas, apartados y compras.`;
        confirmButton.textContent = isDeactivate ? 'Desactivar cliente' : 'Reactivar cliente';

        deactivateIcon.hidden = !isDeactivate;
        reactivateIcon.hidden = isDeactivate;

        submitting = false;
        modal.style.display = 'flex';
        modal.classList.remove('is-closing');
        modal.classList.add('is-active');

        window.setTimeout(() => confirmButton.focus(), 80);
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

    confirmButton.addEventListener('click', () => {
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
