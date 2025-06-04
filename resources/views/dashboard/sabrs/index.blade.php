@extends('dashboard.layouts.app')
@section('title', 'السبر')
@section('content')
    <div class="container mt-5">
        @include('dashboard.layouts.alert')
        <h1 class="h3 mb-4 fw-bold text-center"
            style="font-family: 'IBMPlexSansArabic', sans-serif; font-size: 2.2rem; text-shadow: 1px 1px 2px rgba(0,0,0,0.1); color: var(--bs-primary);">
            أرشيف السبورة
        </h1>
        <div class="mb-4">
            <div class="row g-3">
                <div class="col-12 col-lg-8">
                    <form method="GET" action="{{ route('admin.sabrs.index') }}">
                        <div class="input-group">
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
                                <input type="text" name="search_value" class="form-control search-input rounded-start"
                                    placeholder="🔍 أدخل كلمة البحث.." value="{{ request('search_value') }}"
                                    id="search_value">
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
                    <a href="{{ route('admin.sabrs.create') }}" class="btn btn-success hover-scale">
                        <i class="fas fa-user-plus me-2"></i> تسجيل سبر جديد
                    </a>
                </div>
            </div>
        </div>
        <div class="card shadow-lg border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped table-bordered mb-0">
                        <thead class="bg-gradient-primary text-white">
                            <tr>
                                <th class="py-3"><i class="fas fa-user me-2"></i> الطالب</th>
                                <th class="py-3"><i class="fas fa-chalkboard-teacher me-2"></i> الأستاذ</th>
                                <th class="py-3"><i class="fas fa-star me-2"></i> النتيجة</th>
                                <th class="py-3"><i class="fas fa-eye me-2"></i> التحكم</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($sabrs as $sabr)
                                <tr class="hover-lift">
                                    <td class="align-middle">
                                        {{ $sabr->student->user->name }}
                                    </td>
                                    <td class="align-middle">
                                        @if ($sabr->creator->role_id == 1)
                                            <span class="badge bg-primary">
                                                <i class="fas fa-user-shield me-2"></i> المشرف
                                            </span>
                                        @else
                                            {{ $sabr->creator->name }}
                                        @endif
                                    </td>
                                    @php
                                        // maps from bucket-name to colors/icons
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

                                        // pull directly from our joined column:
                                        $name = $sabr->result_name ?? 'غير معروف';
                                        $color = $colorMap[$name] ?? 'secondary';
                                        $icon = $iconMap[$name] ?? 'fas fa-question-circle';
                                    @endphp

                                    <td class="align-middle">
                                        <span class="badge bg-{{ $color }} rounded-pill fs-6">
                                            <i class="{{ $icon }} me-1"></i> {{ $name }}
                                        </span>
                                    </td>
                                    <td class="align-middle">
                                        <a href="{{ route('admin.sabrs.show', $sabr->id) }}"
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
            {{ $sabrs->appends(request()->query())->links('pagination::bootstrap-5') }}
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const settingsNames = @json($settings->pluck('name')); // e.g. ['ممتاز','جيد جدا',...]
            const searchField = document.getElementById('search_field');
            const inputGroup = document.querySelector('.input-group');

            function createResultSelect(selectedValue) {
                const sel = document.createElement('select');
                sel.name = 'search_value';
                sel.id = 'search_value';
                sel.className = 'form-select rounded-start';

                settingsNames.forEach(name => {
                    const opt = document.createElement('option');
                    opt.value = name;
                    opt.textContent = name;
                    if (name === selectedValue) opt.selected = true;
                    sel.appendChild(opt);
                });
                return sel;
            }

            function createTextInput(currentValue) {
                const inp = document.createElement('input');
                inp.type = 'text';
                inp.name = 'search_value';
                inp.id = 'search_value';
                inp.className = 'form-control search-input rounded-start';
                inp.placeholder = '🔍 أدخل كلمة البحث..';
                inp.value = currentValue || '';
                return inp;
            }

            function swapField() {
                const isResult = searchField.value === 'result';
                const oldEl = document.getElementById('search_value');
                const current = oldEl ? oldEl.value : '';
                const newEl = isResult ?
                    createResultSelect(current) :
                    createTextInput(current);

                inputGroup.replaceChild(newEl, oldEl);
            }

            // 1) On change of field dropdown
            searchField.addEventListener('change', swapField);

            // 2) On initial load, do the swap if needed
            swapField();
        });
    </script>
@endsection
