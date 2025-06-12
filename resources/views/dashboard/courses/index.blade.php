@extends('dashboard.layouts.app')
@section('title', 'أرشيف الدورات')

@section('content')
    <div class="container-fluid mt-5">


        <!-- Page Heading -->
        <h1 class="h3 mb-4 fw-bold text-center"
            style="font-family: 'IBMPlexSansArabic', sans-serif; font-size: 2.2rem; text-shadow: 1px 1px 2px rgba(0,0,0,0.1); color: var(--bs-primary);">
            أرشيف الدورات
        </h1>

        <!-- Search Form & Create Button -->
        <div class="mb-4">
            <div class="row g-3">
                <div class="col-12 col-lg-5">
                    <form method="GET" action="{{ route('admin.courses.index') }}">
                        <div class="input-group shadow-sm">
                            <input type="text" name="search_value" class="form-control w-25 search-input rounded-start"
                                placeholder="🔍 ابحث عن دورة..." value="{{ request('search_value') }}">
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>

        <!-- Courses Table -->
        <div class="card shadow-lg border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped table-bordered mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="py-3">#</th>
                                <th class="py-3">الاسم</th>
                                <th class="py-3 text-center">الخيارات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($courses as $course)
                                <tr class="hover-lift">
                                    <td class="align-middle">{{ $course->id }}</td>
                                    <td class="align-middle">{{ $course->name }}</td>
                                    <td class="align-middle text-center">
                                        <a href="{{ route('admin.oldcourse.show', $course->id) }}"
                                            class="btn btn-primary hover-scale">
                                            <i class="fas fa-eye me-1"></i> التفاصيل
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted">لا توجد دورات متاحة.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div class="mt-4 d-flex justify-content-center">
            {{ $courses->appends(request()->query())->links('pagination::bootstrap-5') }}
        </div>
    </div>
@endsection
