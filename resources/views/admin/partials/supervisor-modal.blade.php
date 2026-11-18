<div
    class="modal-backdrop"
    id="supervisor-authorization-modal"
    data-validate-url="{{ route('admin.supervisor.validate_pin') }}"
>
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="supervisor-authorization-title" style="max-width: 460px;">
        <div class="modal-header">
            <div>
                <h2 id="supervisor-authorization-title" style="font-size: 1.05rem; margin: 0;">Autorización de supervisor</h2>
                <p class="muted" style="font-size: 0.78rem; margin: 0.2rem 0 0;">Firma con PIN para continuar</p>
            </div>
            <button type="button" class="modal-close" data-supervisor-cancel aria-label="Cerrar">&times;</button>
        </div>

        <div class="modal-body">
            <p style="margin: 0 0 1rem; color: var(--color-noche); font-size: 0.9rem;">
                Para continuar con <strong id="supervisor-authorization-action">esta acción</strong>, ingresa el PIN de 4 dígitos de un supervisor autorizado.
            </p>

            <div class="form-group" style="margin-bottom: 0.75rem;">
                <label class="form-label" for="supervisor-authorization-pin">PIN de supervisor</label>
                <input
                    type="password"
                    id="supervisor-authorization-pin"
                    class="form-control"
                    inputmode="numeric"
                    autocomplete="one-time-code"
                    maxlength="4"
                    placeholder="••••"
                    style="max-width: 180px; letter-spacing: 0.4em; text-align: center; font-size: 1.25rem; font-weight: 700;"
                >
            </div>

            <p id="supervisor-authorization-error" class="alert alert-error" role="alert" aria-live="polite" style="display: none; margin-bottom: 0;"></p>

            <p class="form-hint" style="margin-top: 0.75rem; margin-bottom: 0;">
                La autorización queda registrada en auditoría y permanece activa durante {{ config('supervisor.session_minutes', 10) }} minutos.
            </p>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-outline" data-supervisor-cancel>Cancelar</button>
            <button type="button" class="btn btn-gold" data-supervisor-submit>Autorizar y continuar</button>
        </div>
    </div>
</div>
