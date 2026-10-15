@extends('layouts.admin')

@section('title', 'Reportes')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title font-serif">Reportes</h1>
        <p class="page-subtitle">Informes exportables de ventas, compras, inventario y caja.</p>
    </div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem;">
    @foreach ($reports as $slug => $report)
        <a href="{{ route('admin.reports.show', $slug) }}" class="card" style="display: flex; flex-direction: column; gap: 0.5rem; text-decoration: none; color: inherit;">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <span class="badge badge-gold">{{ $report['dated'] ? 'Por período' : 'Instantáneo' }}</span>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--color-dorado)" stroke-width="2">
                    <line x1="18" y1="20" x2="18" y2="10"></line>
                    <line x1="12" y1="20" x2="12" y2="4"></line>
                    <line x1="6" y1="20" x2="6" y2="14"></line>
                </svg>
            </div>
            <h2 class="font-serif" style="font-size: 1.25rem; margin: 0.25rem 0 0;">{{ $report['title'] }}</h2>
            <p class="muted" style="font-size: 0.88rem; line-height: 1.5; margin: 0;">{{ $report['description'] }}</p>
        </a>
    @endforeach
</div>
@endsection
