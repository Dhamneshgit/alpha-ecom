<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Required Meta Tags Always Come First -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- Title -->
    <title>{{ \App\CPU\translate('seller_login') }}</title>

    <!-- Favicon -->
    <link rel="shortcut icon" href="favicon.ico">

    <!-- Font -->
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&amp;display=swap" rel="stylesheet">
    <!-- CSS Implementing Plugins -->
    <link rel="stylesheet" href="{{ asset('public/assets/back-end') }}/css/bootstrap.min.css">
    <link rel="stylesheet" href="{{ asset('public/assets/back-end') }}/css/vendor.min.css">
    <link rel="stylesheet" href="{{ asset('public/assets/back-end') }}/vendor/icon-set/style.css">
    <!-- CSS Front Template -->
    <link rel="stylesheet" href="{{ asset('public/assets/back-end') }}/css/toastr.css">
    <link rel="stylesheet" href="{{ asset('public/assets/back-end') }}/css/theme.minc619.css?v=1.0">
    <link rel="stylesheet" href="{{ asset('public/assets/back-end') }}/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

</head>
<style>
    .__shop-apply {
        width: 100%;
        margin: 0 auto;
        max-width: 650px;
    }

    img#viewer {
        width: 200px;
    }

    .card.__card.mb-3 {
        border: 1px solid #0068e885 !important;
    }

    .form-control-user {
        border: 1px solid #0068e885 !important;
    }

    textarea#shop_address {
        border: 1px solid #0068e885 !important;
    }

    .card {
        box-shadow: 0px 5px 10px rgba(51, 66, 87, 0.05);
        transition: all 0.3s ease;
        border: 1px solid #0068e885 !important;
        width: 65%;
        display: block;
        margin: auto;
    }

    img#viewerLogo {
        border: 1px solid #0068e885 !important;
        border-radius: 5px;
        width: 200px;
        object-fit: cover;
    }



    img#viewerBanner {
        border: 1px solid #0068e885 !important;
        border-radius: 5px;
        width: 200px;
        object-fit: cover;
    }

    .logo-label {
        border: 1px solid #0068e885 !important;
    }

    .banner-label {
        border: 1px solid #0068e885 !important;
        box-shadow: rgba(50, 50, 93, 0.25) 0px 2px 5px -1px, rgba(0, 0, 0, 0.3) 0px 1px 3px -1px;
    }

    input.form-control.__h-40 {
        border: 1px solid #0068e885 !important;
    }

    .btn-user {
        background: linear-gradient(126deg, rgba(0, 104, 232, 1) 9%, rgba(0, 129, 254, 0.8519782913165266) 98%);
        color: #fff;
    }

    .btn-user:hover {
        color: #fff;
    }

    img#viewer {
        border: 1px solid #0068e885 !important;
        border-radius: 5px;
    }

    .custom-filed {
        border: 1px solid #0068e885 !important;
    }

    .head-title-login {
        font-family: "Titillium Web", sans-serif;
        font-size: 16px;
        font-weight: 500;
    }

    h5.card-title.m-0 {
        font-family: "Titillium Web", sans-serif;
        font-size: 16px;
        font-weight: 600;
    }

    textarea#shop_address {
        border: 1px solid #0068e885 !important;
    }

    .select2-container--default .select2-selection--single {
        background-color: #fff;
        /* border: 1px solid #aaa; */
        border-radius: 4px;
        border: 1px solid #0068e885 !important;
    }

    input#aadhar {
        border: 1px solid #0068e885 !important;
    }

    input#gst {
        border: 1px solid #0068e885 !important;
    }

    input#pan_card {
        border: 1px solid #0068e885 !important;
    }

    .addressInputFeald input {
        border: 1px solid #0068e885 !important;
        border-radius: 5px;
        outline: none;
        padding: 8px 10px;
    }

    label.custom-file-label {
        border: 1px solid #0068e885 !important;
    }

    .upload-img-view {
        border: 1px solid #0068e885 !important;
    }

    button.btn.submit-btnn.btn-secondary {
        width: 375px;
    }


    button.btn.reset-btnn.btn-secondary {
        width: 380px;
    }

    button#apply {
        width: 380px;
    }

    .d-flex.align-items-center.justify-content-end.button-sec-bottom {
        gap: 28px;
    }
</style>

<body>
    <div class="content pt-5 container-fluid main-card {{ Session::get('direction') }}">

        @php($e_commerce_logo = \App\Model\BusinessSetting::where(['type' => 'company_web_logo'])->first()->value)
        <a class="d-flex justify-content-center mb-5" href="javascript:">
            <img class="z-index-2" height="70" src="{{ asset('storage/app/public/company/' . $e_commerce_logo) }}"
                alt="Logo" onerror="this.src='{{ asset('public/assets/back-end/img/400x400/img2.jpg') }}'">
        </a>

        <form class="user" action="{{ route('shop.apply') }}" method="post" enctype="multipart/form-data">
            @csrf

            <div class="card">
                <div class="text-center mt-5">
                    <div class="mb-5">
                        <h1 class="display-4">{{ \App\CPU\translate('sign_up') }}</h1>
                        <center>
                            <h1 class="h4 text-gray-900 mb-4">
                                {{ \App\CPU\translate('welcome_to_seller_Registration') }}</h1>
                        </center>
                    </div>

                </div>
                <div class="card-body">
                    {{-- <input type="hidden" name="status" value="approved"> --}}
                    <div class="row align-items-center">
                        <div class="col-lg-6 mb-4 mb-lg-0">
                            <div class="form-group">
                                <label for="exampleFirstName"
                                    class="title-color d-flex gap-1 align-items-center">{{ \App\CPU\translate('first_name') }}</label>
                                <input type="text" class="form-control form-control-user" id="exampleFirstName"
                                    name="f_name" value="{{ old('f_name') }}"
                                    placeholder="{{ \App\CPU\translate('Ex') }}: John" required>
                            </div>
                            <div class="form-group">
                                <label for="exampleLastName"
                                    class="title-color d-flex gap-1 align-items-center">{{ \App\CPU\translate('last_name') }}</label>
                                <input type="text" class="form-control form-control-user" id="exampleLastName"
                                    name="l_name" value="{{ old('l_name') }}"
                                    placeholder="{{ \App\CPU\translate('Ex') }}: Doe" required>
                            </div>
                            <div class="form-group">
                                <label for="exampleInputPhone"
                                    class="title-color d-flex gap-1 align-items-center">{{ \App\CPU\translate('phone') }}</label>
                                <input type="text" class="form-control form-control-user" id="exampleInputPhone"
                                    name="phone" value="{{ old('phone') }}"
                                    placeholder="{{ \App\CPU\translate('Ex') }}: +09587498"
                                    oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength); this.setCustomValidity(this.value.length < 10 ? 'Minimum length is 10 digits.' : ''); this.value = this.value.replace(/[^0-9]/g, '');"
                                    maxlength="10" required>
                            </div>

                            {{-- <div class="form-group">
                                <label for="exampleInputtype"
                                    class="title-color">{{ \App\CPU\translate('Seller_type') }}</label>
                                <select name="type" class="form-control" id="type" required>
                                    <option value="" disabled selected>
                                        {{ \App\CPU\translate('Select Seller Type') }}</option>
                                    <option value="goods" {{ old('type') == 'goods' ? 'selected' : '' }}>
                                        {{ \App\CPU\translate('Goods Provider') }}</option>
                                    <option value="service" {{ old('type') == 'service' ? 'selected' : '' }}>
                                        {{ \App\CPU\translate('Service Provider') }}</option>
                                    <option value="both" {{ old('type') == 'both' ? 'selected' : '' }}>
                                        {{ \App\CPU\translate('Both Provider') }}</option>
                                </select>
                            </div> --}}
                            <input type="hidden" name="type" value="goods">
                            <div class="form-group d-none">
                                <label for="exampleInputzipcode"
                                    class="title-color d-flex gap-1 align-items-center">{{ \App\CPU\translate('zipcode') }}</label>
                                <select class="form-control" name="zipcode">
                                    <option value="0" selected disabled>---{{ \App\CPU\translate('select') }}---
                                    </option>
                                    @if (isset($zipcode))
                                        @foreach ($zipcode as $key => $value)
                                            <option value="{{ $value->zipcode }}">{{ $value->zipcode }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group">
                                <center>
                                    <img class="upload-img-view" id="viewer"
                                        src="{{ asset('public\assets\back-end\img\400x400\img2.jpg') }}"
                                        alt="banner image" />
                                </center>
                            </div>

                            <div class="form-group">
                                <div class="title-color mb-2 d-flex gap-1 align-items-center">
                                    {{ \App\CPU\translate('Seller_Image') }} <span
                                        class="text-info">({{ \App\CPU\translate('ratio') }}
                                        {{ \App\CPU\translate('1') }}:{{ \App\CPU\translate('1') }})</span></div>
                                <div class="custom-file text-left">
                                    <input type="file" name="image" id="customFileUpload"
                                        class="custom-file-input selller-img"
                                        accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                                    <label class="custom-file-label"
                                        for="customFileUpload">{{ \App\CPU\translate('Upload') }}
                                        {{ \App\CPU\translate('image') }}</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-body">
                    {{-- <input type="hidden" name="status" value="approved"> --}}
                    <h5 class="mb-0 text-capitalize d-flex align-items-center gap-2 border-bottom pb-3 mb-4 pl-4">
                        <img src="{{ asset('/public/assets/back-end/img/seller-information.png') }}" class="mb-1"
                            alt="">
                        {{ \App\CPU\translate('account_information') }}
                    </h5>
                    <div class="row">

                        <div class="d-none col-md-4">
                            <div class="form-group">
                                <label for="dob"
                                    class="title-color">{{ \App\CPU\translate('Date of Birth') }}</label>
                                <input type="date" name="dob" value="{{ old('dob') }}"
                                    class="form-control" id="dob">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="name"
                                    class="title-color">{{ \App\CPU\translate('Pan Card No') }}</label>
                                <input type="text" name="pan_card" value="{{ old('pan_card') }}"
                                    class="form-control" id="pan_card" maxlength="10"
                                    placeholder="{{ \App\CPU\translate('Ex') }} : GGYFR1025G">
                            </div>
                        </div>
                        <div class="d-none col-md-4">
                            <div class="form-group">
                                <label for="name" class="title-color">{{ \App\CPU\translate('Address') }}</label>
                                <input type="text" name="address" value="{{ old('address') }}"
                                    class="form-control" id="address"
                                    placeholder="{{ \App\CPU\translate('Ex') }} : 123, Green Park Avenue, Near City Center Mall, Sector 12, Gurugram, Haryana, 122018, India">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="name" class="title-color">{{ \App\CPU\translate('Gst No.') }}</label>
                                <input type="text" name="gst" value="{{ old('gst') }}"
                                    class="form-control" id="gst"
                                    placeholder="{{ \App\CPU\translate('Ex') }} : 27ABCDE1234F1Z5" maxlength="16">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="name"
                                    class="title-color">{{ \App\CPU\translate('Aadhaar No.') }}</label>
                                <input type="text" name="aadhar" value="{{ old('aadhar') }}"
                                    class="form-control" id="aadhar"
                                    placeholder="{{ \App\CPU\translate('Ex') }} : 1234 5678 9012" minlength="12"
                                    maxlength="12">
                            </div>
                        </div>
                        <div class="d-none col-md-4">
                            <div class="form-group">
                                <label for="gender" class="title-color">{{ \App\CPU\translate('Gender') }}</label>
                                <select name="gender" class="form-control" id="gender">
                                    <option value="" disabled selected>
                                        {{ \App\CPU\translate('Select Gender') }}</option>
                                    <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>
                                        {{ \App\CPU\translate('Male') }}</option>
                                    <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>
                                        {{ \App\CPU\translate('Female') }}</option>
                                    <option value="other" {{ old('gender') == 'other' ? 'selected' : '' }}>
                                        {{ \App\CPU\translate('Other') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-4 form-group">
                            <label for="exampleInputEmail"
                                class="title-color d-flex gap-1 align-items-center">{{ \App\CPU\translate('email') }}</label>
                            <input type="email" class="form-control form-control-user" id="exampleInputEmail"
                                name="email" value="{{ old('email') }}"
                                placeholder="{{ \App\CPU\translate('Ex') }}: Jhone@company.com" required>
                        </div>
                        <div class="col-lg-4 form-group">
                            <label for="exampleInputPassword"
                                class="title-color d-flex gap-1 align-items-center">{{ \App\CPU\translate('password') }}</label>
                            <input type="password" class="form-control form-control-user" minlength="8"
                                id="exampleInputPassword" name="password"
                                placeholder="{{ \App\CPU\translate('Ex: 8+ Character') }}" required>
                        </div>
                        <div class="col-lg-4 form-group">
                            <label for="exampleRepeatPassword"
                                class="title-color d-flex gap-1 align-items-center">{{ \App\CPU\translate('confirm_password') }}</label>
                            <input type="password" class="form-control form-control-user" minlength="8"
                                id="exampleRepeatPassword" placeholder="{{ \App\CPU\translate('Ex: 8+ Character') }}"
                                required>
                            <div class="pass invalid-feedback">{{ \App\CPU\translate('Repeat') }}
                                {{ \App\CPU\translate('password') }} {{ \App\CPU\translate('not match') }} .</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-3 mb-5">
                <div class="card-body">
                    <h5 class="mb-0 text-capitalize d-flex align-items-center gap-2 border-bottom pb-3 mb-4 pl-4">
                        <img src="{{ asset('/public/assets/back-end/img/seller-information.png') }}" class="mb-1"
                            alt="">
                        {{ \App\CPU\translate('Shop_information') }}
                    </h5>

                    <div class="row">
                        <div class="col-lg-6 form-group">
                            <label for="shop_name"
                                class="title-color d-flex gap-1 align-items-center">{{ \App\CPU\translate('Shop_name') }}</label>
                            <input type="text" class="form-control form-control-user" id="shop_name"
                                name="shop_name" placeholder="{{ \App\CPU\translate('Ex') }}: Jhon"
                                value="{{ old('shop_name') }}"required>
                        </div>
                        <div class="col-lg-6 form-group">
                            <label for="shop_address"
                                class="title-color d-flex gap-1 align-items-center">{{ \App\CPU\translate('Shop_address') }}</label>
                            <textarea name="shop_address" class="form-control" id="shop_address"rows="1" required
                                placeholder="{{ \App\CPU\translate('Ex') }}: Doe">{{ old('shop_address') }}</textarea>

                        </div>
                        <div class="shopAddressDiv col-md-12">
                            <!-- <div class="addressInputFeald"> -->
                            <!-- <label for="lat">Latitude</label> -->
                            <input type="hidden" id="lat" name="latitude" placeholder="Google Address"
                                value="">
                            <!-- </div> -->
                            <!-- <div class="addressInputFeald"> -->
                            <!-- <label for="lang">Language</label> -->
                            <input type="hidden" id="lang" name="longitude" placeholder="Google Address"
                                value="">
                            <!-- </div> -->
                            <div class="addressInputFeald col-md-6">
                                <label for="area">Area</label>
                                <input type="text" id="area" name="area" placeholder="Google Address"
                                    value="" required>
                            </div>
                            <div class="addressInputFeald col-md-6">
                                <label for="city">City</label>
                                <input type="text" id="city" name="city" placeholder="Google Address"
                                    value="" required>
                            </div>
                            <div class="addressInputFeald col-md-6">
                                <label for="state">State</label>
                                <input type="text" id="state" name="state" placeholder="Google Address"
                                    value="" required>
                            </div>
                            <div class="addressInputFeald col-md-6">
                                <label for="pincode">Pincode</label>
                                <input type="text" id="pincode" name="zipcode" placeholder="Google Address"
                                    value="" required>
                            </div>
                        </div>
                        <div class="col-lg-6 form-group">
                            <center>
                                <img class="upload-img-view" id="viewerLogo"
                                    src="{{ asset('public\assets\back-end\img\400x400\img2.jpg') }}"
                                    alt="banner image" />
                            </center>

                            <div class="mt-4">
                                <div class="d-flex gap-1 align-items-center title-color mb-2">
                                    {{ \App\CPU\translate('Shop_logo') }}
                                    <span class="text-info">({{ \App\CPU\translate('ratio') }}
                                        {{ \App\CPU\translate('1') }}:{{ \App\CPU\translate('1') }})</span>
                                </div>

                                <div class="custom-file">
                                    <input type="file" name="logo" id="LogoUpload" class="custom-file-input"
                                        accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                                    <label class="custom-file-label"
                                        for="LogoUpload">{{ \App\CPU\translate('Upload') }}
                                        {{ \App\CPU\translate('logo') }}</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 form-group">
                            <center>
                                <img class="upload-img-view upload-img-view__banner" id="viewerBanner"
                                    src="{{ asset('public\assets\back-end\img\400x400\img2.jpg') }}"
                                    alt="banner image" />
                            </center>

                            <div class="mt-4">
                                <div class="d-flex gap-1 align-items-center title-color mb-2">
                                    {{ \App\CPU\translate('Shop_banner') }}
                                    <span class="text-info">({{ \App\CPU\translate('ratio') }}
                                        {{ \App\CPU\translate('6') }}:{{ \App\CPU\translate('1') }})</span>
                                </div>

                                <div class="custom-file">
                                    <input type="file" name="banner" id="BannerUpload" class="custom-file-input"
                                        accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                                    <label class="custom-file-label"
                                        for="BannerUpload">{{ \App\CPU\translate('Upload') }}
                                        {{ \App\CPU\translate('Banner') }}</label>
                                </div>
                            </div>
                        </div>
                        {{-- aadhar img from here --}}
                        <div class="col-lg-6 form-group">
                            <center>
                                <img class="upload-img-view upload-img-view__aadhar" id="viewerAadharFront"
                                    src="{{ asset('public/assets/back-end/img/400x400/img2.jpg') }}"
                                    alt="Aadhar front image" />
                            </center>

                            <div class="mt-4">
                                <div class="d-flex gap-1 align-items-center title-color mb-2">
                                    {{ \App\CPU\translate('Aadhar_front_img') }}
                                    <span class="text-info">({{ \App\CPU\translate('ratio') }}
                                        {{ \App\CPU\translate('1') }}:{{ \App\CPU\translate('1') }})</span>
                                </div>

                                <div class="custom-file">
                                    <input type="file" name="aadhar_front_img" id="AadharFrontUpload"
                                        class="custom-file-input"
                                        accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                                    <label class="custom-file-label"
                                        for="AadharFrontUpload">{{ \App\CPU\translate('Upload') }}
                                        {{ \App\CPU\translate('Aadhar_front_img') }}</label>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6 form-group">
                            <center>
                                <img class="upload-img-view upload-img-view__aadhar" id="viewerAadharBack"
                                    src="{{ asset('public/assets/back-end/img/400x400/img2.jpg') }}"
                                    alt="Aadhar back image" />
                            </center>

                            <div class="mt-4">
                                <div class="d-flex gap-1 align-items-center title-color mb-2">
                                    {{ \App\CPU\translate('Aadhar_back_img') }}
                                    <span class="text-info">({{ \App\CPU\translate('ratio') }}
                                        {{ \App\CPU\translate('1') }}:{{ \App\CPU\translate('1') }})</span>
                                </div>

                                <div class="custom-file">
                                    <input type="file" name="aadhar_back_img" id="AadharBackUpload"
                                        class="custom-file-input"
                                        accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                                    <label class="custom-file-label"
                                        for="AadharBackUpload">{{ \App\CPU\translate('Upload') }}
                                        {{ \App\CPU\translate('Aadhar_back_img') }}</label>
                                </div>
                            </div>
                        </div>
                        {{-- to here --}}
                        @if (theme_root_path() == 'theme_aster')
                            <div class="col-lg-6 form-group">
                                <center>
                                    <img class="upload-img-view upload-img-view__banner" id="viewerBottomBanner"
                                        src="{{ asset('public\assets\back-end\img\400x400\img2.jpg') }}"
                                        alt="banner image" />
                                </center>

                                <div class="mt-4">
                                    <div class="d-flex gap-1 align-items-center title-color mb-2">
                                        {{ translate('shop_secondary_banner') }}
                                        <span class="text-info">({{ translate('ratio') }}
                                            {{ translate('6') }}:{{ translate('1') }})</span>
                                    </div>

                                    <div class="custom-file">
                                        <input type="file" name="bottom_banner" id="BottomBannerUpload"
                                            class="custom-file-input"
                                            accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                                        <label class="custom-file-label"
                                            for="BottomBannerUpload">{{ translate('Upload') }}
                                            {{ translate('Bottom') }}
                                            {{ translate('Banner') }}</label>
                                    </div>
                                </div>
                            </div>
                        @endif

                    </div>

                    <div class="d-flex align-items-center justify-content-end button-sec-bottom">
                        <input type="hidden" name="from_submit" value="admin">
                        <button type="reset" onclick="resetBtn()"
                            class="btn reset-btnn btn-secondary">{{ \App\CPU\translate('reset') }} </button>
                        <button type="submit" class="btn btn--primary submit-btnn btn-user "
                            id="apply">{{ \App\CPU\translate('submit') }}</button>
                    </div>
                    <div class="card-footer">
                        <div class="row">
                            <div class="col-12 d-flex flex-wrap justify-content-around justify-content-md-between align-items-center __gap-15"
                                style="direction: {{ Session::get('direction') }}">
                                <div class="{{ Session::get('direction') === 'rtl' ? '' : 'ml-2' }}">
                                    <h6 class="m-0">{{ \App\CPU\translate('already_have_an_account?_login.') }}
                                    </h6>
                                </div>
                                <div class="{{ Session::get('direction') === 'rtl' ? 'ml-2' : '' }}">
                                    <a class="btn btn-outline-primary" href="{{ route('seller.auth.login') }}">
                                        <i class="fa fa-user-circle"></i> {{ \App\CPU\translate('sign_in') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        function resetBtn() {
            let placeholderImg = $("#placeholderImg").data('img');
            $('#viewer').attr('src', placeholderImg);
            $('#viewerBanner').attr('src', placeholderImg);
            $('#viewerBottomBanner').attr('src', placeholderImg);
            $('#viewerLogo').attr('src', placeholderImg);
            $('.spartan_remove_row').click();
        }

        function openInfoWeb() {
            var x = document.getElementById("website_info");
            if (x.style.display === "none") {
                x.style.display = "block";
            } else {
                x.style.display = "none";
            }
        }
    </script>
    @push('script')
        <script src="{{ asset('public/assets/back-end') }}/js/vendor.min.js"></script>
        <script src="{{ asset('public/assets/back-end') }}/js/theme.min.js"></script>
        <script src="{{ asset('public/assets/back-end') }}/js/sweet_alert.js"></script>
        <script src="{{ asset('public/assets/back-end') }}/js/toastr.js"></script>
        {!! Toastr::message() !!}

        <script>
            toastr.options = {
                "closeButton": false,
                "debug": false,
                "newestOnTop": false,
                "progressBar": false,
                "positionClass": "toast-bottom-left",
                "preventDuplicates": false,
                "onclick": null,
                "showDuration": "300",
                "hideDuration": "1000",
                "timeOut": "5000",
                "extendedTimeOut": "1000",
                "showEasing": "swing",
                "hideEasing": "linear",
                "showMethod": "fadeIn",
                "hideMethod": "fadeOut"
            }
        </script>
        @if ($errors->any())
            <script>
                @foreach ($errors->all() as $error)
                    toastr.error('{{ $error }}', Error, {
                        CloseButton: true,
                        ProgressBar: true
                    });
                @endforeach
            </script>
        @endif
        <script>
            $('#inputCheckd').change(function() {
                // console.log('jell');
                if ($(this).is(':checked')) {
                    $('#apply').removeAttr('disabled');
                } else {
                    $('#apply').attr('disabled', 'disabled');
                }

            });

            $('#exampleInputPassword ,#exampleRepeatPassword').on('keyup', function() {
                var pass = $("#exampleInputPassword").val();
                var passRepeat = $("#exampleRepeatPassword").val();
                if (pass == passRepeat) {
                    $('.pass').hide();
                } else {
                    $('.pass').show();
                }
            });
            $('#apply').on('click', function() {

                var image = $("#image-set").val();
                if (image == "") {
                    $('.image').show();
                    return false;
                }
                var pass = $("#exampleInputPassword").val();
                var passRepeat = $("#exampleRepeatPassword").val();
                if (pass != passRepeat) {
                    $('.pass').show();
                    setTimeout(function() {
                        swal({
                            title: 'Password do not match',
                            type: 'warning',
                            showCancelButton: false,
                            confirmButtonText: 'ok',
                            reverseButtons: true,
                        });
                    }, 500);
                    return false;
                }


            });

            function Validate(file) {
                var x;
                var le = file.length;
                var poin = file.lastIndexOf(".");
                var accu1 = file.substring(poin, le);
                var accu = accu1.toLowerCase();
                if ((accu != '.png') && (accu != '.jpg') && (accu != '.jpeg')) {
                    x = 1;
                    return x;
                } else {
                    x = 0;
                    return x;
                }
            }

            function readURL(input) {
                if (input.files && input.files[0]) {
                    var reader = new FileReader();

                    reader.onload = function(e) {
                        $('#viewer').attr('src', e.target.result);
                    }

                    reader.readAsDataURL(input.files[0]);
                }
            }

            $("#customFileUpload").change(function() {
                readURL(this);
            });

            function readlogoURL(input) {
                if (input.files && input.files[0]) {
                    var reader = new FileReader();

                    reader.onload = function(e) {
                        $('#viewerLogo').attr('src', e.target.result);
                    }

                    reader.readAsDataURL(input.files[0]);
                }
            }

            function readBannerURL(input) {
                if (input.files && input.files[0]) {
                    var reader = new FileReader();

                    reader.onload = function(e) {
                        $('#viewerBanner').attr('src', e.target.result);
                    }

                    reader.readAsDataURL(input.files[0]);
                }
            }

            function readBottomBannerURL(input) {
                if (input.files && input.files[0]) {
                    var reader = new FileReader();

                    reader.onload = function(e) {
                        $('#viewerBottomBanner').attr('src', e.target.result);
                    }

                    reader.readAsDataURL(input.files[0]);
                }
            }

            $("#LogoUpload").change(function() {
                readlogoURL(this);
            });
            $("#BannerUpload").change(function() {
                readBannerURL(this);
            });
            $("#BottomBannerUpload").change(function() {
                readBottomBannerURL(this);
            });
        </script>
        <script>
            function readAadharFrontURL(input) {
                if (input.files && input.files[0]) {
                    var reader = new FileReader();

                    reader.onload = function(e) {
                        $('#viewerAadharFront').attr('src', e.target.result);
                    }

                    reader.readAsDataURL(input.files[0]);
                }
            }

            function readAadharBackURL(input) {
                if (input.files && input.files[0]) {
                    var reader = new FileReader();

                    reader.onload = function(e) {
                        $('#viewerAadharBack').attr('src', e.target.result);
                    }

                    reader.readAsDataURL(input.files[0]);
                }
            }

            $("#AadharFrontUpload").change(function() {
                readAadharFrontURL(this);
            });

            $("#AadharBackUpload").change(function() {
                readAadharBackURL(this);
            });
        </script>
        <script>
            $(document).ready(function() {
                $('select').select2({
                    placeholder: '---{{ \App\CPU\translate('select') }}---',
                    allowClear: true
                });
            });
        </script>

        <script src="https://maps.google.com/maps/api/js?libraries=places&key=AIzaSyDp5WRm4NU2C0C6NeNkBY1uOUnpGl6ChKY"></script>
        <script type="text/javascript">
            google.maps.event.addDomListener(window, 'load', function() {
                // Initialize the Autocomplete instance
                var autocomplete = new google.maps.places.Autocomplete(document.getElementById('shop_address'));

                google.maps.event.addListener(autocomplete, 'place_changed', function() {
                    var place = autocomplete.getPlace();
                    var address = place.formatted_address;
                    var latitude = place.geometry.location.lat();
                    var longitude = place.geometry.location.lng();

                    var area = "";
                    var city = "";
                    var state = "";
                    var pincode = "";

                    place.address_components.forEach(function(component) {
                        var types = component.types;

                        // Extract area (usually neighborhood or sublocality)
                        if (types.includes("sublocality") || types.includes("neighborhood")) {
                            area = component.long_name;
                        }

                        // Extract city (usually locality)
                        if (types.includes("locality")) {
                            city = component.long_name;
                        }

                        // Extract state (administrative area level 1)
                        if (types.includes("administrative_area_level_1")) {
                            state = component.long_name;
                        }

                        // Extract postal code
                        if (types.includes("postal_code")) {
                            pincode = component.long_name;
                        }
                    });

                    // Set extracted values in input fields
                    document.getElementById('shop_address').value = address;
                    document.getElementById('lat').value = latitude;
                    document.getElementById('lang').value = longitude;
                    document.getElementById('area').value = area;
                    document.getElementById('city').value = city;
                    document.getElementById('state').value = state;

                    // If you have an input for pincode, set its value
                    if (document.getElementById('pincode')) {
                        document.getElementById('pincode').value = pincode;
                    }

                    console.log("Address: ", address);
                    console.log("Latitude: ", latitude);
                    console.log("Longitude: ", longitude);
                    console.log("Area: ", area);
                    console.log("City: ", city);
                    console.log("State: ", state);
                    console.log("Pincode: ", pincode);
                });
            });
        </script>

    </body>

    </html>
