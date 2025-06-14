@extends('dashboard.layouts.app')

@section('title', 'تعديل سبر الأوقاف')
@section('content')
    <div class="container mt-5">
        @include('dashboard.layouts.alert')

        <h1 class="h3 mb-4 fw-bold text-center">تعديل سبر الأوقاف</h1>

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
                            <label class="form-label fw-bold">الأستاذ</label>
                            <input type="text" class="form-control" value="{{ $awqaf->creator->name }}" disabled>
                        </div>


                        @php
                            // Either use the Model accessor:
                            // use App\Models\Awqaf;
                            // $typeLabels = Awqaf::typeLabels();

                            // Or define inline:
                            $typeLabels = [
                                'nomination' => 'ترشيح',
                                'retry' => 'إعادة محاولة',
                                'rejected' => 'مرفوض',
                                'not_attend' => 'لم يحضر',
                                'success' => 'نجاح',
                            ];
                        @endphp
                        {{-- Result (only for retry & success) --}}
                        <div class="mb-4 col-md-6" id="resultField" style="display:none;">
                            <label for="result" class="form-label fw-bold">النتيجة (0-100)</label>
                            <input type="number" name="result" id="result"
                                class="form-control @error('result') is-invalid @enderror"
                                value="{{ old('result', $awqaf->result) }}" step="any" min="0" max="100">
                            @error('result')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        {{-- Type --}}
                        <div class="mb-4 col-md-6">
                            <label for="type" class="form-label fw-bold">الحالة</label>
                            <select name="type" id="type" class="form-select @error('type') is-invalid @enderror">
                                <option value="">-- اختر الحالة --</option>
                                @foreach ($typeLabels as $value => $label)
                                    <option value="{{ $value }}"
                                        {{ old('type', $awqaf->type) === $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    {{-- Actions --}}
                    <div class="d-flex justify-content-between mt-5">
                        <button type="submit" class="btn btn-primary p-2">
                            <i class="fas fa-save me-1"></i> حفظ التغييرات
                        </button>
                        <a href="{{ route('admin.awqafs.index') }}" class="btn btn-secondary p-2">
                            <i class="fas fa-arrow-left me-1"></i> إلغاء
                        </a>

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
