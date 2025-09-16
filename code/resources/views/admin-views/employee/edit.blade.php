@extends('layouts.back-end.app')
@section('title', \App\CPU\translate('Admin Edit'))
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
                {{ \App\CPU\translate('Admin_Update') }}
            </h2>
        </div>
        <!-- End Page Title -->

        <!-- Content Row -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">{{ \App\CPU\translate('Employee') }} {{ \App\CPU\translate('Update') }}
                            {{ \App\CPU\translate('form') }}</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.employee.update', [$e['id']]) }}" method="post"
                            enctype="multipart/form-data"
                            style="text-align: {{ Session::get('direction') === 'rtl' ? 'right' : 'left' }};">
                            @csrf
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label for="name" class="title-color">{{ \App\CPU\translate('Name') }}</label>
                                        <input type="text" name="name" value="{{ $e['name'] }}"
                                            class="form-control" id="name"
                                            placeholder="{{ \App\CPU\translate('Ex') }} : Jhon Doe">
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label for="phone" class="title-color">{{ \App\CPU\translate('Phone') }}</label>
                                        <input type="number" value="{{ $e['phone'] }}" required name="phone"
                                            class="form-control" id="phone"
                                            placeholder="{{ \App\CPU\translate('Ex') }} : +88017********"
                                            oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength); this.setCustomValidity(this.value.length < 10 ? 'Minimum length is 10 digits.' : ''); this.value = this.value.replace(/[^0-9]/g, '');"
                                            maxlength="10">
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label for="email" class="title-color">{{ \App\CPU\translate('Email') }}</label>
                                        <input type="email" value="{{ $e['email'] }}" name="email"
                                            class="form-control" id="email"
                                            placeholder="{{ \App\CPU\translate('Ex') }} : ex@gmail.com" required>
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label class="title-color">{{ \App\CPU\translate('Role') }}</label>
                                        <select class="form-control" name="role_id">
                                            <option value="0" selected disabled>
                                                ---{{ \App\CPU\translate('select') }}---</option>
                                            @foreach ($rls as $r)
                                                <option value="{{ $r->id }}"
                                                    {{ $r['id'] == $e['admin_role_id'] ? 'selected' : '' }}>
                                                    {{ $r->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-6 form-group d-none">
                                        <label for="dob"
                                            class="title-color">{{ \App\CPU\translate('Date of Birth') }}</label>
                                        <input type="date" name="dob" value="{{ $e['dob'] }}"
                                            class="form-control" id="dob" >
                                    </div>

                                    @if($e['admin_role_id'] != 4)
                                    <div class="col-md-6 form-group">
                                        <label for="pan_card"
                                            class="title-color">{{ \App\CPU\translate('Pan Card No') }}</label>
                                        <input type="text" name="pan_card" value="{{ $e['pan_card'] }}"
                                            class="form-control" id="pan_card"
                                            placeholder="{{ \App\CPU\translate('Ex') }} : GGYFR1025G" required>
                                    </div>

                                    <div class="col-md-6 form-group">
                                        <label for="address"
                                            class="title-color">{{ \App\CPU\translate('Address') }}</label>
                                        <input type="text" name="address" value="{{ $e['address'] }}"
                                            class="form-control" id="address"
                                            placeholder="{{ \App\CPU\translate('Ex') }} : 123, Green Park Avenue, Near City Center Mall, Sector 12, Gurugram, Haryana, 122018, India"
                                            required>
                                    </div>

                                    <div class="col-md-6 form-group">
                                        <label for="gst"
                                            class="title-color">{{ \App\CPU\translate('Gst No.') }}</label>
                                        <input type="text" name="gst" value="{{ $e['gst'] }}"
                                            class="form-control" id="gst"
                                            placeholder="{{ \App\CPU\translate('Ex') }} : 27ABCDE1234F1Z5" required>
                                    </div>
                                    @endif

                                    @if($e['admin_role_id'] == 4)
                                    <div class="col-md-6 form-group">
                                        <label for="gender"
                                            class="title-color">{{ \App\CPU\translate('Gender') }}</label>
                                        <select name="gender" class="form-control" id="gender" >
                                            <option value="" disabled selected>
                                                {{ \App\CPU\translate('Select Gender') }}</option>
                                            <option value="male" {{ $e['gender'] == 'male' ? 'selected' : '' }}>
                                                {{ \App\CPU\translate('Male') }}</option>
                                            <option value="female" {{ $e['gender'] == 'female' ? 'selected' : '' }}>
                                                {{ \App\CPU\translate('Female') }}</option>
                                            <option value="other" {{ $e['gender'] == 'other' ? 'selected' : '' }}>
                                                {{ \App\CPU\translate('Other') }}</option>
                                        </select>
                                    </div>
                                    @endif



                                    <div class="col-md-6 form-group">
                                        <label for="password"
                                            class="title-color">{{ \App\CPU\translate('Password') }}</label><small> (
                                            {{ \App\CPU\translate('input if you want to change') }} )</small>
                                        <input type="text" name="password" class="form-control" id="password"
                                            placeholder="{{ \App\CPU\translate('Password') }}">
                                    </div>

                                    @if($e['admin_role_id'] == 3)
                                        <div class="col-md-6 form-group">
                                            <label for="exampleInputzipcode"
                                                class="title-color d-flex gap-1 align-items-center">{{ \App\CPU\translate('zipcode') }}</label>
                                            <select class="form-control" name="zipcode" disabled>
                                                <option value="0" selected disabled>
                                                    ---{{ \App\CPU\translate('select') }}---
                                                </option>
                                                @if (isset($zipcode))
                                                    @foreach ($zipcode as $key => $value)
                                                        <option value="{{ $value->zipcode }}"
                                                            {{ $value->zipcode == $e['zipcode'] ? 'selected' : '' }}>
                                                            {{ $value->zipcode }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>

                                        <div class="col-md-6 form-group">
                                            <div class="form-group">
                                                <label for="name"
                                                    class="title-color">{{ \App\CPU\translate('bank_statement') }}</label>
                                                <span class="text-info">( {{ \App\CPU\translate('Pdf of Bank Statement') }} )</span>
                                                <div class="form-group">
                                                    <!-- <div class="custom-file"> -->
                                                        <input style="display: block;" type="file" name="bank_statement">
                                                    <!-- </div  > -->
                                                </div>

                                                @if($e['bank_statement'])
                                                    <div class="mt-2">
                                                        <label class="text-muted">{{ \App\CPU\translate('View Bank Statement') }}:</label>
                                                        <a href="{{ asset('storage/app/public/admin') }}/{{ $e['bank_statement']}}" target="_blank" class="btn btn-link">
                                                            {{ \App\CPU\translate('View PDF') }}
                                                        </a>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endif    
                                    <!-- @if($e['admin_role_id'] == 5)
                                        <div class="col-md-6 form-group d-none">
                                            <label for="exampleInputzipcode"
                                                class="title-color d-flex gap-1 align-items-center">{{ \App\CPU\translate('City') }}</label>
                                            <select class="form-control" disabled>
                                                </option>
                                                @if (isset($city))
                                                    @foreach ($city as $key => $value)
                                                        <option value="{{ $value->id }}"
                                                            {{ $value->id == $e['city_id'] ? 'selected' : '' }}>
                                                            {{ $value->city }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                    @else
                                        <div class="col-md-6 form-group d-none ">
                                            <label for="exampleInputzipcode"
                                                class="title-color d-flex gap-1 align-items-center">{{ \App\CPU\translate('State') }}</label>
                                            <select class="form-control" disabled>
                                                <option selected>{{ $e['state'] }}</option>
                                            </select>
                                        </div>
                                    @endif -->

                                    <div class="col-md-6 form-group">
                                        <div class="form-group">
                                            <label for="customFileUpload"
                                                class="title-color">{{ \App\CPU\translate('profile_image') }}</label>
                                            <span class="text-danger">( {{ \App\CPU\translate('ratio') }} 1:1 )</span>
                                            <div class="custom-file text-left">
                                                <input type="file" name="image" id="customFileUpload"
                                                    class="custom-file-input"
                                                    accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                                                <label class="custom-file-label"
                                                    for="customFileUpload">{{ \App\CPU\translate('choose') }}
                                                    {{ \App\CPU\translate('file') }}</label>
                                            </div>
                                        </div>
                                        <div class="text-center">
                                            <img class="upload-img-view" id="viewer"
                                                onerror="this.src='{{ asset('public/assets/front-end/img/image-place-holder.png') }}'"
                                                src="{{ asset('storage/app/public/admin') }}/{{ $e['image'] }}"
                                                alt="Employee thumbnail" />
                                        </div>
                                    </div>

                                    @if($e['admin_role_id'] == 2)
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="name"
                                                class="title-color">{{ \App\CPU\translate('specialization') }}</label>
                                            <input type="text" name="specialization" value="{{ $e['specialization'] }}"
                                                class="form-control" id="specialization">
                                        </div>
                                    </div>
                                    @endif


                                </div>
                            </div>
                            <div class="d-flex justify-content-end">
                                <button type="submit"
                                    class="btn btn--primary px-4">{{ \App\CPU\translate('Update') }}</button>
                            </div>
                        </form>
                    </div>
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
        function readURL1(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();

                reader.onload = function(e) {
                    $('#viewer1').attr('src', e.target.result);
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
