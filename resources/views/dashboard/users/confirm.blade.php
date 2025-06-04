@extends('dashboard.layouts.app')
@section('title', 'تأكيد الحساب')
@section('content')
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-lg rounded-lg">
                    <div class="card-header bg-primary text-white text-center">
                        <h4 class="mb-0">يرجى حفظ المعلومات التالية حتى يتمكن المستخدم من تسجيل الدخول</h4>
                    </div>
                    <div class="card-body">
                        <div class="mb-3 text-center">
                            <h5 class="text-secondary">معلومات الحساب</h5>
                        </div>
                        <div class="card-body" dir="rtl">
                            <div class="p-3 bg-light rounded mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <strong class="text-end flex-shrink-0" style="font-size: 1.8rem;">اسم المستخدم :
                                    </strong>
                                    <span class="text-danger font-weight-bold fs-4">{{ $userName }}</span>
                                </div>
                            </div>
                            <div class="p-3 bg-light rounded mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <strong class="text-end flex-shrink-0" style="font-size: 1.8rem;">كلمة السر : </strong>
                                    <span class="text-danger font-weight-bold fs-4">
                                        {{ $password }}
                                    </span>
                                </div>
                            </div>
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
    </div>
@endsection
