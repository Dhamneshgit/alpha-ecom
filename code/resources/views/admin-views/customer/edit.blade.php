@extends('layouts.back-end.app')
@section('title', \App\CPU\translate('Edit User'))
@push('css_or_js')
    <link href="{{ asset('public/assets/back-end') }}/css/select2.min.css" rel="stylesheet" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Title -->
        <div class="mb-3 customBtnDiv">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                <img src="{{ asset('/public/assets/back-end/img/edit-user.png') }}" alt="">
                {{ \App\CPU\translate('Edit_User') }}
            </h2>
            <a href="{{ route('admin.customer.list') }}"><button class="btn btn--primary px-4">Back</button></a>
        </div>
        <!-- End Page Title -->

        <!-- Content Row -->
        <div class="row">
            <div class="col-md-12">
                <form action="{{ route('admin.customer.update', $customer->id) }}" method="POST"
                    enctype="multipart/form-data"
                    style="text-align: {{ Session::get('direction') === 'rtl' ? 'right' : 'left' }};">
                    @csrf
                    @method('PUT') <!-- Ensure the correct HTTP method is used -->

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
                                            value="{{ old('name', $customer->name) }}"
                                            placeholder="{{ \App\CPU\translate('Ex') }} : Jhon Doe" required>
                                    </div> --}}
                                    <div class="form-group">
                                        <label for="name"
                                            class="title-color">{{ \App\CPU\translate('First Name') }}</label>
                                        <input type="text" name="f_name" class="form-control" id="f_name"
                                            value="{{ old('f_name', $customer->f_name) }}"
                                            placeholder="{{ \App\CPU\translate('Ex') }} : Jhon" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="name"
                                            class="title-color">{{ \App\CPU\translate('Last Name') }}</label>
                                        <input type="text" name="l_name" class="form-control" id="l_name"
                                            value="{{ old('l_name', $customer->l_name) }}"
                                            placeholder="{{ \App\CPU\translate('Ex') }} : Doe" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="phone" class="title-color">{{ \App\CPU\translate('Phone') }}</label>
                                        <input type="number" name="phone" value="{{ old('phone', $customer->phone) }}"
                                            class="form-control" id="phone" maxlength="10"
                                            placeholder="{{ \App\CPU\translate('Ex') }} : +88017********" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="age" class="title-color">{{ \App\CPU\translate('Age') }}</label>
                                        <input type="number" name="age" value="{{ old('age', $customer->age) }}"
                                            class="form-control" id="age" maxlength="3"
                                            placeholder="{{ \App\CPU\translate('Ex') }} : 20" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="address"
                                            class="title-color">{{ \App\CPU\translate('Address') }}</label>
                                        <input type="text" name="address"
                                            value="{{ old('address', $customer->street_address) }}" class="form-control"
                                            id="customer_address"
                                            placeholder="{{ \App\CPU\translate('Ex') }} : 123, Green Park Avenue, Near City Center Mall, Sector 12, Gurugram, Haryana, 122018, India"
                                            required>
                                    </div>
                                    <div class="d-none">
                                        <input type="hidden" id="customer_lat" name="latitude"
                                            value="{{ old('latitude', $customer->latitude) }}">
                                        <input type="hidden" id="customer_lang" name="longitude"
                                            value="{{ old('longitude', $customer->longitude) }}">
                                        <input type="hidden" id="customer_area" name="area"
                                            value="{{ old('area', $customer->area) }}">
                                    </div>
                                    <div class="form-group">
                                        <label for="state" class="title-color">{{ \App\CPU\translate('State') }}</label>
                                        <input type="text" name="state" value="{{ old('state', $customer->state) }}"
                                            class="form-control" id="customer_state"
                                            placeholder="{{ \App\CPU\translate('Ex') }} : Maharashtra" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="customer_city"
                                            class="title-color">{{ \App\CPU\translate('City') }}</label>
                                        <input type="text" name="city" value="{{ old('city', $customer->city) }}"
                                            class="form-control" id="customer_city"
                                            placeholder="{{ \App\CPU\translate('Ex') }} : Mumbai" required>
                                    </div>
                                    <div class="d-none form-group">
                                        <label for="customer_zipcode"
                                            class="title-color">{{ \App\CPU\translate('Zipcode') }}</label>
                                        <input type="text" name="zipcode"
                                            value="{{ old('zipcode', $customer->zipcode) }}" class="form-control"
                                            id="customer_zipcode" placeholder="{{ \App\CPU\translate('Ex') }} : 123456"
                                            required>
                                    </div>
                                    <div class="form-group">
                                        <label for="gender"
                                            class="title-color">{{ \App\CPU\translate('Gender') }}</label>
                                        <select name="gender" class="form-control" id="gender" required>
                                            <option value="" disabled>{{ \App\CPU\translate('Select Gender') }}
                                            </option>
                                            <option value="male"
                                                {{ old('gender', $customer->gender) == 'male' ? 'selected' : '' }}>
                                                {{ \App\CPU\translate('Male') }}</option>
                                            <option value="female"
                                                {{ old('gender', $customer->gender) == 'female' ? 'selected' : '' }}>
                                                {{ \App\CPU\translate('Female') }}</option>
                                            <option value="other"
                                                {{ old('gender', $customer->gender) == 'other' ? 'selected' : '' }}>
                                                {{ \App\CPU\translate('Other') }}</option>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="image"
                                            class="title-color">{{ \App\CPU\translate('User Image') }}</label>
                                        <div class="custom-file text-left">
                                            <input type="file" name="image" id="customFileUpload"
                                                class="custom-file-input"
                                                accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                                            <label class="custom-file-label"
                                                for="customFileUpload">{{ \App\CPU\translate('Choose file') }}</label>
                                        </div>
                                    </div>
                                    <div class="text-center">
                                        <img class="upload-img-view" id="viewer"
                                            src="{{ asset('storage/app/public/profile/' . $customer->image) ?? asset('public/assets/back-end/img/400x400/img2.jpg') }}"
                                            alt="Product thumbnail" />
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="email"
                                            class="title-color">{{ \App\CPU\translate('Email') }}</label>
                                        <input type="email" name="email"
                                            value="{{ old('email', $customer->email) }}" class="form-control"
                                            id="email" placeholder="{{ \App\CPU\translate('Ex') }} : ex@gmail.com"
                                            required>
                                    </div>

                                    <div class="form-group">
                                        <label for="password"
                                            class="title-color">{{ \App\CPU\translate('Password') }}</label>
                                        <input type="password" name="password" class="form-control" id="password"
                                            placeholder="{{ \App\CPU\translate('Password') }}">
                                    </div>

                                    <div class="form-group">
                                        <label for="password_confirmation"
                                            class="title-color">{{ \App\CPU\translate('Confirm Password') }}</label>
                                        <input type="password" name="password_confirmation" class="form-control"
                                            id="password_confirmation"
                                            placeholder="{{ \App\CPU\translate('Confirm Password') }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-3">
                        <button type="reset" id="reset"
                            class="btn btn-secondary px-4">{{ \App\CPU\translate('Reset') }}</button>
                        <button type="submit" class="btn btn--primary px-4">{{ \App\CPU\translate('Update') }}</button>
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
