@extends('dashboard.layouts.app')
@section('title', 'إنشاء دورة جديدة')
@section('content')
    <div class="container mt-5" dir="rtl">
        <!-- Page Header -->
        <div class="text-center mb-4">
            <h1 class="display-6 fw-bold text-primary">
                <i class="fas fa-calendar-plus me-2"></i> إنشاء دورة جديدة
            </h1>
            <p class="text-muted">قم بتعريف الدورة الجديدة مع تحديد مواعيدها وأيام العمل</p>
        </div>

        <!-- Error Alert -->
        @if ($errors->any())
            <div class="alert alert-danger d-flex align-items-start">
                <i class="fas fa-exclamation-triangle me-3 mt-1"></i>
                <div class="flex-fill">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <!-- Form Card -->
        <div class="card shadow-sm border-primary mb-5">
            <div class="card-header bg-primary text-white text-center">
                <h5 class="mb-0 text-white"><i class="fas fa-info-circle me-2"></i> تفاصيل الدورة</h5>
            </div>
            <div class="card-body bg-light-gray p-4">
                <form action="{{ route('admin.courses.store') }}" method="POST">
                    @csrf
                    <div class="row g-3">

                        <!-- Course Name -->
                        <div class="col-12">
                            <div class="mb-3">
                                <label class="form-label fw-bold text-dark">اسم الدورة</label>
                                <input type="text" name="name" id="name"
                                    class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}"
                                    placeholder="أدخل اسم الدورة">
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
                                    value="{{ old('start_date') }}">
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
                                    value="{{ old('end_date') }}">
                                @error('end_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Working Days -->
                        <div class="col-12">
                            <div class=" bg-light-gray p-3 mt-3">
                                <h5 class="fw-bold text-black mb-3 text-center">
                                    <i class="fas fa-calendar-alt me-2"></i> أيام العمل
                                </h5>
                                <div class="row g-3">
                                    @php
                                        $days = [
                                            0 => 'السبت',
                                            1 => 'الأحد',
                                            2 => 'الإثنين',
                                            3 => 'الثلاثاء',
                                            4 => 'الأربعاء',
                                            5 => 'الخميس',
                                            6 => 'الجمعة',
                                        ];
                                        $oldDays = old('working_days', []);
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
                                                    {{ in_array($key, $oldDays) ? 'checked' : '' }}>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                @error('working_days')
                                    <div class="text-danger small mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="col-12 mt-5">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="order-2">
                                    <button type="submit" class="btn btn-primary btn-lg px-5 me-3 shadow-sm">
                                        <i class="fas fa-save me-2"></i> حفظ الدورة
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
