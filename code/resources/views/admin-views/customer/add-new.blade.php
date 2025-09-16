@extends('layouts.back-end.app')
@section('title', \App\CPU\translate('User Add'))
@push('css_or_js')
    <link href="{{ asset('public/assets/back-end') }}/css/select2.min.css" rel="stylesheet" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Title -->
        <div class="mb-3 customBtnDiv">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                <img src="{{ asset('/public/assets/back-end/img/add-new-employee.png') }}" alt="">
                {{ \App\CPU\translate('Add_New_User') }}
            </h2>
            <a href="{{ route('admin.customer.list') }}"><button class="btn btn--primary px-4">Back</button></a>
            <!-- customBtnDiv-->
        </div>
        <!-- End Page Title -->

        <!-- Content Row -->
        <div class="row">
            <div class="col-md-12">
                <form action="{{ route('admin.customer.store') }}" method="post" enctype="multipart/form-data"
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
                                    {{-- <div class="form-group">
                                        <label for="name"
                                            class="title-color">{{ \App\CPU\translate('Full Name') }}</label>
                                        <input type="text" name="name" class="form-control" id="name"
                                            placeholder="{{ \App\CPU\translate('Ex') }} : Jhon Doe"
                                            value="{{ old('name') }}" required>
                                    </div> --}}
                                    <div class="form-group">
                                        <label for="f_name"
                                            class="title-color">{{ \App\CPU\translate('First Name') }}</label>
                                        <input type="text" name="f_name" class="form-control" id="f_name"
                                            value="{{ old('f_name') }}" placeholder="{{ \App\CPU\translate('Ex') }} : Jhon"
                                            required>
                                    </div>
                                    <div class="form-group">
                                        <label for="l_name"
                                            class="title-color">{{ \App\CPU\translate('Last Name') }}</label>
                                        <input type="text" name="l_name" class="form-control" id="l_name"
                                            value="{{ old('l_name') }}" placeholder="{{ \App\CPU\translate('Ex') }} : Doe"
                                            required>
                                    </div>
                                    <div class="form-group">
                                        <label for="phone" class="title-color">{{ \App\CPU\translate('Phone') }}</label>
                                        <input type="number" name="phone" value="{{ old('phone') }}"
                                            class="form-control" id="phone"
                                            placeholder="{{ \App\CPU\translate('Ex') }} : +88017********"
                                            oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength); this.setCustomValidity(this.value.length < 10 ? 'Minimum length is 10 digits.' : ''); this.value = this.value.replace(/[^0-9]/g, '');"
                                            maxlength="10" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="age" class="title-color">{{ \App\CPU\translate('Age') }}</label>
                                        <input type="number" name="age" value="{{ old('age') }}"
                                            class="form-control" id="age"
                                            placeholder="{{ \App\CPU\translate('Ex') }} : 20"
                                            oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength); this.setCustomValidity(this.value.length < 10 ? 'Minimum length is 10 digits.' : ''); this.value = this.value.replace(/[^0-9]/g, '');"
                                            maxlength="3" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="customer_address"
                                            class="title-color">{{ \App\CPU\translate('Address') }}</label>
                                        <input type="text" name="address" value="{{ old('address') }}"
                                            class="form-control" id="customer_address"
                                            placeholder="{{ \App\CPU\translate('Ex') }} : 123, Green Park Avenue, Near City Center Mall, Sector 12, Gurugram, Haryana, 122018, India"
                                            required>
                                    </div>
                                    <div class="d-none">
                                        <input type="hidden" id="customer_lat" name="latitude"
                                            value="{{ old('latitude') }}">
                                        <input type="hidden" id="customer_lang" name="longitude"
                                            value="{{ old('longitude') }}">
                                        <input type="hidden" id="customer_area" name="area"
                                            value="{{ old('area') }}">
                                    </div>
                                    <div class="form-group">
                                        <label for="customer_state"
                                            class="title-color">{{ \App\CPU\translate('State') }}</label>
                                        <input type="text" name="state" value="{{ old('state') }}"
                                            class="form-control" id="customer_state"
                                            placeholder="{{ \App\CPU\translate('Ex') }} : Maharashtra" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="customer_city"
                                            class="title-color">{{ \App\CPU\translate('City') }}</label>
                                        <input type="text" name="city" value="{{ old('city') }}"
                                            class="form-control" id="customer_city"
                                            placeholder="{{ \App\CPU\translate('Ex') }} : Mumbai" required>
                                    </div>
                                    <div class="d-none form-group">
                                        <label for="customer_zipcode"
                                            class="title-color">{{ \App\CPU\translate('Zipcode') }}</label>
                                        <input type="text" name="zipcode" value="{{ old('zipcode') }}"
                                            class="form-control" id="customer_zipcode"
                                            placeholder="{{ \App\CPU\translate('Ex') }} : 123456" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="gender"
                                            class="title-color">{{ \App\CPU\translate('Gender') }}</label>
                                        <select name="gender" class="form-control" id="gender" required>
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





                                    {{-- <div class="d-none form-group">
                                        <label for="name"
                                            class="title-color">{{ \App\CPU\translate('Zipcode') }}</label>
                                        <select class="form-control" name="zipcode">
                                            <option value="0" selected disabled>
                                                ---{{ \App\CPU\translate('select') }}---
                                            </option>
                                            @if (isset($zipcode))
                                                @foreach ($zipcode as $key => $value)
                                                    <option value="{{ $value->zipcode }}">{{ $value->zipcode }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div> --}}
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="name"
                                            class="title-color">{{ \App\CPU\translate('user_image') }}</label>
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

    <!-- to get address from google api key -->
    <script src="https://maps.google.com/maps/api/js?libraries=places&key=AIzaSyDp5WRm4NU2C0C6NeNkBY1uOUnpGl6ChKY"></script>
    <script type="text/javascript">
        google.maps.event.addDomListener(window, 'load', function() {
            // Initialize the Autocomplete instance
            var autocomplete = new google.maps.places.Autocomplete(document.getElementById('customer_address'));

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
                console.log(city);

                // Set extracted values in input fields
                document.getElementById('customer_address').value = address;
                document.getElementById('customer_lat').value = latitude;
                document.getElementById('customer_lang').value = longitude;
                document.getElementById('customer_area').value = area;
                document.getElementById('customer_city').value = city;
                document.getElementById('customer_state').value = state;
                document.getElementById('customer_zipcode').value = pincode;

                // If you have an input for pincode, set its value
                // if (document.getElementById('pincode')) {
                //     document.getElementById('pincode').value = pincode;
                // }

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
@endpush
