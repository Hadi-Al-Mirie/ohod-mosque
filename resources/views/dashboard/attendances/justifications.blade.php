@extends('dashboard.layouts.app')
@section('title', 'طلبات تبرير الغياب')
@section('content')
    <div class="container mt-5" dir="rtl">
        <!-- Header -->
        <div class="mb-4">
            <h1 class="h3 mb-4 fw-bold text-center"
                style="font-family:'IBMPlexSansArabic',sans-serif; font-size:2.2rem; text-shadow:1px 1px 2px rgba(0,0,0,0.1); color:var(--bs-primary);">
                طلبات تبرير الغياب قيد الانتظار
            </h1>
        </div>

        @include('dashboard.layouts.alert')

        <!-- Table Section -->
        <div class="card shadow-lg">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped table-bordered border-top-0">
                        <thead class="bg-gradient-primary text-white">
                            <tr>
                                <th class="py-3"><i class="fas fa-user me-2"></i> الطالب</th>
                                <th class="py-3"><i class="fas fa-calendar-day me-2"></i> التاريخ</th>
                                <th class="py-3"><i class="fas fa-file-alt me-2"></i> المبرر</th>
                                <th class="py-3"><i class="fas fa-user-tie me-2"></i> الأستاذ</th>
                                <th class="py-3"><i class="fas fa-tasks me-2"></i> الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($requests as $r)
                                <tr class="align-middle">
                                    <td class="ps-4">
                                        {{ $r->attendance->student->user->name }}
                                    </td>
                                    <td class="text-center fs-5">
                                        {{ $r->attendance->attendance_date->format('Y/m/d') }}
                                    </td>
                                    <td class="text-center">{{ $r->justification }}</td>
                                    <td class="text-center">{{ $r->requester->name }}</td>
                                    <td class="align-middle">
                                        <form action="{{ route('admin.attendances.justifications.approve', $r) }}"
                                            method="POST" class="d-inline">
                                            @csrf @method('PATCH')
                                            <button class="btn btn-success btn-sm hover-scale">
                                                <i class="fas fa-check-circle me-2"></i> قبول
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.attendances.justifications.reject', $r) }}"
                                            method="POST" class="d-inline">
                                            @csrf @method('PATCH')
                                            <button class="btn btn-danger btn-sm hover-scale">
                                                <i class="fas fa-times-circle me-2"></i> رفض
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center bg-secondary py-5">
                                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">لا توجد طلبات حالياً</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @if ($requests->hasPages())
            <div class="mt-4 d-flex justify-content-center">
                {{ $requests->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
@endsection
