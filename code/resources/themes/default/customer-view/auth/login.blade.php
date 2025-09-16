@extends('layouts.front-end.app')
@section('title', \App\CPU\translate('Login'))
@push('css_or_js')
    <style>
        .password-toggle-btn .custom-control-input:checked~.password-toggle-indicator {
            color: {
                    {
                    $web_config['primary_color']
                }
            }

            ;
        }
    </style>
@endpush
@section('content')
    <div class="container loginContainerDiv py-4 py-lg-5 my-4 d-flex"
        style="text-align: {{ Session::get('direction') === 'rtl' ? 'right' : 'left' }};">
        <div class="loginImg col-md-5">
            <img src="{{ asset('public/assets/front-end/img/loginImg.png') }}" alt="">
        </div>
        <div class="col-md-7">
            <div class="card border-0 box-shadow loginContainer">
                <div class="card-body">
                    <h2 class="h4 mb-1 primaryColor">{{ \App\CPU\translate('sign_in') }}</h2>


                    <ul class="nav nav-pills mb-3 mt-3" id="pills-tab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="loginTabBtn active " id="emailSignIn-tab" data-bs-toggle="pill"
                                data-bs-target="#emailSignIn" type="button" role="tab" aria-controls="emailSignIn"
                                aria-selected="true">Login with Mobile</button>
                        </li>
                        <li class="nav-item ml-3 d-none" role="presentation">
                            <button class="loginTabBtn" id="otpSignin-tab" data-bs-toggle="pill" data-bs-target="#otpSignin"
                                type="button" role="tab" aria-controls="otpSignin" aria-selected="false">Login With
                                OTP</button>
                        </li>
                    </ul>
                    <div class="tab-content" id="pills-tabContent">
                        <div class="tab-pane fade show active" id="emailSignIn" role="tabpanel"
                            aria-labelledby="emailSignIn-tab" tabindex="0">

                            <form class="needs-validation mt-2" autocomplete="off"
                                action="{{ route('customer.auth.login') }}" method="post" id="form-id">
                                @csrf
                                <div class="form-group">
                                    <label for="si-email">{{ \App\CPU\translate('phone_number') }}</label>
                                    <input class="form-control" type="number" name="user_id" id="si-email"
                                        style="text-align: {{ Session::get('direction') === 'rtl' ? 'right' : 'left' }};"
                                        value="{{ old('user_id') }}"
                                        placeholder="{{ \App\CPU\translate('Enter_phone_number') }}" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength); this.setCustomValidity(this.value.length < 10 ? 'Minimum length is 10 digits.' : ''); this.value = this.value.replace(/[^0-9]/g, '');" maxlength="10"
                                        required>
                                    <div class="invalid-feedback">
                                        {{ \App\CPU\translate('please_provide_valid_email_or_phone_number') }}

                                        .
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="si-password">{{ \App\CPU\translate('password') }}</label>
                                    <div class="password-toggle">
                                        <input class="form-control" name="password" type="password" id="si-password"
                                            style="text-align: {{ Session::get('direction') === 'rtl' ? 'right' : 'left' }};" placeholder="{{ \App\CPU\translate('password') }}"
                                            required>
                                        <label class="password-toggle-btn">
                                            <input class="custom-control-input" type="checkbox"><i
                                                class="czi-eye password-toggle-indicator"></i><span
                                                class="sr-only">{{ \App\CPU\translate('Show') }}
                                                {{ \App\CPU\translate('password') }} </span>
                                        </label>
                                    </div>
                                </div>
                                <div class="form-group d-flex flex-wrap justify-content-between">

                                    <div class="form-group">
                                        <input type="checkbox"
                                            class="{{ Session::get('direction') === 'rtl' ? 'ml-1' : 'mr-1' }}"
                                            name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>

                                        <label class=""
                                            for="remember">{{ \App\CPU\translate('remember_me') }}</label>
                                    </div>
                                    <a class="font-size-sm" href="{{ route('customer.auth.recover-password') }}">
                                        {{ \App\CPU\translate('forgot_password') }}?
                                    </a>
                                </div>

                                {{-- reCAPTCHA --}}
                                {{--
                        @php($recaptcha = \App\CPU\Helpers::get_business_settings('recaptcha'))
                        @if (isset($recaptcha) && $recaptcha['status'] == 1)
                            <div id="recaptcha_element" class="w-100" data-type="image"></div>
                            <br/>
                        @else
                            <div class="row py-2">
                                <div class="col-6 pr-2">
                                    <input type="text" class="form-control border __h-40" name="default_recaptcha_id_customer_login" value=""
                                        placeholder="{{\App\CPU\translate('Enter captcha value')}}" autocomplete="off">
                    </div>
                    <div class="col-6 input-icons mb-2 w-100 rounded bg-white">
                        <a onclick="re_captcha();" class="d-flex align-items-center align-items-center">
                            <img src="{{ URL('/customer/auth/code/captcha/1?captcha_session_id=default_recaptcha_id_customer_login') }}" class="input-field rounded __h-40" id="customer_login_recaptcha_id">
                            <i class="tio-refresh icon cursor-pointer p-2"></i>
                        </a>
                    </div>
                </div>
                @endif
                --}}
                                <button class="btn btn--primary btn-block btn-shadow"
                                    type="submit">{{ \App\CPU\translate('sign_in') }}</button>
                            </form>
                        </div>
                        <div class="tab-pane fade d-none" id="otpSignin" role="tabpanel" aria-labelledby="otpSignin-tab"
                            tabindex="0">
                            <form class="needs-validation mt-2" autocomplete="off"
                                action="{{ route('customer.auth.login_with_otp') }}" method="post" id="form-id-otp">
                                @csrf <!-- Laravel CSRF token -->
                                <div class="form-group">
                                    <label for="si-phone">Enter Mobile Number</label>
                                    <input class="form-control" type="text" name="phone" id="si-phone"
                                        placeholder="Enter Mobile Number" required maxlength="10">
                                    <div class="invalid-feedback">Please provide a valid phone number</div>
                                </div>
                                <div class="form-group" id="otp-container" style="display: none;">
                                    <label for="si-otp">Enter OTP</label>
                                    <input class="form-control" name="otp" type="text" id="si-otp"
                                        placeholder="Enter OTP" required readonly>
                                    <div class="invalid-feedback">Please enter the OTP sent to your phone</div>
                                </div>

                                <button type="button" class="btn btn-secondary btn-block btn-shadow"
                                    id="send-otp-btn">Send OTP</button>
                                <button type="submit" class="btn btn--primary btn-block btn-shadow" id="submit-otp-btn"
                                    disabled>Sign in</button>
                            </form>
                        </div>




                    </div>
                </div>
                <div class="card-footer">
                    <div class="row">
                        <div class="col-12 d-flex flex-wrap justify-content-around justify-content-md-between align-items-center __gap-15"
                            style="direction: {{ Session::get('direction') }}">
                            <div class="{{ Session::get('direction') === 'rtl' ? '' : 'ml-2' }}">
                                <h6 class="m-0">{{ \App\CPU\translate('no_account_Sign_up_now') }}</h6>
                            </div>
                            <div class="{{ Session::get('direction') === 'rtl' ? 'ml-2' : '' }}">
                                <a class="btn btn-outline-primary" href="{{ route('customer.auth.sign-up') }}">
                                    <i class="fa fa-user-circle"></i> {{ \App\CPU\translate('sign_up') }}
                                </a>
                            </div>
                        </div>
                        @foreach (\App\CPU\Helpers::get_business_settings('social_login') as $socialLoginService)
                            @if (isset($socialLoginService) && $socialLoginService['status'] == true)
                                <div class="col-sm-6 text-center mt-3">
                                    <a class="btn btn-outline-primary w-100"
                                        href="{{ route('customer.auth.service-login', $socialLoginService['login_medium']) }}">
                                        <i
                                            class="czi-{{ $socialLoginService['login_medium'] }} mr-2 ml-n1"></i>{{ \App\CPU\translate('sign_in_with_' . $socialLoginService['login_medium']) }}
                                    </a>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.0/dist/sweetalert2.min.css" rel="stylesheet">

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.0/dist/sweetalert2.all.min.js"></script>


    <script>
        document.getElementById('send-otp-btn').addEventListener('click', function() {
            var phone = document.getElementById('si-phone').value;

            // Validate the phone number
            if (!phone || phone.length !== 10) {
                Swal.fire({
                    icon: "error",
                    title: "Please enter a valid 10-digit phone number.",
                    toast: true,
                    position: "top-end",
                    showConfirmButton: false,
                    timer: 1500,
                    timerProgressBar: true,
                });
                return;
            }

            // Disable button to prevent multiple clicks
            document.getElementById('send-otp-btn').disabled = true;
            document.getElementById('send-otp-btn').innerText = 'Sending...';

            // Send AJAX request to send OTP
            fetch('{{ route('customer.auth.send_otp') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        // 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                        //     'content')
                        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                    },
                    body: JSON.stringify({
                        phone: phone
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        console.log(data.otp);
                        Swal.fire({
                            icon: "success",
                            title: "OTP sent to your phone!",
                            toast: true,
                            position: "top-end",
                            showConfirmButton: false,
                            timer: 1500,
                            timerProgressBar: true,
                        });
                        document.getElementById('si-otp').value = data.otp;

                        // Make OTP input visible and enable it
                        document.getElementById('otp-container').style.display = 'block';
                        document.getElementById('si-otp').disabled = false;

                        // Enable the submit button
                        document.getElementById('submit-otp-btn').disabled = false;

                        // Hide the "Send OTP" button
                        document.getElementById('send-otp-btn').style.display = 'none';
                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "Failed to send OTP. Please try again.",
                            toast: true,
                            position: "top-end",
                            showConfirmButton: false,
                            timer: 1500,
                            timerProgressBar: true,
                        });

                        // Re-enable the Send OTP button
                        document.getElementById('send-otp-btn').disabled = false;
                        document.getElementById('send-otp-btn').innerText = 'Send OTP';
                    }
                })
                .catch(error => {
                    Swal.fire({
                        icon: "error",
                        title: "user not found.",
                        toast: true,
                        position: "top-end",
                        showConfirmButton: false,
                        timer: 1500,
                        timerProgressBar: true,
                    });

                    console.error('Error:', error);

                    // Re-enable the Send OTP button
                    document.getElementById('send-otp-btn').disabled = false;
                    document.getElementById('send-otp-btn').innerText = 'Send OTP';
                });
        });
    </script>
@endsection

@push('script')
    {{-- reCAPTCHA scripts start --}}
    {{--
    @if (isset($recaptcha) && $recaptcha['status'] == 1)
        <script type="text/javascript">
            var onloadCallback = function () {
                grecaptcha.render('recaptcha_element', {
                    'sitekey': '{{ \App\CPU\Helpers::get_business_settings('recaptcha')['site_key'] }}'
});
};
</script>
<script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit" async
    defer></script>
<script>
    $("#form-id").on('submit', function(e) {
        var response = grecaptcha.getResponse();

        if (response.length === 0) {
            e.preventDefault();
            toastr.error("{{\App\CPU\translate('Please check the recaptcha')}}");
        }
    });
</script>
@else
<script type="text/javascript">
    function re_captcha() {
        $url = "{{ URL('/customer/auth/code/captcha') }}";
        $url = $url + "/" + Math.random() + '?captcha_session_id=default_recaptcha_id_customer_login';
        document.getElementById('customer_login_recaptcha_id').src = $url;
        console.log('url: ' + $url);
    }
</script>
@endif
--}}
    {{-- reCAPTCHA scripts end --}}
@endpush

<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
    integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous">
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"
    integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous">
</script>
