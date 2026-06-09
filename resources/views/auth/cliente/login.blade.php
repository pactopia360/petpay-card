@php($portal = 'cliente')

@extends('layouts.app')

@section('title', 'PETPAY-CARD | Login Cliente')

@section('content')
    <section class="petpay-auth-wrap">
        <div class="petpay-auth-card">
            <div class="petpay-auth-icon">🛒</div>

            <h1 class="petpay-auth-title">Hola de nuevo</h1>
            <p class="petpay-auth-text">
                Ingresa para comprar productos y servicios para tu mascota cerca de ti.
            </p>

            <form method="POST" action="{{ route('cliente.login.store') }}">
                @csrf
                <div class="petpay-form-group">
                    <label class="petpay-label">Correo electrónico</label>
                    <input class="petpay-field" type="email" placeholder="tu@email.com">
                </div>

                <div class="petpay-form-group">
                    <label class="petpay-label">Contraseña</label>
                    <input class="petpay-field" type="password" placeholder="••••••••">
                </div>

                <button type="submit" class="petpay-btn petpay-btn-black" style="width:100%;">
                    Entrar
                </button>

                <div class="petpay-auth-actions">
                    <a class="petpay-auth-link" href="{{ route('cliente.password.request') }}">
                        Recuperar contraseña
                    </a>
                    <a class="petpay-auth-link" href="{{ route('cliente.register') }}">
                        Crear cuenta
                    </a>
                </div>
            </form>
        </div>
    </section>
@endsection