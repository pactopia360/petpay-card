@php
    $portal = 'cliente';
    $cliente = auth('cliente')->user();
    $nombreUsuario = $cliente?->first_name ?: 'Usuario';
    $direccionEntrega = $cliente?->main_address ?: 'Agrega tu dirección de entrega';
    $pawpoints = $cliente?->pawpoints_balance ?? 0;
@endphp

@extends('layouts.app')

@section('title', 'PETPAY-CARD | Usuario')

@section('content')
    <section class="petpay-client-market">
        @include('partials.sidebars.cliente')

        <main class="petpay-client-market__main">
            <section class="petpay-client-market__hero-head petpay-client-market__hero-head--clean">
                <div class="petpay-client-market__hello">
                    <span class="petpay-client-market__paw">🐾</span>

                    <div>
                        <p class="petpay-client-market__eyebrow">Portal Usuario</p>
                        <h1>Hola, {{ $nombreUsuario }}</h1>
                        <p>Todo lo que tu mascota necesita, en un solo lugar.</p>
                    </div>
                </div>
            </section>

            <section class="petpay-client-market__layout">
                <div class="petpay-client-market__content">
                    <section class="petpay-client-market__search">
                        <div class="petpay-client-market__search-box">
                            <label for="delivery_address">📍 Dirección de entrega</label>

                            <select id="delivery_address">
                                <option>{{ $direccionEntrega }}</option>
                            </select>

                            <small>Entrega hoy disponible</small>
                        </div>

                        <div class="petpay-client-market__search-box petpay-client-market__search-box--grow">
                            <label for="market_search">🔎 ¿Qué necesitas para tu mascota?</label>

                            <div class="petpay-client-market__search-inline">
                                <input
                                    id="market_search"
                                    type="text"
                                    placeholder="Buscar alimento, juguetes, servicios, tiendas..."
                                >

                                <button type="button">
                                    Buscar
                                </button>
                            </div>
                        </div>
                    </section>

                    <section class="petpay-client-market__categories" aria-label="Categorías principales">
                        <a href="#" class="petpay-client-market__category">
                            <span>🥣</span>
                            <strong>Alimento</strong>
                        </a>

                        <a href="#" class="petpay-client-market__category">
                            <span>🧸</span>
                            <strong>Juguetes</strong>
                        </a>

                        <a href="#" class="petpay-client-market__category">
                            <span>💊</span>
                            <strong>Farmacia</strong>
                        </a>

                        <a href="#" class="petpay-client-market__category">
                            <span>🩺</span>
                            <strong>Veterinaria</strong>
                        </a>

                        <a href="#" class="petpay-client-market__category">
                            <span>🧴</span>
                            <strong>Estética</strong>
                        </a>

                        <a href="#" class="petpay-client-market__category">
                            <span>🏠</span>
                            <strong>Hotel</strong>
                        </a>
                    </section>

                    <section class="petpay-client-market__banner">
                        <button type="button" class="petpay-client-market__banner-arrow" aria-label="Anterior">
                            ‹
                        </button>

                        <div class="petpay-client-market__banner-copy">
                            <span>🐾 PETPAY</span>

                            <h2>
                                Todo para su felicidad,
                                <strong>cerca de ti</strong>
                            </h2>

                            <p>
                                Productos, servicios y tiendas de confianza para el bienestar
                                de tu mejor amigo.
                            </p>

                            <a href="#">
                                Descubre tiendas cercanas
                            </a>
                        </div>

                        <div class="petpay-client-market__banner-pets" aria-hidden="true">
                            <div class="petpay-client-market__pet petpay-client-market__pet--dog">🐶</div>
                            <div class="petpay-client-market__pet petpay-client-market__pet--cat">🐱</div>
                        </div>

                        <button type="button" class="petpay-client-market__banner-arrow" aria-label="Siguiente">
                            ›
                        </button>
                    </section>

                    <section class="petpay-client-market__section">
                        <div class="petpay-client-market__section-head">
                            <h2>Recomendado para ti</h2>
                            <a href="#">Ver más</a>
                        </div>

                        <div class="petpay-client-market__product-grid">
                            <article class="petpay-client-market__product">
                                <div class="petpay-client-market__product-img">🥘</div>

                                <h3>Croquetas Premium</h3>
                                <p>Adulto raza mediana 10 kg</p>

                                <div>
                                    <strong>$849.00</strong>
                                    <span>⭐ 4.8</span>
                                </div>

                                <button type="button">Agregar al carrito</button>
                            </article>

                            <article class="petpay-client-market__product">
                                <div class="petpay-client-market__product-img">🎾</div>

                                <h3>Juguete Pelota</h3>
                                <p>Interactiva con sonido</p>

                                <div>
                                    <strong>$129.00</strong>
                                    <span>⭐ 4.6</span>
                                </div>

                                <button type="button">Agregar al carrito</button>
                            </article>

                            <article class="petpay-client-market__product">
                                <div class="petpay-client-market__product-img">🧴</div>

                                <h3>Shampoo Hipoalergénico</h3>
                                <p>Avena & Aloe 500 ml</p>

                                <div>
                                    <strong>$189.00</strong>
                                    <span>⭐ 4.7</span>
                                </div>

                                <button type="button">Agregar al carrito</button>
                            </article>

                            <article class="petpay-client-market__product">
                                <div class="petpay-client-market__product-img">💊</div>

                                <h3>Antipulgas NexGard</h3>
                                <p>Perros 10.1 - 25 kg</p>

                                <div>
                                    <strong>$499.00</strong>
                                    <span>⭐ 4.9</span>
                                </div>

                                <button type="button">Agregar al carrito</button>
                            </article>
                        </div>
                    </section>

                    <section class="petpay-client-market__bottom-grid">
                        <article class="petpay-client-market__panel">
                            <div class="petpay-client-market__section-head">
                                <h2>Tiendas cercanas</h2>
                                <a href="#">Ver todas</a>
                            </div>

                            <div class="petpay-client-market__store">
                                <div class="petpay-client-market__store-img">🏪</div>

                                <div>
                                    <h3>Pet Center Del Valle</h3>
                                    <p>⭐ 4.9 · 1.2 km</p>
                                    <small>Entrega hoy</small>
                                </div>
                            </div>
                        </article>

                        <article class="petpay-client-market__panel">
                            <div class="petpay-client-market__section-head">
                                <h2>Compra de nuevo</h2>
                                <a href="#">Ver más</a>
                            </div>

                            <div class="petpay-client-market__mini-products">
                                <span>🥘</span>
                                <span>🥫</span>
                                <span>🦴</span>
                            </div>
                        </article>

                        <article class="petpay-client-market__panel petpay-client-market__promo">
                            <span>15% OFF</span>
                            <h2>En alimento seco</h2>
                            <p>Promoción para perros y gatos.</p>
                        </article>
                    </section>
                </div>

                <aside class="petpay-client-market__aside">
                    <article class="petpay-client-market__widget">
                        <div class="petpay-client-market__section-head">
                            <h2>Mis pedidos</h2>
                            <a href="#">Ver todos</a>
                        </div>

                        <div class="petpay-client-market__order">
                            <div>🛍️</div>

                            <div>
                                <strong>En camino</strong>
                                <p>Entrega estimada: Hoy 2:00 - 4:00 pm</p>
                                <small>Pedido #87945</small>
                            </div>

                            <span>›</span>
                        </div>

                        <small class="petpay-client-market__muted">
                            2 pedidos en proceso
                        </small>
                    </article>

                    <article class="petpay-client-market__widget petpay-client-market__points">
                        <div class="petpay-client-market__section-head">
                            <h2>PawPoints</h2>
                            <a href="#">Ver más</a>
                        </div>

                        <strong>{{ $pawpoints }}</strong>
                        <p>puntos disponibles</p>

                        <div class="petpay-client-market__progress">
                            <span style="width: 60%;"></span>
                        </div>

                        <small>Siguiente recompensa: 200 puntos</small>

                        <button type="button">
                            Canjear mis puntos
                        </button>
                    </article>

                    <article class="petpay-client-market__widget">
                        <div class="petpay-client-market__section-head">
                            <h2>Mis mascotas</h2>
                            <a href="#">Ver todas</a>
                        </div>

                        <div class="petpay-client-market__pet-row">
                            <span>🐶</span>

                            <div>
                                <strong>Max</strong>
                                <p>Golden Retriever</p>
                            </div>
                        </div>

                        <div class="petpay-client-market__pet-row">
                            <span>🐱</span>

                            <div>
                                <strong>Luna</strong>
                                <p>Gato · 2 años</p>
                            </div>
                        </div>

                        <button type="button" class="petpay-client-market__outline">
                            🐾 Agregar mascota
                        </button>
                    </article>
                </aside>
            </section>
        </main>
    </section>
@endsection