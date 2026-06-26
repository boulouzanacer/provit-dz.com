<div id="pv-delete-confirm-modal" class="fixed inset-0 z-[120] hidden items-center justify-center bg-slate-950/70 px-4 backdrop-blur-sm">
    <div class="w-full max-w-md overflow-hidden rounded-[28px] border border-white/10 bg-white shadow-2xl shadow-slate-950/30">
        <div class="relative overflow-hidden bg-gradient-to-r from-red-600 via-rose-600 to-orange-500 px-6 pb-8 pt-6 text-white">
            <div class="absolute -right-10 -top-10 h-32 w-32 rounded-full bg-white/10 blur-2xl"></div>
            <div class="relative flex items-start gap-4">
                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl border border-white/20 bg-white/10">
                    <i class="fa-solid fa-triangle-exclamation text-xl"></i>
                </div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-[0.35em] text-white/70">Confirmation</div>
                    <h3 id="pv-delete-confirm-title" class="mt-2 text-2xl font-extrabold">Supprimer cet element ?</h3>
                    <p id="pv-delete-confirm-message" class="mt-2 text-sm leading-6 text-white/85">Cette action est irreversible. Verifiez avant de continuer.</p>
                </div>
            </div>
        </div>
        <div class="space-y-5 px-6 py-6 text-slate-700">
            <div class="rounded-2xl border border-red-100 bg-red-50 px-4 py-3 text-sm text-red-700">
                Cette suppression sera appliquee immediatement apres confirmation.
            </div>
            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <button type="button" id="pv-delete-confirm-cancel" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                    Annuler
                </button>
                <button type="button" id="pv-delete-confirm-submit" class="inline-flex items-center justify-center gap-2 rounded-2xl px-5 py-3 text-sm font-extrabold text-white shadow-lg shadow-red-500/25 transition hover:brightness-105" style="background:linear-gradient(135deg,#dc2626,#f97316)">
                    <i class="fa-solid fa-trash"></i>
                    <span id="pv-delete-confirm-button-label">Oui, supprimer</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        if (window.__pvDeleteConfirmLoaded) {
            return;
        }

        window.__pvDeleteConfirmLoaded = true;

        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.getElementById('pv-delete-confirm-modal');
            const title = document.getElementById('pv-delete-confirm-title');
            const message = document.getElementById('pv-delete-confirm-message');
            const submitLabel = document.getElementById('pv-delete-confirm-button-label');
            const submitButton = document.getElementById('pv-delete-confirm-submit');
            const cancelButton = document.getElementById('pv-delete-confirm-cancel');

            if (!modal || !title || !message || !submitLabel || !submitButton || !cancelButton) {
                return;
            }

            let pendingForm = null;

            const closeModal = function () {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                document.body.classList.remove('overflow-hidden');
                pendingForm = null;
            };

            const openModal = function (form) {
                pendingForm = form;
                title.textContent = form.dataset.confirmTitle || 'Supprimer cet element ?';
                message.textContent = form.dataset.confirmMessage || 'Cette action est irreversible. Verifiez avant de continuer.';
                submitLabel.textContent = form.dataset.confirmButton || 'Oui, supprimer';
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                document.body.classList.add('overflow-hidden');
            };

            document.addEventListener('submit', function (event) {
                const form = event.target;

                if (!(form instanceof HTMLFormElement) || !form.matches('form[data-confirm-delete]')) {
                    return;
                }

                if (form.dataset.confirmed === 'true') {
                    form.dataset.confirmed = 'false';

                    return;
                }

                event.preventDefault();
                openModal(form);
            }, true);

            submitButton.addEventListener('click', function () {
                if (!pendingForm) {
                    return;
                }

                const form = pendingForm;
                closeModal();
                form.dataset.confirmed = 'true';

                if (typeof form.requestSubmit === 'function') {
                    form.requestSubmit();

                    return;
                }

                form.submit();
            });

            cancelButton.addEventListener('click', closeModal);

            modal.addEventListener('click', function (event) {
                if (event.target === modal) {
                    closeModal();
                }
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
                    closeModal();
                }
            });
        });
    })();
</script>
