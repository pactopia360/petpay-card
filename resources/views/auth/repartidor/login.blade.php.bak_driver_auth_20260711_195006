@php($portal = 'repartidor')

@extends('layouts.app')

@section('title', 'PETPAY-CARD | Login Repartidor')

@section('content')
    <section class="petpay-auth-wrap">
        <div class="petpay-auth-card">
            <div class="petpay-auth-icon">🛵</div>

            <h1 class="petpay-auth-title">Acceso repartidor</h1>
            <p class="petpay-auth-text">
                Ingresa para activar disponibilidad, aceptar entregas y consultar tus ingresos.
            </p>

            <form method="POST" action="{{ route('repartidor.login.store') }}">
                @csrf
                <div class="petpay-form-group">
                    <label class="petpay-label">Correo electrónico</label>
                    <input class="petpay-field" type="email" placeholder="repartidor@email.com">
                </div>

                <div class="petpay-form-group">
                    <label class="petpay-label">Contraseña</label>
                    <input class="petpay-field" type="password" placeholder="••••••••">
                </div>

                <button type="submit" class="petpay-btn petpay-btn-black" style="width:100%;">
                    Entrar como repartidor
                </button>

                <div class="petpay-auth-actions">
                    <a class="petpay-auth-link" href="{{ route('repartidor.password.request') }}">
                        Recuperar contraseña
                    </a>
                    <a class="petpay-auth-link" href="{{ route('repartidor.register') }}">
                        Crear perfil
                    </a>
                </div>
            </form>
        </div>
    </section>
@endsection