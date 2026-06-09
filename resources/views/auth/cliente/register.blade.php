@php($portal = 'cliente')

@extends('layouts.app')

@section('title', 'PETPAY-CARD | Registro Cliente')

@section('content')
    <section class="petpay-auth-wrap">
        <div class="petpay-auth-card petpay-auth-card-wide">
            <div class="petpay-auth-icon">🐾</div>

            <h1 class="petpay-auth-title">Crea tu cuenta</h1>
            <p class="petpay-auth-text">
                Regístrate para comprar, guardar direcciones, registrar mascotas y acumular PawPoints.
            </p>

            <form method="POST" action="{{ route('cliente.register.store') }}">
                 @csrf
                <div class="petpay-form-grid">
                    <div class="petpay-form-group">
                        <label class="petpay-label">Nombre</label>
                        <input class="petpay-field" type="text" placeholder="Nombre">
                    </div>

                    <div class="petpay-form-group">
                        <label class="petpay-label">Apellido</label>
                        <input class="petpay-field" type="text" placeholder="Apellido">
                    </div>
                </div>

                <div class="petpay-form-grid">
                    <div class="petpay-form-group">
                        <label class="petpay-label">Correo electrónico</label>
                        <input class="petpay-field" type="email" placeholder="tu@email.com">
                    </div>

                    <div class="petpay-form-group">
                        <label class="petpay-label">Teléfono</label>
                        <input class="petpay-field" type="text" placeholder="55 0000 0000">
                    </div>
                </div>

                <div class="petpay-form-group">
                    <label class="petpay-label">Dirección principal</label>
                    <input class="petpay-field" type="text" placeholder="Dirección de entrega">
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
                    Crear cuenta cliente
                </button>

                <p class="petpay-auth-note">
                    ¿Ya tienes cuenta? <a href="{{ route('cliente.login') }}">Inicia sesión</a>
                </p>
            </form>
        </div>
    </section>
@endsection