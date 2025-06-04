@extends('dashboard.layouts.app')

@section('title', 'إضافة طالب')

@section('content')
    <div class="container mt-5" dir="rtl">
        <!-- Page Header -->
        <div class="text-center mb-5">
            <h1 class="display-6 fw-bold text-success">
                <i class="fas fa-user-graduate me-2"></i> إضافة طالب جديد
            </h1>
            <p class="text-black">املأ البيانات التالية لإضافة طالب جديد للنظام</p>
        </div>
        @include('dashboard.layouts.alert')

        <!-- Form Card -->
        <div class="card shadow-sm border-success mb-5">
            <div class="card-header bg-success text-white text-center">
                <h5 class="mb-0 text-white"><i class="fas fa-user-plus me-2"></i> بيانات الطالب</h5>
            </div>
            <div class="card-body bg-light-gray p-4">
                <form action="{{ route('admin.students.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-3">

                        <!-- Student Name -->
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="name" class="form-label fw-bold">
                                    <i class="fas fa-user me-2"></i> اسم الطالب
                                </label>
                                <input type="text" name="name" id="name"
                                    class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}"
                                    placeholder="أدخل اسم الطالب">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Parents Names -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="father_name" class="form-label fw-bold">
                                    <i class="fas fa-male me-2"></i> اسم الأب
                                </label>
                                <input type="text" name="father_name" id="father_name"
                                    class="form-control @error('father_name') is-invalid @enderror"
                                    value="{{ old('father_name') }}" placeholder="أدخل اسم الأب">
                                @error('father_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="mother_name" class="form-label fw-bold">
                                    <i class="fas fa-female me-2"></i> اسم الأم
                                </label>
                                <input type="text" name="mother_name" id="mother_name"
                                    class="form-control @error('mother_name') is-invalid @enderror"
                                    value="{{ old('mother_name') }}" placeholder="أدخل اسم الأم">
                                @error('mother_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Parents Jobs -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="father_work" class="form-label fw-bold">
                                    <i class="fas fa-briefcase me-2"></i> عمل الأب
                                </label>
                                <input type="text" name="father_work" id="father_work"
                                    class="form-control @error('father_work') is-invalid @enderror"
                                    value="{{ old('father_work') }}" placeholder="أدخل عمل الأب">
                                @error('father_work')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="mother_work" class="form-label fw-bold">
                                    <i class="fas fa-briefcase me-2"></i> عمل الأم
                                </label>
                                <input type="text" name="mother_work" id="mother_work"
                                    class="form-control @error('mother_work') is-invalid @enderror"
                                    value="{{ old('mother_work') }}" placeholder="أدخل عمل الأم">
                                @error('mother_work')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Contact Info -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="student_phone" class="form-label fw-bold">
                                    <i class="fas fa-mobile-alt me-2"></i> رقم الطالب
                                </label>
                                <input type="text" name="student_phone" id="student_phone"
                                    class="form-control @error('student_phone') is-invalid @enderror"
                                    value="{{ old('student_phone') }}" placeholder="أدخل رقم الطالب">
                                @error('student_phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="father_phone" class="form-label fw-bold">
                                    <i class="fas fa-phone-volume me-2"></i> رقم الأهل
                                </label>
                                <input type="text" name="father_phone" id="father_phone"
                                    class="form-control @error('father_phone') is-invalid @enderror"
                                    value="{{ old('father_phone') }}" placeholder="أدخل رقم الأهل">
                                @error('father_phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Additional Info -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="birthday" class="form-label fw-bold">
                                    <i class="fas fa-birthday-cake me-2"></i> تاريخ الميلاد
                                </label>
                                <input type="date" name="birthday" id="birthday"
                                    class="form-control @error('birthday') is-invalid @enderror"
                                    value="{{ old('birthday') }}">
                                @error('birthday')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="location" class="form-label fw-bold">
                                    <i class="fas fa-map-marker-alt me-2"></i> العنوان
                                </label>
                                <input type="text" name="location" id="location"
                                    class="form-control @error('location') is-invalid @enderror"
                                    value="{{ old('location') }}" placeholder="أدخل العنوان">
                                @error('location')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Education Info -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="circle" class="form-label fw-bold">
                                    <i class="fas fa-users me-2"></i> الحلقة
                                </label>
                                <select name="circle" id="circle"
                                    class="form-select @error('circle') is-invalid @enderror">
                                    @foreach ($circles as $circle)
                                        <option value="{{ $circle->id }}"
                                            {{ old('circle') == $circle->id ? 'selected' : '' }}>
                                            {{ $circle->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('circle')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="level" class="form-label fw-bold">
                                    <i class="fas fa-layer-group me-2"></i> المستوى
                                </label>
                                <select name="level" id="level"
                                    class="form-select @error('level') is-invalid @enderror">
                                    @foreach ($levels as $level)
                                        <option value="{{ $level->id }}"
                                            {{ old('level') == $level->id ? 'selected' : '' }}>
                                            {{ $level->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('level')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="class" class="form-label fw-bold">
                                    <i class="fas fa-graduation-cap me-2"></i> الصف
                                </label>
                                <input type="text" name="class" id="class"
                                    class="form-control @error('class') is-invalid @enderror"
                                    value="{{ old('class') }}" placeholder="أدخل الصف الدراسي">
                                @error('class')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="school" class="form-label fw-bold">
                                    <i class="fas fa-school me-2"></i> المدرسة
                                </label>
                                <input type="text" name="school" id="school"
                                    class="form-control @error('school') is-invalid @enderror"
                                    value="{{ old('school') }}" placeholder="أدخل اسم المدرسة">
                                @error('school')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="col-12 mt-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="order-2">
                                    <button type="submit" class="btn btn-success btn-lg px-5 me-3 shadow-sm">
                                        <i class="fas fa-user-plus me-2"></i> إضافة الطالب
                                    </button>
                                </div>
                                <div class="order-1">
                                    <a href="{{ route('admin.students.index') }}"
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
