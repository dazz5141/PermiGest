<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom fixed-top">
    <div class="container-fluid">
        <button class="btn btn-link text-dark me-3" id="sidebarToggle" type="button">
            <i class="bi bi-list fs-4"></i>
        </button>

        <a class="navbar-brand d-flex align-items-center" href="#">
            <i class="bi bi-clipboard-check-fill text-primary me-2 fs-4"></i>
            <span class="fw-semibold">PermiGest Escolar</span>
        </a>

        <div class="ms-auto d-flex align-items-center">
            <div class="dropdown">
                <button class="btn btn-link text-dark dropdown-toggle d-flex align-items-center" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-person-circle fs-4 me-2"></i>
                    <div class="text-start">
                        <div class="fw-semibold d-none d-md-block">
                            {{ auth()->user()->nombres }} {{ auth()->user()->apellidos }}
                        </div>
                        <div class="text-muted small d-none d-md-block">
                            {{ auth()->user()->cargo ?? 'Usuario' }}
                        </div>
                    </div>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                    <li>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="dropdown-item">
                                <i class="bi bi-box-arrow-right me-2"></i>
                                Cerrar sesión
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>
