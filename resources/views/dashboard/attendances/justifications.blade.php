@extends('dashboard.layouts.app')
@section('title', 'طلبات تبرير الغياب')
@section('content')
    <div class="container mt-5" dir="rtl">
        <!-- Header -->
        <div class="mb-4">
            <h1 class="h3 mb-5 fw-bold text-center"
                style="font-family:'IBMPlexSansArabic',sans-serif; font-size:2.2rem; text-shadow:1px 1px 2px rgba(0,0,0,0.1); color:var(--bs-primary);">
                طلبات تبرير الغياب قيد الانتظار
            </h1>
        </div>

        @include('dashboard.layouts.alert')

        @php
            use Illuminate\Support\Str;
        @endphp
        <div class="mb-5 mt-4 d-flex justify-content-end">
            <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#filterModal">
                <i class="fas fa-filter me-2"></i> خيارات البحث
            </button>
        </div>

        {{-- Filter Modal --}}
        <div class="modal fade" id="filterModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title w-100 text-center text-white">
                            <i class="fas fa-filter me-2"></i> فلاتر البحث
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="recitation-filter-form" method="GET"
                        action="{{ route('admin.attendances.justifications.index') }}">
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
                            <div class="row g-2">
                                <div class="col">
                                    <label class="form-label fw-bold">التاريخ</label>
                                    <input type="date" name="date" value="{{ request('date') }}" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer d-flex justify-content-between">
                            <a href="{{ route('admin.attendances.justifications.index') }}" class="btn btn-danger">
                                <i class="fas fa-times me-1"></i> مسح الفلاتر
                            </a>
                            <button type="submit" form="recitation-filter-form" class="btn btn-primary">
                                <i class="fas fa-search me-1"></i> تطبيق
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- Table Section -->
        <div class="card shadow-lg">
            <div class="card-body p-0">
                <div class="table-responsive overflow-x-auto overflow-y-visible">
                    <table class="table table-hover table-striped table-bordered border-top-0">
                        <thead class="bg-gradient-primary text-white">
                            <tr>
                                <th><input type="checkbox" id="select-all" /></th>
                                <th class="py-3"><i class="fas fa-user me-2"></i> الطالب</th>
                                <th class="py-3"><i class="fas fa-calendar-day me-2"></i> التاريخ</th>
                                <th class="py-3"><i class="fas fa-file-alt me-2"></i> المبرر</th>
                                <th class="py-3"><i class="fas fa-user-tie me-2"></i> الأستاذ</th>
                                <th class="py-3"><i class="fas fa-tasks me-2"></i> الخيارات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($requests as $r)
                                <tr class="align-middle">
                                    <td>
                                        <input type="checkbox" name="ids[]" value="{{ $r->id }}"
                                            class="row-checkbox" form="bulk-delete-form" />
                                    </td>
                                    <td class="ps-4">
                                        {{ $r->attendance->student->user->name }}
                                    </td>
                                    <td class="text-center fs-5">
                                        {{ $r->attendance->attendance_date->format('Y/m/d') }}
                                    </td>
                                    <td class="text-center">
                                        {{ $r->justification ? Str::limit($r->justification, 30, '  ....') : '---' }}
                                    </td>
                                    <td class="text-center">{{ $r->requester->name }}</td>
                                    <td class="align-middle">
                                        <form action="{{ route('admin.attendances.justifications.approve', $r) }}"
                                            method="POST" class="d-inline">
                                            @csrf @method('PATCH')
                                            <button class="btn btn-success btn-sm hover-scale">
                                                <i class="fas fa-check-circle me-2"></i> قبول
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.attendances.justifications.reject', $r) }}"
                                            method="POST" class="d-inline">
                                            @csrf @method('PATCH')
                                            <button class="btn btn-danger btn-sm hover-scale">
                                                <i class="fas fa-times-circle me-2"></i> رفض
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center bg-secondary py-5">
                                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">لا توجد طلبات حالياً</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- Bulk Delete Form -->
        <form id="bulk-delete-form" class="bulk-delete-form" method="POST"
            action="{{ route('admin.attendances.justifications.bulkDestroy') }}">
            @csrf
            @method('DELETE')
            <div class="mt-3">
                <button type="button" id="bulk-delete-btn" class="btn btn-danger" disabled>
                    حذف المحدد
                </button>
            </div>
        </form>
        @if ($requests->hasPages())
            <div class="mt-4 d-flex justify-content-center">
                {{ $requests->links('pagination::bootstrap-5') }}
            </div>
        @endif
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
                bulkMsg.textContent = 'هل أنت متأكد من حذف طلبات تبرير الغياب المحددة؟ لا يمكن التراجع.';
                bulkModal.show();
            });

            bulkConfirm.addEventListener('click', () => {
                bulkForm.submit();
                bulkModal.hide();
            });
        });
    </script>
@endsection
