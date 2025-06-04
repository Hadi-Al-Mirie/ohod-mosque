@extends('dashboard.layouts.app')
@section('title', 'تفاصيل الملاحظة')
@section('content')
    <div class="container mt-5" dir="rtl">
        <!-- Page Header -->
        <div class="text-center mb-5">
            <h1 class="display-6 fw-bold text-success">
                <i class="fas fa-clipboard-list me-2"></i> تفاصيل الملاحظة
            </h1>
            <p class="text-black">تفاصيل ملاحظة الطالب مع القيم المسجلة</p>
        </div>
        @include('dashboard.layouts.alert')
        <!-- Note Details Card -->
        <div class="card shadow-sm border-success mb-5">
            <div class="card-header bg-success text-white text-center">
                <h5 class="mb-0 text-white"><i class="fas fa-info-circle me-2"></i> المعلومات الأساسية</h5>
            </div>

            <div class="card-body bg-light-gray p-4">
                <!-- Reason Section -->
                <div class="row g-4 mb-5">
                    <div class="col-12">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-success">
                                <i class="fas fa-comment-dots me-2"></i> سبب الملاحظة
                            </label>
                            <div class="bg-white p-3 rounded-3 shadow-sm">
                                {{ $note->reason }} ({{ $note->type == 'negative' ? ' سلبية ' : ' إيجابية ' }})
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-success">
                                <i class="fas fa-chalkboard-teacher me-2"></i> المسؤول
                            </label>
                            <div class="bg-white p-3 rounded-3 shadow-sm fw-bold">
                                @if ($note->creator->role_id == 1)
                                    <span class="badge bg-success rounded-pill">المشرف</span>
                                @else
                                    {{ $note->creator->name }}
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-success">
                                <i class="fas fa-user-graduate me-2"></i> الطالب
                            </label>
                            <div class="bg-white p-3 rounded-3 shadow-sm fw-bold">
                                {{ $note->student->user->name }}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-success">
                                <i class="fas fa-coins me-2"></i> القيمة
                            </label>
                            <div class="bg-white p-3 rounded-3 shadow-sm fw-bold">
                                {{ $note->value ?? 'لم تحدد بعد' }}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-success">
                                <i class="fas fa-info-circle me-2"></i> الحالة
                            </label>
                            <div class="">
                                @if ($note->status == 'pending')
                                    <div class="bg-white p-3 rounded-3 shadow-sm fw-bold">
                                        قيد الانتظار
                                    </div>
                                @elseif ($note->status == 'approved')
                                    <div class="bg-white p-3 rounded-3 shadow-sm fw-bold">
                                        تم الموافقة عليها
                                    </div>
                                @else
                                    <div class="bg-white p-3 rounded-3 shadow-sm fw-bold">
                                        مرفوضة
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Action Buttons -->
                <div class="d-flex justify-content-center align-items-center gap-5 mt-4">
                    @if ($note->status == 'pending')
                        <a href="{{ route('admin.notes.requests') }}" class="btn btn-secondary btn-lg px-4 shadow-sm">
                            <i class="fas fa-arrow-left me-2"></i> عودة للقائمة
                        </a>
                    @elseif($note->status == 'approved')
                        <a href="{{ route('admin.notes.index') }}" class="btn btn-secondary btn-lg px-4 shadow-sm">
                            <i class="fas fa-arrow-left me-2"></i> عودة للقائمة
                        </a>
                    @endif

                    <form action="{{ route('admin.notes.destroy', $note->id) }}" method="POST" class="d-inline-block">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-lg px-4 shadow-sm"
                            onclick="return confirm('هل أنت متأكد من الحذف؟');">
                            <i class="fas fa-trash-alt me-2"></i> حذف
                        </button>
                    </form>
                    @if ($note->status == 'pending')
                        <form action="{{ route('admin.notes.approve', $note->id) }}" method="POST"
                            class="approve-note-form d-inline-block">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="value" id="note_value">
                            <button type="button" class="approve-note-btn btn btn-success btn-lg px-4 shadow-sm">
                                <i class="fas fa-check-circle me-2"></i> الموافقة
                            </button>
                        </form>
                    @endif


                </div>
            </div>
        </div>


    </div>

    <!-- Value Modal -->
    <div class="modal fade" id="noteValueModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-success text-white d-flex align-items-center justify-content-between">
                    <h5 class="modal-title text-white w-100 text-center">
                        <i class="fas fa-coins me-2"></i> إدخال قيمة الملاحظة
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body bg-light-gray">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">القيمة (رقم موجب)</label>
                        <input type="number" id="valueInput" class="form-control" min="1" value="5">
                    </div>
                    <div id="errorMessage" class="alert alert-danger d-none"></div>
                </div>
                <div class="modal-footer bg-light-gray d-flex justify-content-between">
                    <button type="button" class="btn btn-success" id="confirmValue">تأكيد</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const modal = new bootstrap.Modal('#noteValueModal');
            let currentForm = null;

            document.querySelectorAll('.approve-note-btn').forEach(btn => {
                btn.addEventListener('click', e => {
                    currentForm = btn.closest('form');
                    modal.show();
                });
            });

            document.querySelector('#confirmValue').addEventListener('click', () => {
                const val = document.querySelector('#valueInput').value;
                const errorMessage = document.querySelector('#errorMessage');

                errorMessage.classList.add('d-none');

                const numeric = parseInt(val, 10);
                if (isNaN(numeric) || numeric <= 0) {
                    errorMessage.textContent = '❗ الرجاء إدخال رقم صحيح موجب';
                    errorMessage.classList.remove('d-none');
                    return;
                }

                currentForm.querySelector('#note_value').value = numeric;
                currentForm.submit();
                modal.hide();
            });
        });
    </script>
@endsection
