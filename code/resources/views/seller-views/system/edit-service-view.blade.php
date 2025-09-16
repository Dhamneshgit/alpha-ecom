@extends('layouts.back-end.app-seller')

@section('content')
    <div class="content container-fluid">
        <!-- Page Title -->
        <div class="d-flex flex-wrap gap-2 align-items-center mb-3 customBtnDiv">
            <h2 class="h1 mb-0 d-flex gap-2">
                <img src="{{ asset('/public/assets/back-end/img/inhouse-product-list.png') }}" alt="">
                {{ \App\CPU\translate('Edit') }} {{ \App\CPU\translate('Service') }}
            </h2>
        </div>
        <!-- End Page Title -->

        <div class="row">
            <div class="col-md-12">
                <!-- Display validation errors -->
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('seller.service.update-service', $service->id) }}" method="POST"
                    enctype="multipart/form-data" id="service_form">
                    @csrf
                    @method('POST') <!-- Ensure proper method for updates -->

                    <div class="card">
                        <div class="card-header">
                            <h4 class="mb-0">{{ \App\CPU\translate('Service Details') }}</h4>
                        </div>
                        <div class="card-body">
                            <!-- Service Name -->
                            <div class="form-group">
                                <label class="title-color" for="name">Service Name</label>
                                <input type="text" name="name" id="name" class="form-control"
                                    placeholder="Edit service name" value="{{ $service->name }}" required>
                            </div>

                            <!-- Category -->
                            <div class="form-group">
                                <label class="title-color" for="category_id">Category</label>
                                <select name="category_id" id="category_id" class="form-control" required>
                                    <option value="">Select Category</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}"
                                            {{ $service->category_id == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Service Description -->
                            <div class="form-group">
                                <label class="title-color" for="description">Service Description</label>
                                <textarea name="description" id="editor12343" class="">{{ $service->details }}</textarea>
                            </div>

                            <!-- Pricing and Discount -->
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="title-color" for="unit_price">Unit Price</label>
                                        <input type="number" name="unit_price" id="unit_price" class="form-control"
                                            value="{{ $service->unit_price }}" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="title-color" for="discount_type">Discount Type</label>
                                    <select name="discount_type" id="discount_type" class="form-control" required>
                                        <option value="percent"
                                            {{ $service->discount_type == 'percent' ? 'selected' : '' }}>
                                            Percentage</option>
                                        <option value="flat" {{ $service->discount_type == 'flat' ? 'selected' : '' }}>
                                            Flat
                                        </option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="title-color" for="discount">Discount</label>
                                        <input type="number" name="discount" id="discount" class="form-control"
                                            value="{{ $service->discount }}" required>
                                    </div>
                                </div>
                            </div>

                            <!-- Tax Details -->
                            <div class="d-none row">
                                <div class="col-md-6">
                                    <label class="title-color" for="tax">Tax</label>
                                    <input type="number" name="tax" id="tax" class="form-control"
                                        value="{{ $service->tax }}" min="0">
                                </div>
                                <div class="col-md-6">
                                    <label class="title-color" for="tax_model">Tax Model</label>
                                    <select name="tax_model" id="tax_model" class="form-control">
                                        <option value="exclusive"
                                            {{ $service->tax_model == 'exclusive' ? 'selected' : '' }}>
                                            Exclusive</option>
                                        <option value="inclusive"
                                            {{ $service->tax_model == 'inclusive' ? 'selected' : '' }}>
                                            Inclusive</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Time Slots -->
                            <div class="d-none row mt-4">
                                <div class="col-md-6">
                                    <label class="title-color" for="from_time">From Time</label>
                                    <input type="time" name="from_time[]" class="form-control"
                                        value="{{ $service->from_time }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="title-color" for="to_time">To Time</label>
                                    <input type="time" name="to_time[]" class="form-control"
                                        value="{{ $service->to_time }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Images -->
                    <div class="row">
                        <!-- Product Images -->
                        <div class="col-md-8 form-group">
                            <div class="mb-2">
                                <label class="title-color">{{ \App\CPU\translate('Upload product images') }}</label>
                                <span class="text-info">* ( {{ \App\CPU\translate('ratio') }} 1:1 )</span>
                            </div>

                            <!-- Existing Images -->
                            <div id="color_wise_image" class="row g-2 mb-4">
                                @php
                                    $images = json_decode($service->images, true); // Decoding the JSON field for images
                                @endphp
                                @foreach ($images as $image)
                                    <div class="col-3 position-relative">
                                        <img src="{{ asset("storage/app/public/product/$image") }}"
                                            alt="Image" class="img-thumbnail" style="width: 100%; height: auto;"
                                            onerror="this.src='{{ asset('/public/assets/back-end/img/brand-logo.png') }}'">
                                        {{-- <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0"
                                            onclick="removeImage('{{ $image }}')">X</button> --}}
                                    </div>
                                @endforeach
                            </div>

                            <!-- Image Upload Section -->
                            <div class="p-2 border border-dashed coba-area">
                                <div class="row" id="coba"></div>
                            </div>
                        </div>

                        <!-- Thumbnail Image -->
                        <div class="col-md-4 form-group">
                            <div class="mb-2">
                                <label for="name"
                                    class="title-color text-capitalize">{{ \App\CPU\translate('Upload thumbnail') }}</label>
                                <span class="text-info">* ( {{ \App\CPU\translate('ratio') }} 1:1 )</span>
                            </div>
                            <div>
                                <!-- Display existing thumbnail -->
                                @if ($service->thumbnail)
                                    <img src="{{ \App\CPU\ProductManager::product_image_path1('thumbnail') }}/{{ $service->thumbnail }}"
                                        alt="Thumbnail" class="img-thumbnail" style="width: 100%; height: auto;"
                                        onerror="this.src='{{ asset('/public/assets/back-end/img/brand-logo.png') }}'">
                                @endif
                            </div>
                        </div>
                    </div>


                    <!-- Submit Button -->
                    <div class="form-group mt-4">
                        <button type="submit" class="btn btn--primary px-4">Update Service</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- CKEDITOR --}}
    <script src="https://cdn.ckeditor.com/ckeditor5/39.0.0/classic/ckeditor.js"></script>
    <script>
        ClassicEditor
            .create(document.querySelector('#editor12343'))
            .catch(error => {
                console.error(error);
            });
    </script>
