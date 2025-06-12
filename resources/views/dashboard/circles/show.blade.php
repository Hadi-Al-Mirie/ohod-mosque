@extends('dashboard.layouts.app')
@section('title', 'تفاصيل الحلقة')
@section('content')
    <div class="container mt-5" dir="rtl">
        <div class="position-relative mb-5">
            <div class="col-12 mb-5">
                <a href="{{ route('admin.circles.edit', $circle->id) }}"
                    class="btn btn-secondary btn-lg position-absolute start-0 mt-4">
                    <i class="fas fa-edit me-2"></i> تعديل
                </a>
            </div>
            <div class="col-12 mb-4">
                <h1 class="h2 text-primary fw-bold text-center">
                    <i class="fas fa-chart-pie me-2"></i>
                    إحصائيات الحلقة: {{ $circle->name }}
                </h1>
            </div>
        </div>

        @include('dashboard.layouts.alert')

        <div class="row g-4 mb-5">
            <!-- Attendance Stats -->
            @foreach ($attendanceStats as $typeName => $stat)
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100 hover-shadow-lg transition-all">
                        <div class="card-header bg-primary text-white text-center">
                            <span class="fw-bold">{{ $typeName }}</span>
                        </div>
                        <div class="card-body py-4">
                            <div class="d-flex justify-content-around align-items-center">
                                <div>
                                    <small class="text-muted me-5">العدد</small>
                                    <h2 class="display-6 fw-bold text-primary mb-0">{{ $stat['count'] }}</h2>
                                </div>
                                <div class="vr"></div>
                                <div>
                                    <small class="text-muted me-5">النسبة</small>
                                    <h2 class="display-6 fw-bold text-success mb-0">{{ $stat['ratio'] }}%</h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="row g-4 mb-5">
            <!-- Recitation Stats -->
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100 hover-shadow-lg transition-all">
                    <div class="card-header bg-gradient-info text-white text-center">
                        <span class="fw-bold">التسميعات</span>
                    </div>
                    <div class="card-body py-4">
                        <div class="row text-center">
                            <div class="col-4">
                                <small class="text-muted">العدد الكلي</small>
                                <h2 class="display-6 fw-bold text-primary mb-1">{{ $recitationCount }}</h2>
                            </div>
                            <div class="col-4">
                                <small class="text-muted">الترتيب</small>
                                <h2 class="display-6 fw-bold text-primary mb-1">#{{ $recitationRank }}</h2>
                            </div>
                            <div class="col-4">
                                <small class="text-muted">متوسط التسميع</small>
                                <h2 class="display-6 fw-bold text-primary mb-1">{{ number_format($recitationAvg, 2) }}</h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sabr Stats -->
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100 hover-shadow-lg transition-all">
                    <div class="card-header bg-gradient-warning text-white text-center">
                        <span class="fw-bold">السبر</span>
                    </div>
                    <div class="card-body py-4">
                        <div class="row text-center">
                            <div class="col-4">
                                <small class="text-muted">العدد الكلي</small>
                                <h2 class="display-6 fw-bold text-primary mb-1">{{ $sabrCount }}</h2>
                            </div>
                            <div class="col-4">
                                <small class="text-muted">الترتيب</small>
                                <h2 class="display-6 fw-bold text-primary mb-1">#{{ $sabrRank }}</h2>
                            </div>
                            <div class="col-4">
                                <small class="text-muted">متوسط السبر</small>
                                <h2 class="display-6 fw-bold text-primary mb-1">{{ number_format($sabrAvg, 2) }}</h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Top performers --}}
        <div class="row g-4 mb-5">
            <!-- Top Reciters -->
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-info text-white text-center">أفضل 3 في التسميع</div>
                    <div class="card-body">
                        <ol class="list-group list-group-numbered">
                            @foreach ($topReciters as $item)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    {{ $item['student']->user->name }}
                                    <span class="badge bg-primary rounded-pill">{{ $item['points'] }}</span>
                                </li>
                            @endforeach
                        </ol>
                    </div>
                </div>
            </div>

            <!-- Top Sabrs -->
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-warning text-white text-center">أفضل 3 في السبر</div>
                    <div class="card-body">
                        <ol class="list-group list-group-numbered">
                            @foreach ($topSabrs as $item)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    {{ $item['student']->user->name }}
                                    <span class="badge bg-primary rounded-pill">{{ $item['points'] }}</span>
                                </li>
                            @endforeach
                        </ol>
                    </div>
                </div>
            </div>

            <!-- Top Attendees -->
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-success text-white text-center">أفضل 3 في الحضور</div>
                    <div class="card-body">
                        <ol class="list-group list-group-numbered">
                            @foreach ($topAttendees as $item)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    {{ $item['student']->user->name }}
                                    <span class="badge bg-primary rounded-pill">{{ $item['points'] }}</span>
                                </li>
                            @endforeach
                        </ol>
                    </div>
                </div>
            </div>

            <!-- Top Overall -->
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-primary text-white text-center">أفضل 3 بشكل عام</div>
                    <div class="card-body">
                        <ol class="list-group list-group-numbered">
                            @foreach ($topOverall as $item)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    {{ $item['student']->user->name }}
                                    <span class="badge bg-primary rounded-pill">{{ $item['points'] }}</span>
                                </li>
                            @endforeach
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center mb-5">
            <a href="{{ route('admin.circles.index') }}" class="btn btn-secondary btn-lg">
                <i class="fas fa-arrow-right me-2"></i> عودة
            </a>
        </div>
    </div>
@endsection
