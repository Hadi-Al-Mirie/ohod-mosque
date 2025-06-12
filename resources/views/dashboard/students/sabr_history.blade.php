@extends('dashboard.layouts.app')

@section('title', 'سجل السبر')

@section('content')
    <div class="container mt-4">
        <div class="mb-5 text-center">
            <h1 class="h3 fw-bold" style="color: var(--bs-primary);">
                سجل السبر للطالب {{ $student->user->name }}
            </h1>
            <p>الدورة الحالية: {{ $activeCourse->name }}</p>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" style="table-layout: fixed; width:100%;">
                        <colgroup>
                            <col style="width:30%">
                            <col style="width:70%">
                        </colgroup>
                        <thead class="bg-light">
                            <tr>
                                <th>الجزء</th>
                                <th>الحالة</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rows as $row)
                                <tr>
                                    <td>{{ $row['juz'] }}</td>
                                    <td>
                                        @if ($row['result'])
                                            <span class="badge bg-success">{{ $row['result'] }}</span>
                                        @elseif($row['recited'])
                                            <span class="badge bg-info">مسبقًا</span>
                                        @else
                                            <span class="badge bg-danger">غير مسجل</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="text-center mt-4">
            <a href="{{ route('admin.students.show', $student->id) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i> العودة للملف
            </a>
        </div>
    </div>
@endsection
