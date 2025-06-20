@extends('dashboard.layouts.app')
@section('title', 'الملاحظات')
@section('content')
    <div class="container mt-5">
        @include('dashboard.layouts.alert')
        @php
            use Illuminate\Support\Str;
        @endphp
        <h1 class="h3 mb-4 fw-bold text-center"
            style="font-family: 'IBMPlexSansArabic', sans-serif; font-size: 2.2rem; text-shadow: 1px 1px 2px rgba(0,0,0,0.1); color: var(--bs-primary);">
            أرشيف الملاحظات
        </h1>

        <!-- Filters & Add New -->
        <div class="mb-4">
            <div class="row g-3 align-items-center">
                {{-- Filter button on the left --}}
                <div class="col-auto">
                    <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#filterModal">
                        <i class="fas fa-filter me-2"></i> خيارات البحث
                    </button>
                </div>

                {{-- Add‑note button on the right --}}
                <div class="col-auto ms-auto">
                    <a href="{{ route('admin.notes.create') }}" class="btn btn-success hover-scale">
                        <i class="fas fa-plus me-2"></i> إضافة ملاحظة جديدة
                    </a>
                </div>
            </div>
        </div>

        {{-- Filter Modal --}}
        <div class="modal fade" id="filterModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title w-100 text-center">
                            <i class="fas fa-filter me-2"></i> فلاتر البحث
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="GET" action="{{ route('admin.notes.index') }}">
                        <div class="modal-body">
                            {{-- Student Name --}}
                            <div class="mb-3">
                                <label class="form-label fw-bold">اسم الطالب</label>
                                <input type="text" name="student_name" value="{{ request('student_name') }}"
                                    class="form-control" placeholder="🔍 اسم الطالب">
                            </div>

                            {{-- Teacher Name --}}
                            <div class="mb-3">
                                <label class="form-label fw-bold">اسم الأستاذ</label>
                                <input type="text" name="teacher_name" value="{{ request('teacher_name') }}"
                                    class="form-control" placeholder="🔍 اسم الأستاذ">
                            </div>

                            {{-- Type --}}
                            <div class="mb-3">
                                <label class="form-label fw-bold">النوع</label>
                                <select name="type" class="form-select">
                                    <option value="">— اختر النوع —</option>
                                    <option value="positive">إيجابية</option>
                                    <option value="negative">سلبية</option>
                                </select>
                            </div>

                            {{-- Date --}}
                            <div class="mb-3">
                                <label class="form-label fw-bold">التاريخ</label>
                                <input type="date" name="date" value="{{ request('date') }}" class="form-control">
                            </div>
                        </div>

                        <div class="modal-footer d-flex justify-content-between">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-1"></i> تطبيق
                            </button>
                            <a href="{{ route('admin.notes.index') }}" class="btn btn-danger">
                                <i class="fas fa-times me-1"></i> مسح الفلاتر
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>


        <!-- Notes Table -->
        <div class="card shadow-lg border-0">
            <div class="card-body p-0">
                <div class="table-responsive overflow-x-auto overflow-y-visible">
                    <table class="table table-hover table-striped table-bordered mb-0">
                        <thead class="bg-gradient-primary text-white">
                            <tr>
                                <th><input type="checkbox" id="select-all" /></th>
                                <th class="py-3"><i class="fas fa-user me-2"></i> الطالب</th>
                                <th class="py-3"><i class="fas fa-chalkboard-teacher me-2"></i> الأستاذ</th>
                                <th class="py-3"><i class="fa-solid fa-file"></i>النوع</th>
                                <th class="py-3"><i class="fa-solid fa-file"></i> السبب</th>
                                <th class="py-3"><i class="fa-solid fa-file"></i> التاريخ</th>
                                <th class="py-3"><i class="fas fa-eye me-2"></i> الخيارات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($notes as $note)
                                <tr class="hover-lift">
                                    <td>
                                        <input type="checkbox" name="ids[]" value="{{ $note->id }}"
                                            class="row-checkbox" form="bulk-delete-form" />
                                    </td>
                                    <td class="align-middle">
                                        {{ $note->student->user->name }}
                                    </td>
                                    <td class="align-middle">
                                        @if ($note->creator->role_id == 1)
                                            <span class="badge bg-primary">
                                                <i class="fas fa-user-shield me-2"></i> المشرف
                                            </span>
                                        @else
                                            <i class="fas fa-user-tie me-2"></i>
                                            {{ $note->creator->name }}
                                        @endif
                                    </td>
                                    <td class="align-middle text-info">
                                        @if ($m = $note->type == 'positive')
                                            أيجابية
                                        @else
                                            سلبية
                                        @endif
                                    </td>
                                    <td class="align-middle fw-bold text-info">
                                        {{ $note->reason ? Str::limit($note->reason, 30, '  ....') : '---' }}
                                    </td>
                                    <td class="align-middle fw-bold text-info">
                                        {{ \Carbon\Carbon::parse($note->created_at)->format('Y/m/d') }}
                                    </td>
                                    <td class="align-middle">
                                        <a href="{{ route('admin.notes.show', $note->id) }}"
                                            class="btn btn-sm btn-primary hover-scale">
                                            <i class="fas fa-external-link-alt me-2"></i> التفاصيل
                                        </a>
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
            action="{{ route('admin.notes.bulkDestroy') }}">
            @csrf
            @method('DELETE')
            <div class="mt-3">
                <button type="button" id="bulk-delete-btn" class="btn btn-danger" disabled>
                    حذف المحدد
                </button>
            </div>
        </form>
        <div class="mt-4 justify-content-center">
            {{ $notes->appends(request()->query())->links('pagination::bootstrap-5') }}
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
                bulkMsg.textContent = 'هل أنت متأكد من حذف الملاحظات المحددة؟ لا يمكن التراجع.';
                bulkModal.show();
            });

            bulkConfirm.addEventListener('click', () => {
                bulkForm.submit();
                bulkModal.hide();
            });
        });
    </script>
@endsection
