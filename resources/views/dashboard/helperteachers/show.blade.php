@extends('dashboard.layouts.app')

@section('title', 'معلومات الأستاذ المساعد')

@section('content')
    <div class="container mt-5" dir="rtl">
        <!-- Page Header -->
        <div class="text-center mb-5">
            <h1 class="display-6 fw-bold text-success">
                <i class="fas fa-chalkboard-teacher me-2"></i> الملف الشخصي للأستاذ المساعد
            </h1>
            <p class="text-black"> المعلومات الأساسية للأستاذ المساعد</p>
        </div>
        @include('dashboard.layouts.alert')

        <!-- Details Card -->
        <div class="card shadow-sm border-success mb-5">
            <div class="card-header bg-success text-white text-center">
                <h5 class="mb-0 text-white"><i class="fas fa-id-card me-2"></i> البيانات الأساسية</h5>
            </div>
            <div class="card-body bg-light-gray p-4">
                <div class="row g-4">
                    <!-- ID -->
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-success">
                                <i class="fas fa-id-badge me-2"></i> الرقم التعريفي
                            </label>
                            <div class="bg-white p-3 rounded-3 shadow-sm">
                                {{ $helperTeacher->id }}

                            </div>
                        </div>
                    </div>

                    <!-- Name -->
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-success">
                                <i class="fas fa-user me-2"></i> الاسم الكامل
                            </label>
                            <div class="bg-white p-3 rounded-3 shadow-sm">
                                {{ $user->name }}
                            </div>
                        </div>
                    </div>

                    <!-- Phone -->
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-success">
                                <i class="fas fa-phone me-2"></i> رقم الهاتف
                            </label>
                            <div class="bg-white p-3 rounded-3 shadow-sm">
                                {{ $user->helperTeacher->phone ?? 'غير محدد' }}
                            </div>
                        </div>
                    </div>

                    @if ($password)
                        <!-- Password -->
                        <div class="col-12">
                            <div class="mb-3">
                                <label class="form-label fw-bold text-success">
                                    <i class="fas fa-key me-2"></i> كلمة المرور
                                </label>
                                <div class="bg-white p-3 rounded-3 shadow-sm">
                                    {{ $password }}
                                </div>
                            </div>
                        </div>
                    @endif
                    <!-- Permissions -->
                    <div class="col-12">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-success">
                                <i class="fas fa-lock me-2"></i> الصلاحيات
                            </label>
                            <div class="bg-white p-3 rounded-3 shadow-sm">
                                @if ($helperTeacher->permissions->isEmpty())
                                    <span class="text-muted">لا توجد صلاحيات</span>
                                @else
                                    @foreach ($helperTeacher->permissions as $perm)
                                        <span class="badge bg-primary me-1">{{ $perm->name }}</span>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="col-12 mt-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="order-2">
                                <a href="{{ route('admin.helper-teachers.edit', $user->helperTeacher->id) }}"
                                    class="btn btn-success btn-lg px-5 me-3 shadow-sm">
                                    <i class="fas fa-edit me-2"></i> تعديل
                                </a>
                            </div>
                            <div class="order-1">
                                <a href="{{ route('admin.helper-teachers.index') }}"
                                    class="btn btn-secondary btn-lg px-4 shadow-sm">
                                    <i class="fas fa-arrow-left me-2"></i> عودة
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-dismiss alerts
            ['.alert-success', '.alert-danger'].forEach(selector => {
                const alert = document.querySelector(selector);
                if (alert) {
                    setTimeout(() => {
                        alert.style.opacity = '0';
                        setTimeout(() => alert.remove(), 300);
                    }, 5000);
                }
            });
        });
    </script>
@endsection
