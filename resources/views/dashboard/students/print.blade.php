<!DOCTYPE html>
<html dir="rtl" lang="ar">

    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title></title>
        <style>
            @import url('https://fonts.googleapis.com/css2?family=Tajawal&display=swap');

            body {
                margin: 0;
                padding: 0;
                /* Use the imported font */
                font-family: 'Tajawal', sans-serif;
                direction: rtl;
            }

            .container {
                width: 99.9%;
                margin: 0 auto;
            }

            .row {
                display: flex;
                justify-content: center;
                margin-top: 2rem;
            }

            .card {
                width: 100%;
                border: 1px solid #dee2e6;
                border-radius: 0;
                margin-top: -32.6px;
            }

            .card-header {
                background-color: #28a745;
                color: #fff;
                padding: 1rem;
                font-size: 1.75rem;
                text-align: center;
            }

            .card-body {
                font-size: 1.25rem;
                padding: 1rem;
            }

            .card-text {
                margin-bottom: 0.75rem;
            }

            .row-inner {
                display: flex;
                align-items: center;
                justify-content: space-between;
            }

            .col-md-8 {
                width: 70%;
            }

            .col-md-3 {
                width: 30%;
                text-align: center;
            }

            /* Make the QR code larger */
            .qr-code {
                width: 200px;
                height: 200px;
            }
        </style>
    </head>

    <body>
        <div class="container">
            <div class="row">
                <div class="card">
                    <div class="card-header">
                        معلومات الطالب
                    </div>
                    <div class="card-body">
                        <div class="row-inner">
                            <div class="col-md-8">
                                <p class="card-text">
                                    <strong>الرقم:</strong> {{ $student->id }}
                                </p>
                                <p class="card-text">
                                    <strong>الاسم:</strong> {{ $student->user->name }}
                                </p>
                            </div>
                            <div class="col-md-3">
                                <img src="data:image/png;base64,{{ base64_encode($qrcode) }}" alt="QR Code"
                                    class="qr-code">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>

</html>
