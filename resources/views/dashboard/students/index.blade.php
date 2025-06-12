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
                <div class="col-12 col-lg-6">
                    <button class="btn btn-secondary mb-3" data-bs-toggle="modal" data-bs-target="#filterModal">
                        <i class="fas fa-filter me-2"></i> خيارات البحث
                    </button>
                </div>
                <div class="col-12 col-lg-6 text-lg-end">
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
                                                السبر
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
                                    @php($orderBy = request('order_by', 'points'))
                                    @endphp
                                    <td>
                                        @if ($orderBy === 'points')
                                            {{ $student->cashed_points }}
                                        @elseif($orderBy === 'attendance')
                                            {{ $student->attendance_points }}
                                        @elseif($orderBy === 'sabrs')
                                            {{ $student->sabrs_points }}
                                        @elseif($orderBy === 'recitations')
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



        {{-- Filter Modal --}}
        <div class="modal fade" id="filterModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title w-100 text-center text-white">
                            <i class="fas fa-filter me-2"></i> فلاتر البحث
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal">
                        </button>
                    </div>
                    <div class="modal-body">
                        @php
                            $myFilters = [
                                ['key' => 'search_value', 'label' => 'اسم الطالب', 'type' => 'text'],
                                [
                                    'key' => 'circle_id',
                                    'label' => 'اسم الحلقة',
                                    'type' => 'select',
                                    'options' => $circles,
                                ],
                            ];
                        @endphp
                        <x-filter-box :action="route('admin.students.index')" :filters="$myFilters" />
                    </div>
                    <div class="modal-footer d-flex justify-content-between">
                        <div>
                            {{-- Apply filters --}}
                            <button type="submit" form="filter-form" class="btn btn-primary">
                                <i class="fas fa-search me-1"></i> تطبيق
                            </button>
                        </div>
                        {{-- Clear all filters by reloading the page without any query --}}
                        <a href="{{ route('admin.students.index') }}" class="btn btn-danger">
                            <i class="fas fa-times me-1"></i> مسح الفلاتر
                        </a>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
