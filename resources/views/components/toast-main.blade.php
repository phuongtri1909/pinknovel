<div class="toast-container position-fixed bottom-0 end-0 p-3">
    <div id="liveToast" class="toast align-items-center d-none" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body"></div>
            <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        function showToast(message, type = 'success') {
            const toast = $('#liveToast');
            const bgClass = type === 'success' ? 'bg-success' : 'bg-danger';

            toast.removeClass('d-none bg-success bg-danger');
            toast.find('.toast-body').text(message);
            toast.addClass(`${bgClass} text-white`);

            const bsToast = new bootstrap.Toast(toast);
            bsToast.show();
        }
    </script>
@endpush

@push('scripts-main')
    <script>
        function showToast(message, type = 'success') {
            const toast = $('#liveToast');
            const bgClass = type === 'success' ? 'bg-success' : 'bg-danger';

            toast.removeClass('d-none bg-success bg-danger');
            toast.find('.toast-body').text(message);
            toast.addClass(`${bgClass} text-white`);

            const bsToast = new bootstrap.Toast(toast);
            bsToast.show();
        }
    </script>
@endpush
