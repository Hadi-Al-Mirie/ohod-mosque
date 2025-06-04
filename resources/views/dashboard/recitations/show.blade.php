@php
    $lvlId = $recitation->student->level_id;
@endphp
@extends('dashboard.layouts.app')
@section('title', 'تفاصيل التسميع')
@section('content')
    <div class="container mt-5" dir="rtl">
        <!-- Page Header -->
        <div class="text-center mb-5">
            <h1 class="display-6 fw-bold text-success">
                <i class="fas fa-book-open me-2"></i> تفاصيل التسميع
            </h1>
            <p class="text-black">تفاصيل التسميع مع الأخطاء المسجلة</p>
        </div>
        @include('dashboard.layouts.alert')
        <!-- Basic Info Card -->
        <div class="card shadow-sm border-success mb-5">
            <div class="card-header bg-success text-white text-center">
                <h5 class="mb-0 text-white"><i class="fas fa-info-circle me-2"></i> المعلومات الأساسية</h5>
            </div>
            <div class="card-body bg-light-gray p-4">
                <div class="row g-4">
                    <!-- Student Name -->
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-success">
                                <i class="fas fa-user-graduate me-2"></i> اسم الطالب
                            </label>
                            <div class="bg-white p-3 rounded-3 shadow-sm">
                                {{ $recitation->student->user->name }}
                            </div>
                        </div>
                    </div>

                    <!-- Teacher Name -->
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-success">
                                <i class="fas fa-chalkboard-teacher me-2"></i> الأستاذ
                            </label>
                            <div class="bg-white p-3 rounded-3 shadow-sm">
                                @if ($recitation->creator->role_id == 1)
                                    المشرف
                                @else
                                    {{ $recitation->creator->name }}
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Page -->
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-success">
                                <i class="fas fa-file-alt me-2"></i> الصفحة
                            </label>
                            <div class="bg-white p-3 rounded-3 shadow-sm">
                                {{ $recitation->page }}
                            </div>
                        </div>
                    </div>

                    <!-- Result -->
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-success">
                                <i class="fas fa-star me-2"></i> النتيجة
                            </label>
                            <div class="bg-white p-3 rounded-3 shadow-sm">
                                {{ $recitation->calculateResult() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mistakes Card -->
        <div class="card shadow-sm border-success mb-5">
            <div class="card-header bg-success text-white text-center">
                <h5 class="mb-0 text-white"><i class="fas fa-exclamation-triangle me-2"></i> تفاصيل الأخطاء</h5>
            </div>
            <div class="card-body bg-light-gray p-4">
                <div class="table-responsive rounded-3 shadow-sm">
                    <table class="table table-striped table-hover table-bordered mb-0">
                        <thead class="bg-success text-white">
                            <tr>
                                <th class="py-3"><i class="fas fa-bug me-2"></i> الخطأ</th>
                                <th class="py-3"><i class="fas fa-hashtag me-2"></i> القيمة</th>
                                <th class="py-3"><i class="fas fa-calculator me-2"></i> الكمية</th>
                                <th class="py-3 text-end"><i class="fas fa-coins me-2"></i> الإجمالي</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recitation->recitationMistakes as $m)
                                <tr>
                                    <td class="align-middle">{{ $m->mistake->name }}</td>
                                    <td class="align-middle">
                                        {{ $m->mistake->levels->firstWhere('id', $lvlId)->pivot->value ?? 0 }}</td>
                                    <td class="align-middle">{{ $m->quantity }}</td>
                                    <td class="align-middle text-end fw-bold text-success">
                                        {{ number_format($m->mistake->levels->firstWhere('id', $lvlId)->pivot->value * $m->quantity) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-warning-subtle">
                                <td colspan="3" class="text-end fw-bold">المجموع:</td>
                                <td class="text-end fw-bold text-danger">
                                    100 -
                                    {{ number_format(
                                        $recitation->recitationMistakes->sum(function ($item) use ($lvlId) {
                                            return $item->mistake->levels->firstWhere('id', $lvlId)->pivot->value * $item->quantity;
                                        }),
                                    ) }}
                                    =
                                    {{ 100 -
                                        number_format(
                                            $recitation->recitationMistakes->sum(function ($item) use ($lvlId) {
                                                return $item->mistake->levels->firstWhere('id', $lvlId)->pivot->value * $item->quantity;
                                            }),
                                        ) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="d-flex justify-content-between align-items-center mt-4">
            <div>
                <a href="{{ route('admin.recitations.index') }}" class="btn btn-secondary btn-lg px-4 shadow-sm">
                    <i class="fas fa-arrow-left me-2"></i> عودة للقائمة
                </a>
            </div>
            <div class="d-flex gap-3">
                <form action="{{ route('admin.recitations.destroy', $recitation->id) }}" method="POST"
                    class="delete-recitation-form d-inline-block">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn btn-danger btn-lg px-4 shadow-sm delete-recitation-btn">
                        <i class="fa-solid fa-trash me-2"></i> حذف
                    </button>
                </form>
            </div>
            <!-- Delete Confirmation Modal -->
            <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow-lg">
                        <div class="modal-header bg-danger text-white d-flex align-items-center justify-content-between">
                            <h5 class="modal-title  w-100 text-center text-white">
                                <i class="fas fa-exclamation-triangle me-2"></i> تأكيد الحذف
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body bg-light-gray text-center">
                            <p class="fs-5 mb-0">هل أنت متأكد أنك تريد حذف هذا التسميع؟</p>
                        </div>
                        <div class="modal-footer bg-light-gray d-flex justify-content-center gap-3">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                إلغاء
                            </button>
                            <button type="button" class="btn btn-danger" id="deleteConfirmBtn">
                                <i class="fas fa-trash me-1"></i> حذف
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
            let formToDelete = null;
            document.querySelectorAll('.delete-recitation-btn').forEach(btn => {
                btn.addEventListener('click', e => {
                    formToDelete = btn.closest('form');
                    deleteModal.show();
                });
            });
            document.getElementById('deleteConfirmBtn').addEventListener('click', () => {
                if (formToDelete) {
                    formToDelete.submit();
                    formToDelete = null;
                }
            });
        });
    </script>
@endsection
