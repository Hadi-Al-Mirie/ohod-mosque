@extends('dashboard.layouts.app')

@section('title', 'تفاصيل الدورة')

@section('content')
    <div class="container mt-5" dir="rtl">
        @include('dashboard.layouts.alert')
        <div class="container-fluid mb-5">
            <h1 class="mb-5 text-center fw-bold text-primary">
                <i class="fas fa-chart-line me-2"></i> إحصائيات الدورة
            </h1>
            @if ($course->is_active == true)
                <div class="order-2">
                    <a href="{{ route('admin.courses.edit', $course->id) }}"
                        class="btn btn-secondary btn-lg px-5 me-3 shadow-sm">
                        <i class="fas fa-edit me-2"></i> تعديل
                    </a>
                </div>
            @endif
        </div>

        {{-- Metrics Cards Row --}}
        <div class="row g-4 mb-5 mt-5">
            {{-- Attendance Card --}}
            <div class="col-md-4">
                <div class="card shadow-sm border-success h-100">
                    <div class="card-header bg-primary text-white text-center">
                        <i class="fas fa-calendar-check me-2"></i> الحضور
                    </div>
                    <div class="card-body scrollable-card-body">
                        @foreach ($tableRows as $row)
                            <div class="metric-item mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="d-flex flex-column">
                                        <span class="fw-bold text-dark">{{ $row['circle'] }}</span>
                                        <div class="text-muted small mt-1">
                                            نسبة الحضور
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <div class="small text-muted">نقاط الحضور: {{ $row['att_points'] }}</div>
                                        <div class="badge bg-primary rounded-pill">{{ $row['att_rate'] }}%</div>
                                    </div>
                                </div>
                                <hr class="my-2">
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>


            {{-- Sabr Card --}}
            <div class="col-md-4">
                <div class="card shadow-sm border-success h-100">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0 text-white text-center">
                            <i class="fas fa-book-quran me-2"></i> السبر
                        </h5>
                    </div>
                    <div class="card-body scrollable-card-body">
                        @foreach ($tableRows as $row)
                            <div class="metric-item mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    {{-- Left side: circle name + description --}}
                                    <div class="d-flex flex-column">
                                        <span class="fw-bold text-dark">{{ $row['circle'] }}</span>
                                        <div class="text-muted small mt-1">
                                            النسبة المئوية لنتائج السبر
                                        </div>
                                    </div>

                                    {{-- Right side: sabr metrics --}}
                                    <div class="text-end">
                                        <div class="text-muted small">العدد: {{ $row['sabr_count'] }}</div>
                                        <div class="badge bg-success rounded-pill">
                                            {{ $row['sabr_avg'] }} %
                                        </div>
                                    </div>
                                </div>
                                <hr class="my-2">
                            </div>
                        @endforeach
                    </div>

                </div>
            </div>

            {{-- Recitation Card --}}
            <div class="col-md-4">
                <div class="card shadow-sm border-success h-100">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0 text-white text-center">
                            <i class="fas fa-microphone-lines me-2"></i> التسميع
                        </h5>
                    </div>
                    <div class="card-body scrollable-card-body">
                        @foreach ($tableRows as $row)
                            <div class="metric-item mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    {{-- Left side: circle name + description stacked vertically --}}
                                    <div class="d-flex flex-column">
                                        <span class="fw-bold text-dark">{{ $row['circle'] }}</span>
                                        <div class="text-muted small mt-1">
                                            النسبة المئوية لنتائج التسميع
                                        </div>
                                    </div>

                                    {{-- Right side: metrics --}}
                                    <div class="text-end">
                                        <div class="text-muted small">العدد: {{ $row['rec_count'] }}</div>
                                        <div class="badge bg-warning rounded-pill">
                                            {{ $row['rec_avg'] }} %
                                        </div>
                                    </div>
                                </div>
                                <hr class="my-2">
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Performance Chart Card --}}
        <div class="card shadow-lg border-primary mb-5">
            <div class="card-header bg-primary text-white mb-5">
                <h4 class="mb-0 text-center text-white fw-bold">
                    <i class="fas fa-chart-bar me-2"></i> مخطط أداء الحلقات
                </h4>
            </div>
            <div class="card-body">
                <div id="circle-performance-chart"></div>
            </div>
        </div>

        {{-- Detailed Table --}}
        <div class="card shadow-sm border-primary">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-3 text-center text-white fw-bold">
                    <i class="fas fa-table me-2"></i> الجدول التفصيلي
                </h4>
            </div>
            <div class="card-body w-100">
                <table
                    class="table table-responsive overflow-x-auto overflow-y-visible table-hover  table-striped table-bordered">
                    <thead class="bg-light">
                        <tr class="text-center">
                            <th class="align-middle"><i class="fas fa-users me-2"></i> الحلقة</th>
                            <th class="align-middle"><i class="fas fa-user-graduate me-2"></i> الطلاب</th>
                            <th class="align-middle"><i class="fas fa-pen me-2"></i> السبر</th>
                            <th class="align-middle"><i class="fas fa-microphone me-2"></i> التسميع</th>
                            <th class="align-middle"><i class="fas fa-calendar-days me-2"></i> الحضور</th>
                            <th class="align-middle"><i class="fas fa-note-sticky me-2"></i> الملاحظات</th>
                            <th class="align-middle"><i class="fas fa-star me-2"></i> الأداء</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($tableRows as $row)
                            <tr>
                                <td class="text-center">{{ $row['circle'] }}</td>
                                <td class="text-center">{{ $row['students'] }}</td>
                                <td class="text-center">
                                    <span class="badge bg-success">العدد : {{ $row['sabr_count'] }}</span>
                                    <div class="text-muted small">المعدل: {{ $row['sabr_avg'] }}</div>
                                    <div class="text-muted small">النقاط: {{ $row['sabr_points'] }}</div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-warning">العدد : {{ $row['rec_count'] }}</span>
                                    <div class="text-muted small">المعدل: {{ $row['rec_avg'] }}</div>
                                    <div class="text-muted small">النقاط: {{ $row['recitation_points'] }}</div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-primary">النقاط : {{ $row['att_points'] }}</span>
                                    <div class="text-muted small">المعدل: {{ $row['att_rate'] }}%</div>
                                    <div class="text-muted small">حضور: {{ $row['presentCount'] }}</div>
                                    <div class="text-muted small">غياب: {{ $row['absentCount'] }}</div>
                                </td>

                                <td class="text-center">
                                    <span class="badge bg-primary">العدد : {{ $row['notes_count'] }}</span>
                                    <div class="text-muted small">إيجابية: {{ $row['positive_notes'] }}</div>
                                    <div class="text-muted small">سلبية: {{ $row['negative_notes'] }}</div>
                                    <div class="text-muted small">النقاط: {{ $row['net_notes_points'] }}</div>
                                </td>
                                <td class="text-center fw-bold text-danger">{{ $row['perf_score'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const options = {
                series: [{
                    name: 'أداء الحلقة',
                    data: @json($chartData)
                }],
                chart: {
                    type: 'bar',
                    height: 450,
                    toolbar: {
                        show: true
                    },
                    fontFamily: 'Cairo, sans-serif',
                    foreColor: '#373d3f',
                    rtl: true
                },
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: '60%',
                        borderRadius: 8,
                        dataLabels: {
                            position: 'top'
                        }
                    }
                },
                colors: ['#049977'], // Use danger color
                dataLabels: {
                    enabled: false
                },
                xaxis: {
                    categories: @json($categories),
                    labels: {
                        style: {
                            fontSize: '13px',
                            fontWeight: 600
                        }
                    }
                },
                yaxis: {
                    title: {
                        text: 'مؤشر الأداء',
                        offsetX: -100,
                        offsetY: -0,
                        style: {
                            fontSize: '25px',
                            fontWeight: 600
                        }
                    },
                    labels: {
                        offsetX: -30,
                        offsetY: -0,
                        style: {
                            fontSize: '13px'
                        }
                    }
                },
                tooltip: {
                    theme: 'light',
                    style: {
                        fontSize: '14px'
                    }
                }
            };

            const chart = new ApexCharts(
                document.querySelector('#circle-performance-chart'),
                options
            );
            chart.render();
        });
    </script>
@endsection

<style>
    .metric-item {
        padding: 0.75rem;
        transition: all 0.2s ease;
    }

    .metric-item:hover {
        background-color: rgba(0, 0, 0, 0.03);
        transform: translateX(5px);
    }

    .card-header {
        border-radius: 0.75rem 0.75rem 0 0 !important;
    }

    .card {
        border-radius: 0.75rem 0.75rem 0.75rem 0.75rem !important;
    }

    table.table-hover tbody tr {
        transition: all 0.2s ease;
    }

    table.table-hover tbody tr:hover {
        background-color: rgba(0, 0, 0, 0.03) !important;
        transform: scale(1.01);
    }

    .badge {
        min-width: 70px;
        padding: 0.5em 0.75em;
    }

    .scrollable-card-body {
        max-height: 500px;
        /* Adjust based on your needs */
        overflow-y: auto;
        padding-right: 0.5rem;
        /* Space for scrollbar */
    }

    /* Custom scrollbar styling */
    .scrollable-card-body::-webkit-scrollbar {
        width: 8px;
    }

    .scrollable-card-body::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }

    .scrollable-card-body::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }

    .scrollable-card-body::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
</style>
