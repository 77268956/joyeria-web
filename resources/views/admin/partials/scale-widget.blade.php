{{--
    Indicador de conexión de balanza digital (Web Serial API).

    Requiere cargar `resources/js/scale.js` en el stack de scripts de la vista.
    La lógica de conexión vive en el módulo; este partial solo presenta el estado
    y expone los controles mediante atributos `data-scale-*`.
--}}
<div class="scale-panel" data-scale-panel>
    <div class="scale-panel-head">
        <div class="scale-status" data-scale-status="unsupported">
            <span class="scale-dot"></span>
            <span data-scale-status-text>Verificando balanza…</span>
        </div>
        <div class="scale-reading">
            <span class="num" data-scale-reading>—</span>
            <span class="scale-unit">g</span>
        </div>
    </div>

    <div class="scale-actions">
        <select class="form-select" data-scale-baud aria-label="Velocidad de la balanza">
            <option value="9600" selected>9600 baudios</option>
            <option value="4800">4800 baudios</option>
            <option value="19200">19200 baudios</option>
            <option value="38400">38400 baudios</option>
            <option value="115200">115200 baudios</option>
        </select>
        <button type="button" class="btn btn-gold btn-sm" data-scale-connect>Conectar balanza</button>
        <button type="button" class="btn btn-outline btn-sm" data-scale-disconnect hidden>Desconectar</button>
    </div>

    <p class="form-hint" data-scale-hint>
        Conecta la balanza por USB (Chrome o Edge). Si no está disponible, escribe el peso a mano: quedará marcado como captura manual.
    </p>
</div>
