@extends('layouts.back-end.app')
@section('title', \App\CPU\translate('Doctor Edit'))
@push('css_or_js')
    <link href="{{ asset('public/assets/back-end') }}/css/select2.min.css" rel="stylesheet" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Title -->
        <div class="mb-3">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                <img src="{{ asset('/public/assets/back-end/img/add-new-employee.png') }}" alt="">
                {{ \App\CPU\translate('Doctor_Update') }}
            </h2>
        </div>
        <!-- End Page Title -->

        <!-- Content Row -->
        <div class="row">
            <div class="col-md-12">
                <div class="card-custom">
                    <div class="card-header">
                        <h5 class="mb-0">{{ \App\CPU\translate('Doctor') }} {{ \App\CPU\translate('Update') }}
                            {{ \App\CPU\translate('form') }}</h5>
                    </div>

                    <form action="{{ route('admin.employee.update-doctor', [$e['id']]) }}" method="post" enctype="multipart/form-data"
                        style="text-align: {{ Session::get('direction') === 'rtl' ? 'right' : 'left' }};">
                        @csrf
                        <div class="form-group">
                            <div class="card">
                                <div class="card-body">
                                    <h5
                                        class="mb-0 page-header-title d-flex align-items-center gap-2 border-bottom pb-3 mb-3">
                                        <i class="tio-user"></i>
                                        {{ \App\CPU\translate('General_Information') }}
                                    </h5>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="name"
                                                    class="title-color">{{ \App\CPU\translate('Full Name') }}</label>
                                                <input type="text" name="name" class="form-control" id="name"
                                                    placeholder="{{ \App\CPU\translate('Ex') }} : Jhon Doe"
                                                    value="{{ $e['name'] }}" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="name"
                                                    class="title-color">{{ \App\CPU\translate('Phone') }}</label>
                                                <input type="number" name="phone" value="{{ $e['phone'] }}"
                                                    class="form-control" id="phone"
                                                    placeholder="{{ \App\CPU\translate('Ex') }} : +88017********"
                                                    oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength); this.setCustomValidity(this.value.length < 10 ? 'Minimum length is 10 digits.' : ''); this.value = this.value.replace(/[^0-9]/g, '');"
                                                    maxlength="10" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="name"
                                                    class="title-color">{{ \App\CPU\translate('Role') }}</label>
                                                <select class="form-control" name="role_id">
                                                    <option value="0" selected disabled>
                                                        ---{{ \App\CPU\translate('select') }}---
                                                    </option>
                                                    @foreach ($rls as $r)
                                                        <option value="{{ $r->id }}"
                                                            {{ $r['id'] == $e['admin_role_id'] ? 'selected' : '' }}>
                                                            {{ $r->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="form-group d-none">
                                                <label for="exampleInputzipcode"
                                                    class="title-color d-flex gap-1 align-items-center">{{ \App\CPU\translate('City') }}</label>
                                                <select class="form-control" name="city_id">
                                                    <option value="0" selected disabled>
                                                        ---{{ \App\CPU\translate('select') }}---
                                                    </option>
                                                    @if (isset($city))
                                                        @foreach ($city as $key => $value)
                                                            <option value="{{ $value->id }}">{{ $value->city }}
                                                            </option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label for="name"
                                                    class="title-color">{{ \App\CPU\translate('admin_image') }}</label>
                                                <span class="text-info">( {{ \App\CPU\translate('ratio') }} 1:1
                                                    )</span>
                                                <div class="form-group">
                                                    <div class="custom-file text-left">
                                                        <input type="file" name="image" id="customFileUpload"
                                                            class="custom-file-input"
                                                            accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*"
                                                            >
                                                        <label class="custom-file-label"
                                                            for="customFileUpload">{{ \App\CPU\translate('choose') }}
                                                            {{ \App\CPU\translate('file') }}</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="text-center">
                                                {{-- <img class="upload-img-view" id="viewer"
                                                        src="{{ asset('public\assets\back-end\img\400x400\img2.jpg') }}"
                                                        alt="Product thumbnail" /> --}}
                                                <img class="upload-img-view" id="viewer"
                                                    onerror="this.src='{{ asset('public/assets/front-end/img/image-place-holder.png') }}'"
                                                    src="{{ asset('storage/app/public/admin') }}/{{ $e['image'] }}"
                                                    alt="Doctor thumbnail" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card mt-3">
                                <div class="card-body">
                                    <h5
                                        class="mb-0 page-header-title d-flex align-items-center gap-2 border-bottom pb-3 mb-3">
                                        <i class="tio-user"></i>
                                        {{ \App\CPU\translate('General_Information') }}
                                    </h5>
                                    <div class="row">
                                        <div class="col-md-4 d-none">
                                            <div class="form-group">
                                                <label for="dob"
                                                    class="title-color">{{ \App\CPU\translate('Date of Birth') }}</label>
                                                <input type="date" name="dob" value="{{ $e['dob'] }}"
                                                    class="form-control" id="dob">
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="gender"
                                                    class="title-color">{{ \App\CPU\translate('Gender') }}</label>
                                                <select name="gender" class="form-control" id="gender" required>
                                                    <option value="" disabled selected>
                                                        {{ \App\CPU\translate('Select Gender') }}</option>
                                                    <option value="male" {{ $e['gender'] == 'male' ? 'selected' : '' }}>
                                                        {{ \App\CPU\translate('Male') }}</option>
                                                    <option value="female"
                                                        {{ $e['gender'] == 'female' ? 'selected' : '' }}>
                                                        {{ \App\CPU\translate('Female') }}</option>
                                                    <option value="other"
                                                        {{ $e['gender'] == 'other' ? 'selected' : '' }}>
                                                        {{ \App\CPU\translate('Other') }}</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="name"
                                                    class="title-color">{{ \App\CPU\translate('Pan Card No') }}</label>
                                                <input type="text" name="pan_card" value="{{ $e['pan_card'] }}"
                                                    class="form-control" id="pan_card"
                                                    placeholder="{{ \App\CPU\translate('Ex') }} : GGYFR1025G" required>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="name"
                                                    class="title-color">{{ \App\CPU\translate('Address') }}</label>
                                                <input type="text" name="address" value="{{ $e['address'] }}"
                                                    class="form-control" id="address"
                                                    placeholder="{{ \App\CPU\translate('Ex') }} : 123, Green Park Avenue, Near City Center Mall, Sector 12, Gurugram, Haryana, 122018, India"
                                                    required>
                                            </div>
                                        </div>
                                        <div class="col-md-4 d-none">
                                            <div class="form-group">
                                                <label for="name"
                                                    class="title-color">{{ \App\CPU\translate('Gst No.') }}</label>
                                                <input type="text" name="gst" value="{{ $e['gst'] }}"
                                                    class="form-control" id="gst"
                                                    placeholder="{{ \App\CPU\translate('Ex') }} : 27ABCDE1234F1Z5"
                                                    >
                                            </div>
                                        </div>
                                      
                                        
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="name"
                                                    class="title-color">{{ \App\CPU\translate('Email') }}</label>
                                                <input type="email" name="email" value="{{ $e['email'] }}"
                                                    class="form-control" id="email"
                                                    placeholder="{{ \App\CPU\translate('Ex') }} : ex@gmail.com" required>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="password"
                                                    class="title-color">{{ \App\CPU\translate('password') }}</label>
                                                <input type="text" name="password" class="form-control"
                                                    id="password" placeholder="{{ \App\CPU\translate('Password') }}"
                                                    >
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="name"
                                                    class="title-color">{{ \App\CPU\translate('Medical Registration / License Number') }}</label>
                                                <input type="text" name="license_number"
                                                    value="{{ $e['license_number'] }}" class="form-control"
                                                    id="license_number"
                                                    placeholder="{{ \App\CPU\translate('Ex') }} : 25MB45784578" required>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="name"
                                                    class="title-color">{{ \App\CPU\translate('Medical Council') }}</label>
                                                <input type="text" name="medical_council" value="{{ $e['medical_council'] }}"
                                                    class="form-control" id="medical_council"
                                                    placeholder="Medical Council Name" required>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="name"
                                                    class="title-color">{{ \App\CPU\translate('Qualification') }}</label>
                                                <input type="text" name="qualification"
                                                    value="{{ $e['qualification'] }}" class="form-control"
                                                    id="qualification"
                                                    placeholder="{{ \App\CPU\translate('Ex') }} : MBBS" required>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="name"
                                                    class="title-color">{{ \App\CPU\translate('passing_year') }}</label>
                                                <input type="text" name="passing_year"
                                                    value="{{ $e['passing_year'] }}" class="form-control"
                                                    id="passing_year"
                                                    placeholder="{{ \App\CPU\translate('Ex') }} : 2001,2002" required>
                                            </div>
                                        </div>

                                        <div class="col-lg-6 form-group">

                                            <div class="mt-0">
                                                <div class="d-flex gap-1 align-items-center title-color mb-2">
                                                    {{ \App\CPU\translate('Certificate Image') }}
                                                    <span class="text-info">({{ \App\CPU\translate('ratio') }}
                                                        {{ \App\CPU\translate('1') }}:{{ \App\CPU\translate('1') }})</span>
                                                </div>

                                                <div class="custom-file">
                                                    <input type="file" name="certificate_image" id="LogoUpload"
                                                        class="custom-file-input"
                                                        accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                                                    <label class="custom-file-label"
                                                        for="LogoUpload">{{ \App\CPU\translate('Upload') }}
                                                        {{ \App\CPU\translate('certificate_img') }}</label>
                                                </div>
                                            </div>
                                            <center style="margin-top: 15px;">
                                                {{-- <img class="upload-img-view" id="viewerLogo"
                                                        src="{{ asset('public\assets\back-end\img\400x400\img2.jpg') }}"
                                                        alt="banner image" /> --}}

                                                <img class="upload-img-view" id="viewer"
                                                    onerror="this.src='{{ asset('public/assets/front-end/img/image-place-holder.png') }}'"
                                                    src="{{ asset('storage/app/public/admin') }}/{{ $e['certificate_image'] }}"
                                                    alt="Doctor thumbnail" />
                                            </center>
                                        </div>
                                    </div>


                                </div>
                            </div>
                            <div class="card mt-3">
                                <div class="card-body">
                                    <h5
                                        class="mb-0 text-capitalize d-flex align-items-center gap-2 border-bottom pb-3 mb-4 pl-4">
                                        <img src="{{ asset('/public/assets/back-end/img/seller-information.png') }}"
                                            class="mb-1" alt="">
                                        {{ \App\CPU\translate('clinic_/_hospital_information') }}
                                    </h5>

                                    <div class="row">
                                        <div class="col-lg-6 form-group">
                                            <label for="clinic_name"
                                                class="title-color d-flex gap-1 align-items-center">{{ \App\CPU\translate('clinic_name / Hospital Name') }}</label>
                                            <input type="text" class="form-control form-control-user" id="clinic_name"
                                                name="clinic_name" placeholder="{{ \App\CPU\translate('Ex') }}: Jhon"
                                                value="{{ $e['clinic_name'] }}"required>
                                        </div>
                                        <div class="col-lg-6 form-group">
                                            <label for="clinic_address"
                                                class="title-color d-flex gap-1 align-items-center">{{ \App\CPU\translate('clinic_address_/_hospital_address') }}</label>
                                            <textarea name="clinic_address" class="form-control" id="clinic_address"rows="1"
                                                placeholder="{{ \App\CPU\translate('Ex') }}: Doe">{{ $e['clinic_address'] }}</textarea>
                                        </div>

                                        <div class="col-lg-4 form-group">
                                            <label for="standard_aggrement"
                                                class="title-color d-flex gap-1 align-items-center">{{ \App\CPU\translate('standard_aggrement') }}
                                                <span class="text-info">({{ \App\CPU\translate('PFD of Aggrement') }})</span>
                                            </label>
                                            <input type="file" style="display:block" name="standard_aggrement" class="form-control" id="standard_aggrement" />
                                            @if($e['standard_aggrement'])
                                                <div class="mt-2">
                                                    <label class="text-muted">{{ \App\CPU\translate('View Aggrement') }}:</label>
                                                    <a href="{{ asset('storage/app/public/admin') }}/{{ $e['standard_aggrement']}}" target="_blank" class="btn btn-link">
                                                        {{ \App\CPU\translate('View PDF') }}
                                                    </a>
                                                </div>
                                            @endif
                                        </div>
                                        
                                        <div class="col-lg-6 form-group d-none">
                                            <center>
                                                {{-- <img class="upload-img-view" id="viewerLogo"
                                                        src="{{ asset('public\assets\back-end\img\400x400\img2.jpg') }}"
                                                        alt="banner image" /> --}}
                                                <img class="upload-img-view" id="viewer"
                                                    onerror="this.src='{{ asset('public/assets/front-end/img/image-place-holder.png') }}'"
                                                    src="{{ asset('storage/app/public/admin') }}/{{ $e['clinic_logo'] }}"
                                                    alt="Doctor thumbnail" />
                                            </center>

                                            <div class="mt-4">
                                                <div class="d-flex gap-1 align-items-center title-color mb-2">
                                                    {{ \App\CPU\translate('Clinic_logo') }}
                                                    <span class="text-info">({{ \App\CPU\translate('ratio') }}
                                                        {{ \App\CPU\translate('1') }}:{{ \App\CPU\translate('1') }})</span>
                                                </div>

                                                <div class="custom-file">
                                                    <input type="file" name="clinic_logo" id="LogoUpload"
                                                        class="custom-file-input"
                                                        accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                                                    <label class="custom-file-label"
                                                        for="LogoUpload">{{ \App\CPU\translate('Upload') }}
                                                        {{ \App\CPU\translate('clinic_logo') }}</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-6 form-group d-none">
                                            <center>
                                                {{-- <img class="upload-img-view upload-img-view__banner" id="viewerBanner"
                                                        src="{{ asset('public\assets\back-end\img\400x400\img2.jpg') }}"
                                                        alt="banner image" /> --}}
                                                <img class="upload-img-view" id="viewer"
                                                    onerror="this.src='{{ asset('public/assets/front-end/img/image-place-holder.png') }}'"
                                                    src="{{ asset('storage/app/public/admin') }}/{{ $e['clinic_banner'] }}"
                                                    alt="Doctor thumbnail" />
                                            </center>

                                            <div class="mt-4">
                                                <div class="d-flex gap-1 align-items-center title-color mb-2">
                                                    {{ \App\CPU\translate('clinic_banner') }}
                                                    <span class="text-info">({{ \App\CPU\translate('ratio') }}
                                                        {{ \App\CPU\translate('6') }}:{{ \App\CPU\translate('1') }})</span>
                                                </div>

                                                <div class="custom-file">
                                                    <input type="file" name="clinic_banner" id="BannerUpload"
                                                        class="custom-file-input"
                                                        accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                                                    <label class="custom-file-label"
                                                        for="BannerUpload">{{ \App\CPU\translate('Upload') }}
                                                        {{ \App\CPU\translate('clinic_banner') }}</label>
                                                </div>
                                            </div>
                                        </div>

                                        @if (theme_root_path() == 'theme_aster')
                                            <div class="col-lg-6 form-group">
                                                <center>
                                                    <img class="upload-img-view upload-img-view__banner"
                                                        id="viewerBottomBanner"
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
                                                        <input type="file" name="bottom_banner"
                                                            id="BottomBannerUpload" class="custom-file-input"
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


                                </div>
                                <div class="d-flex justify-content-end gap-3">
                                    <button type="reset" id="reset"
                                        class="btn btn-secondary d-none px-4">{{ \App\CPU\translate('reset') }}</button>
                                    <button type="submit"
                                        class="btn btn--primary px-4">{{ \App\CPU\translate('Update') }}</button>
                                </div>
                            </div>
                        </div>

                        {{-- <div class="d-flex justify-content-end">
                            <button type="submit"
                                class="btn btn--primary px-4">{{ \App\CPU\translate('Update') }}</button>
                        </div> --}}
                    </form>

                </div>

            </div>
        </div>

        <!--modal-->
        @include('shared-partials.image-process._image-crop-modal', ['modal_id' => 'employee-image-modal'])
        <!--modal-->
    </div>
@endsection

@push('script')
    <script src="{{ asset('public/assets/back-end') }}/js/select2.min.js"></script>
    <script>
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


        $(".js-example-theme-single").select2({
            theme: "classic"
        });

        $(".js-example-responsive").select2({
            width: 'resolve'
        });
    </script>

    @include('shared-partials.image-process._script', [
        'id' => 'employee-image-modal',
        'height' => 200,
        'width' => 200,
        'multi_image' => false,
        'route' => route('image-upload'),
    ])
@endpush
