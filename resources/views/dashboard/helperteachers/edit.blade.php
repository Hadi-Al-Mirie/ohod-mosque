@extends('dashboard.layouts.app')

@section('title', 'تعديل بيانات الأستاذ المساعد')

@section('content')
    <div class="container" dir="rtl">
        <!-- Page Header -->
        <div class="text-center mb-4">
            <h1 class="display-6 fw-bold text-success">
                <i class="fas fa-chalkboard-teacher me-2"></i> تعديل بيانات الأستاذ المساعد
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
                <form id="teacher-form" action="{{ route('admin.helper-teachers.update', $helperTeacher->id) }}"
                    method="POST" data-requires-admin-confirm="false">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <!-- Name -->
                        <div class="col-md-6">
                            <label for="name" class="form-label fw-bold">الاسم الكامل</label>
                            <input type="text" name="name" id="name"
                                class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name', $helperTeacher->user->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Phone -->
                        <div class="col-md-6">
                            <label for="phone" class="form-label fw-bold">رقم الهاتف</label>
                            <input type="text" name="phone" id="phone"
                                class="form-control @error('phone') is-invalid @enderror"
                                value="{{ old('phone', $helperTeacher->phone) }}" placeholder="09........">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div class="col-md-6">
                            <label for="password" class="form-label fw-bold">كلمة السر</label>
                            <input type="password" name="password" id="password"
                                class="form-control @error('password') is-invalid @enderror"
                                placeholder="اتركه فارغاً إن لم ترغب في التغيير">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Admin confirmation -->
                        <input type="hidden" name="admin_password" id="admin_password">

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

                        <!-- Actions -->
                        <div class="col-12 mt-4">
                            <div class="d-flex justify-content-between">
                                <a href="{{ route('admin.helper-teachers.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-2"></i> عودة
                                </a>
                                <button type="submit" class="btn btn-success">
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
