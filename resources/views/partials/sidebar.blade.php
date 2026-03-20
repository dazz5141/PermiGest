@php
    $usuario = Auth::user();
    $rolActual = $usuario?->rol?->nombre ?? 'funcionario';
    $rolMostrado = match($rolActual) {
        'jefe_directo' => 'Director',
        'encargado_sistema' => 'Encargado del sistema',
        'secretaria' => 'Secretaria',
        'admin' => 'Admin',
        'funcionario' => 'Funcionario',
        default => ucfirst(str_replace('_', ' ', $rolActual)),
    };
@endphp

<aside class="sidebar border-end" id="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-profile-card">
            <div class="sidebar-profile-glow"></div>
            <div class="user-avatar">
                <i class="bi bi-person-circle"></i>
            </div>
            <h6 class="user-name mb-1 fw-semibold">
                {{ $usuario->nombres ?? 'Usuario' }} {{ $usuario->apellidos ?? '' }}
            </h6>
            <p class="user-role mb-0">{{ $rolMostrado }}</p>
        </div>
    </div>

    <nav class="sidebar-menu p-3">
        <ul class="nav flex-column gap-2">
            @if($rolActual === 'funcionario')
                <li class="nav-item">
                    <a class="nav-link sidebar-link {{ request()->routeIs('solicitudes.index') ? 'active' : '' }}" href="{{ route('solicitudes.index') }}">
                        <span class="nav-icon-wrap"><i class="bi bi-search"></i></span>
                        <span>Mis solicitudes</span>
                    </a>
                </li>

                <li class="nav-section-label">Solicitudes</li>

                <li class="nav-item">
                    <a class="nav-link sidebar-link d-flex align-items-center collapsed {{ request()->routeIs('solicitudes.create') ? 'active' : '' }}"
                       data-bs-toggle="collapse"
                       href="#solicitudesMenu"
                       role="button"
                       aria-expanded="{{ request()->routeIs('solicitudes.create') ? 'true' : 'false' }}"
                       aria-controls="solicitudesMenu">
                        <span class="nav-icon-wrap"><i class="bi bi-file-earmark-text"></i></span>
                        <span>Solicitudes</span>
                        <i class="bi bi-chevron-down ms-auto"></i>
                    </a>

                    <div class="collapse {{ request()->routeIs('solicitudes.create') ? 'show' : '' }}" id="solicitudesMenu">
                        <ul class="nav flex-column submenu-panel mt-2">
                            <li class="nav-item">
                                <a class="nav-link submenu-link {{ request()->fullUrlIs('*tipo=con_goce*') ? 'active' : '' }}" href="{{ route('solicitudes.create', ['tipo' => 'con_goce']) }}">
                                    <span class="submenu-dot"></span>
                                    <span>Permiso con goce de sueldo</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link submenu-link {{ request()->fullUrlIs('*tipo=sin_goce*') ? 'active' : '' }}" href="{{ route('solicitudes.create', ['tipo' => 'sin_goce']) }}">
                                    <span class="submenu-dot"></span>
                                    <span>Permiso sin goce de sueldo</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link submenu-link {{ request()->fullUrlIs('*tipo=defuncion*') ? 'active' : '' }}" href="{{ route('solicitudes.create', ['tipo' => 'defuncion']) }}">
                                    <span class="submenu-dot"></span>
                                    <span>Permiso por defuncion</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link submenu-link {{ request()->fullUrlIs('*tipo=varios*') ? 'active' : '' }}" href="{{ route('solicitudes.create', ['tipo' => 'varios']) }}">
                                    <span class="submenu-dot"></span>
                                    <span>Permisos varios</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
            @elseif($rolActual === 'secretaria')
                <li class="nav-section-label">Gestion</li>
                <li class="nav-item">
                    <a class="nav-link sidebar-link {{ request()->routeIs('reportes.mensuales') ? 'active' : '' }}" href="{{ route('reportes.mensuales') }}">
                        <span class="nav-icon-wrap"><i class="bi bi-bar-chart-line"></i></span>
                        <div>
                            <span class="d-block">Solicitudes del establecimiento</span>
                            <small class="text-muted">Reportes y estadisticas</small>
                        </div>
                    </a>
                </li>
            @elseif($rolActual === 'jefe_directo')
                <li class="nav-section-label">Direccion</li>
                <li class="nav-item">
                    <a class="nav-link sidebar-link {{ request()->routeIs('resoluciones.index') ? 'active' : '' }}" href="{{ route('resoluciones.index') }}">
                        <span class="nav-icon-wrap"><i class="bi bi-journal-check"></i></span>
                        <div>
                            <span class="d-block">Solicitudes pendientes</span>
                            <small class="text-muted">Resolucion de Direccion</small>
                        </div>
                    </a>
                </li>
            @elseif($rolActual === 'encargado_sistema')
                <li class="nav-section-label">Operacion</li>
                <li class="nav-item">
                    <a class="nav-link sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                        <span class="nav-icon-wrap"><i class="bi bi-speedometer2"></i></span>
                        <span>Panel operativo</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link sidebar-link {{ request()->routeIs('admin.usuarios.*') ? 'active' : '' }}" href="{{ route('admin.usuarios.index') }}">
                        <span class="nav-icon-wrap"><i class="bi bi-people-fill"></i></span>
                        <span>Usuarios</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link sidebar-link {{ request()->routeIs('admin.feriados.*') ? 'active' : '' }}" href="{{ route('admin.feriados.index') }}">
                        <span class="nav-icon-wrap"><i class="bi bi-calendar-event"></i></span>
                        <span>Feriados</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link sidebar-link {{ request()->routeIs('admin.restauraciones.*') ? 'active' : '' }}" href="{{ route('admin.restauraciones.index') }}">
                        <span class="nav-icon-wrap"><i class="bi bi-arrow-counterclockwise"></i></span>
                        <span>Restauraciones</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link sidebar-link {{ request()->routeIs('auditoria.*') ? 'active' : '' }}" href="{{ route('auditoria.index') }}">
                        <span class="nav-icon-wrap"><i class="bi bi-search"></i></span>
                        <span>Auditoria</span>
                    </a>
                </li>
            @elseif($rolActual === 'admin')
                <li class="nav-section-label">Vision general</li>
                <li class="nav-item">
                    <a class="nav-link sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                        <span class="nav-icon-wrap"><i class="bi bi-graph-up-arrow"></i></span>
                        <span>Reportes generales</span>
                    </a>
                </li>

                <li class="nav-section-label">Administracion</li>
                <li class="nav-item">
                    @php
                        $adminAbierto = request()->routeIs('tipos.*')
                            || request()->routeIs('estados.*')
                            || request()->routeIs('parentescos.*')
                            || request()->routeIs('tiposvarios.*')
                            || request()->routeIs('admin.feriados.*')
                            || request()->routeIs('admin.restauraciones.*')
                            || request()->routeIs('admin.usuarios.*')
                            || request()->routeIs('admin.roles.*')
                            || request()->routeIs('auditoria.*');
                    @endphp
                    <a class="nav-link sidebar-link d-flex align-items-center collapsed {{ $adminAbierto ? 'active' : '' }}"
                       data-bs-toggle="collapse"
                       href="#adminMenu"
                       role="button"
                       aria-expanded="{{ $adminAbierto ? 'true' : 'false' }}"
                       aria-controls="adminMenu">
                        <span class="nav-icon-wrap"><i class="bi bi-gear-fill"></i></span>
                        <span>Administracion</span>
                        <i class="bi bi-chevron-down ms-auto"></i>
                    </a>

                    <div class="collapse {{ $adminAbierto ? 'show' : '' }}" id="adminMenu">
                        <ul class="nav flex-column submenu-panel mt-2">
                            <li class="nav-item">
                                <a class="nav-link submenu-link {{ request()->routeIs('tipos.*') ? 'active' : '' }}" href="{{ route('tipos.index') }}">
                                    <span class="submenu-dot"></span>
                                    <span>Tipos de solicitud</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link submenu-link {{ request()->routeIs('estados.*') ? 'active' : '' }}" href="{{ route('estados.index') }}">
                                    <span class="submenu-dot"></span>
                                    <span>Estados de solicitud</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link submenu-link {{ request()->routeIs('parentescos.*') ? 'active' : '' }}" href="{{ route('parentescos.index') }}">
                                    <span class="submenu-dot"></span>
                                    <span>Parentescos</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link submenu-link {{ request()->routeIs('tiposvarios.*') ? 'active' : '' }}" href="{{ route('tiposvarios.index') }}">
                                    <span class="submenu-dot"></span>
                                    <span>Tipos varios</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link submenu-link {{ request()->routeIs('admin.feriados.*') ? 'active' : '' }}" href="{{ route('admin.feriados.index') }}">
                                    <span class="submenu-dot"></span>
                                    <span>Feriados</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link submenu-link {{ request()->routeIs('admin.restauraciones.*') ? 'active' : '' }}" href="{{ route('admin.restauraciones.index') }}">
                                    <span class="submenu-dot"></span>
                                    <span>Restauraciones</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link submenu-link {{ request()->routeIs('admin.usuarios.*') ? 'active' : '' }}" href="{{ route('admin.usuarios.index') }}">
                                    <span class="submenu-dot"></span>
                                    <span>Usuarios</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link submenu-link {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}" href="{{ route('admin.roles.index') }}">
                                    <span class="submenu-dot"></span>
                                    <span>Roles</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link submenu-link {{ request()->routeIs('auditoria.*') ? 'active' : '' }}" href="{{ route('auditoria.index') }}">
                                    <span class="submenu-dot"></span>
                                    <span>Auditoria del sistema</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
            @endif
        </ul>
    </nav>
</aside>
