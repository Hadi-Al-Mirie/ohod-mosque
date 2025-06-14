@extends('dashboard.layouts.app')
@section('title', 'أرشيف الأوقاف')
@section('content')
    <div class="container mt-5">
        @include('dashboard.layouts.alert')
        <h1 class="h3 mb-4 fw-bold text-center">أرشيف الأوقاف</h1>
        <div class="mb-4">
            <div class="row g-3">
                <div class="col-12 col-lg-8">
                    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#filterModal">
                        <i class="fas fa-filter me-2"></i> خيارات البحث
                    </button>
                </div>
            </div>
        </div>

        <div class="card shadow-lg border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped table-bordered mb-0">
                        <thead class="bg-gradient-primary text-white">
                            <tr>
                                <th>الطالب</th>
                                <th>الأستاذ</th>
                                <th>الحالة</th>
                                <th>النتيجة</th>
                                <th>الإجراء</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($awqafs as $a)
                                <tr>
                                    <td>{{ $a->student->user->name }}</td>
                                    <td>{{ $a->creator->name }}</td>
                                    <td>{{ $a->type_label }}</td>
                                    <td class="fw-bold">{{ $a->result ?? 'لم تحدد' }}</td>
                                    <td>
                                        <a href="{{ route('admin.awqafs.edit', $a->id) }}" class="btn btn-sm btn-primary">
                                            <i class="fas fa-external-link-alt me-2"></i> تعديل
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">لا توجد سجلات</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @if ($awqafs->hasPages())
            <div class="mt-4 d-flex justify-content-center">
                {{ $awqafs->links('pagination::bootstrap-5') }}
            </div>
        @endif
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
                <div class="modal-body">
                    @php
                        $myFilters = [
                            ['key' => 'student', 'label' => 'اسم الطالب', 'type' => 'text'],
                            ['key' => 'teacher', 'label' => 'اسم الأستاذ', 'type' => 'text'],
                            ['key' => 'result', 'label' => 'النتيجة', 'type' => 'number'],
                            ['key' => 'date', 'label' => 'التاريخ', 'type' => 'date'],
                        ];
                    @endphp
                    <x-filter-box :action="route('admin.awqafs.index')" :filters="$myFilters" />
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <button type="submit" form="filter-form" class="btn btn-primary">
                        <i class="fas fa-search me-1"></i> بحث
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        إلغاء
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection
