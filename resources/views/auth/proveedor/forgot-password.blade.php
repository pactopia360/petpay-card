@php($portal = 'proveedor')

@extends('layouts.app')

@section('title', 'PETPAY-CARD | Recuperar Proveedor')

@section('content')
    <section class="petpay-auth-wrap">
        <div class="petpay-auth-card">
            <div class="petpay-auth-icon">🔐</div>

            <h1 class="petpay-auth-title">Recuperar acceso</h1>
            <p class="petpay-auth-text">
                Te enviaremos instrucciones al correo registrado de tu negocio.
            </p>

            <form>
                <div class="petpay-form-group">
                    <label class="petpay-label">Correo del negocio</label>
                    <input class="petpay-field" type="email" placeholder="negocio@email.com">
                </div>

                <button type="button" class="petpay-btn petpay-btn-black" style="width:100%;">
                    Enviar instrucciones
                </button>

                <p class="petpay-auth-note">
                    <a href="{{ route('proveedor.login') }}">Volver al login</a>
                </p>
            </form>
        </div>
    </section>
@endsection