@php($portal = 'repartidor')

@extends('layouts.app')

@section('title', 'PETPAY-CARD | Recuperar Repartidor')

@section('content')
    <section class="petpay-auth-wrap">
        <div class="petpay-auth-card">
            <div class="petpay-auth-icon">🔐</div>

            <h1 class="petpay-auth-title">Recuperar contraseña</h1>
            <p class="petpay-auth-text">
                Te enviaremos instrucciones para recuperar tu perfil de repartidor.
            </p>

            <form>
                <div class="petpay-form-group">
                    <label class="petpay-label">Correo electrónico</label>
                    <input class="petpay-field" type="email" placeholder="repartidor@email.com">
                </div>

                <button type="button" class="petpay-btn petpay-btn-black" style="width:100%;">
                    Enviar instrucciones
                </button>

                <p class="petpay-auth-note">
                    <a href="{{ route('repartidor.login') }}">Volver al login</a>
                </p>
            </form>
        </div>
    </section>
@endsection