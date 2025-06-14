@extends('dashboard.layouts.app')
@section('title', 'الحضور')
@section('content')
    <div class="container mt-5">
        @include('dashboard.layouts.alert')
        <!-- Header -->
        <div class="mb-4">
            <h1 class="h3 mb-4 fw-bold text-center"
                style="font-family:'IBMPlexSansArabic',sans-serif; font-size:2.2rem; text-shadow:1px 1px 2px rgba(0,0,0,0.1); color:var(--bs-primary);">
                <a href="{{ route('admin.attendances.index') }}">أرشيف الحضور</a>
            </h1>
        </div>

        <!-- Filters -->
        <div class="mb-4">
            <div class="row g-3 align-items-center">
                <div class="col-auto">
                    <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#filterModal">
                        <i class="fas fa-filter me-2"></i> خيارات البحث
                    </button>
                </div>
                <div class="col-auto ms-auto">
                    <a href="{{ route('admin.attendances.create') }}" class="btn btn-success hover-scale">
                        <i class="fas fa-user-plus me-2"></i> تسجيل حضور جديد
                    </a>
                </div>
            </div>
        </div>

        {{-- Filter Modal --}}
        <div class="modal fade" id="filterModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title w-100 text-center text-white">
                            <i class="fas fa-filter me-2"></i> فلاتر البحث
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="recitation-filter-form" method="GET" action="{{ route('admin.attendances.index') }}">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label fw-bold">اسم الطالب</label>
                                <input type="text" name="student_name" value="{{ request('student_name') }}"
                                    class="form-control" placeholder="🔍 اسم الطالب">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">النوع</label>
                                <select name="type" class="form-select">
                                    <option value="">— اختر النوع —</option>
                                    @foreach ($types as $t)
                                        <option value="{{ $t->id }}"
                                            {{ request('type') == $t->id ? 'selected' : '' }}>
                                            {{ $t->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="row g-2">
                                <div class="col">
                                    <label class="form-label fw-bold">التاريخ</label>
                                    <input type="date" name="date" value="{{ request('date') }}" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer d-flex justify-content-between">
                            <button type="submit" form="recitation-filter-form" class="btn btn-primary">
                                <i class="fas fa-search me-1"></i> تطبيق
                            </button>
                            <a href="{{ route('admin.attendances.index') }}" class="btn btn-danger">
                                <i class="fas fa-times me-1"></i> مسح الفلاتر
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>


        <!-- Table Section -->
        <div class="card shadow-lg">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped table-bordered border-top-0">
                        <thead class="bg-gradient-primary text-white">
                            <tr>
                                <th class="py-3"><i class="fas fa-user me-2"></i> الطالب</th>
                                <th class="py-3"><i class="fa-solid fa-circle-info"></i> النوع</th>
                                <th class="py-3"><i class="fas fa-archive me-2"></i> التبرير</th>
                                <th class="py-3"><i class="fas fa-archive me-2"></i> التاريخ</th>
                                <th class="py-3"><i class="fas fa-eye me-2"></i> الاجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($attendances as $attendance)
                                <tr class="align-middle">
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            {{ $attendance->student->user->name }}
                                        </div>
                                    </td>
                                    <td>
                                        <span
                                            class="badge rounded-pill {{ $attendance->type->name == 'حضور' ? 'bg-success' : 'bg-danger' }}">
                                            {{ $attendance->type->name }}
                                        </span>
                                    </td>
                                    <td class="text-center">{{ $attendance->justification ?? '---' }}</td>
                                    <td class="text-center fs-5">
                                        {{ \Carbon\Carbon::parse($attendance->attendance_date)->format('Y/m/d') }}
                                    </td>
                                    <td class="align-middle">
                                        <a href="{{ route('admin.attendances.edit', $attendance->id) }}"
                                            class="btn btn-sm btn-primary hover-scale">
                                            <i class="fas fa-external-link-alt me-2"></i> تعديل
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if ($attendances->isEmpty())
                    <div class="text-center bg-secondary py-5">
                        <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                        <p class="text-muted">لا توجد سجلات حضور متاحة</p>
                    </div>
                @endif
            </div>
        </div>
        @if ($attendances->hasPages())
            <div class="mt-4 d-flex justify-content-center">
                {{ $attendances->appends(request()->query())->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.date-picker-icon').forEach(container => {
                const dateInput = container.querySelector('input[type="date"]');
                if (!dateInput) return;

                container.addEventListener('click', () => {
                    // Remove any existing focus
                    if (document.activeElement) document.activeElement.blur();

                    // Show picker with delay to ensure blur completes
                    setTimeout(() => {
                        if (typeof dateInput.showPicker === 'function') {
                            dateInput.showPicker();
                        } else {
                            // Fallback for browsers without showPicker()
                            dateInput.focus();
                        }
                    }, 50);
                });
            });
        });
    </script>
@endsection
