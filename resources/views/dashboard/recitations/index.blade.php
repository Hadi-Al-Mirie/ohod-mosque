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
                                <th class="py-3"><i class="fa-solid fa-calendar-days"></i> التاريخ</th>
                                <th class="py-3"><i class="fas fa-star me-2"></i> النتيجة </th>
                                <th class="py-3"><i class="fas fa-eye me-2"></i> الخيارات </th>
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
                                    <td class="align-middle fw-bold text-info">
                                        {{ \Carbon\Carbon::parse($recitation->created_at)->format('Y/m/d') }}
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

                                        {{-- Toggle final/unfinal --}}
                                        <form method="POST"
                                            action="{{ route('admin.recitations.toggleFinal', $recitation->id) }}"
                                            class="d-inline-block ms-1 toggle-final-form">
                                            @csrf
                                            @method('PATCH')

                                            <!-- hidden input to hold the “are you sure?” flag if you need it -->
                                            <input type="hidden" name="confirm_toggle" id="confirm_toggle" value="0">

                                            <button type="button"
                                                class="btn btn-sm btn-secondary hover-scale toggle-final-btn"
                                                data-bs-toggle="modal" data-bs-target="#toggleFinalModal">
                                                <i
                                                    class="fas {{ $recitation->is_final ? 'fa-lock-open' : 'fa-lock' }} me-1"></i>
                                                {{ $recitation->is_final ? 'السماح بالإعادة' : 'تثبيت كتسميع نهائي' }}
                                            </button>
                                        </form>

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

    <!-- Toggle Final Confirmation Modal -->
    <div class="modal fade" id="toggleFinalModal" tabindex="-1" aria-labelledby="toggleFinalModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-warning text-dark d-flex align-items-center justify-content-between">
                    <h5 class="modal-title text-center text-white  w-100" id="toggleFinalModalLabel">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        هل أنت متأكد؟
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0 text-center">
                        <span id="toggleFinalMessage">
                            {{ $recitation->is_final ? 'السماح بالإعادة' : 'تثبيت كتسميع نهائي' }} ؟
                        </span>
                    </p>
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <button type="button" class="btn btn-danger" id="confirmToggleFinal">تأكيد</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                </div>

            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const toggleModal = new bootstrap.Modal('#toggleFinalModal');
            let currentForm;
            let isFinalState;

            // 1. When any “.toggle-final-btn” is clicked, capture its form and state
            document.querySelectorAll('.toggle-final-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    currentForm = btn.closest('form.toggle-final-form');
                    isFinalState = btn.querySelector('i').classList.contains('fa-lock-open');

                    // Set the modal message dynamically
                    const msgEl = document.getElementById('toggleFinalMessage');
                    msgEl.textContent = isFinalState ?
                        'سيتم إلغاء تثبيت هذا التسميع، وسيصبح قابلًا للإعادة.' :
                        'سيتم تثبيت هذا التسميع كـ "نهائي". لا يمكن التراجع بعد التثبيت.';

                    // Reset hidden flag if you’re using it
                    currentForm.querySelector('#confirm_toggle').value = 0;

                    // Show the modal
                    toggleModal.show();
                });
            });

            // 2. When the user confirms, submit the form
            document.getElementById('confirmToggleFinal').addEventListener('click', () => {
                // If you need to send a flag:
                currentForm.querySelector('#confirm_toggle').value = 1;
                currentForm.submit();
                toggleModal.hide();
            });
        });
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
