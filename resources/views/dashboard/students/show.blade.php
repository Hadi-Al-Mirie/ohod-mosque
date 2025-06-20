@extends('dashboard.layouts.app')

@section('title', 'معلومات الطالب')

@section('content')
    <div class="container mt-6" dir="rtl">
        <div class="d-flex justify-content-between mb-4">
            <a href="#" class="btn btn-secondary" onclick="printSelectedInfo(); return false;">
                <i class="fas fa-print me-2"></i> طباعة
            </a>

            <a href="{{ route('admin.students.edit', $student->id) }}" class="btn btn-primary  border-standout text-white">
                <i class="fas fa-edit me-2"></i> تعديل
            </a>
        </div>
        @include('dashboard.layouts.alert')
        <!-- Basic Info Card -->
        <div class="row" id="basic-info">
            <div class="col-12">
                <div class="card mb-5 shadow card-hover">
                    <div class="card-header bg-primary text-white text-center py-3 shadow-sm">
                        <h2 class="mb-0  text-white ">
                            <i class="fas fa-user-graduate me-3 "></i>
                            معلومات الطالب الأساسية
                        </h2>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <!-- Left Column -->
                            <div class="col-md-6">
                                <div class="info-item bg-light-gray mb-4 p-3 rounded-3 shadow-sm text-black print-id">
                                    <span class="badge bg-standout text-primary me-2">ID</span>
                                    <strong class="">الرقم:</strong> {{ $student->id }}
                                </div>

                                <div class="info-item bg-light-gray mb-4 p-3 rounded-3 shadow-sm print-name">
                                    <i id="userIcon" class="fas fa-user-tag me-2 text-standout"></i>
                                    <strong>الاسم : </strong> {{ $student->user->name ?? 'غير محدد' }}
                                </div>

                                <div class="info-item bg-light-gray mb-4 p-3 rounded-3 shadow-sm">
                                    <i class="fas fa-users me-2 text-standout"></i>
                                    <strong>الحلقة:</strong> {{ $student->circle->name ?? 'غير محدد' }}
                                </div>

                                <div class="info-item bg-light-gray mb-4 p-3 rounded-3 shadow-sm">
                                    <i class="fas fa-birthday-cake me-2 text-standout"></i>
                                    <strong>تاريخ الميلاد:</strong>
                                    {{ $student->birth ? \Carbon\Carbon::parse($student->birth)->format('Y/m/d') : 'غير محدد' }}
                                </div>

                                <div class="info-item bg-light-gray mb-4 p-3 rounded-3 shadow-sm">
                                    <i class="fas fa-school me-2 text-standout"></i>
                                    <strong>المدرسة:</strong> {{ $student->school ?? 'غير محدد' }}
                                </div>

                                <div class="info-item bg-light-gray mb-4 p-3 rounded-3 shadow-sm">
                                    <i class="fas fa-chalkboard-user me-2 text-standout"></i>
                                    <strong>الصف:</strong> {{ $student->class ?? 'غير محدد' }}
                                </div>
                                <div class="info-item bg-light-gray text-dark mb-1 p-3 rounded-3 shadow-sm">
                                    <i class="fas fa-layer-group me-2 text-standout"></i>
                                    <strong>المستوى:</strong> {{ $student->level->name ?? 'غير محدد' }}
                                </div>
                            </div>

                            <!-- Right Column -->
                            <div class="col-md-6">
                                <div class="info-item bg-light-gray mb-4 p-3 rounded-3 shadow-sm print-phone">
                                    <i class="fas fa-mobile-alt me-2 text-standout"></i>
                                    <strong>هاتف الطالب:</strong> {{ $student->student_phone ?? 'غير محدد' }}
                                </div>

                                <div class="info-item bg-light-gray mb-4 p-3 rounded-3 shadow-sm">
                                    <i class="fas fa-male me-2 text-standout"></i>
                                    <strong>اسم الأب:</strong> {{ $student->father_name ?? 'غير محدد' }}
                                </div>

                                <div class="info-item bg-light-gray mb-4 p-3 rounded-3 shadow-sm">
                                    <i class="fas fa-phone-volume me-2 text-standout"></i>
                                    <strong>هاتف الأب:</strong> {{ $student->father_phone ?? 'غير محدد' }}
                                </div>

                                <div class="info-item bg-light-gray mb-4 p-3 rounded-3 shadow-sm">
                                    <i class="fas fa-briefcase me-2 text-standout"></i>
                                    <strong>وظيفة الأب:</strong> {{ $student->father_job ?? 'غير محدد' }}
                                </div>

                                <div class="info-item bg-light-gray mb-4 p-3 rounded-3 shadow-sm">
                                    <i class="fas fa-female me-2 text-standout"></i>
                                    <strong>اسم الأم:</strong> {{ $student->mother_name ?? 'غير محدد' }}
                                </div>

                                <div class="info-item bg-light-gray mb-4 p-3 rounded-3 shadow-sm">
                                    <i class="fas fa-user-tie me-2 text-standout"></i>
                                    <strong>وظيفة الأم:</strong> {{ $student->mother_job ?? 'غير محدد' }}
                                </div>
                                <div class="info-item bg-light-gray text-dark mb-1 p-3 rounded-3 shadow-sm">
                                    <i class="fas fa-map-marker-alt me-2 text-standout"></i>
                                    <strong>الموقع:</strong> {{ $student->location ?? 'غير محدد' }}
                                </div>
                            </div>
                        </div>

                        <!-- QR Code Section -->
                        <div class="text-center mt-5 ">
                            <div class="qr-container bg-white p-4 rounded-4 shadow-lg d-inline-block">
                                <img src="data:image/png;base64,{{ base64_encode($qrcode) }}" alt="QR Code"
                                    class="print-qr" style="max-width: 220px;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recitation Metrics Card -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="card mb-2 shadow card-hover h-100">
                    <div class="card-header bg-primary text-white text-center py-4">
                        <h2 class="mb-0 text-white">
                            <i class="fa fa-book-open me-3"></i> معلومات التسميعات
                        </h2>
                    </div>
                    <div class="card-body p-4 ">
                        <div class="row g-4 w-100"> <!-- Added h-100 here -->
                            {{-- Info Column --}}
                            <div class="col-md-6 h-100 d-flex flex-column gap-3">
                                <div
                                    class="info-item bg-light-gray p-3 rounded-3 shadow-sm flex-grow-1 d-flex align-items-center">
                                    <i class="fas fa-file-alt me-2 text-standout"></i>
                                    <div class="d-flex align-items-center">
                                        <!-- First value pair -->
                                        <div class="d-flex align-items-center me-4 ms-3">
                                            <strong class="fs-5">عدد التسميعات:</strong> {{ $recitationsCount }}
                                        </div>

                                        <!-- Vertical divider (line) -->
                                        <div class="vr mx-1 mx-sm-2 opacity-75"></div>

                                        <!-- Second value pair -->
                                        <div class="d-flex align-items-center ms-4 me-3">
                                            <strong class="fs-5">نقاط التسميعات : </strong> {{ $recitationsPoints }}
                                        </div>
                                    </div>
                                </div>

                                <div class="info-item bg-light-gray p-3 rounded-3 shadow-sm flex-grow-1">
                                    <i class="fas fa-star me-2 text-standout"></i>
                                    <strong class="text-center mb-3 mt-3 fs-5">التقييم:</strong>
                                    <div class="d-flex flex-column h-100 justify-content-center">
                                        @foreach ($recitationResultCounts as $name => $count)
                                            <span class="badge bg-light-gray text-black fs-5 mb-2">
                                                {{ $name }} : {{ $count }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="info-item fs-5 bg-light-gray p-3 rounded-3 shadow-sm flex-grow-1">
                                    <i class="fas fa-chart-line me-2 text-standout"></i>
                                    <strong>معدل التسميع باليوم:</strong> {{ number_format($recitationAvg, 2) }}
                                </div>
                            </div>

                            {{-- Chart Column --}}
                            <div class="col-md-6 d-flex flex-column ">
                                @if ($recitationAvg >= 5)
                                    <div id="recitation-chart"
                                        class="chart-container bg-light-gray h-100  w-100 p-4 rounded-4 shadow-sm"
                                        style="min-height: 300px;padding-top: 5rem !important;">
                                    </div>
                                @else
                                    <div
                                        class="info-item bg-light-gray mt-2 h-100 w-100 p-4 rounded-4 shadow-sm
                                      d-flex align-items-center justify-content-center">
                                        <div>
                                            <i class="fas fa-info-circle me-2 text-standout fa-2x"></i>
                                            <h5 class="mt-2 mb-0">متوسط التسميع: {{ number_format($recitationAvg, 2) }}
                                            </h5>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div class="col-12 d-flex justify-content-center mt-4">
                                <a href="{{ route('admin.recitations.index', [
                                    'student_name' => $student->user->name,
                                ]) }}"
                                    class="btn btn-secondary mt-4 w-100 fs-5 bg-light-gray text-black">
                                    عرض التفاصيل <i class="fas fa-arrow-left ms-2"></i>
                                </a>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sabr Metrics Card -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="card shadow card-hover h-100">
                    <div class="card-header bg-primary text-white text-center py-4">
                        <h2 class="mb-0 text-white">
                            <i class="fas fa-chalkboard me-3"></i> معلومات السبر
                        </h2>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-4 w-100">
                            {{-- Info Column --}}
                            <div class="col-md-6 fs-5 h-100 d-flex flex-column gap-3">
                                <div
                                    class="info-item bg-light-gray p-3 rounded-3 shadow-sm flex-grow-1 d-flex align-items-center">
                                    <i class="fas fa-chalkboard-teacher me-2 text-standout"></i>
                                    <!-- Flex container for pairs + separator -->
                                    <div class="d-flex align-items-center">
                                        <!-- First value pair -->
                                        <div class="d-flex align-items-center me-4 ms-3">
                                            <strong class="fs-5">عدد السبور:</strong> {{ $sabrCount }}
                                        </div>

                                        <!-- Vertical divider (line) -->
                                        <div class="vr mx-1 mx-sm-2 opacity-75"></div>

                                        <!-- Second value pair -->
                                        <div class="d-flex align-items-center me-3 ms-4">
                                            <strong class="fs-5">نقاط السبور : </strong> {{ $sabrPoints }}
                                        </div>
                                    </div>
                                </div>

                                <div class="info-item bg-light-gray p-3 rounded-3 shadow-sm flex-grow-1">
                                    <i class="fas fa-award me-2 text-standout"></i>
                                    <strong class="text-center mb-3 mt-2 fs-5">التقييم:</strong>
                                    <div class="d-flex flex-column h-100 justify-content-center">
                                        @foreach ($sabrResultCounts as $name => $count)
                                            <span class="badge bg-light-gray text-black fs-5 mb-2">
                                                {{ $name }} : {{ $count }}
                                            </span>
                                        @endforeach

                                    </div>
                                </div>
                            </div>



                            {{-- Chart Column --}}
                            <div class="col-md-6 h-100 d-flex flex-column">
                                @if ($sabrAvg >= 5)
                                    <div id="sabr-chart"
                                        class="chart-container bg-light-gray h-100 w-100 p-3 rounded-4 shadow-sm"
                                        style="min-height: 300px;">
                                    </div>
                                @else
                                    <div
                                        class="info-item bg-light-gray h-100 w-100 p-4 rounded-4 shadow-sm
                                    d-flex align-items-center justify-content-center">
                                        <div>
                                            <i class="fas fa-info-circle me-2 text-standout fa-2x"></i>
                                            <h5 class="mt-2 mb-0">متوسط السبر: {{ number_format($sabrAvg, 2) }}</h5>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div class="col-12 d-flex justify-content-center mt-4">
                                <a href="{{ route('admin.sabrs.index', [
                                    'search_value' => $student->user->name,
                                    'search_field' => 'student',
                                ]) }}"
                                    class="btn btn-secondary mt-4 w-75 fs-5 bg-light-gray text-black" style="">
                                    عرض التفاصيل
                                    <i class="fas fa-arrow-left ms-2"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card mb-5 shadow-lg card-hover">
                    <div class="card-header py-4 bg-primary">
                        <h2 class="mb-0 text-white text-center fw-bold display-6">
                            <i class="fas fa-calendar-check me-3"></i> معلومات الدوام و النقاط
                        </h2>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <!-- Attendance Column -->
                            <div class="col-md-6">
                                <div
                                    class="info-item bg-light-gray bg-light-gray p-3 rounded-3 shadow-sm flex-grow-1 mb-3">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="fas fa-clock me-3 fs-4 text-primary"></i>
                                        <h5 class="mb-0 fw-semibold  me-2"> عدد أيام الدوام الكلية للدورة: </h5>
                                    </div>
                                    <p class="fs-3 fw-bold text-dark mb-0 me-4">{{ $numberOfWorkingDays }}</p>
                                </div>

                                <div class="info-ite bg-light-gray bg-light-gray p-3 rounded-3 shadow-sm flex-grow-1">
                                    <div class="d-flex align-items-center mb-3">
                                        <i class="fas fa-chart-pie me-3 fs-4 text-primary"></i>
                                        <h5 class="mb-0 fw-semibold  me-2">توزيع الدوام:</h5>
                                    </div>
                                    <div class="row g-3">
                                        @foreach ($attendanceStats as $typeName => $stat)
                                            <div class="col-6">
                                                <div class="bg-white p-3 rounded-2 text-center">
                                                    <p class="text-muted mb-1">{{ $typeName }}</p>
                                                    <p class="fw-bold fs-4 text-dark mb-0">
                                                        {{ $stat['count'] }}
                                                        <span class="text-muted">({{ $stat['ratio'] }}%)</span>
                                                    </p>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <a href="{{ route('admin.attendances.index', ['search_value' => $student->user->name]) }}"
                                    class="btn btn-secondary mt-4 w-100 fs-5 bg-light-gray text-black">
                                    عرض التفاصيل <i class="fas fa-arrow-left ms-2"></i>
                                </a>
                            </div>

                            <!-- Notes Column -->
                            <div class="col-md-6">
                                <div class="info-item bg-light-gray p-3 rounded-3 shadow-sm flex-grow-1 mb-3">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="fas fa-clipboard-list me-3 fs-4 text-primary"> </i>
                                        <h5 class="mb-0 fw-semibold me-2"> نقاط الملاحظات : </h5>
                                    </div>
                                    <p class="fs-3 fw-bold text-dark mb-0 me-4"> {{ $notesStats['points'] }} </p>
                                </div>

                                <div class="info-item bg-light-gray p-3 rounded-3 shadow-sm flex-grow-1">
                                    <div class="d-flex align-items-center mb-3">
                                        <i class="fas fa-chart-pie me-3 fs-4 text-primary"></i>
                                        <h5 class="mb-0 fw-semibold  me-2">توزيع الملاحظات:</h5>
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-6">
                                            <div class="bg-white p-3 rounded-2 text-center">
                                                <p class="text-muted mb-1">إيجابية</p>
                                                <p class="fw-bold fs-4 text-success mb-0">
                                                    {{ $notesStats['positive']['count'] }}</p>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="bg-white p-3 rounded-2 text-center">
                                                <p class="text-muted mb-1">نقاطها</p>
                                                <p class="fw-bold fs-4 text-success mb-0">
                                                    {{ $notesStats['positive']['sum'] }}</p>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="bg-white p-3 rounded-2 text-center">
                                                <p class="text-muted mb-1">سلبية</p>
                                                <p class="fw-bold fs-4 text-danger mb-0">
                                                    {{ $notesStats['negative']['count'] }}</p>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="bg-white p-3 rounded-2 text-center">
                                                <p class="text-muted mb-1">نقاطها</p>
                                                <p class="fw-bold fs-4 text-danger mb-0">
                                                    {{ $notesStats['negative']['sum'] }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <a href="{{ route('admin.notes.index', ['search' => $student->user->name]) }}"
                                    class="btn btn-secondary mt-4 w-100 fs-5 bg-light-gray text-black">
                                    عرض التفاصيل <i class="fas fa-arrow-left ms-2"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rankings & Improvement Card -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="card shadow card-hover h-100">
                    <div class="card-header bg-success text-white text-center py-4">
                        <h2 class="mb-0 text-white">
                            <i class="fas fa-trophy me-3"></i> التصنيف والتحسن
                        </h2>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-4">

                            <!-- Full-width Student Points -->
                            <div class="col-4 d-flex">
                                <div class="info-item bg-light-gray p-3 rounded-3 shadow-sm flex-fill">
                                    <i class="fas fa-star me-2 text-standout"></i>
                                    <strong class="fs-5">نقاط الطالب الحالية : </strong>
                                    <span class="badge bg-primary fs-4 mt-2">{{ $student->points }}</span>
                                </div>
                            </div>

                            <!-- Rankings Column -->
                            <div class="col-md-4 d-flex">
                                <div class="info-item bg-light-gray p-3 rounded-3 shadow-sm flex-fill">
                                    <i class="fas fa-users me-2 text-standout"></i>
                                    <strong class="fs-5">التصنيف في الحلقة:</strong>
                                    <span class="badge bg-primary fs-4 mt-2">#{{ $rankInCircle }}</span>
                                    <div class="mt-2 text-muted fs-6 mb-3">
                                        من أصل {{ $circleStudentsCount }} طالب
                                    </div>
                                    <div class="mt-2 text-muted fs-6 mb-3">
                                        تصنيف الأسبوع الماضي :{{ $rankInCirclePrev }}#
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 d-flex">
                                <div class="info-item bg-light-gray p-3 rounded-3 shadow-sm flex-fill">
                                    <i class="fas fa-mosque me-2 text-standout"></i>
                                    <strong class="fs-5">التصنيف في المسجد:</strong>
                                    <span class="badge bg-primary fs-4 mt-2">#{{ $rankInMosque }}</span>
                                    <div class="mt-2 text-muted fs-6 mb-3">
                                        من أصل {{ $mosqueStudentsCount }} طالب
                                    </div>
                                    <div class="mt-2 text-muted fs-6 mb-3">
                                        تصنيف الأسبوع الماضي :{{ $rankInMosquePrev }}#
                                    </div>
                                </div>
                            </div>

                            <!-- Points Gained This Week -->
                            <div class="col-md-4 d-flex">
                                <div class="info-item bg-light-gray p-3 rounded-3 shadow-sm flex-fill">
                                    <i class="fas fa-calendar-week me-2 text-standout"></i>
                                    <strong class="fs-5"> نقاط هذا الأسبوع: </strong>
                                    <span class="badge bg-info fs-4 mt-2">{{ $pointsGainedThisWeek }}</span>
                                </div>
                            </div>

                            <!-- Points Gained Last Week -->
                            <div class="col-md-4 d-flex">
                                <div class="info-item bg-light-gray p-3 rounded-3 shadow-sm flex-fill">
                                    <i class="fas fa-calendar-minus me-2 text-standout"></i>
                                    <strong class="fs-5"> نقاط الأسبوع الماضي: </strong>
                                    <span class="badge bg-warning fs-4 mt-2">{{ $pointsGainedLastWeek }}</span>
                                </div>
                            </div>

                            <!-- Percentage Improvement -->
                            <div class="col-md-4 d-flex">
                                <div class="info-item bg-light-gray p-3 rounded-3 shadow-sm flex-fill"
                                    style="padding-bottom: 1.4rem !important">
                                    <i class="fas fa-chart-line me-2 text-standout"></i>
                                    <strong class="fs-5"> نسبة التحسن عن الأسبوع الماضي: </strong>
                                    <div class="d-flex flex-column h-100 justify-content-center">
                                        <span
                                            class="display-5 {{ $improvementPercent >= 0 ? 'text-success text-center' : 'text-danger' }}">
                                            {{ number_format($improvementPercent) }}%
                                            <i class="fas fa-arrow-{{ $improvementPercent >= 0 ? 'up' : 'down' }}"></i>
                                        </span>

                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="text-center mt-4 mb-4">
            <a href="{{ route('admin.students.index') }}" class="btn btn-secondary hover-scale mt-5 mb-4 px-5 py-3 me-2">
                <i class="fas fa-arrow-left me-2 ms-3"></i> العودة للقائمة
            </a>
            <form action="{{ route('admin.students.destroy', $student->id) }}" method="POST"
                class="delete-student-form d-inline-block">
                @csrf
                @method('DELETE')
                <button type="button" class="btn btn-danger hover-scale mt-5 mb-4 px-5 py-3 me-5 delete-student-btn">
                    <i class="fa-solid fa-trash me-2"></i> حذف
                </button>
            </form>
            <a href="{{ route('admin.sabr.history', $student->id) }}"
                class="btn btn-primary hover-scale mt-5 mb-4 px-5 py-3 me-5">
                <i class="fas fa-book me-2"></i> عرض سجل السبر
            </a>
            <a href="{{ route('admin.recitation.history', $student->id) }}"
                class="btn btn-primary hover-scale mt-5 mb-4 px-5 py-3 me-5">
                <i class="fas fa-book me-2"></i> عرض سجل التسميع
            </a>
        </div>
    </div>

    <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-danger text-white d-flex align-items-center justify-content-between">
                    <h5 class="modal-title  w-100 text-center text-white">
                        <i class="fas fa-exclamation-triangle me-2"></i> تأكيد الحذف
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body bg-light-gray text-center">
                    <p class="fs-5 mb-0">هل أنت متأكد أنك تريد حذف هذا الطالب ؟</p>
                </div>
                <div class="modal-footer bg-light-gray d-flex justify-content-center gap-3">
                    <button type="button" class="btn btn-danger" id="deleteConfirmBtn">
                        <i class="fas fa-trash me-1"></i> حذف
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        إلغاء
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Reusable donut chart function
            function renderDonutChart(selector, rawValue, label, color) {
                const container = document.querySelector(selector);
                if (!container) return;

                const value = parseFloat(rawValue);
                const roundedValue = parseFloat(value.toFixed(1));
                const roundedRemaining = parseFloat((100 - value).toFixed(1));

                const options = {
                    series: [roundedValue, roundedRemaining],
                    chart: {
                        type: 'donut',
                        height: 300,
                        fontFamily: 'Cairo, sans-serif',
                        foreColor: '#373d3f',
                        rtl: true,
                        events: {
                            dataPointMouseEnter(_, __, {
                                seriesIndex
                            }) {
                                const totalLabel = container.querySelector('.apexcharts-donut-total-label');
                                if (totalLabel) totalLabel.textContent = `${roundedValue}%`;
                            },
                            dataPointMouseLeave() {
                                const totalLabel = container.querySelector('.apexcharts-donut-total-label');
                                if (totalLabel) totalLabel.textContent = `${roundedValue}%`;
                            }
                        }
                    },
                    legend: {
                        show: false
                    },
                    labels: [label, 'المتبقي'],
                    colors: [color, '#A9A9A9'],
                    plotOptions: {
                        pie: {
                            donut: {
                                size: '70%',
                                labels: {
                                    show: true,
                                    total: {
                                        show: true,
                                        label,
                                        formatter() {
                                            return `${roundedValue}%`;
                                        },
                                        color,
                                        fontSize: '20px'
                                    }
                                }
                            }
                        }
                    },
                    tooltip: {
                        enabled: false,
                        fillSeriesColor: false,
                        y: {
                            formatter: val => ` ${val.toFixed(1)} % `
                        }
                    }
                };

                new ApexCharts(container, options).render();
            }

            @if (isset($recitationAvg) && $recitationAvg >= 5)
                renderDonutChart(
                    "#recitation-chart",
                    "{{ number_format($recitationAvg, 2, '.', '') }}",
                    "التسميع",
                    "#049977"
                );
            @endif

            @if (isset($sabrAvg) && $sabrAvg >= 5)
                renderDonutChart(
                    "#sabr-chart",
                    "{{ number_format($sabrAvg, 2, '.', '') }}",
                    "السبر",
                    "#049977"
                );
            @endif
        });

        function copyHead(sourceDoc, targetDoc) {
            sourceDoc.querySelectorAll('link[rel="stylesheet"], style').forEach(node => {
                const clone = node.cloneNode(true);
                if (clone.rel === 'stylesheet') clone.media = 'all';
                targetDoc.head.appendChild(clone);
            });
            sourceDoc.querySelectorAll('meta[charset], meta[name="viewport"]').forEach(node => {
                targetDoc.head.appendChild(node.cloneNode(true));
            });
        }

        // opens a print window with a styled student card
        function printSelectedInfo() {
            const qrElem = document.querySelector('.print-qr');
            const nameElem = document.querySelector('.print-name');
            const nameClone = nameElem?.cloneNode(true);

            // remove the <i> with id="userIcon"
            nameClone
                ?.querySelectorAll('#userIcon')
                .forEach(icon => icon.remove());

            const nameHTML = nameClone ? nameClone.outerHTML : '';
            const qrHTML = document.querySelector('.print-qr')?.outerHTML || '';

            const printContent = `
      <!DOCTYPE html>
      <html dir="rtl">
        <head>
          <meta charset="utf-8">
          <title>Student Card</title>
          <style>
            /* reset browser print margins */
            @page { margin: 0 !important; }
            body {
              margin: 10mm !important;
              font-family: 'Segoe UI', Tahoma, sans-serif;
              background: #f7f9fc;
              color: #333;
            }

            /* card container */
            .card-print {
              display: flex;
              flex-direction: row-reverse;
              align-items: center;
              background: #ffffff;
              border-radius: 12px;
              box-shadow: 0 4px 12px rgba(0,0,0,0.1);
              padding: 16px;
              gap: 24px;
              max-width: 200mm;
              margin: auto;
            }

            /* text side */
            .card-print .text-block {
              flex: 1;
            }
            .card-print .text-block .print-id,
            .card-print .text-block .print-name {
              display: block;
              margin-bottom: 25px !important;
              font-size: 18pt;
              font-weight: 600;
              padding-left: 8px;
            }
            .card-print .text-block .print-id{
            margin-bottom: 75px !important;
            }
            .card-print .text-block .print-id span.label,
            .card-print .text-block .print-name span.label {
              color: #049977;
              margin-right: 4px;
              font-size: 16pt;
            }

            /* QR side */
            .card-print .print-qr {
              flex-shrink: 0;
              text-align: center;
            }
            .card-print .print-qr .qr-container {
              background: #fafafa;
              padding: 12px;
              border-radius: 8px;
              box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            }
            .card-print .print-qr img {
              max-width: 180px;
              height: auto;
              display: block;
              margin: auto;
            }
            .card-print .print-qr p {
              margin: 8px 0 0;
              font-size: 12pt;
              color: #555;
            }

            /* hide anything not explicitly printed */
            .no-print, #basic-info { display: none !important; }
            .print-name i { display: none !important; }
          </style>
        </head>
        <body>
          <div class="card-print">
            <div class="text-block">
              ${nameHTML.replace(
                /<strong>([^<]+)<\/strong>\s*([^<]+)/,
                `<span class="label">\$1</span>\$2`
              )}
            </div>
            ${qrHTML}
          </div>
        </body>
      </html>
    `;

            const w = window.open('', '_blank');
            if (!w) return;
            w.document.write(printContent);
            w.document.close();
            copyHead(document, w.document);

            w.onload = () => {
                setTimeout(() => {
                    w.focus();
                    w.print();
                    w.onafterprint = () => w.close();
                }, 200);
            };
        }

        document.addEventListener('DOMContentLoaded', () => {
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
            let formToDelete = null;
            document.querySelectorAll('.delete-student-btn').forEach(btn => {
                btn.addEventListener('click', e => {
                    formToDelete = btn.closest('form');
                    deleteModal.show();
                });
            });
            document.getElementById('deleteConfirmBtn').addEventListener('click', () => {
                if (formToDelete) {
                    formToDelete.submit();
                    formToDelete = null;
                }
            });
        });
    </script>
@endsection
