@php($portal = 'admin')

@extends('layouts.app')

@section('title', 'PETPAY-CARD | Login Admin')

@section('content')
    <section class="petpay-auth-wrap">
        <div class="petpay-auth-card">
            <div class="petpay-auth-icon">⚙️</div>

            <h1 class="petpay-auth-title">Acceso Admin</h1>
            <p class="petpay-auth-text">
                Ingresa con tu usuario administrador para controlar la operación general de PETPAY-CARD.
            </p>

            @if ($errors->any())
                <div class="petpay-alert petpay-alert-error">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('admin.login.store') }}">
                @csrf

                <div class="petpay-form-group">
                    <label class="petpay-label">Correo electrónico</label>
                    <input
                        class="petpay-field"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        placeholder="admin@petpay-card.com"
                        required
                        autofocus
                    >
                </div>

                <div class="petpay-form-group">
                    <label class="petpay-label">Contraseña</label>
                    <input
                        class="petpay-field"
                        type="password"
                        name="password"
                        placeholder="••••••••"
                        required
                    >
                </div>

                <label class="petpay-check">
                    <input type="checkbox" name="remember" value="1">
                    <span>Recordar sesión</span>
                </label>

                <button type="submit" class="petpay-btn petpay-btn-black" style="width:100%;">
                    Entrar al Admin
                </button>

                <div class="petpay-auth-actions">
                    <a class="petpay-auth-link" href="{{ route('admin.password.request') }}">
                        Recuperar contraseña
                    </a>
                    <a class="petpay-auth-link" href="{{ route('home') }}">
                        Volver
                    </a>
                </div>
            </form>
        </div>
    </section>
@endsection