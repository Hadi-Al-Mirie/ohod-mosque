@extends('dashboard.layouts.app')

@section('title', 'تعديل بيانات الأستاذ')

@section('content')
    <div class="container" dir="rtl">
        <!-- Page Header -->
        <div class="text-center mb-4">
            <h1 class="display-6 fw-bold text-success">
                <i class="fas fa-chalkboard-teacher me-2"></i> تعديل بيانات الأستاذ
            </h1>
            <p class="text-black">قم بتعديل المعلومات الأساسية للأستاذ</p>
        </div>

        @include('dashboard.layouts.alert')
        <!-- Form Card -->
        <div class="card shadow-sm border-success mb-5">
            <div class="card-header bg-success text-white text-center">
                <h5 class="mb-0 text-white"><i class="fas fa-edit me-2"></i> البيانات الأساسية</h5>
            </div>
            <div class="card-body bg-light-gray p-4">
                <form id="teacher-form" action="{{ route('admin.teachers.update', $teacher->id) }}" method="POST"
                    data-requires-admin-confirm="false">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <!-- Name -->
                        <div class="col-md-6">
                            <label for="name" class="form-label fw-bold">الاسم الكامل</label>
                            <input type="text" name="name" id="name"
                                class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name', $teacher->user->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Phone -->
                        <div class="col-md-6">
                            <label for="phone" class="form-label fw-bold">رقم الهاتف</label>
                            <input type="text" name="phone" id="phone"
                                class="form-control @error('phone') is-invalid @enderror"
                                value="{{ old('phone', $teacher->phone) }}" placeholder="09........">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Circle -->
                        <div class="col-md-6">
                            <label for="circle" class="form-label fw-bold">الحلقة</label>
                            <select name="circle" id="circle" class="form-select @error('circle') is-invalid @enderror">
                                @foreach ($circles as $circle)
                                    <option value="{{ $circle->id }}"
                                        {{ old('circle', $teacher->circle_id) == $circle->id ? 'selected' : '' }}>
                                        {{ $circle->id }} – {{ $circle->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('circle')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Teacher Password (optional) -->
                        <div class="col-md-6">
                            <label for="password" class="form-label fw-bold">كلمة السر</label>
                            <input type="text" name="password" id="password"
                                class="form-control @error('password') is-invalid @enderror"
                                placeholder="اتركه فارغاً إن لم ترغب في التغيير" value="" {{-- ensure it's empty on load --}}>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Hidden admin confirmation password -->
                        <input type="hidden" name="admin_password" id="admin_password">

                        <!-- Form Actions -->
                        <div class="col-12 mt-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <a href="{{ route('admin.teachers.index') }}"
                                    class="btn btn-secondary btn-lg px-4 shadow-sm order-1">
                                    <i class="fas fa-arrow-left me-2"></i> عودة
                                </a>

                                <button type="submit" class="btn btn-success btn-lg px-5 me-3 shadow-sm order-2">
                                    <i class="fas fa-save me-2"></i> تحديث
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Admin Password Confirmation Modal -->
    <div class="modal fade" id="adminConfirmModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title w-100 text-center text-white">تأكيد كلمة مرور المشرف</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">كلمة مرور المشرف</label>
                        <input type="password" id="adminPasswordInput" class="form-control" autofocus>
                    </div>
                    <div id="adminError" class="alert alert-danger d-none"></div>
                </div>
                <div class="modal-footer bg-light d-flex justify-content-between">
                    <button type="button" id="confirmAdminPwd" class="btn btn-primary">تأكيد</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('teacher-form');
            const pwdField = document.getElementById('password');
            const adminModal = new bootstrap.Modal(document.getElementById('adminConfirmModal'));
            const adminPwdIn = document.getElementById('adminPasswordInput');
            const adminErr = document.getElementById('adminError');
            const confirmBtn = document.getElementById('confirmAdminPwd');

            // 0) On load, clear any previous state
            pwdField.value = '';
            form.dataset.requiresAdminConfirm = 'false';

            // 1) Toggle the "requiresAdminConfirm" flag when typing
            pwdField.addEventListener('input', () => {
                form.dataset.requiresAdminConfirm = pwdField.value.trim() ? 'true' : 'false';
            });

            // 2) Intercept submission if confirmation is required
            form.addEventListener('submit', e => {
                if (form.dataset.requiresAdminConfirm === 'true') {
                    e.preventDefault();
                    adminErr.classList.add('d-none');
                    adminPwdIn.value = '';
                    adminModal.show();
                }
            });

            // 3) Handle the admin’s confirmation
            confirmBtn.addEventListener('click', () => {
                const adminPwd = adminPwdIn.value.trim();
                if (!adminPwd) {
                    adminErr.textContent = '❗ الرجاء إدخال كلمة مرور المشرف';
                    adminErr.classList.remove('d-none');
                    return;
                }

                document.getElementById('admin_password').value = adminPwd;
                adminModal.hide();
                form.submit();
            });
        });
    </script>
@endsection
