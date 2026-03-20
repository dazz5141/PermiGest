@php
    $usuario = auth()->user();
    $nombreCorto = trim(($usuario->nombres ?? 'Usuario') . ' ' . ($usuario->apellidos ?? ''));
    $cargoMostrado = $usuario->cargo ?? 'Usuario del sistema';
@endphp

<nav class="navbar navbar-expand-lg fixed-top">
    <div class="container-fluid navbar-shell">
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-link text-dark navbar-toggle" id="sidebarToggle" type="button" aria-label="Mostrar u ocultar menu lateral">
                <i class="bi bi-list fs-4"></i>
            </button>

            <a class="navbar-brand navbar-brand-custom d-flex align-items-center" href="{{ route('dashboard') }}">
                <span class="brand-mark">
                    <i class="bi bi-clipboard-check-fill"></i>
                </span>
                <span class="brand-copy">
                    <strong>PermiGest Escolar</strong>
                    <small>Control de permisos administrativos</small>
                </span>
            </a>
        </div>

        <div class="ms-auto d-flex align-items-center">
            <div class="dropdown">
                <button class="btn navbar-user-toggle dropdown-toggle d-flex align-items-center" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="navbar-user-avatar">
                        <i class="bi bi-person-circle"></i>
                    </span>
                    <div class="text-start d-none d-md-block">
                        <div class="navbar-user-name">{{ $nombreCorto }}</div>
                        <div class="navbar-user-role">{{ $cargoMostrado }}</div>
                    </div>
                </button>

                <ul class="dropdown-menu dropdown-menu-end navbar-user-menu" aria-labelledby="userDropdown">
                    <li class="dropdown-menu-header d-md-none">
                        <strong>{{ $nombreCorto }}</strong>
                        <small>{{ $cargoMostrado }}</small>
                    </li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="dropdown-item">
                                <i class="bi bi-box-arrow-right me-2"></i>
                                Cerrar sesion
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>
