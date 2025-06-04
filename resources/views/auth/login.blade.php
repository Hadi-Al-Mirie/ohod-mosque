<!DOCTYPE html>
<html lang="ar">

    <head>
        <title>مسجد أحد-تسجيل الدخول</title>

        <!-- Meta -->
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <meta name="description" content="Portal - Bootstrap 5 Admin Dashboard Template For Developers">
        <meta name="author" content="Xiaoying Riley at 3rd Wave Media">
        <link rel="shortcut icon" href="favicon.ico">

        <!-- FontAwesome JS-->
        <script defer src="{{ asset('assets/plugins/fontawesome/js/all.min.js') }}"></script>

        <!-- App CSS -->
        <link id="theme-style" rel="stylesheet" href="{{ asset('assets/css/portal.css') }}">
    </head>

    <body class="app app-login p-0">
        <div class="row g-0 app-auth-wrapper">
            <div class="col-12 text-center p-5">
                <div class="d-flex flex-column align-content-end">
                    <div class="app-auth-body mx-auto">
                        <div class="app-auth-branding mb-3">
                            <a class="app-logo" href="#">
                                <img class="logo-icon mb-1" src="{{ asset('assets/images/app-logo.png') }}"
                                    alt="logo" style="width: 190px; height: auto;">
                            </a>
                        </div>
                        <h2 class="auth-heading text-center mb-4">قم بتسجيل الدخول</h2>
                        <div class="auth-form-container text-start">
                            <form class="auth-form login-form" action="{{ route('login.submit') }}" method="POST">
                                @csrf
                                <div class="mb-4">
                                    <input id="name" name="name" type="text"
                                        class="form-control @error('name') is-invalid @enderror"
                                        placeholder="اسم المستخدم" value="{{ old('name') }}" required
                                        aria-label="اسم المستخدم">
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="input-group">
                                    <input id="password" name="password" type="password"
                                        class="form-control @error('password') is-invalid @enderror"
                                        placeholder="كلمة السر" value="{{ old('password') }}" required>
                                    <img id="togglePassword" class="toggle-password"
                                        src="{{ asset('assets/images/eye-close.png') }}" alt="logo">
                                </div>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                        </div>
                        @if ($errors->any())
                            <div class="alert alert-danger" style="padding: 5px; margin: 10px;">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <div class="text-center my-4">
                            <button type="submit" class="btn app-btn-primary w-100 theme-btn mx-auto">تسجيل
                                الدخول</button>
                        </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        </div>
        <script>
            let icon = document.getElementById("togglePassword");
            let password = document.getElementById("password");
            icon.onclick = function() {
                if (password.type == "password") {
                    password.type = "text";
                    icon.src = "{{ asset('assets/images/eye-open.png') }}";
                } else {
                    password.type = "password";
                    icon.src = "{{ asset('assets/images/eye-close.png') }}";
                }
            }
            // Push a dummy state so there’s always one “forward” to go to
            history.pushState(null, null, location.href);
            window.addEventListener('popstate', function() {
                // Whenever they hit “Back,” push them forward again
                history.pushState(null, null, location.href);
            });
        </script>
    </body>

</html>
