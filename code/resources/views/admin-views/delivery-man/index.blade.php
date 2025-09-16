@extends('layouts.back-end.app')

@section('title',\App\CPU\translate('Add new delivery-man'))

@push('css_or_js')

@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Title -->
        <div class="mb-3">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                <img src="{{asset('/public/assets/back-end/img/add-new-delivery-man.png')}}" alt="">
                {{\App\CPU\translate('Add new delivery-man')}}
            </h2>
        </div>
        <!-- End Page Title -->

        <!-- Page Header -->
        <div class="row">
            <div class="col-12">

                <form action="{{route('admin.delivery-man.store')}}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="card">
                        <!-- End Page Header -->
                        <div class="card-body">
                            <h5 class="mb-0 page-header-title d-flex align-items-center gap-2 border-bottom pb-3 mb-3">
                                <i class="tio-user"></i>
                                {{\App\CPU\translate('General_Information')}}
                            </h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="title-color d-flex" for="f_name">{{\App\CPU\translate('first_name')}}</label>
                                        <input type="text" name="f_name" value="{{old('f_name')}}" class="form-control" placeholder="{{\App\CPU\translate('first_name')}}"
                                               required>
                                    </div>
                                    <div class="form-group">
                                        <label class="title-color d-flex" for="exampleFormControlInput1">{{\App\CPU\translate('last')}} {{\App\CPU\translate('name')}}</label>
                                        <input value="{{old('l_name')}}"  type="text" name="l_name" class="form-control" placeholder="{{\App\CPU\translate('last')}} {{\App\CPU\translate('name')}}"
                                               required>
                                    </div>
                                    <div class="form-group">
                                        <label class="title-color d-flex" for="exampleFormControlInput1">{{\App\CPU\translate('phone')}}</label>
                                        <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <select
                                                    class="js-example-basic-multiple js-states js-example-responsive form-control"
                                                    name="country_code" required>
                                                    @foreach ($telephone_codes as $code)
                                                        <option value="{{ $code['code'] }}" {{old($code['code']) == $code['code']? 'selected' : ''}}>{{ $code['name'] }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <input value="{{old('phone')}}" type="text" name="phone" class="form-control" placeholder="{{\App\CPU\translate('Ex : 017********')}}"
                                                maxlength="10"   required>
                                        </div>
                                    </div>

                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="title-color d-flex" for="exampleFormControlInput1">{{\App\CPU\translate('identity')}} {{\App\CPU\translate('type')}}</label>
                                        <select name="identity_type" class="form-control">
                                            <option value="passport">{{\App\CPU\translate('passport')}}</option>
                                            <option value="driving_license">{{\App\CPU\translate('driving')}} {{\App\CPU\translate('license')}}</option>
                                            <option value="nid">{{\App\CPU\translate('nid')}}</option>
                                            <option value="company_id">{{\App\CPU\translate('company')}} {{\App\CPU\translate('id')}}</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label class="title-color d-flex" for="exampleFormControlInput1">{{\App\CPU\translate('identity')}} {{\App\CPU\translate('number')}}</label>
                                        <input value="{{ old('identity_number') }}"  type="text" name="identity_number" class="form-control"
                                               placeholder="{{\App\CPU\translate('Ex : DH-23434-LS')}}"
                                            maxlength="20"   required>
                                    </div>
                                    <div class="form-group">
                                        <label class="title-color d-flex" for="exampleFormControlInput1">{{\App\CPU\translate('address')}}</label>
                                        <div class="input-group mb-3">
                                            <textarea name="address" class="form-control" id="shop_address" rows="1" placeholder="{{\App\CPU\translate('address')}}" required>{{ old('address') }}</textarea>
                                        </div>
                                    </div>
                                    <div class="shopAddressDiv col-md-12 d-none">
                                        <!-- <div class="addressInputFeald"> -->
                                        <!-- <label for="lat">Latitude</label> -->
                                        <input type="hidden" id="lat" name="latitude" placeholder="Google Address"
                                            value="" >
                                        <!-- </div> -->
                                        <!-- <div class="addressInputFeald"> -->
                                        <!-- <label for="lang">Language</label> -->
                                        <input type="hidden" id="lang" name="longitude" placeholder="Google Address"
                                            value="" >
                                        <!-- </div> -->
                                        <div class="addressInputFeald col-md-6">
                                            <label for="area">Area</label>
                                            <input type="text" id="area" name="area" placeholder="Google Address"
                                                value="" >
                                        </div>
                                        <div class="addressInputFeald col-md-6">
                                            <label for="city">City</label>
                                            <input type="text" id="city" name="city" placeholder="Google Address"
                                                value="" >
                                        </div>
                                        <div class="addressInputFeald col-md-6">
                                            <label for="state">State</label>
                                            <input type="text" id="state" name="state" placeholder="Google Address"
                                                value="" >
                                        </div>
                                        <div class="addressInputFeald col-md-6">
                                            <label for="pincode">Pincode</label>
                                            <input type="text" id="pincode" name="pincode" placeholder="Google Address"
                                                value="" >
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="title-color">{{\App\CPU\translate('deliveryman_image')}}</label>
                                        <span class="text-info">* ( {{\App\CPU\translate('ratio')}} 1:1 )</span>
                                        <div class="custom-file">
                                            <input value="{{ old('image') }}" type="file" name="image" id="customFileEg1" class="custom-file-input"
                                                   accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*" required>
                                            <label class="custom-file-label" for="customFileEg1">{{\App\CPU\translate('choose')}} {{\App\CPU\translate('file')}}</label>
                                        </div>
                                        <center class="mt-4">
                                            <img class="upload-img-view" id="viewer"
                                                 src="{{asset('public\assets\back-end\img\400x400\img2.jpg')}}" alt="delivery-man image"/>
                                        </center>


                                    </div>

                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="title-color" for="exampleFormControlInput1">{{\App\CPU\translate('identity')}} {{\App\CPU\translate('image')}}</label>
                                        <div>
                                            <div class="row" id="coba"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card mt-3">
                        <!-- End Page Header -->
                        <div class="card-body">
                            <h5 class="mb-0 page-header-title d-flex align-items-center gap-2 border-bottom pb-3 mb-3">
                                <i class="tio-user"></i>
                                {{\App\CPU\translate('Account_Information')}}
                            </h5>

                            <form action="{{route('admin.delivery-man.store')}}" method="post" enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="title-color d-flex" for="exampleFormControlInput1">{{\App\CPU\translate('email')}}</label>
                                            <input value="{{old('email')}}" type="email" name="email" class="form-control" placeholder="{{\App\CPU\translate('Ex : ex@example.com')}}"
                                                required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="title-color d-flex" for="exampleFormControlInput1">{{\App\CPU\translate('password')}}</label>
                                            <input type="text" name="password" class="form-control" placeholder="{{\App\CPU\translate('password_minimum_8_characters')}}"
                                                required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="title-color d-flex" for="exampleFormControlInput1">{{\App\CPU\translate('confirm_password')}}</label>
                                            <input type="text" name="confirm_password" class="form-control" placeholder="{{\App\CPU\translate('password_minimum_8_characters')}}"
                                                required>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex gap-3 justify-content-end">
                                    <button type="reset" id="reset" class="btn btn-secondary px-4">{{\App\CPU\translate('reset')}}</button>
                                    <button type="submit" class="btn btn--primary px-4">{{\App\CPU\translate('submit')}}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('script_2')
    <script>
        $(".js-example-responsive").select2({
            width: 'resolve'
        });
    </script>
    <script>
        function readURL(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();

                reader.onload = function (e) {
                    $('#viewer').attr('src', e.target.result);
                }

                reader.readAsDataURL(input.files[0]);
            }
        }

        $("#customFileEg1").change(function () {
            readURL(this);
        });
    </script>

    <script src="{{asset('public/assets/back-end/js/spartan-multi-image-picker.js')}}"></script>
    <script type="text/javascript">
        $(function () {
            $("#coba").spartanMultiImagePicker({
                fieldName: 'identity_image[]',
                maxCount: 5,
                rowHeight: 'auto',
                groupClassName: 'col-6 col-lg-4',
                maxFileSize: '',
                placeholderImage: {
                    image: '{{asset('public/assets/back-end/img/400x400/img2.jpg')}}',
                    width: '100%'
                },
                dropFileLabel: "Drop Here",
                onAddRow: function (index, file) {

                },
                onRenderedPreview: function (index) {

                },
                onRemoveRow: function (index) {

                },
                onExtensionErr: function (index, file) {
                    toastr.error('Please only input png or jpg type file', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                },
                onSizeErr: function (index, file) {
                    toastr.error('File size too big', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                }
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
            console.log("pincode: ", pincode);
        });
    });
</script>


@endpush
