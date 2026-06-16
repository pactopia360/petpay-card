@extends('layouts.portal')

@php($portal = 'comercio')

@section('title', 'PETPAY-CARD | Dashboard Comercio')

@section('content')
    <section class="petpay-dashboard">
        <div class="petpay-dashboard__hero">
            <p class="petpay-eyebrow">Portal Comercio</p>
            <h1>Bienvenido a tu comercio</h1>
            <p>
                Desde aquí podrás administrar productos, servicios, pedidos, promociones
                y la operación de ventas dentro de Petpay.
            </p>
        </div>

        <div class="petpay-dashboard__grid">
            <article class="petpay-dashboard-card">
                <span class="petpay-dashboard-card__icon">🛍️</span>
                <h2>Productos</h2>
                <p>Administra el catálogo que venderás en la plataforma.</p>
            </article>

            <article class="petpay-dashboard-card">
                <span class="petpay-dashboard-card__icon">🐾</span>
                <h2>Servicios</h2>
                <p>Publica servicios para mascotas y controla disponibilidad.</p>
            </article>

            <article class="petpay-dashboard-card">
                <span class="petpay-dashboard-card__icon">📦</span>
                <h2>Pedidos</h2>
                <p>Consulta pedidos recibidos, preparación y entregas.</p>
            </article>

            <article class="petpay-dashboard-card">
                <span class="petpay-dashboard-card__icon">📊</span>
                <h2>Ventas</h2>
                <p>Revisa tus métricas, comisiones y rendimiento.</p>
            </article>
        </div>
    </section>
@endsection