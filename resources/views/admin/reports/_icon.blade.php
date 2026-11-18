@php($iconSize = $size ?? 18)
<svg width="{{ $iconSize }}" height="{{ $iconSize }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    @switch($name)
        @case('sales')
        @case('ventas')
            <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
            <path d="M3 6h18"></path>
            <path d="M16 10a4 4 0 0 1-8 0"></path>
            @break

        @case('purchases')
        @case('compras')
            <path d="M12 3v18"></path>
            <path d="M17 7H9.5a3.5 3.5 0 0 0 0 7H14a3.5 3.5 0 0 1 0 7H6"></path>
            @break

        @case('ticket')
            <path d="M3 9a3 3 0 0 0 0 6v4h18v-4a3 3 0 0 0 0-6V5H3z"></path>
            <path d="M9 5v14"></path>
            @break

        @case('inventory')
        @case('inventario')
            <path d="M6 3h12l4 6-10 12L2 9z"></path>
            <path d="M2 9h20"></path>
            @break

        @case('layaways')
        @case('apartados')
            <circle cx="12" cy="12" r="9"></circle>
            <path d="M12 7v5l3 2"></path>
            @break

        @case('cash')
        @case('caja')
            <rect x="2" y="5" width="20" height="14" rx="2"></rect>
            <circle cx="12" cy="12" r="2.5"></circle>
            <path d="M6 9h.01M18 15h.01"></path>
            @break

        @default
            <path d="M3 3v18h18"></path>
            <rect x="7" y="12" width="3" height="5"></rect>
            <rect x="12" y="8" width="3" height="9"></rect>
            <rect x="17" y="5" width="3" height="12"></rect>
    @endswitch
</svg>
