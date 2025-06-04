@extends('dashboard.layouts.app')
@section('title', 'الملاحظات')
@section('content')
    <div class="container mt-5">
        @include('dashboard.layouts.alert')

        <h1 class="h3 mb-4 fw-bold text-center"
            style="font-family: 'IBMPlexSansArabic', sans-serif; font-size: 2.2rem; text-shadow: 1px 1px 2px rgba(0,0,0,0.1); color: var(--bs-primary);">
            أرشيف الملاحظات
        </h1>

        <!-- Search and Actions Section -->
        <div class="mb-4">
            <div class="row g-3">
                <div class="col-12 col-lg-8">
                    <form method="GET" action="{{ route('admin.notes.index') }}">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control search-input rounded-start"
                                placeholder="🔍 ابحث بالاسم أو العنوان" value="{{ request('search') }}">
                            <button class="btn btn-primary hover-scale" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                </div>
                <div class="col-12 col-lg-4 text-lg-end">
                    <a href="{{ route('admin.notes.create') }}" class="btn btn-success hover-scale">
                        <i class="fas fa-eye me-2"></i> تسجيل ملاحظة
                    </a>
                </div>
            </div>
        </div>

        <!-- Notes Table -->
        <div class="card shadow-lg border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped table-bordered mb-0">
                        <thead class="bg-gradient-primary text-white">
                            <tr>
                                <th class="py-3"><i class="fas fa-user me-2"></i> الطالب</th>
                                <th class="py-3"><i class="fas fa-chalkboard-teacher me-2"></i> الأستاذ</th>
                                <th class="py-3"><i class="fa-solid fa-file"></i>النوع</th>
                                <th class="py-3"><i class="fa-solid fa-file"></i> السبب</th>
                                <th class="py-3"><i class="fa-solid fa-file"></i> التاريخ</th>
                                <th class="py-3"><i class="fas fa-eye me-2"></i> الخيارات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($notes as $note)
                                <tr class="hover-lift">
                                    <td class="align-middle">
                                        {{ $note->student->user->name }}
                                    </td>
                                    <td class="align-middle">
                                        @if ($note->creator->role_id == 1)
                                            <span class="badge bg-primary">
                                                <i class="fas fa-user-shield me-2"></i> المشرف
                                            </span>
                                        @else
                                            <i class="fas fa-user-tie me-2"></i>
                                            {{ $note->creator->name }}
                                        @endif
                                    </td>
                                    <td class="align-middle fw-bold text-info">
                                        @if ($m = $note->type == 'positive')
                                            أيجابية
                                        @else
                                            سلبية
                                        @endif
                                    </td>
                                    <td class="align-middle fw-bold text-info">
                                        {{ $note->reason ?? '---' }}
                                    </td>
                                    <td class="align-middle fw-bold text-info">
                                        {{ \Carbon\Carbon::parse($note->created_at)->format('Y/m/d') }}
                                    </td>
                                    <td class="align-middle">
                                        <a href="{{ route('admin.notes.show', $note->id) }}"
                                            class="btn btn-sm btn-primary hover-scale">
                                            <i class="fas fa-external-link-alt me-2"></i> التفاصيل
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="mt-4 justify-content-center">
            {{ $notes->appends(request()->query())->links('pagination::bootstrap-5') }}
        </div>
    </div>
@endsection
