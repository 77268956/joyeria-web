@extends('layouts.admin')

@section('title', 'Bitácora de Auditoría')

@section('body_class', 'full-viewport audit-body')

@section('content')
@php
    $hasFilters = request()->hasAny(['search', 'log_name', 'event', 'causer_id', 'from', 'to']);
    $formatValue = function ($value): string {
        if ($value === null || $value === '') {
            return '—';
        }

        if (is_bool($value)) {
            return $value ? 'Sí' : 'No';
        }

        if (is_array($value) || is_object($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '—';
        }

        return (string) $value;
    };
    $subjectLabels = [
        'Sale' => 'Venta',
        'MetalPurchase' => 'Compra',
        'Product' => 'Pieza',
        'Layaway' => 'Apartado',
        'Customer' => 'Cliente',
        'CashSession' => 'Turno de caja',
        'MaterialLot' => 'Lote',
        'StockCount' => 'Conteo',
        'User' => 'Usuario',
        'MetalPrice' => 'Precio',
        'DiscountAuthorization' => 'Descuento',
        'PurchaseAdjustment' => 'Ajuste',
        'SystemSetting' => 'Configuración',
    ];
    $moduleLabels = [
        'ventas' => 'Ventas',
        'precios' => 'Precios',
        'compras' => 'Compras',
        'apartados' => 'Apartados',
        'inventario' => 'Inventario',
        'caja' => 'Caja',
        'descuentos' => 'Descuentos',
        'clientes' => 'Clientes',
        'usuarios' => 'Usuarios',
        'configuracion' => 'Configuración',
        'autorizacion' => 'Autorización',
    ];
    $eventLabels = [
        'opened' => 'Apertura',
        'closed' => 'Cierre',
        'movement' => 'Movimiento',
        'created' => 'Creación',
        'updated' => 'Actualización',
        'deleted' => 'Eliminación',
        'restored' => 'Reactivación',
        'requested' => 'Solicitud',
        'approved' => 'Aprobación',
        'rejected' => 'Rechazo',
        'payment' => 'Abono',
        'completed' => 'Entrega',
        'cancelled' => 'Cancelación',
        'adjusted' => 'Ajuste',
        'sent_to_melting' => 'Fundición',
        'refreshed' => 'Actualización automática',
        'price_set' => 'Precio fijado',
        'voided' => 'Anulación',
        'started' => 'Inicio',
        'finished' => 'Finalización',
        'applied' => 'Aplicación',
        'authorized' => 'Autorización',
        'activated' => 'Activación',
        'deactivated' => 'Desactivación',
        'failed' => 'Fallo',
    ];
    $propertyLabels = [
        'reason' => 'Motivo',
        'motivo' => 'Motivo',
        'amount' => 'Monto',
        'number' => 'Folio',
        'status' => 'Estado',
        'type' => 'Tipo',
        'subtotal' => 'Subtotal',
        'total' => 'Total',
        'total_amount' => 'Total',
        'total_price' => 'Total',
        'paid_total' => 'Total pagado',
        'balance' => 'Saldo',
        'discount_percent' => 'Descuento (%)',
        'discount_amount' => 'Descuento ($)',
        'price_per_gram' => 'Precio por gramo',
        'price_before' => 'Precio anterior',
        'price_after' => 'Precio nuevo',
        'cost' => 'Costo',
        'price' => 'Precio',
        'metal' => 'Metal',
        'metal_id' => 'Metal',
        'purity_id' => 'Pureza',
        'weight_g' => 'Peso (g)',
        'initial_weight_g' => 'Peso inicial (g)',
        'current_weight_g' => 'Peso actual (g)',
        'weight_delta_g' => 'Cambio de peso (g)',
        'quantity_delta' => 'Cambio de existencias',
        'opening_amount' => 'Fondo de apertura',
        'difference_total' => 'Diferencia total',
        'counted_cash' => 'Efectivo contado',
        'counted_card' => 'Tarjeta contada',
        'counted_transfer' => 'Transferencia contada',
        'close_note' => 'Nota de cierre',
        'email' => 'Correo',
        'role' => 'Rol',
        'is_active' => 'Activo',
        'store_id' => 'Sucursal',
        'full_name' => 'Nombre',
        'document_type' => 'Tipo de documento',
        'document_number' => 'Número de documento',
        'phone' => 'Teléfono',
        'address' => 'Dirección',
        'birth_date' => 'Fecha de nacimiento',
        'id_photo_path' => 'Foto del documento',
        'notes' => 'Notas',
        'cancel_reason' => 'Motivo de cancelación',
        'approval_note' => 'Nota de aprobación',
        'void_reason' => 'Motivo de anulación',
        'voided_at' => 'Fecha de anulación',
        'voided_by' => 'Anulado por',
        'approved_by' => 'Aprobado por',
        'approved_at' => 'Fecha de aprobación',
        'started_by' => 'Iniciado por',
        'started_at' => 'Fecha de inicio',
        'finished_by' => 'Finalizado por',
        'finished_at' => 'Fecha de finalización',
        'applied_by' => 'Aplicado por',
        'applied_at' => 'Fecha de aplicación',
        'location_id' => 'Ubicación',
        'category_id' => 'Categoría',
        'sku' => 'SKU',
        'barcode' => 'Código de barras',
        'name' => 'Nombre',
        'description' => 'Descripción',
        'origin' => 'Origen',
        'is_featured' => 'Destacado',
        'is_public' => 'Público',
        'provider' => 'Proveedor',
        'source' => 'Origen',
        'price_date' => 'Fecha del precio',
        'set_by' => 'Fijado por',
        'updated' => 'Actualizados',
        'failed' => 'Fallidos',
        'changes' => 'Cambios',
        'system_name' => 'Nombre del sistema',
        'supervisor_id' => 'ID del supervisor',
        'supervisor_name' => 'Supervisor',
        'deleted_at' => 'Fecha de desactivación',
        'cash_session_id' => 'Turno de caja',
        'user_id' => 'Usuario',
        'customer_id' => 'Cliente',
        'product_id' => 'Pieza',
    ];
@endphp

<style>
    body.audit-body .topbar {
        height: 44px;
        padding: 0 1rem;
    }

    body.audit-body .topbar-left,
    body.audit-body #supervisor-notif-container,
    body.audit-body .user-role {
        display: none;
    }

    body.audit-body .topbar-right {
        gap: 0.75rem;
    }

    body.audit-body .btn-logout {
        padding: 0.35rem 0.55rem;
    }

    body.audit-body .btn-logout span {
        display: none;
    }

    body.audit-body .content-area {
        padding-top: 0.75rem;
        padding-bottom: 0.75rem;
    }

    .audit-readonly {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.35rem 0.65rem;
        border: 1px solid var(--color-linea);
        border-radius: 999px;
        background-color: var(--color-blanco);
        color: var(--color-piedra);
        font-size: 0.72rem;
        font-weight: 600;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .audit-readonly svg {
        color: var(--color-dorado);
    }

    .audit-stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 0.5rem;
    }

    .audit-stat {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        min-width: 0;
        padding: 0.5rem 0.7rem;
        border: 1px solid var(--color-linea);
        border-radius: 6px;
        background-color: var(--color-blanco);
    }

    .audit-stat-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        width: 28px;
        height: 28px;
        border-radius: 6px;
        background-color: rgba(184, 154, 94, 0.13);
        color: var(--color-dorado);
    }

    .audit-stat-body {
        min-width: 0;
    }

    .audit-stat-label {
        display: block;
        overflow: hidden;
        color: var(--color-piedra);
        font-size: 0.68rem;
        font-weight: 600;
        letter-spacing: 0.06em;
        text-overflow: ellipsis;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .audit-stat-value {
        display: block;
        color: var(--color-noche);
        font-family: var(--font-serif);
        font-size: 1.15rem;
        font-weight: 600;
        line-height: 1.1;
    }

    .audit-stat-hint {
        display: none;
    }

    .audit-filter-card {
        padding: 0.55rem 0.75rem;
    }

    .audit-filter-form {
        display: flex;
        align-items: flex-end;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .audit-filter-field {
        flex: 1 1 105px;
        min-width: 0;
    }

    .audit-filter-field--search {
        flex: 2 1 180px;
    }

    .audit-filter-field label {
        display: block;
        margin-bottom: 0.15rem;
        color: var(--color-piedra);
        font-size: 0.62rem;
        font-weight: 600;
        letter-spacing: 0.05em;
        text-transform: uppercase;
    }

    .audit-filter-field .form-control,
    .audit-filter-field .form-select {
        height: 34px;
        font-size: 0.8rem;
    }

    .audit-search {
        position: relative;
    }

    .audit-search svg {
        position: absolute;
        top: 50%;
        left: 0.7rem;
        color: var(--color-piedra);
        pointer-events: none;
        transform: translateY(-50%);
    }

    .audit-search .form-control {
        padding-left: 2rem;
    }

    .audit-filter-actions {
        display: flex;
        flex: 0 0 auto;
        gap: 0.5rem;
    }

    .audit-filter-actions .btn {
        height: 34px;
        padding-top: 0;
        padding-bottom: 0;
    }

    .audit-table-card {
        display: flex;
        flex-direction: column;
        flex: 1 1 auto;
        min-height: 0;
        overflow: hidden;
        padding: 0;
    }

    .paginated-page > .card.audit-table-card {
        flex: 1 1 auto;
        min-height: 0;
    }

    .audit-table-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        flex-shrink: 0;
        padding: 1rem 1.25rem;
        border-bottom: 1px solid var(--color-linea);
    }

    .audit-table-title {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.6rem;
    }

    .audit-count {
        color: var(--color-piedra);
        font-size: 0.75rem;
        font-weight: 600;
    }

    .audit-table-header h2 {
        margin: 0;
        color: var(--color-noche);
        font-family: var(--font-serif);
        font-size: 1.05rem;
    }

    .audit-filter-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.25rem 0.55rem;
        border-radius: 999px;
        background-color: rgba(184, 154, 94, 0.13);
        color: var(--color-dorado);
        font-size: 0.7rem;
        font-weight: 600;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .audit-table-wrap {
        flex: 1;
        min-height: 0;
        overflow: auto;
    }

    .audit-table {
        width: 100%;
        min-width: 860px;
        border-collapse: collapse;
        text-align: left;
        font-size: 0.86rem;
    }

    .audit-table thead th {
        position: sticky;
        top: 0;
        z-index: 1;
        padding: 0.8rem 1.1rem;
        border-bottom: 1px solid var(--color-linea);
        background-color: var(--color-tabla-header);
        color: var(--color-noche);
        font-size: 0.7rem;
        font-weight: 600;
        letter-spacing: 0.05em;
        text-transform: uppercase;
    }

    .audit-row {
        border-bottom: 1px solid var(--color-linea);
        transition: background-color 0.15s;
    }

    .audit-row:hover {
        background-color: #FBF9F5;
    }

    .audit-row td {
        padding: 0.85rem 1.1rem;
        vertical-align: top;
    }

    .audit-when {
        white-space: nowrap;
    }

    .audit-date {
        display: block;
        color: var(--color-noche);
        font-size: 0.82rem;
        font-weight: 600;
    }

    .audit-time {
        display: block;
        color: var(--color-piedra);
        font-size: 0.75rem;
    }

    .audit-module {
        display: inline-flex;
        align-items: center;
        padding: 0.2rem 0.55rem;
        border-radius: 3px;
        font-size: 0.68rem;
        font-weight: 600;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    .audit-module.is-gold {
        background-color: rgba(184, 154, 94, 0.14);
        color: var(--color-dorado);
    }

    .audit-module.is-blue {
        background-color: #EBF3FB;
        color: #205493;
    }

    .audit-module.is-green {
        background-color: #EAF5EC;
        color: #2E7D32;
    }

    .audit-module.is-red {
        background-color: #FDECEC;
        color: var(--color-error);
    }

    .audit-module.is-dark {
        background-color: #EDF0F4;
        color: var(--color-noche);
    }

    .audit-module.is-stone {
        background-color: #F1EFEA;
        color: var(--color-piedra);
    }

    .audit-event {
        display: inline-flex;
        margin-top: 0.35rem;
        padding: 0.15rem 0.45rem;
        border: 1px solid var(--color-linea);
        border-radius: 3px;
        color: var(--color-piedra);
        font-size: 0.68rem;
        font-weight: 500;
    }

    .audit-causer {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .audit-avatar {
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        width: 28px;
        height: 28px;
        border: 1px solid var(--color-dorado-claro);
        border-radius: 50%;
        background-color: var(--color-noche);
        color: var(--color-dorado);
        font-family: var(--font-serif);
        font-size: 0.72rem;
        font-weight: 600;
    }

    .audit-causer-name {
        color: var(--color-noche);
        font-size: 0.82rem;
        font-weight: 600;
    }

    .audit-system {
        color: var(--color-piedra);
        font-size: 0.8rem;
        font-style: italic;
    }

    .audit-description {
        display: block;
        color: var(--color-noche);
        font-weight: 500;
        line-height: 1.45;
    }

    .audit-subject {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.2rem 0.5rem;
        border: 1px solid var(--color-linea);
        border-radius: 4px;
        background-color: var(--color-blanco);
        color: var(--color-piedra);
        font-size: 0.75rem;
        white-space: nowrap;
    }

    .audit-subject span {
        color: var(--color-noche);
        font-weight: 600;
    }

    .audit-muted {
        color: var(--color-piedra);
    }

    .audit-details {
        margin-top: 0.45rem;
    }

    .audit-details summary {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        color: var(--color-dorado);
        cursor: pointer;
        font-size: 0.75rem;
        font-weight: 600;
        list-style: none;
    }

    .audit-details summary::-webkit-details-marker {
        display: none;
    }

    .audit-details summary svg {
        transition: transform 0.15s;
    }

    .audit-details[open] summary svg {
        transform: rotate(90deg);
    }

    .audit-details-count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 18px;
        height: 18px;
        padding: 0 0.3rem;
        border-radius: 999px;
        background-color: rgba(184, 154, 94, 0.15);
        font-size: 0.68rem;
    }

    .audit-details-panel {
        margin-top: 0.6rem;
        padding: 0.75rem;
        border: 1px solid var(--color-linea);
        border-radius: 4px;
        background-color: #FCFBF8;
    }

    .audit-detail-block + .audit-detail-block {
        margin-top: 0.85rem;
        padding-top: 0.85rem;
        border-top: 1px dashed var(--color-linea);
    }

    .audit-detail-block h4 {
        margin: 0 0 0.45rem;
        color: var(--color-piedra);
        font-size: 0.7rem;
        letter-spacing: 0.05em;
        text-transform: uppercase;
    }

    .audit-change-wrap {
        overflow-x: auto;
    }

    .audit-change-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.78rem;
    }

    .audit-change-table th,
    .audit-change-table td {
        padding: 0.35rem 0.5rem;
        border-bottom: 1px solid var(--color-linea);
        text-align: left;
    }

    .audit-change-table th {
        color: var(--color-piedra);
        font-size: 0.68rem;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    .audit-change-table td:first-child {
        color: var(--color-noche);
        font-weight: 600;
        white-space: nowrap;
    }

    .audit-properties {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 0.45rem 0.9rem;
        margin: 0;
    }

    .audit-property {
        display: flex;
        flex-direction: column;
        gap: 0.1rem;
    }

    .audit-property dt {
        color: var(--color-piedra);
        font-size: 0.68rem;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    .audit-property dd {
        margin: 0;
        color: var(--color-noche);
        font-size: 0.8rem;
        word-break: break-word;
    }

    .audit-empty {
        padding: 3rem 1rem;
        text-align: center;
    }

    .audit-empty-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 46px;
        height: 46px;
        margin: 0 auto 0.75rem;
        border-radius: 50%;
        background-color: rgba(184, 154, 94, 0.13);
        color: var(--color-dorado);
    }

    .audit-empty strong {
        display: block;
        color: var(--color-noche);
    }

    .audit-empty span:not(.audit-empty-icon) {
        display: block;
        margin-top: 0.2rem;
        color: var(--color-piedra);
        font-size: 0.8rem;
    }

    .audit-empty .btn {
        margin-top: 0.85rem;
    }

    .audit-pagination {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        flex-shrink: 0;
        gap: 1rem;
        padding: 0.8rem 1.25rem;
        border-top: 1px solid var(--color-linea);
    }

    .audit-pagination-info {
        color: var(--color-piedra);
        font-size: 0.8rem;
    }

    @media (max-width: 1200px) {
        .audit-table {
            display: block;
            min-width: 0;
        }

        .audit-table thead {
            display: none;
        }

        .audit-table tbody {
            display: block;
            padding: 0.85rem;
        }

        .audit-table .audit-row {
            display: grid;
            grid-template-areas:
                "when module"
                "causer entity"
                "action action";
            grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
            gap: 0.75rem 1rem;
            padding: 0.9rem;
            border: 1px solid var(--color-linea);
            border-radius: 6px;
            background-color: var(--color-blanco);
        }

        .audit-table .audit-row + .audit-row {
            margin-top: 0.75rem;
        }

        .audit-table .audit-row td {
            padding: 0;
            border-bottom: 0;
        }

        .audit-table .audit-row td::before {
            content: attr(data-label);
            display: block;
            margin-bottom: 0.25rem;
            color: var(--color-piedra);
            font-size: 0.64rem;
            font-weight: 600;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        .audit-table .audit-row td:nth-child(1) { grid-area: when; }
        .audit-table .audit-row td:nth-child(2) { grid-area: module; }
        .audit-table .audit-row td:nth-child(3) { grid-area: causer; }
        .audit-table .audit-row td:nth-child(4) { grid-area: action; }
        .audit-table .audit-row td:nth-child(5) { grid-area: entity; }

        .audit-table .audit-empty {
            display: block;
            padding: 1.5rem 1rem;
        }
    }

    @media (max-width: 1100px) {
        .audit-stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 720px) {
        .audit-filter-field--search {
            flex-basis: 100%;
        }

        .audit-filter-actions {
            width: 100%;
        }

        .audit-filter-actions .btn {
            flex: 1;
        }
    }

    @media (max-width: 560px) {
        .audit-stats {
            grid-template-columns: 1fr;
        }

        .audit-table .audit-row {
            grid-template-areas: none;
            grid-template-columns: 1fr;
        }

        .audit-table .audit-row td {
            grid-area: auto !important;
        }

        .audit-table-header {
            flex-direction: column;
        }

        .audit-pagination {
            flex-direction: column;
            align-items: flex-start;
        }
    }
</style>

<div class="paginated-page audit-page">
    <div class="audit-stats">
        <div class="audit-stat">
            <span class="audit-stat-icon" aria-hidden="true">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <path d="M8 13h8M8 17h6"></path>
                </svg>
            </span>
            <div class="audit-stat-body">
                <span class="audit-stat-label">Eventos</span>
                <strong class="audit-stat-value">{{ number_format($summary['total']) }}</strong>
                <span class="audit-stat-hint">Coinciden con los filtros</span>
            </div>
        </div>

        <div class="audit-stat">
            <span class="audit-stat-icon" aria-hidden="true">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="9"></circle>
                    <path d="M12 7v5l3 2"></path>
                </svg>
            </span>
            <div class="audit-stat-body">
                <span class="audit-stat-label">Hoy</span>
                <strong class="audit-stat-value">{{ number_format($summary['today']) }}</strong>
                <span class="audit-stat-hint">Actividad registrada hoy</span>
            </div>
        </div>

        <div class="audit-stat">
            <span class="audit-stat-icon" aria-hidden="true">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
            </span>
            <div class="audit-stat-body">
                <span class="audit-stat-label">Responsables</span>
                <strong class="audit-stat-value">{{ number_format($summary['users']) }}</strong>
                <span class="audit-stat-hint">Usuarios con actividad</span>
            </div>
        </div>

        <div class="audit-stat">
            <span class="audit-stat-icon" aria-hidden="true">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="7" height="7" rx="1"></rect>
                    <rect x="14" y="3" width="7" height="7" rx="1"></rect>
                    <rect x="3" y="14" width="7" height="7" rx="1"></rect>
                    <rect x="14" y="14" width="7" height="7" rx="1"></rect>
                </svg>
            </span>
            <div class="audit-stat-body">
                <span class="audit-stat-label">Módulos</span>
                <strong class="audit-stat-value">{{ number_format($summary['modules']) }}</strong>
                <span class="audit-stat-hint">Áreas con registros</span>
            </div>
        </div>
    </div>

    <div class="card audit-filter-card">
        <form action="{{ route('admin.audit.index') }}" method="GET" class="audit-filter-form">
            <div class="audit-filter-field audit-filter-field--search">
                <label for="audit-search">Buscar</label>
                <div class="audit-search">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="11" cy="11" r="7"></circle>
                        <path d="M21 21l-4.3-4.3"></path>
                    </svg>
                    <input
                        type="search"
                        id="audit-search"
                        name="search"
                        value="{{ request('search') }}"
                        class="form-control"
                        placeholder="Descripción, módulo o evento..."
                        autocomplete="off"
                    >
                </div>
            </div>

            <div class="audit-filter-field">
                <label for="audit-module">Módulo</label>
                <select name="log_name" id="audit-module" class="form-select">
                    <option value="">Todos</option>
                    @foreach ($logNames as $name)
                        <option value="{{ $name }}" @selected(request('log_name') === $name)>
                            {{ $moduleLabels[$name] ?? ucfirst($name) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="audit-filter-field">
                <label for="audit-causer">Responsable</label>
                <select name="causer_id" id="audit-causer" class="form-select">
                    <option value="">Todos</option>
                    @foreach ($causers as $causer)
                        <option value="{{ $causer->id }}" @selected((string) request('causer_id') === (string) $causer->id)>
                            {{ $causer->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            @if ($events->isNotEmpty())
                <div class="audit-filter-field">
                    <label for="audit-event">Evento</label>
                    <select name="event" id="audit-event" class="form-select">
                        <option value="">Todos</option>
                        @foreach ($events as $event)
                            <option value="{{ $event }}" @selected(request('event') === $event)>
                                {{ $eventLabels[$event] ?? ucfirst(str_replace('_', ' ', $event)) }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="audit-filter-field">
                <label for="audit-from">Desde</label>
                <input type="date" id="audit-from" name="from" value="{{ request('from') }}" class="form-control">
            </div>

            <div class="audit-filter-field">
                <label for="audit-to">Hasta</label>
                <input type="date" id="audit-to" name="to" value="{{ request('to') }}" class="form-control">
            </div>

            <div class="audit-filter-actions">
                <button type="submit" class="btn btn-dark">Filtrar</button>
                @if ($hasFilters)
                    <a href="{{ route('admin.audit.index') }}" class="btn btn-outline">Limpiar</a>
                @endif
            </div>
        </form>
    </div>

    <div class="card audit-table-card">
        <div class="audit-table-header">
            <div>
                <div class="audit-table-title">
                    <h2>Bitácora de Auditoría</h2>
                    <span class="audit-count">{{ number_format($activities->total()) }} eventos</span>
                    <span class="audit-readonly">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect x="3" y="11" width="18" height="11" rx="2"></rect>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                        </svg>
                        Solo lectura
                    </span>
                </div>
            </div>
            @if ($hasFilters)
                <span class="audit-filter-chip">Filtros activos</span>
            @endif
        </div>

        <div class="audit-table-wrap">
            <table class="data-table audit-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Módulo</th>
                        <th>Responsable</th>
                        <th>Acción</th>
                        <th>Entidad</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($activities as $activity)
                        @php
                            $moduleTone = match ($activity->log_name) {
                                'ventas', 'precios' => 'is-gold',
                                'compras', 'apartados' => 'is-blue',
                                'inventario' => 'is-green',
                                'caja' => 'is-dark',
                                'autorizacion' => 'is-dark',
                                'descuentos' => 'is-red',
                                default => 'is-stone',
                            };
                            $subjectType = $activity->subject_type ? class_basename($activity->subject_type) : null;
                            $subjectLabel = $subjectType ? ($subjectLabels[$subjectType] ?? $subjectType) : null;
                            $properties = $activity->properties?->toArray() ?? [];
                            $changes = $activity->attribute_changes?->toArray() ?? [];
                            $changeAttributes = is_array($changes['attributes'] ?? null) ? $changes['attributes'] : [];
                            $changeOld = is_array($changes['old'] ?? null) ? $changes['old'] : [];
                            $changeKeys = array_values(array_unique(array_merge(array_keys($changeAttributes), array_keys($changeOld))));
                            $changeTitle = $changeAttributes !== [] && $changeOld === []
                                ? 'Datos registrados'
                                : ($changeAttributes === [] && $changeOld !== [] ? 'Datos anteriores' : 'Cambios');
                            $hasDetails = $properties !== [] || $changeKeys !== [];
                        @endphp
                        <tr class="audit-row">
                            <td class="audit-when" data-label="Fecha">
                                <span class="audit-date">{{ $activity->created_at?->format('d/m/Y') ?? '—' }}</span>
                                <span class="audit-time">{{ $activity->created_at?->format('H:i:s') ?? '—' }}</span>
                            </td>

                            <td data-label="Módulo">
                                <span class="audit-module {{ $moduleTone }}">
                                    {{ $activity->log_name ? ($moduleLabels[$activity->log_name] ?? $activity->log_name) : 'Sistema' }}
                                </span>
                                @if ($activity->event)
                                    <span class="audit-event">{{ $eventLabels[$activity->event] ?? ucfirst(str_replace('_', ' ', $activity->event)) }}</span>
                                @endif
                            </td>

                            <td data-label="Responsable">
                                @if ($activity->causer)
                                    <div class="audit-causer">
                                        <span class="audit-avatar">{{ strtoupper(mb_substr($activity->causer->name, 0, 1)) }}</span>
                                        <span class="audit-causer-name">{{ $activity->causer->name }}</span>
                                    </div>
                                @else
                                    <span class="audit-system">Sistema automático</span>
                                @endif
                            </td>

                            <td data-label="Acción">
                                <span class="audit-description">{{ $activity->description }}</span>

                                @if ($hasDetails)
                                    <details class="audit-details">
                                        <summary>
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <path d="M9 18l6-6-6-6"></path>
                                            </svg>
                                            <span>Ver detalles</span>
                                            <span class="audit-details-count">{{ count($properties) + count($changeKeys) }}</span>
                                        </summary>

                                        <div class="audit-details-panel">
                                            @if ($changeKeys !== [])
                                                <div class="audit-detail-block">
                                                    <h4>{{ $changeTitle }}</h4>
                                                    <div class="audit-change-wrap">
                                                        <table class="audit-change-table">
                                                            <thead>
                                                                <tr>
                                                                    <th>Campo</th>
                                                                    <th>Antes</th>
                                                                    <th>Después</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach ($changeKeys as $key)
                                                                    <tr>
                                                                        <td>{{ $propertyLabels[$key] ?? $key }}</td>
                                                                        <td>{{ $formatValue($changeOld[$key] ?? null) }}</td>
                                                                        <td>{{ $formatValue($changeAttributes[$key] ?? null) }}</td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            @endif

                                            @if ($properties !== [])
                                                <div class="audit-detail-block">
                                                    <h4>Propiedades</h4>
                                                    <dl class="audit-properties">
                                                        @foreach ($properties as $key => $value)
                                                            <div class="audit-property">
                                                                <dt>{{ $propertyLabels[$key] ?? $key }}</dt>
                                                                <dd>{{ $formatValue($value) }}</dd>
                                                            </div>
                                                        @endforeach
                                                    </dl>
                                                </div>
                                            @endif
                                        </div>
                                    </details>
                                @endif
                            </td>

                            <td data-label="Entidad">
                                @if ($subjectLabel)
                                    <span class="audit-subject">
                                        {{ $subjectLabel }}
                                        <span>#{{ $activity->subject_id }}</span>
                                    </span>
                                @else
                                    <span class="audit-muted">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="audit-empty">
                                <span class="audit-empty-icon" aria-hidden="true">
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                        <polyline points="14 2 14 8 20 8"></polyline>
                                        <path d="M9 15h6"></path>
                                    </svg>
                                </span>
                                <strong>No hay registros de auditoría</strong>
                                <span>Prueba con otro rango de fechas o limpia los filtros.</span>
                                @if ($hasFilters)
                                    <a href="{{ route('admin.audit.index') }}" class="btn btn-outline btn-sm">Limpiar filtros</a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($activities->hasPages())
            <div class="audit-pagination">
                <span class="audit-pagination-info">
                    Mostrando {{ $activities->firstItem() }}–{{ $activities->lastItem() }} de {{ number_format($activities->total()) }}
                </span>
                {{ $activities->links('vendor.pagination.admin') }}
            </div>
        @endif
    </div>
</div>
@endsection
