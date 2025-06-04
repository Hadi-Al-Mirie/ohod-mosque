@extends('dashboard.layouts.app')

@section('title', 'تعديل الحلقة')

@section('content')
    <div class="container mt-5" dir="rtl">
        <h1 class="h3 mb-4 text-center">تعديل بيانات الحلقة</h1>

        @include('dashboard.layouts.alert')

        <form action="{{ route('admin.circles.update', $circle->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="name" class="form-label">اسم الحلقة</label>
                <input type="text" name="name" id="name" class="form-control"
                    value="{{ old('name', $circle->name) }}" required>
            </div>

            <div class="mb-3">
                <label for="teacher_id" class="form-label">اختر الأستاذ</label>
                <select name="teacher_id" id="teacher_id" class="form-select">
                    <option value="">-- بدون --</option>
                    @foreach ($teachers as $teacher)
                        <option value="{{ $teacher->id }}"
                            {{ old('teacher_id', optional($circle->teacher)->id) == $teacher->id ? 'selected' : '' }}>
                            {{ $teacher->user->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn btn-primary float-start">حفظ التعديلات</button>
            <a href="{{ route('admin.circles.index') }}" class="btn btn-secondary ms-2 float-end">إلغاء</a>
        </form>
    </div>
@endsection
