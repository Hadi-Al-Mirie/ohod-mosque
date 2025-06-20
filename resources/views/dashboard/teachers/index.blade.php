@extends('dashboard.layouts.app')
@section('title', 'الأساتذة')
@section('content')
    <div class="container-fluid mt-5">
        @include('dashboard.layouts.alert')
        <h1 class="h1 mb-5 fw-bold text-center"
            style="font-family: 'IBMPlexSansArabic', sans-serif; font-size: 2.3rem; text-shadow: 1px 1px 2px rgba(0,0,0,0.1); color: var(--bs-primary);">
            قائمة الأساتذة
        </h1>
        <div class="mb-3">
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <form method="GET" action="{{ route('admin.teachers.index') }}">
                        <div class="input-group shadow-sm">
                            <input type="text" name="search_value" id="search_value" class="form-control search-box"
                                placeholder="أدخل اسم الأستاذ" value="{{ request('search_value') }}">
                            <button class="btn btn-primary custom-btn" type="submit">
                                <i class="fas fa-search"></i> بحث
                            </button>
                        </div>
                    </form>
                </div>

                <div class="col-12 col-md-6 text-start text-md-end">
                    <a href="{{ route('admin.teachers.create') }}" class="btn btn-primary custom-btn">
                        <i class="fas fa-user-plus"></i> إضافة أستاذ جديد
                    </a>
                </div>
            </div>
        </div>

        <div class="table-responsive overflow-x-auto overflow-y-visible rounded-3 shadow-sm">
            <table class="table table-striped table-hover mb-0">
                <thead class="table-header">
                    <tr>
                        <th class="py-3" style="width: 40%">اسم الأستاذ</th>
                        <th class="py-3" style="width: 40%">الحلقة</th>
                        <th class="py-3" style="width: 20%">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($teachers as $teacher)
                        <tr>
                            <td class="align-middle">{{ $teacher->name }}</td>
                            <td class="align-middle">
                                {{ $teacher->teacher->circle?->name ?? 'غير محدد' }}
                            </td>
                            <td>
                                <a href="{{ route('admin.teachers.show', $teacher->teacher->id) }}"
                                    class="btn btn-sm btn-primary custom-btn">
                                    <i class="fas fa-eye"></i> التفاصيل
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4 d-flex justify-content-center">
            {{ $teachers->links('pagination::bootstrap-5') }}
        </div>
    </div>
@endsection
