@extends('dashboard.layouts.app')
@section('title', 'تسجيل ملاحظة')
@section('content')
    <div class="container mt-5" dir="rtl">
        <!-- Page Header -->
        <div class="text-center mb-4">
            <h1 class="display-6 fw-bold text-success">
                <i class="fas fa-sticky-note me-2"></i> تسجيل ملاحظة جديدة
            </h1>
            <p class="text-black">املأ البيانات التالية لتسجيل ملاحظة جديدة للطالب</p>
        </div>

        @include('dashboard.layouts.alert')

        <!-- Form Card -->
        <div class="card shadow-sm border-success mb-5">
            <div class="card-header bg-success text-white text-center">
                <h5 class="mb-0 text-white"><i class="fas fa-edit me-2"></i> بيانات الملاحظة</h5>
            </div>

            <div class="card-body bg-light-gray p-4">
                <form action="{{ route('admin.notes.store') }}" method="POST">
                    @csrf
                    <div class="row g-3">

                        <!-- Student Selection -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">الطالب</label>
                                <select name="student_id" id="student_id"
                                    class="form-select @error('student_id') is-invalid @enderror">
                                    <option value="">-- اختر الطالب --</option>
                                    @foreach ($students as $stu)
                                        <option value="{{ $stu->id }}"
                                            {{ old('student_id') == $stu->id ? 'selected' : '' }}>
                                            {{ $stu->user->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('student_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Reason Input -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">السبب</label>
                                <input type="text" name="reason" id="reason"
                                    class="form-control @error('reason') is-invalid @enderror"
                                    placeholder="أدخل سبب الملاحظة" value="{{ old('reason') }}">
                                @error('reason')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Type Selection -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">نوع الملاحظة</label>
                                <div class="border rounded-3 bg-white p-2">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="type" id="positive"
                                                    value="positive" {{ old('type') == 'positive' ? 'checked' : '' }}>
                                                <label class="form-check-label text-success fw-bold" for="positive">
                                                    إيجابية <i class="fas fa-thumbs-up me-2"></i>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="type" id="negative"
                                                    value="negative" {{ old('type') == 'negative' ? 'checked' : '' }}>
                                                <label class="form-check-label  fw-bold text-danger" for="negative">
                                                    سلبية <i class="fas fa-thumbs-down me-2"></i>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @error('type')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Value Input -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">القيمة</label>
                                <div class="input-group">
                                    <input type="number" name="value" id="value" min="1"
                                        class="form-control @error('value') is-invalid @enderror"
                                        placeholder="أدخل قيمة الملاحظة" value="{{ old('value') }}">
                                    @error('value')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="col-12 mt-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="order-2">
                                    <button type="submit" class="btn btn-success btn-lg px-5 me-3 shadow-sm">
                                        <i class="fas fa-save me-2"></i> حفظ
                                    </button>
                                </div>
                                <div class="order-1">
                                    <a href="{{ route('admin.notes.index') }}"
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
@endsection
