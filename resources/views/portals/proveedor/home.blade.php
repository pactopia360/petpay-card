@php($portal = 'proveedor')

@extends('layouts.app')

@section('title', 'PETPAY-CARD | Proveedor / POS')

@section('content')
    <section class="petpay-dashboard">
        @include('partials.sidebars.proveedor')

        <div class="petpay-content-panel">
            <h1>Portal Proveedor</h1>
            <p>
                Administra productos, servicios, inventario, tickets de compra,
                sustituciones, ventas, liquidaciones, horarios y cobertura.
            </p>

            <div class="petpay-stat-grid">
                <div class="petpay-stat">
                    <strong>0</strong>
                    <span>Tickets pendientes</span>
                </div>

                <div class="petpay-stat">
                    <strong>0</strong>
                    <span>Productos activos</span>
                </div>

                <div class="petpay-stat">
                    <strong>$0</strong>
                    <span>Ventas del día</span>
                </div>
            </div>
        </div>
    </section>
@endsection