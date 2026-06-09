@php($portal = 'cliente')

@extends('layouts.app')

@section('title', 'PETPAY-CARD | Cliente')

@section('content')
    <section class="petpay-dashboard">
        @include('partials.sidebars.cliente')

        <div class="petpay-content-panel">
            <h1>Portal Cliente</h1>
            <p>
                Busca productos, servicios, tiendas, veterinarias y promociones
                disponibles cerca de tu dirección de entrega.
            </p>

            <div class="petpay-search-card">
                <input class="petpay-field" type="text" placeholder="📍 Dirección de entrega">
                <input class="petpay-field" type="text" placeholder="🐾 ¿Qué necesita tu mascota?">
                <a href="#" class="petpay-btn petpay-btn-black">
                    Buscar productos
                </a>
            </div>

            <div class="petpay-stat-grid">
                <div class="petpay-stat">
                    <strong>0</strong>
                    <span>Pedidos</span>
                </div>

                <div class="petpay-stat">
                    <strong>0</strong>
                    <span>PawPoints</span>
                </div>

                <div class="petpay-stat">
                    <strong>0</strong>
                    <span>Mascotas</span>
                </div>
            </div>
        </div>
    </section>
@endsection