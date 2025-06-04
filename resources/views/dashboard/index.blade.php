@extends('dashboard.layouts.app')

@section('title', 'الرئيسية')

@section('content')
    @include('dashboard.layouts.alert')
    <div class=" mb-5 mt-3">
        <h1 class="fw-bold text-center text-black mb-5">الإحصائيات</h1>
    </div>
    <div class="container py-5">
        <!-- Stats Cards -->
        <div class="row g-4">
            <!-- Circles Card -->
            <div class="col-12 col-md-6 col-xl-4">
                <div class="card border-0 shadow-lg hover-lift">
                    <div class="card-body bg-primary text-center p-4 rounded-3">
                        <div class="icon-wrapper bg-white-20 rounded-circle p-3 mb-3">
                            <i class="fas fa-quran fa-2x text-white"></i>
                        </div>
                        <h5 class="card-title text-white mb-1">الحلقات</h5>
                        <p class="display-5 text-white mb-0 fw-bold">{{ $circles }}</p>
                        <small class="text-white-50">العدد الكلي</small>
                    </div>
                </div>
            </div>

            <!-- Students Card -->
            <div class="col-12 col-md-6 col-xl-4">
                <div class="card border-0 shadow-lg hover-lift">
                    <div class="card-body bg-primary text-center p-4 rounded-3">
                        <div class="icon-wrapper bg-white-20 rounded-circle p-3 mb-3">
                            <i class="fas fa-users fa-2x text-white"></i>
                        </div>
                        <h5 class="card-title text-white mb-1">الطلاب</h5>
                        <p class="display-5 text-white mb-0 fw-bold">{{ $students }}</p>
                        <small class="text-white-50">العدد الكلي</small>
                    </div>
                </div>
            </div>

            <!-- Teachers Card -->
            <div class="col-12 col-md-6 col-xl-4">
                <div class="card border-0 shadow-lg hover-lift">
                    <div class="card-body bg-primary text-center p-4 rounded-3">
                        <div class="icon-wrapper bg-white-20 rounded-circle p-3 mb-3">
                            <i class="fas fa-chalkboard-teacher fa-2x text-white"></i>
                        </div>
                        <h5 class="card-title text-white mb-1">الأساتذة</h5>
                        <p class="display-5 text-white mb-0 fw-bold">{{ $teachers }}</p>
                        <small class="text-white-50">العدد الكلي</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class=" mb-5 text-center mt-5">
        <a href="{{ route('admin.courses.create') }}" class="h1 text-center bg-secondary text-white shadow-sm p-4"
            style="
        border-radius: 20px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        min-width: 300px;
    ">
            <i class="fas fa-plus-circle me-2"></i> بدء دورة جديدة
        </a>
    </div>

@endsection
