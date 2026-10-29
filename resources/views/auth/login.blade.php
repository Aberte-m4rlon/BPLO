@extends('layouts.guest')

@section('title', 'Login — '.config('permit.municipality'))

@section('content')
    <div class="auth-wrap bplo-auth-wrap">
        <section class="bplo-auth-intro">
            <p class="bplo-eyebrow">MUNICIPAL PERMIT SERVICES</p>
            <h1>Business permits.<br>Handled with care.</h1>
            <p>One secure workspace for reviewing applications, checking requirements, and issuing permits.</p>
            <div class="bplo-auth-feature">
                <span aria-hidden="true">01</span>
                <div><strong>Review applications</strong><small>Check submitted details and supporting documents.</small></div>
            </div>
            <div class="bplo-auth-feature">
                <span aria-hidden="true">02</span>
                <div><strong>Issue verified permits</strong><small>Release only after payment is confirmed.</small></div>
            </div>
        </section>
        <div class="auth-card">
            <div class="bplo-auth-card-heading">
                <span class="bplo-auth-lock" aria-hidden="true">BP</span>
                <p class="bplo-eyebrow">STAFF ACCESS</p>
                <h2>Welcome back</h2>
                <p class="muted">Sign in with your active BPLO account.</p>
            </div>

            <form method="POST" action="{{ url('/login') }}">
                @csrf
                <div class="form-group">
                    <label for="email">Work email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="name@bansud.gov.ph" autocomplete="username" required autofocus>
                    @error('email') <div class="field-error">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password" autocomplete="current-password" required>
                    @error('password') <div class="field-error">{{ $message }}</div> @enderror
                </div>
                <div class="bplo-login-options">
                    <label class="bplo-remember"><input type="checkbox" name="remember" value="1"> Remember me</label>
                    <a href="{{ url('/forgot-password') }}">Forgot password?</a>
                </div>
                <button type="submit" class="btn btn-block">Sign in to BPLO <span aria-hidden="true">→</span></button>
            </form>
            <p class="bplo-auth-note"><span aria-hidden="true">●</span> Authorized municipal personnel only</p>
        </div>
    </div>
@endsection
