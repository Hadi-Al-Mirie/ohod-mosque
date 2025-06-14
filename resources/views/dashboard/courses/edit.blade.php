@extends('dashboard.layouts.app')
@section('title', 'تعديل الدورة')
@section('content')
    @php
        // 1) Get the old input if it exists (it'll be an array)
    @endphp
    <div class="container mt-5" dir="rtl">
        <!-- Page Header -->
        <div class="text-center mb-4">
            <h1 class="display-6 fw-bold text-primary">
                <i class="fa-solid fa-file-pen"></i> تعديل الدورة
            </h1>
            <p class="text-muted">قم بتعديل بيانات الدورة وتحديث أيام الدوام</p>
        </div>
        @include('dashboard.layouts.alert')
        <!-- Form Card -->
        <div class="card shadow-sm border-primary mb-5">
            <div class="card-header bg-primary text-white text-center">
                <h5 class="mb-0 text-white"><i class="fas fa-edit me-2"></i> تحديث بيانات الدورة</h5>
            </div>

            <div class="card-body bg-light-gray p-4">
                <form action="{{ route('admin.courses.update', $course->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row g-3">

                        <!-- Course Name -->
                        <div class="col-12">
                            <div class="mb-3">
                                <label class="form-label fw-bold text-dark">اسم الدورة</label>
                                <input type="text" name="name" id="name"
                                    class="form-control @error('name') is-invalid @enderror"
                                    value="{{ old('name', $course->name) }}" placeholder="اسم الدورة" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Date Range -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold text-dark">تاريخ البدء</label>
                                <input type="date" name="start_date" id="start_date"
                                    class="form-control @error('start_date') is-invalid @enderror"
                                    value="{{ old('start_date', $course->start_date) }}" required>
                                @error('start_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold text-dark">تاريخ الانتهاء</label>
                                <input type="date" name="end_date" id="end_date"
                                    class="form-control @error('end_date') is-invalid @enderror"
                                    value="{{ old('end_date', $course->end_date) }}" required>
                                @error('end_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Working Days -->
                        <div class="col-12 mb-3">
                            <div class="bg-light-gray p-3 mt-3">
                                <h4 class="fw-bold text-black mb-5 text-center">
                                    <i class="fas fa-calendar-check me-2"></i> أيام الدوام
                                </h4>
                                <div class="row g-3">
                                    @php
                                        // Carbon: 0=Sunday, 1=Monday, … 6=Saturday
                                        $days = [
                                            6 => 'السبت',
                                            0 => 'الأحد',
                                            1 => 'الإثنين',
                                            2 => 'الثلاثاء',
                                            3 => 'الأربعاء',
                                            4 => 'الخميس',
                                            5 => 'الجمعة',
                                        ];
                                        // 1) Always grab old input as array (or empty array)
                                        $oldInput = old('working_days', []);

                                        // 2) Model value (casts to array on the Course model)
                                        $modelDays = is_array($course->working_days)
                                            ? $course->working_days
                                            : json_decode($course->working_days, true) ?? [];

                                        // 3) Final selection: prefer old input if any, else model
                                        $selectedDays = count($oldInput) ? $oldInput : $modelDays;
                                    @endphp

                                    @foreach ($days as $key => $day)
                                        <div class="col-md-3">
                                            <div class="d-flex align-items-center bg-white rounded p-2 shadow-sm">
                                                <label class="form-check-label text-end flex-grow-1"
                                                    for="day{{ $key }}">
                                                    {{ $day }}
                                                </label>
                                                <input class="form-check-input m-0" type="checkbox" name="working_days[]"
                                                    value="{{ $key }}" id="day{{ $key }}"
                                                    {{-- safe cast to array just in case --}}
                                                    {{ in_array($key, $selectedDays) ? 'checked' : '' }}>

                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                @error('working_days')
                                    <div class="text-danger small mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Deactivate Course -->
                        <div class="col-12 bg-white rounded-4 shadow-sm mt-5 mb-5">
                            <div class="form-check mt-4 mb-4">
                                <!-- ensure a value is always submitted -->
                                <input type="hidden" name="deactivate" value="0">
                                <input class="form-check-input ms-3 p-3 mb-4" type="checkbox" name="deactivate"
                                    id="deactivate" value="1"
                                    {{ old('deactivate', $course->is_active ? 0 : 1) ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold me-3 fs-4" for="deactivate">
                                    تعطيل الدورة
                                </label>
                            </div>
                            @error('deactivate')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <!-- Form Actions -->
                        <div class="col-12 mt-5">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="order-2">
                                    <button type="submit" class="btn btn-primary btn-lg px-5 me-3 shadow-sm">
                                        <i class="fas fa-save me-2"></i> حفظ التعديلات
                                    </button>
                                </div>
                                <div class="order-1">
                                    <a href="{{ route('admin.courses.index') }}"
                                        class="btn btn-secondary btn-lg px-4 shadow-sm">
                                        <i class="fas fa-arrow-left me-2"></i> عودة
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
