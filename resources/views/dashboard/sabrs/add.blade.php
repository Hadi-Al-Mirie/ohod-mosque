@extends('dashboard.layouts.app')

@section('title', 'تسجيل سبر جديد')

@section('content')
    @php
        $studentLevels = $students->pluck('level_id', 'id')->all();
        $mistakeValues = [];
        foreach ($mistakes as $mistake) {
            foreach ($mistake->levels as $lvl) {
                $mistakeValues[$lvl->id][$mistake->id] = $lvl->pivot->value;
            }
        }
    @endphp
    <div class="container mt-5" dir="rtl">
        <!-- Page Header -->
        <div class="text-center mb-4">
            <h1 class="display-6 fw-bold text-success">
                <i class="fas fa-clipboard-check me-2"></i> تسجيل سبر جديد
            </h1>
            <p class="text-black">املأ البيانات التالية لتسجيل سبر جديد للطالب</p>
        </div>

        @include('dashboard.layouts.alert')

        <!-- Form Card -->
        <div class="card shadow-sm border-success mb-5">
            <div class="card-header bg-success text-white text-center">
                <h5 class="mb-0 text-white"><i class="fas fa-pen me-2"></i> بيانات السبر</h5>
            </div>

            <div class="card-body bg-light-gray p-4">
                <form action="{{ route('admin.sabrs.store') }}" method="POST" id="sabr-form">
                    @csrf
                    <div class="row g-3">
                        <div class="row g-3 ">
                            <!-- Student Selection -->
                            <div class="col-12 mb-4">
                                <h5 class="fw-bold text-success mb-5 text-center">
                                    <i class="fas fa-user-graduate me-2 "></i> اختيار الطالب
                                </h5>
                                <div class="col-11 me-5">
                                    <select name="student_id" id="student_id"
                                        class="form-select mt-4 @error('student_id') is-invalid @enderror" required>
                                        <option value="">-- اختر الطالب --</option>
                                        @foreach ($students as $student)
                                            <option value="{{ $student->id }}"
                                                {{ old('student_id') == $student->id ? 'selected' : '' }}>
                                                {{ $student->user->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('student_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <!-- Mistakes Section -->
                        <div class="col-12 mt-4">
                            <div
                                class=" bg-light p-3
                @error('mistakes') border border-danger rounded @enderror">
                                <h5 class="fw-bold text-success mb-3 text-center">
                                    <i class="fas fa-exclamation-circle me-2"></i> تسجيل الأخطاء
                                </h5>
                                <div class="row g-3" id="mistakes-container">
                                    @foreach ($mistakes as $mistake)
                                        <div class="col-md-4 mistake-item" data-mistake-id="{{ $mistake->id }}">
                                            <label class="form-label d-flex justify-content-between mb-1">
                                                <span>{{ $mistake->name }}</span>
                                                <span class="badge bg-success mistake-value">القيمة: 0</span>
                                            </label>
                                            <input type="number" name="mistakes[{{ $mistake->id }}]"
                                                id="mistake_{{ $mistake->id }}"
                                                value="{{ old("mistakes.{$mistake->id}", 0) }}" min="0"
                                                step="1"
                                                class="form-control @error("mistakes.{$mistake->id}") is-invalid @enderror">
                                            @error("mistakes.{$mistake->id}")
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    @endforeach
                                </div>
                                @error('mistakes')
                                    <div class="d-block text-danger mt-2">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <!-- Quran Parts Selection -->
                        <div class="col-12 mt-5">
                            <div class="border rounded-3 bg-light p-3">
                                <h5 class="fw-bold text-success mb-3 text-center">
                                    <i class="fas fa-quran me-2"></i> أجزاء القرآن المختارة
                                </h5>
                                <button type="button" id="select-all" class="btn btn-outline-success mb-3">
                                    <i class="fas fa-check-double me-2"></i> اختيار الكل
                                </button>
                                <div class=" p-3 bg-light">
                                    <div class="row g-2 ps-3" style="max-height: 300px; overflow-y: auto;">
                                        @for ($i = 1; $i <= 30; $i++)
                                            <div class="col-6 col-md-2 col-lg-2">
                                                <div class="form-check d-flex align-items-center bg-white rounded p-2 shadow-sm"
                                                    style="justify-content: space-between;">
                                                    <label class="form-check-label text-end flex-grow-1"
                                                        for="juz_{{ $i }}">
                                                        جزء
                                                        {{ $i }}
                                                    </label>
                                                    <input class="form-check-input m-0" type="checkbox" name="juz[]"
                                                        value="{{ $i }}" id="juz_{{ $i }}">
                                                </div>
                                            </div>
                                        @endfor
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="col-12 mt-5">
                            <div class="d-flex justify-content-between align-items-center">
                                <a href="{{ route('admin.sabrs.index') }}"
                                    class="btn btn-secondary btn-lg px-4 shadow-sm order-1">
                                    <i class="fas fa-arrow-left me-2"></i> عودة
                                </a>
                                <button type="submit" class="btn btn-success btn-lg px-5 me-3 shadow-sm order-2">
                                    <i class="fas fa-save me-2"></i> تسجيل
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Preload mappings from Blade
            const studentLevels = @json($studentLevels);
            const mistakeValues = @json($mistakeValues);

            const studentSelect = document.getElementById('student_id');
            const mistakeItems = document.querySelectorAll('.mistake-item');

            function updateValuesForStudent(studentId) {
                const lvlId = studentLevels[studentId] || null;
                mistakeItems.forEach(item => {
                    const mid = item.dataset.mistakeId;
                    const badge = item.querySelector('.mistake-value');
                    const val = (lvlId && mistakeValues[lvlId] && mistakeValues[lvlId][mid]) ?
                        mistakeValues[lvlId][mid] :
                        0;
                    badge.textContent = `القيمة: ${val}`;
                });
            }

            // On change
            studentSelect.addEventListener('change', () => {
                updateValuesForStudent(this.value || studentSelect.value);
            });

            // Initialize on page load if a student is pre-selected
            if (studentSelect.value) {
                updateValuesForStudent(studentSelect.value);
            }

            // Select-all Juz logic
            const selectAllBtn = document.getElementById('select-all');
            const checkboxes = document.querySelectorAll('input[name="juz[]"]');
            selectAllBtn.addEventListener('click', () => {
                if (!checkboxes.length) return;
                const shouldCheck = !checkboxes[0].checked;
                checkboxes.forEach(c => c.checked = shouldCheck);
                selectAllBtn.classList.toggle('btn-success', shouldCheck);
                selectAllBtn.classList.toggle('btn-outline-success', !shouldCheck);
            });
        });
    </script>
@endsection
