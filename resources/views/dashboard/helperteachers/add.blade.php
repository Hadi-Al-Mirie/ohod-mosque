@extends('dashboard.layouts.app')
@section('title', ' إضافة أستاذ مساعد')
@section('content')
    <div class="container mt-5" dir="rtl">
        <!-- Page Header -->
        <div class="text-center mb-5">
            <h1 class="display-6 fw-bold text-success">
                <i class="fas fa-chalkboard-teacher me-2"></i> إضافة أستاذ مساعد جديد
            </h1>
            <p class="text-black">املأ البيانات التالية لإضافة أستاذ مساعد جديد </p>
        </div>

        @include('dashboard.layouts.alert')

        <!-- Form Card -->
        <div class="card shadow-sm border-success mb-5">
            <div class="card-header bg-success text-white text-center">
                <h5 class="mb-0 text-white"><i class="fas fa-user-plus me-2"></i> بيانات الأستاذ المساعد</h5>
            </div>
            <div class="card-body bg-light-gray p-4">
                <form action="{{ route('admin.helper-teachers.store') }}" method="POST">
                    @csrf
                    <div class="row g-3">

                        <!-- Teacher Name -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="teacher_name" class="form-label fw-bold">
                                    <i class="fas fa-user me-2"></i> اسم الأستاذ
                                </label>
                                <input type="text" name="teacher_name" id="teacher_name"
                                    class="form-control @error('teacher_name') is-invalid @enderror"
                                    value="{{ old('teacher_name') }}" placeholder="أدخل اسم الأستاذ">
                                @error('teacher_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Password -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="password" class="form-label fw-bold">
                                    <i class="fas fa-key me-2"></i> كلمة المرور
                                </label>
                                <input type="password" name="password" id="password"
                                    class="form-control @error('password') is-invalid @enderror"
                                    placeholder="أدخل كلمة المرور">
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Phone -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="phone" class="form-label fw-bold">
                                    <i class="fas fa-phone me-2"></i> رقم الهاتف
                                </label>
                                <input type="text" name="phone" id="phone"
                                    class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}"
                                    placeholder="أدخل رقم الهاتف">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <!-- Permissions checklist -->
                        <div class="col-12 mt-4">
                            <label class="form-label fw-bold mb-2">الصلاحيات</label>
                            <div class="border rounded p-3" style="max-height: 250px; overflow-y: auto;">
                                @foreach ($permissions as $perm)
                                    <div class="form-check">
                                        <input type="checkbox" name="permissions[]" value="{{ $perm->id }}"
                                            id="perm-{{ $perm->id }}" class="form-check-input"
                                            {{ in_array($perm->id, old('permissions', $current)) ? 'checked' : '' }}>
                                        <label for="perm-{{ $perm->id }}" class="form-check-label">
                                            {{ $perm->name }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            @error('permissions')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <!-- Form Actions -->
                        <div class="col-12 mt-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="order-2">
                                    <button type="submit" class="btn btn-success btn-lg px-5 me-3 shadow-sm">
                                        <i class="fas fa-save me-2"></i> إضافة
                                    </button>
                                </div>
                                <div class="order-1">
                                    <a href="{{ route('admin.teachers.index') }}"
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
