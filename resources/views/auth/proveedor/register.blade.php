@php($portal = 'proveedor')

@extends('layouts.app')

@section('title', 'PETPAY-CARD | Registro Proveedor')

@section('content')
    <section class="petpay-auth-wrap">
        <div class="petpay-auth-card petpay-auth-card-wide">
            <div class="petpay-auth-icon">🏪</div>

            <h1 class="petpay-auth-title">Registra tu negocio</h1>
            <p class="petpay-auth-text">
                Da de alta tu tienda, veterinaria, estética o negocio afiliado para vender en PETPAY-CARD.
            </p>

            <form method="POST" action="{{ route('proveedor.register.store') }}">
                @csrf
                <div class="petpay-form-grid">
                    <div class="petpay-form-group">
                        <label class="petpay-label">Nombre del negocio</label>
                        <input class="petpay-field" type="text" placeholder="Nombre comercial">
                    </div>

                    <div class="petpay-form-group">
                        <label class="petpay-label">Tipo de negocio</label>
                        <input class="petpay-field" type="text" placeholder="Tienda, veterinaria, estética...">
                    </div>
                </div>

                <div class="petpay-form-grid">
                    <div class="petpay-form-group">
                        <label class="petpay-label">Correo</label>
                        <input class="petpay-field" type="email" placeholder="negocio@email.com">
                    </div>

                    <div class="petpay-form-group">
                        <label class="petpay-label">Teléfono</label>
                        <input class="petpay-field" type="text" placeholder="55 0000 0000">
                    </div>
                </div>

                <div class="petpay-form-group">
                    <label class="petpay-label">Dirección del negocio</label>
                    <input class="petpay-field" type="text" placeholder="Dirección del local">
                </div>

                <div class="petpay-form-grid">
                    <div class="petpay-form-group">
                        <label class="petpay-label">Contraseña</label>
                        <input class="petpay-field" type="password" placeholder="••••••••">
                    </div>

                    <div class="petpay-form-group">
                        <label class="petpay-label">Confirmar contraseña</label>
                        <input class="petpay-field" type="password" placeholder="••••••••">
                    </div>
                </div>

                <button type="submit" class="petpay-btn petpay-btn-black" style="width:100%;">
                    Registrar negocio
                </button>

                <p class="petpay-auth-note">
                    ¿Ya tienes cuenta? <a href="{{ route('proveedor.login') }}">Inicia sesión</a>
                </p>
            </form>
        </div>
    </section>
@endsection