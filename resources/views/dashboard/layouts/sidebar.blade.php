<div id="app-sidepanel" class="app-sidepanel">
    <div id="sidepanel-drop" class="sidepanel-drop"></div>
    <div class="sidepanel-inner d-flex flex-column">
        <a href="" id="sidepanel-close" class="sidepanel-close d-xl-none">&times;</a>
        <div class="app-branding">
            <a href="" class="app-logo d-flex flex-column align-items-center text-center">
                <img src="{{ asset('assets/images/app-logo.png') }}" alt="logo" class="logo-icon mb-2"
                    style="width: 100px; height: auto;">
                <span class="logo-text">{{ config('app.name') }}</span>
            </a>
        </div>

        <nav id="app-nav-main" class="app-nav app-nav-main flex-grow-1">
            <ul class="app-menu list-unstyled accordion" id="menu-accordion">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                        href="{{ route('admin.dashboard') }}">
                        <span class="nav-icon">
                            <i class="fas fa-tachometer-alt"></i>
                        </span>
                        <span class="nav-link-text">الرئيسية</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.courses.*') && !request()->routeIs('admin.courses.index') ? 'active' : '' }}"
                        href="{{ route('admin.courses.show', ['course' => course_id()]) }}">
                        <span class="nav-icon">
                            <i class="fas fa-graduation-cap"></i>
                        </span>
                        <span class="nav-link-text">الدورة الحالية</span>
                    </a>
                </li>


                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.circles.*') ? 'active' : '' }}"
                        href="{{ route('admin.circles.index') }}">
                        <span class="nav-icon">
                            <i class="fas fa-users"></i>
                        </span>
                        <span class="nav-link-text">الحلقات</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.teachers.*') ? 'active' : '' }}"
                        href="{{ route('admin.teachers.index') }}">
                        <span class="nav-icon">
                            <i class="fas fa-user-tie"></i>
                        </span>
                        <span class="nav-link-text">الأساتذة</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.helper-teachers.*') ? 'active' : '' }}"
                        href="{{ route('admin.helper-teachers.index') }}">
                        <span class="nav-icon">
                            <i class="fa-solid fa-user-nurse"></i>
                        </span>
                        <span class="nav-link-text">الأساتذة المساعدون</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.students.*') ? 'active' : '' }}"
                        href="{{ route('admin.students.index') }}">
                        <span class="nav-icon">
                            <i class="fas fa-user-alt"></i>
                        </span>
                        <span class="nav-link-text">الطلاب</span>
                    </a>
                </li>

                <li class="nav-item has-submenu">
                    @php
                        $isArchiveActive =
                            request()->routeIs('admin.sabrs.*') ||
                            request()->routeIs('admin.recitations.*') ||
                            request()->routeIs('admin.awqafs.*') ||
                            (request()->routeIs('admin.attendances.*') &&
                                !request()->routeIs('admin.attendances.justifications.*')) ||
                            (request()->routeIs('admin.notes.*') &&
                                !request()->routeIs('admin.notes.requests') &&
                                !request()->routeIs('admin.notes.approve'))
                                ? true
                                : false;
                    @endphp
                    <a class="nav-link submenu-toggle {{ $isArchiveActive ? 'active' : '' }}" href="#"
                        data-bs-toggle="collapse" data-bs-target="#submenu-1"
                        aria-expanded="{{ $isArchiveActive ? 'true' : 'false' }}" aria-controls="submenu-1">
                        <span class="nav-icon">
                            <i class="fa-solid fa-box-archive"></i>
                        </span>
                        <span class="nav-link-text">الأرشيف</span>
                        <span class="submenu-arrow">
                            <svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-chevron-down"
                                fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd"
                                    d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z" />
                            </svg>
                        </span>
                    </a>

                    <div id="submenu-1" class="collapse submenu submenu-1 {{ $isArchiveActive ? 'show' : '' }}"
                        data-bs-parent="#menu-accordion">
                        <ul class="submenu-list list-unstyled">
                            <li class="submenu-item">
                                <a class="submenu-link {{ request()->routeIs('admin.recitations.*') ? 'active' : '' }}"
                                    href="{{ route('admin.recitations.index') }}">
                                    التسميعات
                                </a>
                            </li>
                            <li class="submenu-item">
                                <a class="submenu-link {{ request()->routeIs('admin.sabrs.*') ? 'active' : '' }}"
                                    href="{{ route('admin.sabrs.index') }}">
                                    السبر
                                </a>
                            </li>
                            <li class="submenu-item">
                                <a class="submenu-link {{ request()->routeIs('admin.attendances.*') && !request()->routeIs('admin.attendances.justifications.*') ? 'active' : '' }}"
                                    href="{{ route('admin.attendances.index') }}">
                                    الحضور
                                </a>
                            </li>
                            <li class="submenu-item">
                                <a class="submenu-link {{ request()->routeIs('admin.notes.*') && !request()->routeIs(['admin.notes.requests', 'admin.notes.approve'])
                                    ? 'active'
                                    : '' }}"
                                    href="{{ route('admin.notes.index') }}">
                                    الملاحظات
                                </a>
                            </li>
                            <li class="submenu-item">
                                <a class="submenu-link {{ request()->routeIs('admin.awqaf.*') ? 'active' : '' }}"
                                    href="{{ route('admin.awqafs.index') }}">
                                    سبر الأوقاف
                                </a>
                            </li>

                        </ul>
                    </div>
                </li>





                <li class="nav-item has-submenu">
                    @php
                        $isHistoryActive =
                            request()->routeIs('admin.courses.index') || request()->routeIs('admin.oldcourse.show')
                                ? true
                                : false;
                    @endphp
                    <a class="nav-link submenu-toggle {{ $isHistoryActive ? 'active' : '' }}" href="#"
                        data-bs-toggle="collapse" data-bs-target="#submenu-2"
                        aria-expanded="{{ $isHistoryActive ? 'true' : 'false' }}" aria-controls="submenu-2">
                        <span class="nav-icon">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                        </span>
                        <span class="nav-link-text">السجل</span>
                        <span class="submenu-arrow">
                            <svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-chevron-down"
                                fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd"
                                    d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z" />
                            </svg>
                        </span>
                    </a>

                    <div id="submenu-2" class="collapse submenu submenu-2 {{ $isHistoryActive ? 'show' : '' }}"
                        data-bs-parent="#menu-accordion">
                        <ul class="submenu-list list-unstyled">
                            <li class="submenu-item">
                                <a class="submenu-link {{ request()->routeIs('admin.courses.index') || request()->routeIs('admin.oldcourse.show') ? 'active' : '' }}"
                                    href="{{ route('admin.courses.index') }}">
                                    الدورات السابقة
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>






                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.notes.requests') || request()->routeIs('admin.notes.approve') ? 'active' : '' }}"
                        href="{{ route('admin.notes.requests') }}">
                        <span class="nav-icon">
                            <i class="fa-solid fa-notes-medical"></i>
                        </span>
                        <span class="nav-link-text">طلبات الملاحظات</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.attendances.justifications.index') || request()->routeIs('admin.attendances.justifications.approve') || request()->routeIs('admin.attendances.justifications.reject') ? 'active' : '' }}"
                        href="{{ route('admin.attendances.justifications.index') }}">
                        <span class="nav-icon">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </span>
                        <span class="nav-link-text">طلبات تبرير الغياب</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</div>