@endsection

@push('script')
    {{-- MULTIPLE IMAGE SELECTION --}}
    <script src="{{ asset('public/assets/back-end') }}/js/tags-input.min.js"></script>
    <script src="{{ asset('public/assets/back-end/js/spartan-multi-image-picker.js') }}"></script>
    <script>
        $(function() {
            $('#color_switcher').click(function() {
                var checkBoxes = $("#color_switcher");
                if ($('#color_switcher').prop('checked')) {
                    $('#color_wise_image').show();
                } else {
                    $('#color_wise_image').hide();
                }
            });

            $("#coba").spartanMultiImagePicker({
                fieldName: 'images[]',
                maxCount: 10,
                rowHeight: 'auto',
                groupClassName: 'col-6',
                maxFileSize: '',
                placeholderImage: {
                    image: '{{ asset('public/assets/back-end/img/400x400/img2.jpg') }}',
                    width: '100%',
                },
                dropFileLabel: "Drop Here",
                onAddRow: function(index, file) {

                },
                onRenderedPreview: function(index) {

                },
                onRemoveRow: function(index) {

                },
                onExtensionErr: function(index, file) {
                    toastr.error(
                        '{{ \App\CPU\translate('Please only input png or jpg type file') }}', {
                            CloseButton: true,
                            ProgressBar: true
                        });
                },
                onSizeErr: function(index, file) {
                    toastr.error('{{ \App\CPU\translate('File size too big') }}', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                }
            });

            $("#thumbnail").spartanMultiImagePicker({
                fieldName: 'thumbnail',
                maxCount: 1,
                rowHeight: 'auto',
                groupClassName: 'col-12',
                maxFileSize: '',
                placeholderImage: {
                    image: '{{ asset('public/assets/back-end/img/400x400/img2.jpg') }}',
                    width: '100%',
                },
                dropFileLabel: "Drop Here",
                onAddRow: function(index, file) {

                },
                onRenderedPreview: function(index) {

                },
                onRemoveRow: function(index) {

                },
                onExtensionErr: function(index, file) {
                    toastr.error(
                        '{{ \App\CPU\translate('Please only input png or jpg type file') }}', {
                            CloseButton: true,
                            ProgressBar: true
                        });
                },
                onSizeErr: function(index, file) {
                    toastr.error('{{ \App\CPU\translate('File size too big') }}', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                }
            });

            $("#meta_img").spartanMultiImagePicker({
                fieldName: 'meta_image',
                maxCount: 1,
                rowHeight: '280px',
                groupClassName: 'col-12',
                maxFileSize: '',
                placeholderImage: {
                    image: '{{ asset('public/assets/back-end/img/400x400/img2.jpg') }}',
                    width: '90%',
                },
                dropFileLabel: "Drop Here",
                onAddRow: function(index, file) {

                },
                onRenderedPreview: function(index) {

                },
                onRemoveRow: function(index) {

                },
                onExtensionErr: function(index, file) {
                    toastr.error(
                        '{{ \App\CPU\translate('Please only input png or jpg type file') }}', {
                            CloseButton: true,
                            ProgressBar: true
                        });
                },
                onSizeErr: function(index, file) {
                    toastr.error('{{ \App\CPU\translate('File size too big') }}', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                }
            });
        });

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
            // dir: "rtl",
            width: 'resolve'
        });
    </script>
@endpush
