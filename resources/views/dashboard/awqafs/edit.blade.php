@extends('dashboard.layouts.app')

@section('title', 'تعديل سبر الوقف')
@section('content')
    <div class="container mt-5">
        @include('dashboard.layouts.alert')

        <h1 class="h3 mb-4 fw-bold text-center">تعديل سبر الوقف</h1>

        <div class="card shadow-sm">
            <div class="card-body">
                <form action="{{ route('admin.awqafs.update', $awqaf->id) }}" method="POST">
                    @csrf @method('PUT')

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">الطالب</label>
                            <input type="text" class="form-control" value="{{ $awqaf->student->user->name }}" disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">المسجل</label>
                            <input type="text" class="form-control" value="{{ $awqaf->creator->name }}" disabled>
                        </div>
                    </div>

                    {{-- Type --}}
                    <div class="mb-4">
                        <label for="type" class="form-label fw-bold">الحالة</label>
                        <select name="type" id="type" class="form-select @error('type') is-invalid @enderror">
                            <option value="">-- اختر الحالة --</option>
                            <option value="nomination" {{ old('type', $awqaf->type) === 'nomination' ? 'selected' : '' }}>
                                ترشيح
                            </option>
                            <option value="retry" {{ old('type', $awqaf->type) === 'retry' ? 'selected' : '' }}>إعادة
                                محاولة</option>
                            <option value="not_attend" {{ old('type', $awqaf->type) === 'not_attend' ? 'selected' : '' }}>لم
                                يحضر
                            </option>
                            <option value="success" {{ old('type', $awqaf->type) === 'success' ? 'selected' : '' }}>نجاح
                            </option>
                        </select>
                        @error('type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Result (only for retry & success) --}}
                    <div class="mb-4" id="resultField" style="display:none;">
                        <label for="result" class="form-label fw-bold">النتيجة (0–100)</label>
                        <input type="number" name="result" id="result"
                            class="form-control @error('result') is-invalid @enderror"
                            value="{{ old('result', $awqaf->result) }}" step="any" min="0" max="100">
                        @error('result')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Actions --}}
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.awqafs.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i> إلغاء
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> حفظ التغييرات
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const typeSelect = document.getElementById('type');
            const resultFld = document.getElementById('resultField');
            const resultInput = document.getElementById('result');

            function toggleResult() {
                const show = ['success', 'retry'].includes(typeSelect.value);
                resultFld.style.display = show ? 'block' : 'none';
                if (show) {
                    resultInput.disabled = false;
                } else {
                    resultInput.value = '';
                    resultInput.disabled = true;
                }
            }

            typeSelect.addEventListener('change', toggleResult);
            toggleResult();
        });
    </script>
@endsection
