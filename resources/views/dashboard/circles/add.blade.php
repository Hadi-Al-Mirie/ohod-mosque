@extends('dashboard.layouts.app')

@section('title', 'إنشاء حلقة')

@section('content')
    <div class="container mt-5" dir="rtl">
        <!-- Page Header -->
        <div class="text-center mb-5">
            <h1 class="display-6 fw-bold text-success">
                <i class="fas fa-users me-2"></i> إنشاء حلقة جديدة
            </h1>
            <p class="text-black">املأ البيانات التالية لإنشاء حلقة جديدة</p>
        </div>
        @include('dashboard.layouts.alert')

        <!-- Form Card -->
        <div class="card shadow-sm border-success mb-5">
            <div class="card-header bg-success text-white text-center">
                <h5 class="mb-0 text-white"><i class="fas fa-edit me-2"></i> بيانات الحلقة</h5>
            </div>
            <div class="card-body bg-light-gray p-4">
                <form action="{{ route('admin.circles.store') }}" method="POST">
                    @csrf
                    <div class="row g-3">

                        <!-- Circle Name -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="circle_name" class="form-label fw-bold">
                                    <i class="fas fa-tag me-2"></i> اسم الحلقة
                                </label>
                                <input type="text" name="circle_name" id="circle_name"
                                    class="form-control @error('circle_name') is-invalid @enderror"
                                    value="{{ old('circle_name') }}" placeholder="أدخل اسم الحلقة">
                                @error('circle_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Teacher Selection -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="teacher_id" class="form-label fw-bold">
                                    <i class="fas fa-chalkboard-teacher me-2"></i> أستاذ الحلقة
                                </label>
                                <select name="teacher_id" id="teacher_id"
                                    class="form-select @error('teacher_id') is-invalid @enderror">
                                    <option value="">-- اختر الأستاذ --</option>
                                    @foreach ($teachers as $teacher)
                                        <option value="{{ $teacher->id }}"
                                            {{ old('teacher_id') == $teacher->id ? 'selected' : '' }}>
                                            {{ $teacher->first_name }} ({{ $teacher->id }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('teacher_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="col-12 mt-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="order-2">
                                    <button type="submit" class="btn btn-success btn-lg px-5 me-3 shadow-sm">
                                        <i class="fas fa-save me-2"></i> إنشاء
                                    </button>
                                </div>
                                <div class="order-1">
                                    <a href="{{ route('admin.circles.index') }}"
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
