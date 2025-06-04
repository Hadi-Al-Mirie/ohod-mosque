@extends('dashboard.layouts.app')
@section('title', 'معلومات المستخدم')
@section('content')
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-lg rounded-lg">
                    <div class="card-header bg-primary text-white text-center">
                        <h4 class="mb-0">معلومات الحساب</h4>
                    </div>
                    <div class="card-body" dir="rtl">
                        <!-- Username -->
                        <div class="p-3 bg-light rounded mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <strong class="text-end flex-shrink-0" style="font-size: 1.8rem;">اسم المستخدم:</strong>
                                <span class="text-danger font-weight-bold fs-4">{{ $user->user_name }}</span>
                            </div>
                        </div>

                        <!-- Full Name -->
                        <div class="p-3 bg-light rounded mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <strong class="text-end flex-shrink-0" style="font-size: 1.8rem;">الاسم الثلاثي:</strong>
                                <span class="text-danger font-weight-bold fs-4">
                                    {{ $user->first_name }} {{ $user->middle_name }} {{ $user->last_name }}
                                </span>
                            </div>
                        </div>

                        <!-- Phone Number -->
                        <div class="p-3 bg-light rounded mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <strong class="text-end flex-shrink-0" style="font-size: 1.8rem;">رقم الهاتف:</strong>
                                <span class="text-danger font-weight-bold fs-4">{{ $user->phone }}</span>
                            </div>
                        </div>

                        <!-- Location -->
                        <div class="p-3 bg-light rounded mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <strong class="text-end flex-shrink-0" style="font-size: 1.8rem;">السكن:</strong>
                                <span class="text-danger font-weight-bold fs-4">{{ $user->location }}</span>
                            </div>
                        </div>

                        <!-- Role -->
                        <div class="p-3 bg-light rounded mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <strong class="text-end flex-shrink-0" style="font-size: 1.8rem;">الدور:</strong>
                                <span class="text-danger font-weight-bold fs-4">{{ $user->role->name }}</span>
                            </div>
                        </div>

                        <!-- Back Button -->
                        <div class="text-center mt-4">
                            <a href="{{ route('admin.users.index') }}" class="btn btn-lg btn-outline-primary px-4">
                                <i class="fas fa-arrow-left"></i> العودة للقائمة الرئيسية
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
