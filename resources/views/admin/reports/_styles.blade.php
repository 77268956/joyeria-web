<style>
    .reports-page {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .reports-page .card {
        margin-bottom: 0;
    }

    .reports-hero {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .reports-hero .page-title {
        margin: 0;
    }

    .reports-eyebrow {
        display: inline-block;
        font-size: 0.68rem;
        font-weight: 600;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: var(--color-dorado);
        margin-bottom: 0.25rem;
    }

    .reports-subtitle {
        margin: 0.25rem 0 0;
        font-size: 0.82rem;
        color: var(--color-piedra);
    }

    .reports-back {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        margin-bottom: 0.35rem;
        color: var(--color-piedra);
        font-size: 0.78rem;
        text-decoration: none;
    }

    .reports-back:hover {
        color: var(--color-noche);
    }

    .reports-filter-card {
        padding: 0.85rem 1.1rem;
    }

    .reports-filter {
        display: flex;
        align-items: flex-end;
        gap: 0.6rem;
        flex-wrap: wrap;
        margin: 0;
    }

    .reports-filter .form-group {
        margin: 0;
        min-width: 150px;
    }

    .reports-range-links {
        display: flex;
        align-items: center;
        gap: 0.35rem;
        flex-wrap: wrap;
    }

    .reports-range-links a {
        display: inline-flex;
        align-items: center;
        padding: 0.36rem 0.65rem;
        border: 1px solid var(--color-linea);
        border-radius: 999px;
        background: var(--color-blanco);
        color: var(--color-piedra);
        font-size: 0.72rem;
        text-decoration: none;
        transition: border-color 0.2s, background-color 0.2s, color 0.2s;
    }

    .reports-range-links a:hover,
    .reports-range-links a.active {
        border-color: var(--color-dorado);
        background: var(--color-dorado-claro);
        color: var(--color-noche);
    }

    .reports-metrics {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
        gap: 0.75rem;
    }

    .report-metric {
        display: flex;
        align-items: flex-start;
        gap: 0.7rem;
        min-width: 0;
        padding: 0.8rem 0.9rem;
        background: var(--color-blanco);
        border: 1px solid var(--color-linea);
        border-radius: 6px;
    }

    .report-metric-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        border-radius: 50%;
        flex-shrink: 0;
        background: rgba(184, 154, 94, 0.14);
        color: var(--color-dorado);
    }

    .report-metric-icon.tone-info {
        background: rgba(63, 95, 122, 0.12);
        color: var(--color-info);
    }

    .report-metric-icon.tone-success {
        background: rgba(62, 124, 89, 0.12);
        color: var(--color-exito);
    }

    .report-metric-icon.tone-night {
        background: rgba(18, 22, 31, 0.08);
        color: var(--color-noche);
    }

    .report-metric-icon.tone-warning {
        background: rgba(183, 121, 31, 0.12);
        color: var(--color-aviso);
    }

    .report-metric-icon.tone-danger {
        background: rgba(166, 61, 64, 0.12);
        color: var(--color-error);
    }

    .report-metric-body {
        min-width: 0;
    }

    .report-metric-label {
        display: block;
        font-size: 0.64rem;
        font-weight: 600;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: var(--color-piedra);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .report-metric-value {
        display: block;
        margin-top: 0.18rem;
        font-family: var(--font-serif);
        font-size: 1.35rem;
        line-height: 1.1;
        color: var(--color-noche);
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .report-metric-hint {
        display: block;
        margin-top: 0.22rem;
        font-size: 0.68rem;
        color: var(--color-piedra);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .reports-chart-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
    }

    .report-chart-card {
        min-width: 0;
        padding: 0.85rem 1rem;
        background: var(--color-blanco);
        border: 1px solid var(--color-linea);
        border-radius: 6px;
    }

    .report-chart-head {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        gap: 0.5rem;
        margin-bottom: 0.5rem;
    }

    .report-chart-title {
        font-family: var(--font-serif);
        font-size: 1rem;
        font-weight: 500;
        color: var(--color-noche);
    }

    .report-chart-hint {
        min-width: 0;
        font-size: 0.68rem;
        color: var(--color-piedra);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .report-chart-wrap {
        position: relative;
        height: 230px;
    }

    .report-chart-empty {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--color-piedra);
        font-size: 0.8rem;
        pointer-events: none;
    }

    .reports-two-col {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
    }

    .report-panel {
        padding: 0.9rem 1rem;
    }

    .report-section-head {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        gap: 0.75rem;
        padding-bottom: 0.55rem;
        margin-bottom: 0.55rem;
        border-bottom: 1px solid var(--color-linea);
    }

    .report-section-head h2 {
        font-family: var(--font-serif);
        font-size: 1.05rem;
        font-weight: 500;
        color: var(--color-noche);
    }

    .report-section-head span {
        font-size: 0.7rem;
        color: var(--color-piedra);
        white-space: nowrap;
    }

    .report-list {
        display: flex;
        flex-direction: column;
    }

    .report-list-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 0.48rem 0;
        border-bottom: 1px solid var(--color-linea);
        font-size: 0.82rem;
    }

    .report-list-row:last-child {
        border-bottom: none;
    }

    .report-list-row > div {
        min-width: 0;
    }

    .report-list-row strong {
        display: block;
        color: var(--color-noche);
        font-weight: 500;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .report-list-row span {
        display: block;
        margin-top: 0.1rem;
        font-size: 0.7rem;
        color: var(--color-piedra);
    }

    .report-list-row .report-list-amount {
        flex-shrink: 0;
        font-weight: 600;
        font-variant-numeric: tabular-nums;
    }

    .report-catalogue {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
        gap: 0.75rem;
    }

    .report-link-card {
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
        min-width: 0;
        padding: 0.85rem 0.95rem;
        background: var(--color-blanco);
        border: 1px solid var(--color-linea);
        border-radius: 6px;
        color: inherit;
        text-decoration: none;
        transition: border-color 0.2s, transform 0.2s, box-shadow 0.2s;
    }

    .report-link-card:hover {
        border-color: var(--color-dorado);
        transform: translateY(-1px);
        box-shadow: 0 8px 18px rgba(18, 22, 31, 0.06);
    }

    .report-link-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
    }

    .report-link-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background: rgba(184, 154, 94, 0.13);
        color: var(--color-dorado);
    }

    .report-link-card h3 {
        margin-top: 0.1rem;
        font-family: var(--font-serif);
        font-size: 1rem;
        font-weight: 500;
        color: var(--color-noche);
    }

    .report-link-card p {
        font-size: 0.75rem;
        line-height: 1.45;
        color: var(--color-piedra);
    }

    .report-link-cta {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        margin-top: 0.2rem;
        font-size: 0.7rem;
        font-weight: 600;
        color: var(--color-dorado);
    }

    .report-totals-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 0.6rem;
    }

    .report-total-card {
        min-width: 0;
        padding: 0.7rem 0.8rem;
        background: var(--color-blanco);
        border: 1px solid var(--color-linea);
        border-radius: 6px;
    }

    .report-total-card span {
        display: block;
        font-size: 0.64rem;
        font-weight: 600;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: var(--color-piedra);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .report-total-card strong {
        display: block;
        margin-top: 0.2rem;
        font-family: var(--font-serif);
        font-size: 1.2rem;
        line-height: 1.1;
        color: var(--color-noche);
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .report-table-card {
        padding: 0;
        overflow: hidden;
    }

    .report-table-head {
        padding: 0.85rem 1rem;
        border-bottom: 1px solid var(--color-linea);
    }

    .report-table-head .report-section-head {
        margin: 0;
        padding: 0;
        border: 0;
    }

    .report-table-wrap {
        max-height: 460px;
        overflow: auto;
    }

    .report-table-wrap table.data-table th {
        position: sticky;
        top: 0;
        z-index: 2;
        background: var(--color-crema);
        box-shadow: inset 0 -1px 0 var(--color-linea);
        white-space: nowrap;
    }

    .report-table-wrap table.data-table td {
        white-space: nowrap;
    }

    .reports-empty {
        padding: 1.25rem 1rem;
        text-align: center;
        font-size: 0.8rem;
        color: var(--color-piedra);
    }

    @media (max-width: 1200px) {
        .reports-chart-grid,
        .reports-two-col {
            grid-template-columns: minmax(0, 1fr);
        }
    }

    @media (max-width: 1024px) {
        .reports-hero {
            align-items: flex-start;
        }

        .report-chart-wrap {
            height: 220px;
        }
    }

    @media (max-width: 640px) {
        .reports-filter,
        .reports-filter .form-group {
            width: 100%;
        }

        .reports-filter .form-group {
            min-width: 0;
        }

        .reports-filter .btn {
            width: 100%;
            justify-content: center;
        }

        .report-chart-head {
            align-items: flex-start;
            flex-direction: column;
            gap: 0.15rem;
        }

        .report-chart-wrap {
            height: 200px;
        }

        .report-metric-value {
            font-size: 1.2rem;
        }
    }
</style>
