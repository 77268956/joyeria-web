@php
    $photo = $product->primaryPhoto;
    $metalName = $product->metal?->name;
    $purityLabel = $product->purity?->label;
@endphp

<a href="{{ route('public.product', $product) }}" class="product-card">
    <div class="product-media">
        @if ($photo)
            <img src="{{ $photo->url }}" alt="{{ $product->name }}" loading="lazy">
        @else
            <div class="product-media-placeholder">{{ mb_substr($product->name, 0, 1) }}</div>
        @endif

        @if ($product->is_featured)
            <span class="product-badge is-gold">Destacada</span>
        @elseif ($product->origin === \App\Enums\ProductOrigin::UsedPurchase)
            <span class="product-badge">Ocasión</span>
        @endif
    </div>

    <div class="product-body">
        <span class="product-category">{{ $product->category?->name }}</span>
        <span class="product-name font-serif">{{ $product->name }}</span>
        <div class="product-meta">
            <span>{{ $purityLabel ?? $metalName }} · {{ rtrim(rtrim(number_format((float) $product->weight_g, 3), '0'), '.') }} g</span>
            <span class="product-price">$ {{ number_format((float) $product->price, 2) }}</span>
        </div>
    </div>
</a>
