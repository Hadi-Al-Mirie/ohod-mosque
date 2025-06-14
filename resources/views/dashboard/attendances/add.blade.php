@extends('dashboard.layouts.app')

@section('title', 'تسجيل حضور جديد')

@section('content')
    <div class="container" dir="rtl">
        <!-- Page Header -->
        <div class="text-center mb-4">
            <h1 class="display-6 fw-bold text-success">
                <i class="fas fa-calendar-check me-2"></i> تسجيل حضور جديد
            </h1>
            <p class="text-black">املأ البيانات التالية لتسجيل حضور جديد</p>
        </div>

        @include('dashboard.layouts.alert')

        <div class="card shadow-sm border-success mb-5">
            <div class="card-header bg-success text-white text-center">
                <h5 class="mb-0 text-white"><i class="fas fa-edit me-2"></i> بيانات الحضور</h5>
            </div>
            <div class="card-body bg-light-gray p-4">
                <form action="{{ route('admin.attendances.store') }}" method="POST">
                    @csrf

                    <div class="row g-3">
                        <!-- Attendance Date -->
                        <div class="col-md-6">
                            <label for="attendance_date" class="form-label fw-bold">تاريخ الحضور</label>
                            <input type="date" name="attendance_date" id="attendance_date"
                                class="form-control @error('attendance_date') is-invalid @enderror"
                                value="{{ old('attendance_date', now()->format('Y-m-d')) }}"
                                max="{{ now()->format('Y-m-d') }}">
                            @error('attendance_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Mode Toggle -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold">التسجيل لـ:</label>
                            <div>
                                <label class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="mode" value="student" checked>
                                    <span class="form-check-label">طالب واحد</span>
                                </label>
                                <label class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="mode" value="circle">
                                    <span class="form-check-label">جميع طلاب الحلقة</span>
                                </label>
                            </div>
                        </div>

                        <!-- Circle Selection -->
                        <div class="col-md-6 mode-circle" style="display: none;">
                            <label for="circle_id" class="form-label fw-bold">الحلقة</label>
                            <select name="circle_id" id="circle_id"
                                class="form-select @error('circle_id') is-invalid @enderror">
                                <option value="">-- اختر الحلقة --</option>
                                @foreach ($circles as $c)
                                    <option value="{{ $c->id }}" {{ old('circle_id') == $c->id ? 'selected' : '' }}>
                                        {{ $c->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('circle_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Student Selection -->
                        <div class="col-md-6 mode-student">
                            <label for="student_id" class="form-label fw-bold">الطالب</label>
                            <select name="student_id" id="student_id"
                                class="form-select @error('student_id') is-invalid @enderror">
                                <option value="">-- اختر الطالب --</option>

                                @foreach ($students as $student)
                                    <option value="{{ $student->id }}"
                                        {{ old('student_id') == $student->id ? 'selected' : '' }}>
                                        {{ $student->user->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('student_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <!-- Attendance Type -->
                        <div class="col-md-6">
                            <label for="type_id" class="form-label fw-bold">نوع الحضور</label>
                            <select name="type_id" id="type_id"
                                class="form-select @error('type_id') is-invalid @enderror">
                                <option value="">-- اختر النوع --</option>
                                @foreach ($types as $type)
                                    <option value="{{ $type->id }}"
                                        {{ old('type_id') == $type->id ? 'selected' : '' }}>
                                        {{ $type->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('type_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Justification Field -->
                        <div class="col-12" id="justificationField" style="display: none;">
                            <label for="justification" class="form-label fw-bold">تبرير الغياب</label>
                            <input type="text" name="justification" id="justification"
                                class="form-control @error('justification') is-invalid @enderror"
                                value="{{ old('justification') }}">
                            @error('justification')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Form Actions -->
                        <div class="col-12 mt-5 mb-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <a href="{{ route('admin.attendances.index') }}"
                                    class="btn btn-secondary btn-lg px-4 shadow-sm">
                                    <i class="fas fa-arrow-left me-2"></i> عودة
                                </a>
                                <button type="submit" class="btn btn-success btn-lg px-5 shadow-sm">
                                    <i class="fas fa-save me-2"></i> حفظ
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
            const circleStudents = @json($circles->keyBy('id')->map(fn($c) => $c->students->map(fn($s) => ['id' => $s->id, 'name' => $s->user->name])));

            const oldCircle = '{{ old('circle_id') }}';
            const oldStudent = '{{ old('student_id') }}';

            function populateStudents(circleId) {
                const sel = document.getElementById('student_id');
                sel.innerHTML = '<option value=\"\">-- اختر الطالب --</option>';
                if (circleStudents[circleId]) {
                    circleStudents[circleId].forEach(s => {
                        const opt = new Option(s.name, s.id);
                        if (s.id == oldStudent) opt.selected = true;
                        sel.add(opt);
                    });
                }
            }

            // Mode toggle
            const modes = document.querySelectorAll('input[name="mode"]');

            function showMode(mode) {
                document.querySelectorAll('.mode-student')
                    .forEach(el => el.style.display = mode === 'student' ? '' : 'none');
                document.querySelectorAll('.mode-circle')
                    .forEach(el => el.style.display = mode === 'circle' ? '' : 'none');
            }
            modes.forEach(r => r.addEventListener('change', () => showMode(r.value)));
            showMode(document.querySelector('input[name="mode"]:checked').value);

            // Populate students when circle changes
            document.getElementById('circle_id').addEventListener('change', e => {
                populateStudents(e.target.value);
            });
            if (oldCircle) populateStudents(oldCircle);

            // Justification toggle
            function toggleJust() {
                const typeText = document.getElementById('type_id')
                    .selectedOptions[0].text.trim();
                document.getElementById('justificationField').style.display =
                    (typeText === 'غياب مبرر') ? 'block' : 'none';
            }
            document.getElementById('type_id')
                .addEventListener('change', toggleJust);
            toggleJust();
        });
    </script>
@endsection
