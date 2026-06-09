@php($portal = 'proveedor')

@extends('layouts.app')

@section('title', 'PETPAY-CARD | Login Proveedor')

@section('content')
    <section class="petpay-auth-wrap">
        <div class="petpay-auth-card">
            <div class="petpay-auth-icon">🏪</div>

            <h1 class="petpay-auth-title">Acceso proveedor</h1>
            <p class="petpay-auth-text">
                Ingresa para administrar tu tienda, productos, tickets, ventas y cobertura.
            </p>

            <form method="POST" action="{{ route('proveedor.login.store') }}">
                @csrf
                <div class="petpay-form-group">
                    <label class="petpay-label">Correo del negocio</label>
                    <input class="petpay-field" type="email" placeholder="negocio@email.com">
                </div>

                <div class="petpay-form-group">
                    <label class="petpay-label">Contraseña</label>
                    <input class="petpay-field" type="password" placeholder="••••••••">
                </div>

                <button type="submit" class="petpay-btn petpay-btn-black" style="width:100%;">
                    Entrar como proveedor
                </button>

                <div class="petpay-auth-actions">
                    <a class="petpay-auth-link" href="{{ route('proveedor.password.request') }}">
                        Recuperar contraseña
                    </a>
                    <a class="petpay-auth-link" href="{{ route('proveedor.register') }}">
                        Registrar negocio
                    </a>
                </div>
            </form>
        </div>
    </section>
@endsection