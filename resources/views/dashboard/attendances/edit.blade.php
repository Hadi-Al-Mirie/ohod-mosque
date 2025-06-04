@extends('dashboard.layouts.app')

@section('title', 'تعديل الحضور')

@section('content')
    <div class="container" dir="rtl">
        <!-- Page Header -->
        <div class="text-center mb-4">
            <h1 class="display-6 fw-bold text-success">
                <i class="fas fa-calendar-check me-2"></i> تعديل بيانات الحضور
            </h1>
            <p class="text-black">قم بتعديل بيانات الحضور للطالب</p>
        </div>
        @include('dashboard.layouts.alert')
        <div class="card shadow-sm border-success mb-5">
            <div class="card-header bg-success text-white text-center">
                <h5 class="mb-0 text-white"><i class="fas fa-edit me-2"></i> بيانات الحضور</h5>
            </div>
            <div class="card-body bg-light-gray p-4">
                <form action="{{ route('admin.attendances.update', $attendance->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="attendance_date" class="form-label fw-bold">تاريخ الحضور</label>
                                <div class="input-group">
                                    <input type="hidden" name="attendance_date"
                                        value="{{ old('attendance_date', $attendance->attendance_date->format('Y-m-d')) }}">
                                    <input type="date" id="attendance_date"
                                        class="form-control @error('attendance_date') is-invalid @enderror"
                                        value="{{ old('attendance_date', $attendance->attendance_date->format('Y-m-d')) }}"
                                        disabled style="cursor: not-allowed; background-color: #f8f9fa">
                                    @error('attendance_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Attendance Type -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="type_id" class="form-label fw-bold">نوع الحضور</label>
                                <select name="type_id" id="type_id"
                                    class="form-select @error('type_id') is-invalid @enderror">
                                    @foreach ($types as $type)
                                        <option value="{{ $type->id }}"
                                            {{ old('type_id', $attendance->type_id) == $type->id ? 'selected' : '' }}>
                                            {{ $type->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('type_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Justification Field -->
                        <div class="col-12" id="justificationField" style="display: none;">
                            <div class="mb-3">
                                <label for="justification" class="form-label fw-bold">تبرير الغياب</label>
                                <input type="text" name="justification" id="justification"
                                    class="form-control @error('justification') is-invalid @enderror"
                                    value="{{ old('justification', $attendance->justification) }}">
                                @error('justification')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="col-12 mt-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="order-2">
                                    <button type="submit" class="btn btn-success btn-lg px-5 me-3 shadow-sm">
                                        <i class="fas fa-save me-2"></i> تحديث
                                    </button>
                                </div>
                                <div class="order-1">
                                    <a href="{{ route('admin.attendances.index') }}"
                                        class="btn btn-secondary btn-lg px-4 shadow-sm">
                                        <i class="fas fa-arrow-left me-2"></i> عودة
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggleJustification = () => {
                const typeSelect = document.getElementById('type_id');
                const justificationField = document.getElementById('justificationField');
                const selectedType = typeSelect.options[typeSelect.selectedIndex].text.trim();
                justificationField.style.display = selectedType === 'غياب مبرر' ? 'block' : 'none';
            }
            toggleJustification();
            document.getElementById('type_id').addEventListener('change', toggleJustification);
            if (window.history.replaceState) {
                window.history.replaceState(null, null, window.location.href);
            }
        });
    </script>
@endsection
