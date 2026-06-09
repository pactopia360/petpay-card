@php($portal = 'repartidor')

@extends('layouts.app')

@section('title', 'PETPAY-CARD | Repartidor')

@section('content')
    <section class="petpay-dashboard">
        @include('partials.sidebars.repartidor')

        <div class="petpay-content-panel">
            <h1>Portal Repartidor</h1>
            <p>
                Activa disponibilidad, recibe tickets de entrega, inicia rutas de
                recolección, confirma códigos, entrega pedidos y consulta ingresos.
            </p>

            <div class="petpay-mobile-ticket">
                <div class="petpay-toggle">
                    <span class="on">ON</span>
                    <span>OFF</span>
                </div>

                <h2>MXN 58.32</h2>
                <p>
                    Recolección: 10 min / 1.5 Km<br>
                    Entrega: 15 min / 2.3 Km<br>
                    Distancia total: 3.8 Km<br>
                    Tiempo total: 25 min
                </p>

                <a href="#" class="petpay-btn petpay-btn-orange">TAKE</a>
            </div>
        </div>
    </section>
@endsection