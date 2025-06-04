@extends('dashboard.layouts.app')
@section('title', 'الطلاب')
@section('content')
    <div class="container-fluid mt-5">
        @include('dashboard.layouts.alert')

        <h1 class="h3 mb-4 fw-bold text-center"
            style="font-family: 'IBMPlexSansArabic', sans-serif; font-size: 2.2rem; text-shadow: 1px 1px 2px rgba(0,0,0,0.1); color: var(--bs-primary);">
            قائمة الطلاب
        </h1>
        <div class="mb-4">
            <div class="row g-3">
                <div class="col-12 col-lg-5">
                    <form method="GET" action="{{ route('admin.students.index') }}">
                        <div class="input-group shadow-sm">
                            <input type="text" name="search_value" class="form-control w-25 search-input rounded-start"
                                placeholder="🔍 ابحث عن طالب..." value="{{ request('search_value') }}">
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                </div>
                <div class="col-12 col-lg-7 text-lg-end">
                    <a href="{{ route('admin.students.create') }}" class="btn btn-success hover-scale">
                        <i class="fas fa-user-plus me-2"></i> إضافة طالب
                    </a>
                </div>
            </div>
        </div>
        <div class="card shadow-lg border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped table-bordered mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="py-3 "> الرقم </th>
                                <th class="py-3 "> الاسم </th>
                                <th class="py-3 text-center"> الحلقة </th>
                                <th class="py-2 text-center">
                                    <form method="GET" action="{{ route('admin.students.index') }}">
                                        <input type="hidden" name="search_value" value="{{ request('search_value') }}">
                                        <select name="order_by" class="table-select " onchange="this.form.submit()">
                                            <option value="points" {{ request('order_by') == 'points' ? 'selected' : '' }}>
                                                النقاط
                                            </option>
                                            <option value="attendance"
                                                {{ request('order_by') == 'attendance' ? 'selected' : '' }}>
                                                الحضور
                                            </option>
                                            <option value="sabrs" {{ request('order_by') == 'sabrs' ? 'selected' : '' }}>
                                                السبورة
                                            </option>
                                            <option value="recitations"
                                                {{ request('order_by') == 'recitations' ? 'selected' : '' }}>
                                                التسميعات
                                            </option>
                                        </select>
                                    </form>
                                </th>

                                <th class="py-3text-center">الخيارات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($students as $student)
                                <tr class="hover-lift">
                                    <td class="align-middle">
                                        {{ $loop->iteration + ($students->currentPage() - 1) * $students->perPage() }}</td>
                                    <td class="align-middle">{{ $student->user->name }}</td>
                                    <td class="align-middle">
                                        <span class="badge custom-badge">
                                            {{ $student->circle->name ?? 'غير محدد' }}
                                        </span>
                                    </td>
                                    @php($order = request('order_by', 'points'))

                                    <td>
                                        @if ($order === 'points')
                                            {{ $student->cashed_points }}
                                        @elseif($order === 'attendance')
                                            {{ $student->attendance_points }}
                                        @elseif($order === 'sabrs')
                                            {{ $student->sabrs_points }}
                                        @elseif($order === 'recitations')
                                            {{ $student->recitations_points }}
                                        @endif
                                    </td>

                                    <td class="align-middle">
                                        <a href="{{ route('admin.students.show', $student->id) }}"
                                            class="btn btn-primary hover-scale">
                                            <i class="fas fa-eye me-1"></i> التفاصيل
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Pagination --}}
        <div class="mt-4 d-flex justify-content-center">
            {{ $students->appends(request()->query())->links('pagination::bootstrap-5') }}
        </div>
    </div>
@endsection
