@if (session('success') || session('danger'))
    @php
        $type = session('success') ? 'success' : 'danger';
        $message = session($type);
        $alertClass = $type === 'success' ? 'alert alert-success-custom' : 'alert alert-danger-custom';
    @endphp
    <div class="{{ $alertClass }} custom-alert" role="alert">
        <div class="d-flex align-items-center">
            <div class="me-3">{{ $message }}</div>
        </div>
        <button type="button" class="close-btn" aria-label="Close">&times;</button>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const alertEl = document.querySelector('.custom-alert');
            if (!alertEl) return;
            const closeBtn = alertEl.querySelector('.close-btn');
            closeBtn.addEventListener('click', () => {
                alertEl.classList.add('hide');
                setTimeout(() => alertEl.remove(), 300);
            });
            setTimeout(() => {
                alertEl.classList.add('hide');
                setTimeout(() => alertEl.remove(), 300);
            }, 7500);
        });
    </script>
@endif
