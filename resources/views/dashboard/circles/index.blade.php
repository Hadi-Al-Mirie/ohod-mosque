@extends('dashboard.layouts.app')
@section('title', 'الحلقات')
@section('content')
    <div class="container-fluid mt-5">
        <h1 class="h3 mb-4 fw-bold text-center"
            style="font-family: 'IBMPlexSansArabic', sans-serif; font-size: 2.2rem; text-shadow: 1px 1px 2px rgba(0,0,0,0.1); color: var(--bs-primary);">
            قائمة الحلقات
        </h1>
        @include('dashboard.layouts.alert')
        <div class="mb-4">
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <form method="GET" action="{{ route('admin.circles.index') }}">
                        <div class="input-group shadow-sm hover-scale">
                            <input type="text" name="search_value" class="form-control search-input rounded-start"
                                placeholder="أدخل اسم الحلقة" value="{{ request('search_value') }}">
                            <button class="btn btn-primary " type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                </div>

                <div class="col-12 col-md-6 text-end">
                    <a href="{{ route('admin.circles.create') }}" class="btn btn-primary hover-scale">
                        <i class="fas fa-user-plus me-2"></i> إضافة حلقة جديدة
                    </a>
                </div>
            </div>
        </div>

        <div class="card shadow-lg border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped table-bordered">
                        <thead class="bg-light">
                            <tr>
                                <th class="py-3">اسم الحلقة</th>
                                <th class="py-3">الأستاذ</th>
                                <th class="py-3">عدد الطلاب</th>
                                <th class="py-3">
                                    النقاط
                                </th>
                                <th class="py-3">عرض التفاصيل</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($circles as $circle)
                                <tr class="hover-lift">
                                    <td class="align-middle">{{ $circle->name }}</td>
                                    <td class="align-middle">
                                        <span class="badge custom-badge">
                                            {{ $circle->teacher->user->name ?? 'غير محدد' }}
                                        </span>
                                    </td>
                                    <td class="align-middle fw-bold text-info">{{ $circle->students_count }}</td>
                                    <td class="align-middle">
                                        <span class="fw-bold text-success">
                                            {{ $circle->points ?? 0 }}
                                        </span>
                                    </td>
                                    <td class="align-middle">
                                        <a href="{{ route('admin.circles.show', $circle->id) }}"
                                            class="btn btn-sm btn-primary hover-scale">
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

        <div class="mt-3">
            {{ $circles->appends(request()->query())->links('pagination::bootstrap-5') }}
        </div>
    </div>
@endsection
