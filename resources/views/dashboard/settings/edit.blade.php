@php
    // from controller
    /** @var \Illuminate\Database\Eloquent\Collection|\App\Models\AttendanceType[] $attendanceTypes */
@endphp
@extends('dashboard.layouts.app')
@section('title', 'الإعدادات العامة')
@section('content')
    <div class="container mt-5" dir="rtl">
        @include('dashboard.layouts.alert')
        <div class="text-center mb-5 mt-3">
            <h1 class="display-6 fw-bold text-success">
                <i class="fas fa-cogs me-2"></i> إعدادات التسميع والسبر
            </h1>
            <p class="text-muted">قم بتعديل النطاقات والقيم أدناه ثم احفظ التغييرات مرة واحدة.</p>
        </div>

        @include('dashboard.layouts.alert')

        <form action="{{ route('admin.settings.update') }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <h3 class="mb-2 text-center text-white"><i class="fas fa-chart-bar me-2"></i> إعدادات النتيجة</h3>
                </div>
                <div class="card-body">
                    @foreach (['recitation' => 'التسميع', 'sabr' => 'السبر'] as $typeKey => $typeLabel)
                        <h4 class="fw-bold text-primary text-center mt-4 mb-3">{{ $typeLabel }}</h4>
                        <div class="row g-3 mt-2">
                            @foreach ($resultSet->where('type', $typeKey) as $setting)
                                <div class="col-md-3">
                                    <div class="border rounded-3 p-3 h-100">
                                        <h6 class="text-center mb-3">{{ $setting->name }}</h6>
                                        <div class="row g-2 align-items-center">
                                            <div class="col-3 text-end">
                                                <label for="min_{{ $setting->id }}" class="form-label">من</label>
                                            </div>
                                            <div class="col-9">
                                                <input type="number" id="min_{{ $setting->id }}"
                                                    name="result_settings[{{ $setting->id }}][min_res]"
                                                    class="border form-control fw-bold form-control-sm"
                                                    value="{{ old("result_settings.{$setting->id}.min_res", $setting->min_res) }}">
                                            </div>
                                            <div class="col-3 text-end">
                                                <label for="max_{{ $setting->id }}" class="form-label">إلى</label>
                                            </div>
                                            <div class="col-9">
                                                <input type="number" id="max_{{ $setting->id }}"
                                                    name="result_settings[{{ $setting->id }}][max_res]"
                                                    class="border fw-bold form-control form-control-sm"
                                                    value="{{ old("result_settings.{$setting->id}.max_res", $setting->max_res) }}">
                                            </div>
                                        </div>
                                        <div class="mt-3">
                                            <label for="points_{{ $setting->id }}"
                                                class="text-center form-label">النقاط</label>
                                            <input type="number" id="points_{{ $setting->id }}"
                                                name="result_settings[{{ $setting->id }}][points]"
                                                class="border fw-bold form-control form-control-sm"
                                                value="{{ old("result_settings.{$setting->id}.points", $setting->points) }}">
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @if ($typeLabel == 'التسميع')
                            <br>
                        @endif
                    @endforeach
                </div>
            </div>

            <!-- Level Mistakes -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-info text-white">
                    <h3 class="mb-2 text-white text-center"><i class="fas fa-exclamation-triangle me-2"></i> قيم الأخطاء
                        بحسب المستوى</h3>
                </div>
                <div class="card-body">
                    @foreach ($levels as $level)
                        <div class=" mb-4">
                            @if ($level->name != 'مبتدئ')
                                <br><br>
                            @endif
                            <h3 class="fw-bold text-primary text-center mb-3">المستوى: {{ $level->name }}</h3>
                            @foreach (['recitation' => 'التسميع', 'sabr' => 'السبر'] as $typeKey => $typeLabel)
                                <h5 class="fw-semibold text-black mt-3 mb-3">{{ $typeLabel }}</h5>
                                <div class="row g-3 mt-2">
                                    @foreach ($mistakes->where('type', $typeKey) as $mistake)
                                        @php
                                            $pivot =
                                                $level->mistakes->firstWhere('id', $mistake->id)?->pivot->value ?? 0;
                                        @endphp
                                        <div class="col-md-4">
                                            <div class="border rounded-3 p-3 h-100">
                                                <h6 class="text-center mb-3">قيمة خطأ ال{{ $mistake->name }}</h6>
                                                <div class="mt-2">
                                                    <input type="number" id="lm_{{ $level->id }}_{{ $mistake->id }}"
                                                        name="level_mistakes[{{ $level->id }}][{{ $mistake->id }}]"
                                                        class="form-control form-control-sm"
                                                        value="{{ old("level_mistakes.{$level->id}.{$mistake->id}", $pivot) }}">
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>
            <!-- Attendance Types -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-warning text-white">
                    <h3 class="mb-2 text-white text-center">
                        <i class="fas fa-user-check me-2"></i> قيم الحضور
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @foreach ($attendanceTypes as $type)
                            <div class="col-md-3">
                                <div class="border rounded-3 p-3 h-100">
                                    <h6 class="text-center mb-3">{{ $type->name }}</h6>
                                    <input type="number" name="attendance_types[{{ $type->id }}]"
                                        class="form-control form-control-sm"
                                        value="{{ old("attendance_types.{$type->id}", $type->value) }}">
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="d-flex justify-content-center mb-5">
                <button type="submit" class="btn btn-success btn-lg">
                    <i class="fas fa-save me-2"></i> حفظ التغييرات
                </button>
            </div>
        </form>
    </div>
@endsection
