@extends('dashboard.layouts.app')
@section('title', 'التسميع')
@section('content')
    <div class="container mt-5">
        @include('dashboard.layouts.alert')
        <h1 class="h3 mb-4 fw-bold text-center"
            style="font-family: 'IBMPlexSansArabic', sans-serif; font-size: 2.2rem; text-shadow: 1px 1px 2px rgba(0,0,0,0.1); color: var(--bs-primary);">
            أرشيف التسميعات
        </h1>
        <!-- Search and Actions Section -->
        <div class="mb-4">
            <div class="row g-3">
                <div class="col-12 col-lg-8">
                    <form method="GET" action="{{ route('admin.recitations.index') }}">
                        <div class="input-group">
                            {{-- we’ll always replace the element with id="search_value" here --}}
                            @if (request('search_field') == 'result')
                                <select name="search_value" id="search_value" class="form-select rounded-start">
                                    @foreach ($settings as $setting)
                                        <option value="{{ $setting->name }}"
                                            {{ request('search_value') == $setting->name ? 'selected' : '' }}>
                                            {{ $setting->name }}
                                        </option>
                                    @endforeach
                                </select>
                            @else
                                <input type="text" name="search_value" id="search_value"
                                    class="form-control search-input rounded-start" placeholder="🔍 أدخل كلمة البحث.."
                                    value="{{ request('search_value') }}">
                            @endif

                            <select name="search_field" class="form-select select-style" id="search_field">
                                <option value="student" {{ request('search_field') == 'student' ? 'selected' : '' }}>
                                    <i class="fas fa-user me-2"></i> اسم الطالب
                                </option>
                                <option value="teacher" {{ request('search_field') == 'teacher' ? 'selected' : '' }}>
                                    <i class="fas fa-chalkboard-teacher me-2"></i> اسم الاستاذ
                                </option>
                                <option value="result" {{ request('search_field') == 'result' ? 'selected' : '' }}>
                                    <i class="fas fa-star me-2"></i> النتيجة
                                </option>
                            </select>
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                </div>
                <div class="col-12 col-lg-4 text-lg-end">
                    <a href="{{ route('admin.recitations.create') }}" class="btn btn-success hover-scale">
                        <i class="fas fa-user-plus me-2"></i> تسجيل تسميع جديد
                    </a>
                </div>
            </div>
        </div>

        <!-- Recitations Table -->
        <div class="card shadow-lg border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped table-bordered mb-0">
                        <thead class="bg-gradient-primary text-white">
                            <tr>
                                <th class="py-3"><i class="fas fa-user me-2"></i> الطالب </th>
                                <th class="py-3"><i class="fas fa-chalkboard-teacher me-2"></i> الأستاذ </th>
                                <th class="py-3"><i class="fas fa-file-alt me-2"></i> الصفحة </th>
                                <th class="py-3"><i class="fas fa-star me-2"></i> النتيجة </th>
                                <th class="py-3"><i class="fas fa-eye me-2"></i> التحكم </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recitations as $recitation)
                                <tr class="hover-lift">
                                    <td class="align-middle">
                                        {{ $recitation->student->user->name }}
                                    </td>

                                    <td class="align-middle">
                                        @if ($recitation->creator->role_id == 1)
                                            <span class="badge bg-primary">
                                                <i class="fas fa-user-shield me-2"></i> المشرف
                                            </span>
                                        @else
                                            {{ $recitation->creator->name }}
                                        @endif
                                    </td>

                                    <td class="align-middle fw-bold text-info">
                                        {{ $recitation->page }}
                                    </td>

                                    @php
                                        $colorMap = [
                                            'ممتاز' => 'success',
                                            'جيد جداً' => 'primary',
                                            'جيد' => 'info',
                                            'إعادة' => 'warning',
                                        ];
                                        $iconMap = [
                                            'ممتاز' => 'fas fa-medal',
                                            'جيد جداً' => 'fas fa-thumbs-up',
                                            'جيد' => 'fas fa-thumbs-up',
                                            'إعادة' => 'fa-solid fa-triangle-exclamation',
                                        ];
                                        $name = $recitation->result_name ?? 'غير معروف';
                                        $color = $colorMap[$name] ?? 'secondary';
                                        $icon = $iconMap[$name] ?? 'fas fa-question-circle';
                                    @endphp
                                    <td class="align-middle">
                                        <span class="badge bg-{{ $color }} rounded-pill fs-6">
                                            <i class="{{ $icon }} me-1"></i>
                                            {{ $name }}
                                        </span>
                                    </td>

                                    <td class="align-middle">
                                        <a href="{{ route('admin.recitations.show', $recitation->id) }}"
                                            class="btn btn-sm btn-primary hover-scale">
                                            <i class="fas fa-external-link-alt me-2"></i> التفاصيل
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="mt-4 d-flex justify-content-center">
            {{ $recitations->appends(request()->query())->links('pagination::bootstrap-5') }}
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const labels = @json($settings->pluck('name'));
            const fieldSel = document.getElementById('search_field');
            const group = document.querySelector('.input-group');

            function makeSelect(current) {
                const s = document.createElement('select');
                s.name = 'search_value';
                s.id = 'search_value';
                s.className = 'form-select rounded-start';
                labels.forEach(lbl => {
                    const o = document.createElement('option');
                    o.value = lbl;
                    o.textContent = lbl;
                    if (lbl === current) o.selected = true;
                    s.appendChild(o);
                });
                return s;
            }

            function makeInput(current) {
                const i = document.createElement('input');
                i.type = 'text';
                i.name = 'search_value';
                i.id = 'search_value';
                i.className = 'form-control search-input rounded-start';
                i.placeholder = '🔍 أدخل كلمة البحث..';
                i.value = current || '';
                return i;
            }

            function swap() {
                const oldEl = document.getElementById('search_value');
                const curr = oldEl.value;
                const isRes = fieldSel.value === 'result';
                const newEl = isRes ?
                    makeSelect(curr) :
                    makeInput(curr);
                group.replaceChild(newEl, oldEl);
            }
            fieldSel.addEventListener('change', swap);
            swap();
        });
    </script>
@endsection
