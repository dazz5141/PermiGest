/**
 * PermiGest Escolar - JavaScript principal
 * Manejo de interacciones del sidebar y funcionalidades generales
 */

document.addEventListener('DOMContentLoaded', function() {

    // ================================================
    // Toggle del sidebar
    // ================================================

    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    const contentArea = document.getElementById('content-area');

    if (sidebarToggle && sidebar && contentArea) {
        sidebarToggle.addEventListener('click', function() {
            // En móvil: mostrar/ocultar sidebar
            if (window.innerWidth <= 991) {
                sidebar.classList.toggle('show');

                // Agregar overlay para cerrar el sidebar al hacer clic fuera
                if (sidebar.classList.contains('show')) {
                    createOverlay();
                } else {
                    removeOverlay();
                }
            }
            // En desktop: colapsar/expandir
            else {
                sidebar.classList.toggle('collapsed');
                contentArea.classList.toggle('expanded');
            }
        });
    }

    // ================================================
    // Overlay para cerrar sidebar en móvil
    // ================================================

    function createOverlay() {
        // Verificar si ya existe un overlay
        if (document.getElementById('sidebar-overlay')) return;

        const overlay = document.createElement('div');
        overlay.id = 'sidebar-overlay';
        overlay.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1015;
            cursor: pointer;
        `;

        overlay.addEventListener('click', function() {
            sidebar.classList.remove('show');
            removeOverlay();
        });

        document.body.appendChild(overlay);
    }

    function removeOverlay() {
        const overlay = document.getElementById('sidebar-overlay');
        if (overlay) {
            overlay.remove();
        }
    }

    // ================================================
    // Cerrar sidebar al hacer clic en un enlace (móvil)
    // ================================================

    const sidebarLinks = document.querySelectorAll('.sidebar .nav-link:not([data-bs-toggle="collapse"])');

    sidebarLinks.forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth <= 991 && sidebar.classList.contains('show')) {
                sidebar.classList.remove('show');
                removeOverlay();
            }
        });
    });

    // ================================================
    // Ajustar sidebar en resize
    // ================================================

    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            // Si se cambia a desktop y el sidebar estaba visible en móvil
            if (window.innerWidth > 991) {
                sidebar.classList.remove('show');
                removeOverlay();

                // Restaurar el estado collapsed si existía
                if (!sidebar.classList.contains('collapsed')) {
                    contentArea.classList.remove('expanded');
                }
            }
            // Si se cambia a móvil
            else {
                sidebar.classList.remove('collapsed');
                contentArea.classList.remove('expanded');
            }
        }, 250);
    });

    // ================================================
    // Manejo del menú de solicitudes (collapse)
    // ================================================

    const solicitudesMenu = document.getElementById('solicitudesMenu');

    if (solicitudesMenu) {
        solicitudesMenu.addEventListener('show.bs.collapse', function() {
            // Agregar animación de entrada
            this.style.transition = 'all 0.3s ease';
        });

        solicitudesMenu.addEventListener('hide.bs.collapse', function() {
            // Agregar animación de salida
            this.style.transition = 'all 0.3s ease';
        });
    }

    // ================================================
    // Validación de formularios
    // ================================================

    const forms = document.querySelectorAll('form');

    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }

            form.classList.add('was-validated');
        });
    });

    // ================================================
    // Manejo del campo "días" y "jornada" en con_goce
    // ================================================

    const diasSelect = document.getElementById('dias');
    const jornadaSelect = document.getElementById('jornada');

    if (diasSelect && jornadaSelect) {
        diasSelect.addEventListener('change', function() {
            // Si selecciona medio día (0.5), habilitar jornada
            if (this.value === '0.5') {
                jornadaSelect.disabled = false;
                jornadaSelect.required = true;
            }
            // Si selecciona día completo (1), deshabilitar jornada
            else {
                jornadaSelect.disabled = true;
                jornadaSelect.required = false;
                jornadaSelect.value = '';
            }
        });

        // Ejecutar al cargar la página por si hay un valor previo
        if (diasSelect.value === '1' || diasSelect.value === '') {
            jornadaSelect.disabled = true;
            jornadaSelect.required = false;
        }
    }

    // ================================================
    // Calcular fecha hasta basada en días solicitados
    // ================================================

    const fechaDesde = document.getElementById('fecha_desde');
    const fechaHasta = document.getElementById('fecha_hasta');
    const diasInput = document.getElementById('dias');

    if (fechaDesde && fechaHasta && diasInput) {
        fechaDesde.addEventListener('change', calcularFechaHasta);
        diasInput.addEventListener('change', calcularFechaHasta);

        function calcularFechaHasta() {
            const desde = new Date(fechaDesde.value);
            const dias = parseInt(diasInput.value) || 0;

            if (fechaDesde.value && dias > 0) {
                // Sumar días (restando 1 porque el primer día cuenta)
                const hasta = new Date(desde);
                hasta.setDate(hasta.getDate() + dias - 1);

                // Formatear fecha para input type="date"
                const year = hasta.getFullYear();
                const month = String(hasta.getMonth() + 1).padStart(2, '0');
                const day = String(hasta.getDate()).padStart(2, '0');

                fechaHasta.value = `${year}-${month}-${day}`;
            }
        }
    }

    // ================================================
    // Tooltip de Bootstrap
    // ================================================

    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // ================================================
    // Auto-dismiss de alertas
    // ================================================

    const alerts = document.querySelectorAll('.alert:not(.alert-static)');

    alerts.forEach(alert => {
        // Auto-dismiss después de 5 segundos
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });

    // ================================================
    // Confirmación antes de enviar formularios
    // ================================================

    const submitButtons = document.querySelectorAll('button[type="submit"]');

    submitButtons.forEach(button => {
        button.addEventListener('click', function(event) {
            const form = this.closest('form');

            // Solo mostrar confirmación si el formulario es válido
            if (form && form.checkValidity()) {
                const confirmed = confirm('¿Está seguro de enviar esta solicitud?');

                if (!confirmed) {
                    event.preventDefault();
                    event.stopPropagation();
                }
            }
        });
    });

    // ================================================
    // Smooth scroll
    // ================================================

    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const target = document.querySelector(this.getAttribute('href'));

            if (target) {
                e.preventDefault();
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // ================================================
    // Mantener el estado del menú de solicitudes
    // ================================================

    // Verificar si estamos en una página de solicitudes
    const currentPath = window.location.pathname;

    if (currentPath.includes('/solicitudes/')) {
        const solicitudesCollapse = document.getElementById('solicitudesMenu');

        if (solicitudesCollapse) {
            // Mostrar el menú expandido
            const bsCollapse = new bootstrap.Collapse(solicitudesCollapse, {
                show: true
            });

            // Marcar el enlace activo
            const activeLink = document.querySelector(`.sidebar a[href="${currentPath}"]`);
            if (activeLink) {
                activeLink.classList.add('active');
            }
        }
    }

});
