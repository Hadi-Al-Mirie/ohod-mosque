@extends('dashboard.layouts.app')

@section('title', 'المستخدمين')

@section('content')

    <div class="container mt-5">

        <h1 class="h3 mb-4">قائمة المستخدمين</h1>
        @if (session('success'))
            <div class="alert alert-success" id="success-alert">{{ session('success') }}</div>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    setTimeout(function() {
                        const alertElement = document.getElementById('success-alert');
                        if (alertElement) {
                            alertElement.style.display = 'none';
                        }
                    }, 5000);
                });
            </script>
        @endif
        <div class="mb-3">
            <div class="row">
                <div class="col-12 col-md-6">
                    <form method="GET" action="{{ route('admin.users.index') }}" class="mb-4">
                        <div class="input-group">
                            <select name="search_type" class="form-select" id="search_type" onchange="updateSearchInput()">
                                <option value="all_users"{{ request('search_type') == 'all_users' ? 'selected' : '' }}>
                                    جميع المستخدمين </option>
                                <option value="user_name" {{ request('search_type') == 'user_name' ? 'selected' : '' }}>
                                    اسم المستخدم
                                </option>
                                <option value="full_name" {{ request('search_type') == 'full_name' ? 'selected' : '' }}>
                                    الاسم الثلاثي
                                </option>
                                <option value="role" {{ request('search_type') == 'role' ? 'selected' : '' }}>
                                    الدور
                                </option>
                                <option value="phone" {{ request('search_type') == 'phone' ? 'selected' : '' }}>
                                    رقم الهاتف
                                </option>
                            </select>
                            <input type="text" name="search_value" id="search_value" class="form-control"
                                placeholder="أدخل قيمة للبحث" value="{{ request('search_value') }}">
                            <select name="search_value_role" id="search_value_role" class="form-select d-none">
                                <option value="1" {{ request('search_value') == '1' ? 'selected' : '' }}>المدير
                                </option>
                                <option value="2" {{ request('search_value') == '2' ? 'selected' : '' }}>أستاذ</option>
                                <option value="3" {{ request('search_value') == '3' ? 'selected' : '' }}>أستاذ مساعد
                                </option>
                                <option value="4" {{ request('search_value') == '4' ? 'selected' : '' }}>طالب</option>
                            </select>
                            <button class="btn btn-primary custom-dimensions" type="submit">بحث</button>
                        </div>
                    </form>
                </div>
                <div class="col-12 col-md-6 text-md-end">
                    <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                        <i class="fas fa-user-plus"></i> إضافة مستخدم جديد
                    </a>
                </div>
            </div>
        </div>



        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>الرقم التسلسلي</th>
                        <th>اسم المستخدم</th>
                        <th>الاسم الثلاثي</th>
                        <th>الدور</th>
                        <th>رقم الهاتف</th>
                        <th>القيام ب</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>{{ $user->user_name }}</td>
                            <td>{{ $user->first_name }} {{ $user->middle_name }} {{ $user->last_name }}</td>
                            <td>{{ $user->role ? $user->role->name : 'Not Found' }}</td>
                            <td>{{ $user->phone }}</td>
                            <td>
                                <a href="{{ route('admin.users.show', $user->id) }}"
                                    class="btn btn-sm btn-primary">التفاصيل</a>
                                <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST"
                                    class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger"
                                        onclick="return confirm('Are you sure?')">حذف</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $users->links('pagination::bootstrap-5') }}
        </div>
    </div>
    <script>
        function updateSearchInput() {
            const searchType = document.getElementById('search_type').value;
            const textInput = document.getElementById('search_value');
            const roleSelect = document.getElementById('search_value_role');
            if (searchType === 'role') {
                textInput.classList.add('d-none');
                roleSelect.classList.remove('d-none');
                textInput.name = '';
                roleSelect.name = 'search_value';
            } else if (searchType === 'all_users') {
                textInput.classList.add('d-none');
                roleSelect.classList.add('d-none');
                textInput.name = '';
                roleSelect.name = '';
                textInput.value = '';
            } else {
                textInput.classList.remove('d-none');
                roleSelect.classList.add('d-none');
                textInput.name = 'search_value';
                roleSelect.name = '';
            }
        }
        document.addEventListener('DOMContentLoaded', () => {
            updateSearchInput();
        });
    </script>

@endsection
