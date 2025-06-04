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
            <div class="row g-3">
                <div class="col-12 col-lg-8">
                    <form method="GET" action="{{ route('admin.attendances.index') }}">
                        <div class="input-group">
                            {{-- 1) Date-picker icon only --}}
                            <div class="input-group-text bg-white d-flex justify-content-center align-items-center date-picker-icon"
                                style="position: relative; width:50px; cursor:pointer; padding:0;">
                                <!-- Transparent date input covering whole container -->
                                <input type="date" name="date" value="{{ old('date', request('date')) }}"
                                    class="position-absolute top-0 start-0 w-100 h-100 opacity-0"
                                    style="z-index:2; pointer-events: none;">
                                @error('date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <!-- Calendar icon -->
                                <i class="fas fa-calendar-alt text-black" style="z-index:1; font-size:1.2em;"></i>
                            </div>

                            {{-- 2) Attendance Type --}}
                            <select name="type" class="form-select @error('type') is-invalid @enderror">
                                <option value="">الكل</option>
                                <option value="1" {{ request('type') == '1' ? 'selected' : '' }}>حضور</option>
                                <option value="2" {{ request('type') == '2' ? 'selected' : '' }}>غياب غير مبرر</option>
                                <option value="3" {{ request('type') == '3' ? 'selected' : '' }}>غياب مبرر</option>
                                <option value="4" {{ request('type') == '4' ? 'selected' : '' }}>تأخير</option>
                            </select>

                            {{-- 3) Student search --}}
                            <input type="text" name="search_value"
                                class="form-control @error('search_value') is-invalid @enderror"
                                placeholder="🔍 أدخل اسم الطالب.."
                                value="{{ old('search_value', request('search_value')) }}">

                            {{-- 4) Submit --}}
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>

                        {{-- Validation --}}
                        @error('date')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        @error('type')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        @error('search_value')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </form>
                </div>

                <div class="col-12 col-lg-4 text-lg-end">
                    <a href="{{ route('admin.attendances.create') }}" class="btn btn-success hover-scale">
                        <i class="fas fa-user-plus me-2"></i> تسجيل حضور جديد
                    </a>
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
