@extends('dashboard.layouts.app')

@section('title', 'سجل التسميع حسب الجزء')

@section('content')
    <div class="container mt-4">
        <div class="mb-5 text-center">
            <h1 class="h3 mb-4 fw-bold"
                style="font-family: 'IBMPlexSansArabic', sans-serif; font-size: 2.2rem; color: var(--bs-primary);">
                سجل التسميع للطالب {{ $student->user->name }}
            </h1>
        </div>

        {{-- Juz Filter --}}
        @php
            $selectedJuz = request()->get('juz');
        @endphp
        <div class="row mb-4 justify-content-center">
            <div class="col-md-4">
                <form method="GET" class="d-flex">
                    <select name="juz" class="form-select me-2">
                        <option value="">كل الأجزاء</option>
                        @for ($j = 1; $j <= 30; $j++)
                            <option value="{{ $j }}" {{ $selectedJuz == $j ? 'selected' : '' }}>
                                الجزء {{ $j }}
                            </option>
                        @endfor
                    </select>
                    <button type="submit" class="btn btn-primary">تصفية</button>
                </form>
            </div>
        </div>

        {{-- Table --}}
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive overflow-x-auto overflow-y-visible">
                    <table class="table table-hover table-striped table-bordered mb-5"
                        style="table-layout: fixed; width: 100%;">
                        <colgroup>
                            <col style="width: 30%;">
                            <col style="width: 70%;">
                        </colgroup>
                        <thead class="bg-light">
                            <tr>
                                <th>رقم الصفحة</th>
                                <th class="ps-5 pe-5">الحالة</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rows as $row)
                                @php
                                    // compute page range for selected juz
                                    if ($selectedJuz) {
                                        if ($selectedJuz == 1) {
                                            $start = 1;
                                            $end = 21;
                                        } elseif ($selectedJuz == 30) {
                                            $start = 582;
                                            $end = 604;
                                        } else {
                                            $start = 22 + ($selectedJuz - 2) * 20;
                                            $end = $start + 19;
                                        }
                                        if ($row['page'] < $start || $row['page'] > $end) {
                                            // skip pages outside the selected juz
                                            continue;
                                        }
                                    }
                                @endphp
                                <tr>
                                    <td>{{ $row['page'] }}</td>
                                    <td>
                                        @if ($row['result'])
                                            <span class="badge bg-success fs-5">{{ $row['result'] }}</span>
                                        @elseif ($row['recited'])
                                            <span class="badge bg-success fs-5">سمعها في دورة سابقة </span>
                                        @else
                                            <span class="badge bg-danger fs-5">لم يسمع</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Back Button --}}
        <div class="text-center mt-4">
            <a href="{{ route('admin.students.show', $student->id) }}" class="btn btn-secondary px-5 py-2">
                <i class="fas fa-arrow-left me-2"></i> العودة
            </a>
        </div>
    </div>
@endsection
