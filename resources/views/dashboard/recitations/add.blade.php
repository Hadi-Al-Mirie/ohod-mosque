@extends('dashboard.layouts.app')
@section('title', 'تسجيل تسميع جديد')
@section('content')
    <div class="container mt-5" dir="rtl">
        <!-- Page Header -->
        <div class="text-center mb-4">
            <h1 class="display-6 fw-bold text-success">
                <i class="fas fa-book-open me-2"></i> تسجيل تسميع جديد
            </h1>
            <p class="text-black">املأ البيانات التالية لتسجيل تسميع جديد للطالب</p>
        </div>

        @include('dashboard.layouts.alert')

        <!-- Form Card -->
        <div class="card shadow-sm border-success mb-5">
            <div class="card-header bg-success text-white text-center">
                <h5 class="mb-0 text-white"><i class="fas fa-pen me-2"></i> بيانات التسميع</h5>
            </div>
            <div class="card-body bg-light-gray p-4">
                <form action="{{ route('admin.recitations.store') }}" method="POST">
                    @csrf
                    <div class="row g-3">

                        {{-- Student --}}
                        <div class="col-md-6">
                            <div class=" mb-3">
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

                        {{-- Page --}}
                        <div class="col-md-6">
                            <div class=" mb-3">
                                <input type="number" name="page" id="page"
                                    class="form-control @error('page') is-invalid @enderror" value="{{ old('page') }}"
                                    placeholder="رقم الصفحة" min="0" max="604">
                                @error('page')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="mb-2"></div>
                        {{-- Mistakes --}}
                        <div class="col-12">
                            <div
                                class="bg-light p-3
                @error('mistakes') border border-danger rounded @enderror">
                                <h5 class="fw-bold text-success mb-3 text-center">
                                    <i class="fas fa-exclamation-circle me-2"></i> أدخل عدد كل خطأ
                                    </h6>
                                    <div class="row g-3">
                                        @foreach ($mistakes as $mistake)
                                            <div class="col-md-4">
                                                <label class="form-label d-flex justify-content-between mb-1">
                                                    <span>{{ $mistake->name }}</span>
                                                    <span class="badge bg-success">القيمة : {{ $mistake->value }}</span>
                                                </label>
                                                <input type="number" name="mistakes[{{ $mistake->id }}]"
                                                    id="mistake_{{ $mistake->id }}"
                                                    value="{{ old("mistakes.{$mistake->id}", 0) }}" min="0"
                                                    class="form-control form-control-sm @error("mistakes.{$mistake->id}") is-invalid @enderror">
                                                @error("mistakes.{$mistake->id}")
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        @endforeach
                                    </div>
                                    @error('mistakes')
                                        <div class="d-block text-danger mt-2">
                                            {{ $message }}
                                        </div>
                                    @enderror
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div class="col-12 mt-5">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="order-2">
                                    <button type="submit" class="btn btn-success btn-lg px-5 me-3 shadow-sm">
                                        <i class="fas fa-save me-2"></i> حفظ
                                    </button>
                                </div>
                                <div class="order-1">
                                    <a href="{{ route('admin.recitations.index') }}"
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
