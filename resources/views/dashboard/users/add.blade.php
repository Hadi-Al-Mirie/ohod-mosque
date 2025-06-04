@extends('dashboard.layouts.app')

@section('title', 'إضافة مستخدم')

@section('content')
    <div class="container mt-5">
        <h1 class="h3 mb-4">إضافة مستخدم</h1>
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form action="{{ route('admin.users.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label for="first_name" class="form-label">الاسم الأول</label>
                <input type="text" name="first_name" id="first_name" class="form-control" value="{{ old('first_name') }}"
                    required>
            </div>
            <div class="mb-3">
                <label for="middle_name" class="form-label">اسم الأب</label>
                <input type="text" name="middle_name" id="middle_name" class="form-control"
                    value="{{ old('middle_name') }}" required>
            </div>
            <div class="mb-3">
                <label for="last_name" class="form-label">الكنية</label>
                <input type="text" name="last_name" id="last_name" class="form-control" value="{{ old('last_name') }}"
                    required>
            </div>

            <div class="mb-3">
                <label for="phone" class="form-label">رقم الهاتف</label>
                <input type="text" placeholder="09........" name="phone" id="phone" class="form-control"
                    value="{{ old('phone') }}" required>
            </div>

            <div class="mb-3">
                <label for="location" class="form-label">الموقع</label>
                <input type="text" placeholder="دمشق/../../.." name="location" id="location" class="form-control"
                    value="{{ old('location') }}" required>
            </div>

            <div class="mb-3">
                <label for="role_id" class="form-label">تسجيل المستخدم ك</label>
                <select name="role_id" id="role_id" class="form-control" required>
                    @foreach ($roles as $role)
                        <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                            {{ $role->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary">انشئ المستخدم</button>
        </form>
    </div>

    <script>
        let icon = document.getElementById("togglePassword");
        let password = document.getElementById("password");
        let password_confirmation = document.getElementById("password_confirmation");
        icon.onclick = function() {
            if (password.type == "password") {
                password.type = "text";
                password_confirmation.type = "text";
                icon.src = "{{ asset('assets/images/eye-open.png') }}";
            } else {
                password.type = "password";
                password_confirmation.type = "password";
                icon.src = "{{ asset('assets/images/eye-close.png') }}";
            }
        }
    </script>
@endsection
