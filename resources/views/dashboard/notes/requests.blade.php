@extends('dashboard.layouts.app')
@section('title', 'طلبات الملاحظات')
@section('content')
    <div class="container mt-5">
        @include('dashboard.layouts.alert')

        <!-- Page Header -->
        <h1 class="h3 mb-4 fw-bold text-center"
            style="font-family: 'IBMPlexSansArabic', sans-serif; font-size: 2.2rem; text-shadow: 1px 1px 2px rgba(0,0,0,0.1); color: var(--bs-primary);">
            طلبات الملاحظات (قيد الانتظار)
        </h1>

        <!-- Search and Actions Section -->
        <div class="mb-4">
            <div class="row g-3">
                <div class="col-12 col-lg-8">
                    <form method="GET" action="{{ route('admin.notes.requests') }}">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control search-input rounded-start"
                                placeholder="🔍 ابحث بالاسم أو العنوان" value="{{ request('search') }}">
                            <button class="btn btn-primary hover-scale" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Notes Table -->
        <div class="card shadow-lg border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped table-bordered mb-0">
                        <thead class="bg-gradient-primary text-white">
                            <tr>
                                <th class="py-3"><i class="fas fa-user me-2"></i> الطالب</th>
                                <th class="py-3"><i class="fas fa-chalkboard-teacher me-2"></i> الأستاذ</th>
                                <th class="py-3"><i class="fa-solid fa-file"></i>النوع</th>
                                <th class="py-3"><i class="fa-solid fa-file me-2"></i> السبب</th>
                                <th class="py-3"><i class="fa-solid fa-file"></i>التاريخ</th>
                                <th class="py-3"><i class="fas fa-eye me-2"></i> التحكم</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($notes as $note)
                                <tr class="hover-lift">
                                    <td class="align-middle">
                                        {{ $note->student->user->name }}
                                    </td>
                                    <td class="align-middle">
                                        @if ($note->creator->role_id == 1)
                                            <span class="badge bg-primary">
                                                <i class="fas fa-user-shield me-2"></i> المشرف
                                            </span>
                                        @else
                                            {{ $note->creator->name }}
                                        @endif
                                    </td>
                                    <td class="align-middle fw-bold text-info">
                                        @if ($m = $note->type == 'positive')
                                            أيجابية
                                        @else
                                            سلبية
                                        @endif
                                    </td>
                                    <td class="align-middle fw-bold text-info">
                                        {{ $note->reason ?? '---' }}
                                    </td>
                                    <td class="align-middle fw-bold text-info">
                                        {{ \Carbon\Carbon::parse($note->created_at)->format('Y/m/d') }}
                                    </td>
                                    <td class="align-middle">
                                        <form action="{{ route('admin.notes.approve', $note->id) }}" method="POST"
                                            class="approve-note-form d-inline-block">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="value" id="note_value">
                                            <button type="button" class="btn btn-sm btn-primary hover-scale">
                                                <i class="fas fa-check-circle me-2"></i> الموافقة
                                            </button>
                                        </form>
                                        <a href="{{ route('admin.notes.show', $note->id) }}"
                                            class="btn btn-sm btn-secondary hover-scale">
                                            <i class="fas fa-external-link-alt me-2"></i> التفاصيل
                                        </a>
                                        <form action="{{ route('admin.notes.destroy', $note->id) }}" method="POST"
                                            class="d-inline-block delete-note-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-danger hover-scale show-delete-modal">
                                                <i class="fas fa-trash-alt me-2"></i> حذف
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div class="mt-4 justify-content-center">
            {{ $notes->appends(request()->query())->links('pagination::bootstrap-5') }}
        </div>
    </div>


    <!-- Delete Modal -->
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title w-100 text-center text-white">
                        <i class="fas fa-trash-alt me-2"></i> تأكيد الحذف
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body bg-light-gray text-center">
                    <p class="fw-bold text-dark fs-5">هل أنت متأكد أنك تريد حذف هذه الملاحظة؟</p>
                    <div id="deleteModalError" class="alert alert-danger d-none"></div>
                </div>
                <div class="modal-footer justify-content-between bg-light-gray">
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">نعم، احذف</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const deleteModal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));
            let currentDeleteForm = null;

            document.querySelectorAll('.show-delete-modal').forEach(button => {
                button.addEventListener('click', e => {
                    currentDeleteForm = button.closest('form');
                    deleteModal.show();
                });
            });
            document.getElementById('confirmDeleteBtn').addEventListener('click', () => {
                if (currentDeleteForm) {
                    currentDeleteForm.submit();
                    deleteModal.hide();
                }
            });
        });
    </script>
@endsection
