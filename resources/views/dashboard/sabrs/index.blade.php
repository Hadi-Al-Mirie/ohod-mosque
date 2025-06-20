@extends('dashboard.layouts.app')
@section('title', 'السبر')
@section('content')
    <div class="container mt-5">
        @include('dashboard.layouts.alert')
        <h1 class="h3 mb-4 fw-bold text-center"
            style="font-family: 'IBMPlexSansArabic', sans-serif; font-size: 2.2rem; text-shadow: 1px 1px 2px rgba(0,0,0,0.1); color: var(--bs-primary);">
            أرشيف السبر
        </h1>
        <div class="mb-4">
            <div class="row g-3">
                <div class="col-12 col-lg-8">
                    <div class="mb-4">
                        <div class="row g-3">
                            <div class="col-12 col-lg-6">
                                <button class="btn btn-secondary mb-3" data-bs-toggle="modal" data-bs-target="#filterModal">
                                    <i class="fas fa-filter me-2"></i> خيارات البحث
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Filter Modal --}}
                    <div class="modal fade" id="filterModal" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 shadow-lg">
                                <div class="modal-header bg-primary text-white">
                                    <h5 class="modal-title w-100 text-center text-white">
                                        <i class="fas fa-filter me-2"></i> فلاتر البحث
                                    </h5>
                                    <button type="button" class="btn-close btn-close-white"
                                        data-bs-dismiss="modal"></button>
                                </div>
                                <form id="recitation-filter-form" method="GET" action="{{ route('admin.sabrs.index') }}">
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">اسم الطالب</label>
                                            <input type="text" name="student_name" value="{{ request('student_name') }}"
                                                class="form-control" placeholder="🔍 اسم الطالب">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">اسم الأستاذ</label>
                                            <input type="text" name="teacher_name" value="{{ request('teacher_name') }}"
                                                class="form-control" placeholder="🔍 اسم الأستاذ">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">النتيجة</label>
                                            <select name="result" class="form-select">
                                                <option value="">— اختر النتيجة —</option>
                                                @foreach ($settings as $s)
                                                    <option value="{{ $s->name }}"
                                                        {{ request('result') == $s->name ? 'selected' : '' }}>
                                                        {{ $s->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="row g-2">
                                            <div class="col">
                                                <label class="form-label fw-bold">من تاريخ</label>
                                                <input type="date" name="date_from" value="{{ request('date_from') }}"
                                                    class="form-control">
                                            </div>
                                            <div class="col">
                                                <label class="form-label fw-bold">إلى تاريخ</label>
                                                <input type="date" name="date_to" value="{{ request('date_to') }}"
                                                    class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer d-flex justify-content-between">
                                        <button type="submit" form="recitation-filter-form" class="btn btn-primary">
                                            <i class="fas fa-search me-1"></i> تطبيق
                                        </button>
                                        <a href="{{ route('admin.sabrs.index') }}" class="btn btn-danger">
                                            <i class="fas fa-times me-1"></i> مسح الفلاتر
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <div class="card shadow-lg border-0">
            <div class="card-body p-0">
                <div class="table-responsive overflow-x-auto overflow-y-visible">
                    <table class="table table-hover table-striped table-bordered mb-0">
                        <thead class="bg-gradient-primary text-white">
                            <tr>
                                <th><input type="checkbox" id="select-all" /></th>
                                <th class="py-3"><i class="fas fa-user me-2"></i> الطالب</th>
                                <th class="py-3"><i class="fas fa-chalkboard-teacher me-2"></i> الأستاذ</th>
                                <th class="py-3"><i class="fa-solid fa-file"></i> التاريخ</th>
                                <th class="py-3"><i class="fas fa-star me-2"></i> النتيجة</th>
                                <th class="py-3"><i class="fas fa-eye me-2"></i> الخيارات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($sabrs as $sabr)
                                <tr class="hover-lift">
                                    <td>
                                        <input type="checkbox" name="ids[]" value="{{ $sabr->id }}"
                                            class="row-checkbox" form="bulk-delete-form" />
                                    </td>
                                    <td class="align-middle">
                                        {{ $sabr->student->user->name }}
                                    </td>
                                    <td class="align-middle">
                                        @if ($sabr->creator->role_id == 1)
                                            <span class="badge bg-primary">
                                                <i class="fas fa-user-shield me-2"></i> المشرف
                                            </span>
                                        @else
                                            {{ $sabr->creator->name }}
                                        @endif
                                    </td>
                                    <td class="align-middle fw-bold text-info">
                                        {{ \Carbon\Carbon::parse($sabr->created_at)->format('Y/m/d') }}
                                    </td>
                                    @php
                                        // maps from bucket-name to colors/icons
                                        $colorMap = [
                                            'ممتاز' => 'success',
                                            'جيد جداً' => 'primary',
                                            'جيد' => 'info',
                                            'إعادة' => 'warning',
                                        ];
                                        $iconMap = [
                                            'ممتاز' => 'fas fa-medal',
                                            'جيد جداً' => 'fas fa-thumbs-up',
                                            'جيد' => 'fas fa-thumbs-up',
                                            'إعادة' => 'fa-solid fa-triangle-exclamation',
                                        ];

                                        // pull directly from our joined column:
                                        $name = $sabr->result_name ?? 'غير معروف';
                                        $color = $colorMap[$name] ?? 'secondary';
                                        $icon = $iconMap[$name] ?? 'fas fa-question-circle';
                                    @endphp

                                    <td class="align-middle">
                                        <span class="badge bg-{{ $color }} rounded-pill fs-6">
                                            <i class="{{ $icon }} me-1"></i> {{ $name }}
                                        </span>
                                    </td>
                                    <td class="align-middle">
                                        <a href="{{ route('admin.sabrs.show', $sabr->id) }}"
                                            class="btn btn-sm btn-primary hover-scale">
                                            <i class="fas fa-external-link-alt me-2"></i> التفاصيل
                                        </a>
                                        <!-- Toggle-Final Form -->
                                        <form method="POST" action="{{ route('admin.sabrs.toggleFinal', $sabr) }}"
                                            class="d-inline-block ms-1 toggle-final-form">
                                            @csrf
                                            @method('PATCH')

                                            <button type="button" class="btn btn-sm btn-secondary toggle-final-btn">
                                                <i id="toggle_iconn"
                                                    class="fas {{ $sabr->is_final ? 'fa-lock-open' : 'fa-lock' }}"></i>
                                                {{ $sabr->is_final ? 'السماح بالإعادة' : 'تثبيت نهائي' }}
                                            </button>
                                            <input type="hidden" name="confirm_toggle" id="confirm_toggle"
                                                value="0">
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- Bulk Delete Form -->
        <form id="bulk-delete-form" class="bulk-delete-form" method="POST"
            action="{{ route('admin.sabrs.bulkDestroy') }}">
            @csrf
            @method('DELETE')
            <div class="mt-3">
                <button type="button" id="bulk-delete-btn" class="btn btn-danger" disabled>
                    حذف المحدد
                </button>
            </div>
        </form>
        <div class="mt-4 d-flex justify-content-center">
            {{ $sabrs->appends(request()->query())->links('pagination::bootstrap-5') }}
        </div>
    </div>

    <!-- Bulk Delete Confirmation Modal -->
    <div class="modal fade" id="bulkDeleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-danger">
                    <h5 class="modal-title w-100 text-white text-center"><i class="fas fa-exclamation-triangle me-2"></i>
                        تأكيد الحذف</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <span id="bulkDeleteMessage"></span>
                </div>
                <div class="modal-footer d-flex justify-content-between gap-3">
                    <button type="button" id="confirmbulkDelete" class="btn btn-danger">تأكيد</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toggle Final Confirmation Modal -->
    <div class="modal fade" id="toggleFinalModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-warning  text-center">
                    <h5 class="modal-title w-100 text-white text-center"><i class="fas fa-exclamation-triangle me-2"></i>
                        تأكيد </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <span id="toggleFinalMessage"></span>
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <button type="button" id="confirmToggleFinal" class="btn btn-primary float-end">تأكيد</button>
                    <button type="button" class="btn btn-secondary float-start" data-bs-dismiss="modal">إلغاء</button>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Bulk-delete elements
            const selectAll = document.getElementById('select-all');
            const rowCheckboxes = Array.from(document.querySelectorAll('.row-checkbox'));
            const deleteBtn = document.getElementById('bulk-delete-btn');
            const bulkForm = document.querySelector('form.bulk-delete-form');
            const bulkModal = new bootstrap.Modal(
                document.getElementById('bulkDeleteModal')
            );

            const bulkMsg = document.getElementById('bulkDeleteMessage');
            const bulkConfirm = document.getElementById('confirmbulkDelete');

            function updateDeleteBtn() {
                deleteBtn.disabled = !rowCheckboxes.some(cb => cb.checked);
            }

            selectAll.addEventListener('change', () => {
                rowCheckboxes.forEach(cb => cb.checked = selectAll.checked);
                updateDeleteBtn();
            });
            rowCheckboxes.forEach(cb => {
                cb.addEventListener('change', () => {
                    selectAll.checked = rowCheckboxes.every(x => x.checked);
                    updateDeleteBtn();
                });
            });

            deleteBtn.addEventListener('click', () => {
                bulkMsg.textContent = 'هل أنت متأكد من حذف بيانات السبر المحددة؟ لا يمكن التراجع.';
                bulkModal.show();
            });

            bulkConfirm.addEventListener('click', () => {
                bulkForm.submit();
                bulkModal.hide();
            });

            // Toggle-final elements
            const toggleModal = new bootstrap.Modal(
                document.getElementById('toggleFinalModal')
            );
            console.log(toggleModal);
            const toggleMsg = document.getElementById('toggleFinalMessage');
            let currentForm, isFinal;

            document.querySelectorAll('.toggle-final-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    const icon = btn.querySelector('#toggle_iconn');
                    if (!icon) {
                        console.error('No <i> found inside button', btn);
                        return;
                    }

                    currentForm = btn.closest('form.toggle-final-form');
                    isFinal = icon.classList.contains('fa-lock-open');

                    toggleMsg.textContent = isFinal ?
                        'سيصبح هذا السبر قابلًا للإعادة  , هل أنت متأكد ؟' :
                        'سيتم حفظ هذا السبر كـ "نهائي" , هل أنت متأكد ؟';

                    currentForm
                        .querySelector('input[name="confirm_toggle"]')
                        .value = 0;

                    toggleModal.show();
                });
            });

            document.getElementById('confirmToggleFinal').addEventListener('click', () => {
                currentForm.querySelector('#confirm_toggle').value = 1;
                currentForm.submit();
                toggleModal.hide();
            });
        });
    </script>
@endsection
