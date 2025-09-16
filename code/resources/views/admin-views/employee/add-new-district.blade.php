@extends('layouts.back-end.app')
@section('title', \App\CPU\translate('Aggregator Add'))
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
                {{ \App\CPU\translate('Add_New_Aggregator') }}
            </h2>
        </div>
        <!-- End Page Title -->

        <!-- Content Row -->
        <div class="row">
            <div class="col-md-12">
                <form action="{{ route('admin.employee.add-new-district') }}" method="post" enctype="multipart/form-data"
                    style="text-align: {{ Session::get('direction') === 'rtl' ? 'right' : 'left' }};">
                    @csrf
                    <div class="card">
                        <div class="card-body">
                            <h5 class="mb-0 page-header-title d-flex align-items-center gap-2 border-bottom pb-3 mb-3">
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
                                            value="{{ old('name') }}" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="name" class="title-color">{{ \App\CPU\translate('Phone') }}</label>
                                        <input type="number" name="phone" value="{{ old('phone') }}"
                                            class="form-control" id="phone"
                                            placeholder="{{ \App\CPU\translate('Ex') }} : +88017********"
                                            oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength); this.setCustomValidity(this.value.length < 10 ? 'Minimum length is 10 digits.' : ''); this.value = this.value.replace(/[^0-9]/g, '');"
                                            maxlength="10" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="name" class="title-color">{{ \App\CPU\translate('Role') }}</label>
                                        <select class="form-control" name="role_id">
                                            <option value="0" selected disabled>
                                                ---{{ \App\CPU\translate('select') }}---
                                            </option>
                                            @foreach ($rls as $r)
                                                <option value="{{ $r->id }}"
                                                    {{ 3 == $r->id ? 'selected' : '' }}>{{ $r->name }}
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
                                                    <option value="{{ $value->id }}">{{ $value->city }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    <div class="form-group ">
                                        <label for="exampleInputzipcode"
                                            class="title-color d-flex gap-1 align-items-center">{{ \App\CPU\translate('zipcode') }}</label>
                                        <select class="form-control" name="zipcode">
                                            <option value="0" selected disabled>
                                                ---{{ \App\CPU\translate('select') }}---</option>
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
                                        <label for="name"
                                            class="title-color">{{ \App\CPU\translate('admin_image') }}</label>
                                        <span class="text-info">( {{ \App\CPU\translate('ratio') }} 1:1 )</span>
                                        <div class="form-group">
                                            <div class="custom-file text-left">
                                                <input type="file" name="image" id="customFileUpload"
                                                    class="custom-file-input"
                                                    accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*" required>
                                                <label class="custom-file-label"
                                                    for="customFileUpload">{{ \App\CPU\translate('choose') }}
                                                    {{ \App\CPU\translate('file') }}</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-center">
                                        <img class="upload-img-view" id="viewer"
                                            src="{{ asset('public\assets\back-end\img\400x400\img2.jpg') }}"
                                            alt="Product thumbnail" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card mt-3">
                        <div class="card-body">
                            <h5 class="mb-0 page-header-title d-flex align-items-center gap-2 border-bottom pb-3 mb-3">
                                <i class="tio-user"></i>
                                {{ \App\CPU\translate('General_Information') }}
                            </h5>
                            <div class="row">
                                <div class="col-md-4 d-none">
                                    <div class="form-group">
                                        <label for="dob"
                                            class="title-color">{{ \App\CPU\translate('Date of Birth') }}</label>
                                        <input type="date" name="dob" value="{{ old('dob') }}"
                                            class="form-control" id="dob" >
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name"
                                            class="title-color">{{ \App\CPU\translate('Pan Card No') }}</label>
                                        <input type="text" name="pan_card" value="{{ old('pan_card') }}"
                                            class="form-control" id="pan_card"
                                            placeholder="{{ \App\CPU\translate('Ex') }} : GGYFR1025G" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name"
                                            class="title-color">{{ \App\CPU\translate('Aadhar Number') }}</label>
                                        <input type="text" name="aadhar_number" value="{{ old('aadhar_number') }}"
                                            class="form-control" id="aadhar_number"
                                            placeholder="{{ \App\CPU\translate('Ex') }} : 1234567891011" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name"
                                        class="title-color">{{ \App\CPU\translate('Gst No.') }}</label>
                                        <input type="text" name="gst" value="{{ old('gst') }}"
                                        class="form-control" id="gst"
                                        placeholder="{{ \App\CPU\translate('Ex') }} : 27ABCDE1234F1Z5" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name"
                                            class="title-color">{{ \App\CPU\translate('Address') }}</label>
                                        <input type="text" name="address" value="{{ old('address') }}"
                                            class="form-control" id="address"
                                            placeholder="{{ \App\CPU\translate('Ex') }} : 123, Green Park Avenue, Near City Center Mall, Sector 12, Gurugram, Haryana, 122018, India"
                                            required>
                                    </div>
                                </div>
                                <div class="col-md-4 d-none">
                                    <div class="form-group">
                                        <label for="gender"
                                            class="title-color">{{ \App\CPU\translate('Gender') }}</label>
                                        <select name="gender" class="form-control" id="gender" >
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
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="name"
                                            class="title-color">{{ \App\CPU\translate('Email') }}</label>
                                        <input type="email" name="email" value="{{ old('email') }}"
                                            class="form-control" id="email"
                                            placeholder="{{ \App\CPU\translate('Ex') }} : ex@gmail.com" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="password"
                                            class="title-color">{{ \App\CPU\translate('password') }}</label>
                                        <input type="text" name="password" class="form-control" id="password"
                                            placeholder="{{ \App\CPU\translate('Password') }}" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="confirm_password"
                                            class="title-color">{{ \App\CPU\translate('confirm_password') }}</label>
                                        <input type="text" name="confirm_password" class="form-control"
                                            id="confirm_password"
                                            placeholder="{{ \App\CPU\translate('Confirm Password') }}" required>
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="name"
                                            class="title-color">{{ \App\CPU\translate('bank_statement') }}</label>
                                        <span class="text-info">( {{ \App\CPU\translate('Pdf of Bank Statement') }} )</span>
                                        <div class="form-group">
                                            <div class="custom-file text-left">
                                                <input type="file" name="bank_statement" required>
                                                <!-- <label class="custom-file-label"
                                                    for="customFileUpload">{{ \App\CPU\translate('choose') }}
                                                    {{ \App\CPU\translate('file') }}</label> -->
                                            </div>
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-3">
                                <button type="reset" id="reset"
                                    class="btn btn-secondary px-4">{{ \App\CPU\translate('reset') }}</button>
                                <button type="submit"
                                    class="btn btn--primary px-4">{{ \App\CPU\translate('submit') }}</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
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

    <script src="{{ asset('public/assets/back-end/js/spartan-multi-image-picker.js') }}"></script>
    <script type="text/javascript">
        $(function() {
            $("#coba").spartanMultiImagePicker({
                fieldName: 'identity_image[]',
                maxCount: 5,
                rowHeight: 'auto',
                groupClassName: 'col-6 col-lg-4',
                maxFileSize: '',
                placeholderImage: {
                    image: '{{ asset('public/assets/back-end/img/400x400/img2.jpg') }}',
                    width: '100%'
                },
                dropFileLabel: "Drop Here",
                onAddRow: function(index, file) {

                },
                onRenderedPreview: function(index) {

                },
                onRemoveRow: function(index) {

                },
                onExtensionErr: function(index, file) {
                    toastr.error('Please only input png or jpg type file', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                },
                onSizeErr: function(index, file) {
                    toastr.error('File size too big', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                }
            });
        });
    </script>
@endpush
