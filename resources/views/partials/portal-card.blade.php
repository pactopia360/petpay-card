<article class="petpay-portal-card">
    <div>
        <div class="petpay-portal-icon">{{ $icon ?? '🐾' }}</div>
        <h3>{{ $title }}</h3>
        <p>{{ $description }}</p>
    </div>

    <a href="{{ $url }}" class="petpay-btn petpay-btn-black">
        Entrar
    </a>
</article>