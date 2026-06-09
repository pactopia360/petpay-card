@php($portal = 'admin')

@extends('layouts.app')

@section('title', 'PETPAY-CARD | Recuperar Admin')

@section('content')
    <section class="petpay-auth-wrap">
        <div class="petpay-auth-card">
            <div class="petpay-auth-icon">🔐</div>

            <h1 class="petpay-auth-title">Recuperar acceso</h1>
            <p class="petpay-auth-text">
                Ingresa el correo de tu cuenta administradora para iniciar la recuperación.
            </p>

            <form>
                <div class="petpay-form-group">
                    <label class="petpay-label">Correo electrónico</label>
                    <input class="petpay-field" type="email" placeholder="admin@petpay-card.com">
                </div>

                <button type="button" class="petpay-btn petpay-btn-black" style="width:100%;">
                    Enviar instrucciones
                </button>

                <p class="petpay-auth-note">
                    <a href="{{ route('admin.login') }}">Volver al login</a>
                </p>
            </form>
        </div>
    </section>
@endsection