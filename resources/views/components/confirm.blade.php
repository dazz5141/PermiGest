@push('scripts')

    <script>
    (function () {
        // Intercepta clicks en cualquier elemento con [data-confirm]
        document.addEventListener('click', function (e) {
            const btn = e.target.closest('[data-confirm]');
            if (!btn) return;

            e.preventDefault();

            // Lee atributos personalizables del botón/enlace
            const title       = btn.dataset.confirmTitle || '¿Estás seguro?';
            const text        = btn.dataset.confirmText  || 'Esta acción no se puede deshacer.';
            const confirmText = btn.dataset.confirmBtn   || 'Sí, continuar';
            const cancelText  = btn.dataset.cancelBtn    || 'Cancelar';
            const icon        = btn.dataset.confirmIcon  || 'warning'; // success | warning | info | question | error

            // Busca si el botón está dentro de un form
            const form = btn.closest('form');

            Swal.fire({
                title: title,
                text: text,
                icon: icon,
                showCancelButton: true,
                confirmButtonText: confirmText,
                cancelButtonText: cancelText,
                reverseButtons: true,
                focusCancel: true,
            }).then((result) => {
                if (result.isConfirmed) {
                    if (form) {
                        // Caso 1: dentro de un <form> → lo enviamos
                        form.submit();
                    } else {
                        // Caso 2: no hay form (ej. enlace con data-action/data-method) → creamos form temporal
                        const action = btn.dataset.action;
                        const method = (btn.dataset.method || 'POST').toUpperCase();
                        if (!action) return;

                        const tmp = document.createElement('form');
                        tmp.method = 'POST';
                        tmp.action = action;

                        // CSRF
                        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                        if (token) {
                            const csrf = document.createElement('input');
                            csrf.type = 'hidden'; csrf.name = '_token'; csrf.value = token;
                            tmp.appendChild(csrf);
                        }

                        // Spoofing de método si no es POST
                        if (method !== 'POST') {
                            const m = document.createElement('input');
                            m.type = 'hidden'; m.name = '_method'; m.value = method;
                            tmp.appendChild(m);
                        }

                        document.body.appendChild(tmp);
                        tmp.submit();
                    }
                }
            });
        });
    })();
    </script>
@endpush
