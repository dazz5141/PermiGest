<aside class="sidebar bg-white border-end" id="sidebar">
    <div class="sidebar-header p-4 border-bottom">
        <div class="user-info text-center">
            <div class="user-avatar mb-3">
                <i class="bi bi-person-circle text-primary" style="font-size: 3.5rem;"></i>
            </div>
            <h6 class="user-name mb-1 fw-semibold">{{ Auth::user()->nombres ?? 'Usuario' }} {{ Auth::user()->apellidos ?? '' }}</h6>
            <p class="user-role text-muted small mb-0">{{ Auth::user()->cargo ?? 'Funcionario' }}</p>
        </div>
    </div>

    <nav class="sidebar-menu p-3">
        <ul class="nav flex-column">

            {{-- 🔍 Consultas --}}
            <li class="nav-item mb-2">
                <a class="nav-link d-flex align-items-center" href="{{ route('solicitudes.index') }}">
                    <i class="bi bi-search me-2"></i>
                    <span>Mis solicitudes</span>
                </a>
            </li>

            {{-- 📄 Módulo de solicitudes --}}
            <li class="nav-item mb-2">
                <a class="nav-link d-flex align-items-center collapsed" data-bs-toggle="collapse" href="#solicitudesMenu" role="button" aria-expanded="false" aria-controls="solicitudesMenu">
                    <i class="bi bi-file-earmark-text me-2"></i>
                    <span>Solicitudes</span>
                    <i class="bi bi-chevron-down ms-auto"></i>
                </a>

                <div class="collapse" id="solicitudesMenu">
                    <ul class="nav flex-column ms-3 mt-2">

                        <li class="nav-item mb-2">
                            <a class="nav-link submenu-link d-flex align-items-center"
                               href="{{ route('solicitudes.create', ['tipo' => 'con_goce']) }}">
                                <i class="bi bi-circle-fill me-2 text-primary" style="font-size: 0.4rem;"></i>
                                <span>Permiso con goce de sueldo</span>
                            </a>
                        </li>

                        <li class="nav-item mb-2">
                            <a class="nav-link submenu-link d-flex align-items-center"
                               href="{{ route('solicitudes.create', ['tipo' => 'sin_goce']) }}">
                                <i class="bi bi-circle-fill me-2 text-primary" style="font-size: 0.4rem;"></i>
                                <span>Permiso sin goce de sueldo</span>
                            </a>
                        </li>

                        <li class="nav-item mb-2">
                            <a class="nav-link submenu-link d-flex align-items-center"
                               href="{{ route('solicitudes.create', ['tipo' => 'defuncion']) }}">
                                <i class="bi bi-circle-fill me-2 text-primary" style="font-size: 0.4rem;"></i>
                                <span>Permiso por defunción</span>
                            </a>
                        </li>

                        <li class="nav-item mb-2">
                            <a class="nav-link submenu-link d-flex align-items-center"
                               href="{{ route('solicitudes.create', ['tipo' => 'varios']) }}">
                                <i class="bi bi-circle-fill me-2 text-primary" style="font-size: 0.4rem;"></i>
                                <span>Permisos varios</span>
                            </a>
                        </li>

                    </ul>
                </div>
            </li>

            {{-- ⚙️ (opcional) Administración --}}
            @if(Auth::user()?->rol?->nombre === 'admin')
            <li class="nav-item mb-2">
                <a class="nav-link d-flex align-items-center collapsed" data-bs-toggle="collapse" href="#adminMenu" role="button" aria-expanded="false" aria-controls="adminMenu">
                    <i class="bi bi-gear-fill me-2"></i>
                    <span>Administración</span>
                    <i class="bi bi-chevron-down ms-auto"></i>
                </a>

                <div class="collapse" id="adminMenu">
                    <ul class="nav flex-column ms-3 mt-2">
                        <li class="nav-item mb-2">
                            <a class="nav-link submenu-link d-flex align-items-center" href="{{ route('tipos.index') }}">
                                <i class="bi bi-circle-fill me-2 text-secondary" style="font-size: 0.4rem;"></i>
                                <span>Tipos de solicitud</span>
                            </a>
                        </li>
                        <li class="nav-item mb-2">
                            <a class="nav-link submenu-link d-flex align-items-center" href="{{ route('estados.index') }}">
                                <i class="bi bi-circle-fill me-2 text-secondary" style="font-size: 0.4rem;"></i>
                                <span>Estados de solicitud</span>
                            </a>
                        </li>
                        <li class="nav-item mb-2">
                            <a class="nav-link submenu-link d-flex align-items-center" href="{{ route('parentescos.index') }}">
                                <i class="bi bi-circle-fill me-2 text-secondary" style="font-size: 0.4rem;"></i>
                                <span>Parentescos</span>
                            </a>
                        </li>
                        <li class="nav-item mb-2">
                            <a class="nav-link submenu-link d-flex align-items-center" href="{{ route('tiposvarios.index') }}">
                                <i class="bi bi-circle-fill me-2 text-secondary" style="font-size: 0.4rem;"></i>
                                <span>Tipos varios de solicitud</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            @endif

        </ul>
    </nav>
</aside>
