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
                                    <i class="fas fa-user-tag me-2 text-standout"></i>
                                    <strong>الاسم:</strong> {{ $student->user->name ?? 'غير محدد' }}
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
                        <div class="text-center mt-5 print-qr">
                            <div class="qr-container bg-white p-4 rounded-4 shadow-lg d-inline-block">
                                <img src="data:image/png;base64,{{ base64_encode($qrcode) }}" alt="QR Code"
                                    class="img-fluid qr-border" style="max-width: 220px; border: 2px solid #e7e9ed">
                                <p class="text-black mt-2 mb-0 fw-semibold">
                                    <i class="fas fa-qrcode me-2 text-standout"></i> امسح الكود لتسجيل الدخول
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recitation Metrics Card -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="card mb-5 shadow card-hover h-100">
                    <div class="card-header bg-primary text-white text-center py-4">
                        <h2 class="mb-0 text-white">
                            <i class="fa fa-book-open me-3"></i> معلومات التسميعات
                        </h2>
                    </div>
                    <div class="card-body p-4 ">
                        <div class="row g-4 w-100"> <!-- Added h-100 here -->
                            {{-- Info Column --}}
                            <div class="col-md-6 h-100 d-flex flex-column gap-3">
                                <div class="info-item  fs-5 bg-light-gray p-3 rounded-3 shadow-sm flex-grow-1">
                                    <i class="fas fa-file-alt me-2 text-standout"></i>
                                    <strong class="fs-5">عدد الصفحات المسمعة:</strong> {{ $recitationsCount }}
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
                            <div class="col-md-6 h-100 d-flex flex-column ">
                                @if ($recitationAvg >= 5)
                                    <div id="recitation-chart"
                                        class="chart-container bg-light-gray h-100 mt-4 w-100 p-4 rounded-4 shadow-sm"
                                        style="min-height: 300px;">
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
                            <a href="{{ route('admin.recitations.index', [
                                'search_value' => $student->user->name,
                                'search_field' => 'student',
                            ]) }}"
                                class="btn mt-4 fs-4 w-100 bg-light-gray" style="">
                                تفاصيل
                            </a>
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
                            <i class="fas fa-chalkboard me-3"></i> معلومات السبورة
                        </h2>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-4 w-100">
                            {{-- Info Column --}}
                            <div class="col-md-6 fs-5 h-100 d-flex flex-column gap-3">
                                <div class="info-item bg-light-gray p-3 rounded-3 shadow-sm flex-grow-1">
                                    <i class="fas fa-chalkboard-teacher me-2 text-standout"></i>
                                    <strong class="fs-5">عدد السبور:</strong> {{ $sabrCount }}
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
                                            <h5 class="mt-2 mb-0">متوسط السبورة: {{ number_format($sabrAvg, 2) }}</h5>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <a href="{{ route('admin.sabrs.index', [
                                'search_value' => $student->user->name,
                                'search_field' => 'student',
                            ]) }}"
                                class="btn mt-4 fs-4 w-100 bg-light-gray" style="">
                                تفاصيل
                            </a>
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
                                        <h5 class="mb-0 fw-semibold  me-2"> عدد أيام الدوام : </h5>
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
                                    class="btn btn-secondary mt-4 w-100">
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
                                    class="btn btn-secondary mt-4 w-100">
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
                                    <strong class="fs-5">نقاط الطالب الحالسية : </strong>
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


        <div class="text-center mt-4">
            <a href="{{ route('admin.students.index') }}" class="btn btn-secondary hover-scale mt-5 mb-4 px-5 py-3">
                العودة للقائمة<i class="fas fa-arrow-left me-2"></i>
            </a>
        </div>
    </div>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Reusable donut chart function
            function renderDonutChart(selector, rawValue, label, color) {
                // Convert the incoming value to a float
                const value = parseFloat(rawValue);

                // Round both value and remaining to one decimal place
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
                            // On hover, always show "roundedValue" in the donut center
                            dataPointMouseEnter: function(event, chartContext, {
                                seriesIndex
                            }) {
                                const totalLabel = document.querySelector(
                                    `${selector} .apexcharts-donut-total-label`);
                                if (totalLabel) {
                                    totalLabel.textContent = `${roundedValue}%`;
                                }
                            },
                            dataPointMouseLeave: function() {
                                const totalLabel = document.querySelector(
                                    `${selector} .apexcharts-donut-total-label`);
                                if (totalLabel) {
                                    totalLabel.textContent = `${roundedValue}%`;
                                }
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
                                        label: label,
                                        formatter: function() {
                                            // Center label text, e.g. "8.4%"
                                            return `${roundedValue}%`;
                                        },
                                        color: color,
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
                            formatter: function(val) {
                                // Also ensure tooltips show only one decimal place
                                return ` ${val.toFixed(1)} % `;
                            }
                        }
                    }
                };

                new ApexCharts(document.querySelector(selector), options).render();
            }

            @if ($recitationAvg >= 5)
                renderDonutChart(
                    "#recitation-chart",
                    {{ number_format($recitationAvg, 2, '.', '') }},
                    "التسميع",
                    '#049977'
                );
            @endif

            @if ($sabrAvg >= 5)
                renderDonutChart(
                    "#sabr-chart",
                    {{ number_format($sabrAvg, 2, '.', '') }},
                    "السبورة",
                    '#049977'
                );
            @endif
        });

        function copyHead(sourceDoc, targetDoc) {
            // استنساخ روابط CSS و <style>
            sourceDoc.querySelectorAll('link[rel="stylesheet"], style').forEach(node => {
                const clone = node.cloneNode(true);
                if (clone.rel === 'stylesheet') clone.media =
                    'all'; // يضمن تطبيقها في الطباعة :contentReference[oaicite:3]{index=3}
                targetDoc.head.appendChild(clone);
            });
            // استنساخ meta charset و viewport
            sourceDoc.querySelectorAll('meta[charset], meta[name="viewport"]').forEach(node => {
                targetDoc.head.appendChild(node.cloneNode(true));
            });
        }

        /**
         * ينشئ مستند HTML جديد يحتوي فقط على العناصر المخصصة للطباعة
         * ثم يستدعي window.print() بعد تحميل الأنماط والصورة.
         */
        function printSelectedInfo() {
            const idHTML = document.querySelector('.print-id')?.outerHTML || '';
            const nameHTML = document.querySelector('.print-name')?.outerHTML || '';
            const phoneHTML = document.querySelector('.print-phone')?.outerHTML || '';
            const qrHTML = document.querySelector('.print-qr')?.outerHTML || '';

            // بناء محتوى الصفحة للطباعة
            const printContent = `
      <!DOCTYPE html>
      <html dir="rtl">
        <head>
          <title>طباعة بيانات الطالب</title>
          <!-- سيتم نسخ الأنماط والميتا هنا -->
        </head>
        <body>
          ${idHTML}
          ${nameHTML}
          ${phoneHTML}
          ${qrHTML}
        </body>
      </html>
    `;

            const printWindow = window.open('', '_blank');
            printWindow.document.write(printContent);
            printWindow.document.close();

            // نسخ الأنماط والميتا بعد document.close()
            copyHead(window.document, printWindow.document);

            // انتظار تحميل المحتوى ثم الطباعة
            printWindow.onload = () => {
                // تأخير بسيط لضمان تطبيق CSS :contentReference[oaicite:4]{index=4}
                setTimeout(() => {
                    printWindow.focus();
                    printWindow.print();
                    printWindow.onafterprint = () => printWindow.close();
                }, 250);
            };
        }
    </script>
@endsection
