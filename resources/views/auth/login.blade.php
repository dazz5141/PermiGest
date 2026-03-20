<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Iniciar Sesión - PermiGest Escolar</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
    <div class="login-shell">
        <div class="login-layout">
            <section class="login-showcase">
                <div class="showcase-copy">
                    <span class="showcase-kicker">
                        <i class="bi bi-stars"></i>
                        Plataforma institucional
                    </span>

                    <h1 class="showcase-title">Gestiona permisos con una entrada más clara y segura.</h1>
                    <p class="showcase-text">
                        PermiGest Escolar centraliza solicitudes, resolución de Dirección y seguimiento anual en una experiencia simple para todo el establecimiento.
                    </p>
                </div>

                <div class="showcase-pills">
                    <span class="showcase-pill"><i class="bi bi-shield-check"></i> Acceso institucional</span>
                    <span class="showcase-pill"><i class="bi bi-calendar-check"></i> Control anual</span>
                    <span class="showcase-pill"><i class="bi bi-journal-text"></i> Historial centralizado</span>
                </div>

                <span class="floating-orb orb-one"><i class="bi bi-person-lock fs-3"></i></span>
                <span class="floating-orb orb-two"><i class="bi bi-patch-check-fill"></i></span>
                <span class="orb-connector"></span>
            </section>

            <section class="login-panel">
                <div class="login-form-wrap">
                    <div class="login-mini-brand">
                        <span class="login-mini-brand-badge">
                            <i class="bi bi-clipboard-check-fill"></i>
                        </span>
                        <span>PermiGest Escolar</span>
                    </div>

                    <h2>Inicio de Sesión</h2>
                    <p class="login-subtitle">Ingresa con tu correo institucional para acceder al sistema.</p>

                    @if (session('error'))
                        <div class="alert alert-danger login-alert alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form action="{{ route('login.post') }}" method="POST">
                        @csrf

                        <div class="login-field">
                            <label for="correo_institucional" class="login-label">Correo institucional</label>
                            <div class="login-input-wrap">
                                <span class="login-input-icon">
                                    <i class="bi bi-envelope-fill"></i>
                                </span>
                                <input
                                    type="email"
                                    class="form-control login-input"
                                    id="correo_institucional"
                                    name="correo_institucional"
                                    placeholder="correo@institucion.cl"
                                    value="{{ old('correo_institucional') }}"
                                    required
                                    autofocus
                                >
                            </div>
                        </div>

                        <div class="login-field">
                            <label for="password" class="login-label">Contraseña</label>
                            <div class="login-input-wrap">
                                <span class="login-input-icon" style="color:#7d8aa6;">
                                    <i class="bi bi-lock-fill"></i>
                                </span>
                                <input
                                    type="password"
                                    class="form-control login-input"
                                    id="password"
                                    name="password"
                                    placeholder="Ingrese su contraseña"
                                    required
                                >
                            </div>
                        </div>

                        <div class="login-check">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="remember" name="remember">
                                <label class="form-check-label" for="remember">
                                    Mantener sesión iniciada
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="login-submit">
                            <i class="bi bi-box-arrow-in-right me-2"></i>
                            Iniciar sesión
                        </button>
                    </form>

                    <div class="login-footer">
                        © 2025 PermiGest Escolar
                    </div>
                </div>
            </section>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
