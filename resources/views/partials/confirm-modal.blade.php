<div class="modal fade" id="confirmActionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title h5" id="confirmActionTitle">Confirm action</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="confirmActionMessage">Are you sure?</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmActionButton">Continue</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const modalEl = document.getElementById('confirmActionModal');
    if (!modalEl) return;
    const modal = new bootstrap.Modal(modalEl);
    const title = document.getElementById('confirmActionTitle');
    const message = document.getElementById('confirmActionMessage');
    const button = document.getElementById('confirmActionButton');
    let form = null;

    document.querySelectorAll('[data-confirm-submit]').forEach((trigger) => {
        trigger.addEventListener('click', () => {
            form = trigger.closest('form') || (trigger.form ? trigger.form : (trigger.getAttribute('form') ? document.getElementById(trigger.getAttribute('form')) : null));
            if (form && trigger.name) {
                form.querySelectorAll('input[data-confirm-value]').forEach((input) => input.remove());
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = trigger.name;
                input.value = trigger.value;
                input.dataset.confirmValue = 'true';
                form.appendChild(input);
            }
            title.textContent = trigger.dataset.confirmTitle || 'Confirm action';
            message.textContent = trigger.dataset.confirmMessage || 'Are you sure?';
            button.textContent = trigger.dataset.confirmButton || 'Continue';
            button.className = 'btn ' + (trigger.dataset.confirmClass || 'btn-danger');
            modal.show();
        });
    });

    button.addEventListener('click', () => {
        if (form) form.submit();
    });
});
</script>
@endpush
