@extends('dashboard.layouts.app')

@section('title', 'تعديل طالب')

@section('content')
    <div class="container mt-5">
        <div class="mb-5 text-center">
            <h1 class="h2 fw-bold text-standout">
                <i class="fas fa-user-edit me-2"></i> تعديل بيانات الطالب
            </h1>
            <p class="text-black-60">قم بتعديل البيانات التالية و حفظها</p>
        </div>
        @include('dashboard.layouts.alert')
        <div class="card shadow-sm border-light-gray" style="border-color: #e7e9ed !important;">
            <div class="card-body p-4">
                <form action="{{ route('admin.students.update', $student->id) }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="row g-4">
                        <!-- Left Column -->
                        <div class="col-md-6">
                            <div class="form-group floating-label">
                                <input type="date" name="birthday" id="birthday" class="form-control"
                                    value="{{ old('birthday', $student->birth) }}">
                                <label for="birthday"><i class="fas fa-birthday-cake me-2"></i> تاريخ الميلاد</label>
                            </div>

                            <div class="form-group floating-label">
                                <input type="text" name="mother_name" id="mother_name" class="form-control"
                                    value="{{ old('mother_name', $student->mother_name) }}">
                                <label for="mother_name"><i class="fas fa-female me-2"></i> اسم الأم</label>
                            </div>

                            <div class="form-group floating-label">
                                <input type="text" name="mother_work" id="mother_work" class="form-control"
                                    value="{{ old('mother_work', $student->mother_job) }}">
                                <label for="mother_work"><i class="fas fa-briefcase me-2"></i> عمل الأم</label>
                            </div>

                            <div class="form-group floating-label">
                                <input type="text" name="student_phone" id="student_phone" class="form-control"
                                    placeholder="09........" value="{{ old('student_phone', $student->student_phone) }}">
                                <label for="student_phone"><i class="fas fa-mobile-alt me-2 text-standout"></i> رقم
                                    الطالب</label>
                            </div>

                            <div class="form-group floating-label">
                                <select name="level" id="level" class="form-select">
                                    @foreach ($levels as $level)
                                        <option value="{{ $level->id }}"
                                            {{ old('level', $student->level_id) == $level->id ? 'selected' : '' }}>
                                            {{ $level->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <label for="level"><i class="fas fa-layer-group me-2 text-standout"></i> المستوى</label>
                            </div>

                            <div class="form-group floating-label">
                                <input type="text" name="class" id="class" class="form-control"
                                    value="{{ old('class', $student->class) }}">
                                <label for="class"><i class="fas fa-graduation-cap me-2"></i> الصف</label>
                            </div>
                        </div>
                        <!-- Right Column -->
                        <div class="col-md-6">
                            <div class="form-group floating-label">
                                <input type="text" name="name" id="name" class="form-control"
                                    value="{{ old('name', $student->user->name) }}" required>
                                <label for="name"><i class="fas fa-user me-2 text-standout"></i> اسم الطالب</label>
                            </div>

                            <div class="form-group floating-label">
                                <input type="text" name="father_name" id="father_name" class="form-control"
                                    value="{{ old('father_name', $student->father_name) }}">
                                <label for="father_name"><i class="fas fa-male me-2"></i> اسم الأب</label>
                            </div>

                            <div class="form-group floating-label">
                                <input type="text" name="father_work" id="father_work" class="form-control"
                                    value="{{ old('father_work', $student->father_job) }}">
                                <label for="father_work"><i class="fas fa-briefcase me-2"></i> عمل الأب</label>
                            </div>

                            <div class="form-group floating-label">
                                <input type="text" name="father_phone" id="father_phone" class="form-control"
                                    placeholder="09........" value="{{ old('father_phone', $student->father_phone) }}">
                                <label for="father_phone"><i class="fas fa-phone-volume me-2"></i> رقم الأهل</label>
                            </div>

                            <div class="form-group floating-label">
                                <select name="circle" id="circle" class="form-select">
                                    @foreach ($circles as $circle)
                                        <option value="{{ $circle->id }}"
                                            {{ old('circle', $student->circle_id) == $circle->id ? 'selected' : '' }}>
                                            {{ $circle->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <label for="circle"><i class="fas fa-users me-2"></i> الحلقة</label>
                            </div>

                            <div class="form-group floating-label">
                                <input type="text" name="school" id="school" class="form-control"
                                    value="{{ old('school', $student->school) }}">
                                <label for="school"><i class="fas fa-school me-2 text-standout"></i> المدرسة</label>
                            </div>
                        </div>
                        <!-- Full Width Location -->
                        <div class="col-12">
                            <div class="form-group floating-label">
                                <input type="text" name="location" id="location" class="form-control"
                                    placeholder="دمشق/../../.." value="{{ old('location', $student->location) }}">
                                <label for="location"><i class="fas fa-map-marker-alt me-2"></i> العنوان</label>
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="col-12 text-center mt-5">
                            <button type="submit" class="btn btn-lg btn-primary hover-scale float-start"
                                style="background: #049977 !important; border-color: #049977 !important;">
                                <i class="fas fa-save me-2"></i> حفظ التعديلات
                            </button>
                            <a href="{{ route('admin.students.index') }}"
                                class="btn btn-lg btn-outline-standout hover-scale float-end">
                                <i class="fas fa-arrow-left me-2"></i> العودة للقائمة
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
