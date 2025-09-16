@extends('layouts.front-end.app')

@section('title', \App\CPU\translate('services'))

@push('css_or_js')
    <meta property="og:image" content="{{ asset('storage/app/public/company') }}/{{ $web_config['web_logo'] }}" />
    <meta property="og:title" content="Products of {{ $web_config['name'] }} " />
    <meta property="og:url" content="{{ env('APP_URL') }}">
    <meta property="og:description" content="{!! substr($web_config['about']->value, 0, 100) !!}">

    <meta property="twitter:card" content="{{ asset('storage/app/public/company') }}/{{ $web_config['web_logo'] }}" />
    <meta property="twitter:title" content="Products of {{ $web_config['name'] }}" />
    <meta property="twitter:url" content="{{ env('APP_URL') }}">
    <meta property="twitter:description" content="{!! substr($web_config['about']->value, 0, 100) !!}">

    <style>
        .for-count-value {

            {{ Session::get('direction') === 'rtl' ? 'left' : 'right' }}: 0.6875 rem;
            ;
        }

        .for-count-value {

            {{ Session::get('direction') === 'rtl' ? 'left' : 'right' }}: 0.6875 rem;
        }

        .for-brand-hover:hover {
            color: {{ $web_config['primary_color'] }};
        }

        .for-hover-lable:hover {
            color: {{ $web_config['primary_color'] }} !important;
        }

        .page-item.active .page-link {
            background-color: {{ $web_config['primary_color'] }} !important;
        }

        .for-shoting {
            padding- {{ Session::get('direction') === 'rtl' ? 'left' : 'right' }}: 9px;
        }

        .sidepanel {
            {{ Session::get('direction') === 'rtl' ? 'right' : 'left' }}: 0;
        }

        .sidepanel .closebtn {
            {{ Session::get('direction') === 'rtl' ? 'left' : 'right' }}: 25 px;
        }

        .pac-container {
            z-index: 1055;
            /* Modal's z-index is 1050 */
        }

        @media (max-width: 360px) {
            .for-shoting-mobile {
                margin- {{ Session::get('direction') === 'rtl' ? 'left' : 'right' }}: 0% !important;
            }

            .for-mobile {

                margin- {{ Session::get('direction') === 'rtl' ? 'right' : 'left' }}: 10% !important;
            }

        }

        @media (max-width: 500px) {
            .for-mobile {

                margin- {{ Session::get('direction') === 'rtl' ? 'right' : 'left' }}: 27%;
            }
        }

        .customRadioBtn [type="radio"] {
            border: unset;
            clip: unset;
            height: auto;
            margin: unset;
            overflow: hidden;
            padding: 0;
            position: unset;
            width: auto;

        }

        .available {
            color: green;
        }

        .booked {
            color: grey;
            background-color: lightgray;
        }

        input[disabled] {
            background-color: lightgrey;
            cursor: not-allowed;
        }

        .review-table {
            width: 100%;
            border-collapse: collapse;
        }

        .review-table td {
            padding: 10px;
            vertical-align: middle;
        }

        .review-image {
            text-align: center;
        }

        .review-index {
            text-align: center;
        }

        .review-info,
        .review-comment {
            vertical-align: top;
        }

        .review-date {
            text-align: center;
        }

        .no-reviews {
            text-align: center;
            padding: 20px;
        }

        .rounded-image {
            border-radius: 100%;
        }

        .sr-star {
            color: gold;
        }

        .__max-h-323px {
            max-height: 170px;
        }

        .cz-product-gallery .cz-preview {
            border: 1px solid #cdcdcd;
        }

        .btn-primary:focus,
        .btn-primary.focus {
            color: #fff;
            background-color: #0081FE;
            border-color: #0081FE;
            box-shadow: #0081FE;
        }
    </style>
@endpush

@section('content')

    @php($decimal_point_settings = \App\CPU\Helpers::get_business_settings('decimal_point_settings'))
    <!-- Page Title-->
    <div class="d-flex w-100 justify-content-center align-items-center mb-3 __min-h-70px __inline-35"
        style="background:{{ $web_config['primary_color'] }}10;">
        <div class="text-capitalize container text-center">
            @foreach ($services as $service)
                <span class="__text-18px font-semibold">Service: {{ $service->name }}</span>
            @endforeach
        </div>

    </div>
    <!-- Page Content-->
    <div class="container pb-5 mb-2 mb-md-4 rtl __inline-35"
        style="text-align: {{ Session::get('direction') === 'rtl' ? 'right' : 'left' }};">
        <div class="row">
            @foreach ($services as $product)
                <div class="col-lg-6 col-md-4 col-12">
                    <div class="cz-product-gallery" style="flex-direction: column">
                        <div class="cz-preview">
                            @if ($product->images != null && json_decode($product->images) > 0)
                                @if (json_decode($product->colors) && $product->color_image)
                                    @foreach (json_decode($product->color_image) as $key => $photo)
                                        @if ($photo->color != null)
                                            <div class="cz-preview-item d-flex align-items-center justify-content-center {{ $key == 0 ? 'active' : '' }}"
                                                id="image{{ $photo->color }}">
                                                <img class="cz-image-zoom img-responsive w-100 __max-h-323px"
                                                    onerror="this.src='{{ asset('public/assets/front-end/img/image-place-holder.png') }}'"
                                                    src="{{ asset("storage/app/public/product/$photo->image_name") }}"
                                                    data-zoom="{{ asset("storage/app/public/product/$photo->image_name") }}"
                                                    alt="Product image" width="">
                                                <div class="cz-image-zoom-pane"></div>
                                            </div>
                                        @else
                                            <div class="cz-preview-item d-flex align-items-center justify-content-center {{ $key == 0 ? 'active' : '' }}"
                                                id="image{{ $key }}">
                                                <img class="cz-image-zoom img-responsive w-100 __max-h-323px"
                                                    onerror="this.src='{{ asset('public/assets/front-end/img/image-place-holder.png') }}'"
                                                    src="{{ asset("storage/app/public/product/$photo->image_name") }}"
                                                    data-zoom="{{ asset("storage/app/public/product/$photo->image_name") }}"
                                                    alt="Product image" width="">
                                                <div class="cz-image-zoom-pane"></div>
                                            </div>
                                        @endif
                                    @endforeach
                                @else
                                    @foreach (json_decode($product->images) as $key => $photo)
                                        <div class="cz-preview-item d-flex align-items-center justify-content-center {{ $key == 0 ? 'active' : '' }}"
                                            id="image{{ $key }}">
                                            <img class="cz-image-zoom img-responsive w-100 __max-h-323px"
                                                onerror="this.src='{{ asset('public/assets/front-end/img/image-place-holder.png') }}'"
                                                src="{{ asset("storage/app/public/product/$photo") }}"
                                                data-zoom="{{ asset("storage/app/public/product/$photo") }}"
                                                alt="Product image" width="">
                                            <div class="cz-image-zoom-pane"></div>
                                        </div>
                                    @endforeach
                                @endif
                            @endif
                        </div>
                        <div class="cz">
                            <div class="table-responsive __max-h-515px" data-simplebar>
                                <div class="d-flex">
                                    @if ($product->images != null && json_decode($product->images) > 0)
                                        @if (json_decode($product->colors) && $product->color_image)
                                            @foreach (json_decode($product->color_image) as $key => $photo)
                                                @if ($photo->color != null)
                                                    <div class="cz-thumblist">
                                                        <a class="cz-thumblist-item  {{ $key == 0 ? 'active' : '' }} d-flex align-items-center justify-content-center"
                                                            id="preview-img{{ $photo->color }}"
                                                            href="#image{{ $photo->color }}">
                                                            <img onerror="this.src='{{ asset('public/assets/front-end/img/image-place-holder.png') }}'"
                                                                src="{{ asset("storage/app/public/product/$photo->image_name") }}"
                                                                alt="Product thumb">
                                                        </a>
                                                    </div>
                                                @else
                                                    <div class="cz-thumblist">
                                                        <a class="cz-thumblist-item  {{ $key == 0 ? 'active' : '' }} d-flex align-items-center justify-content-center"
                                                            id="preview-img{{ $key }}"
                                                            href="#image{{ $key }}">
                                                            <img onerror="this.src='{{ asset('public/assets/front-end/img/image-place-holder.png') }}'"
                                                                src="{{ asset("storage/app/public/product/$photo->image_name") }}"
                                                                alt="Product thumb">
                                                        </a>
                                                    </div>
                                                @endif
                                            @endforeach
                                        @else
                                            @foreach (json_decode($product->images) as $key => $photo)
                                                <div class="cz-thumblist">
                                                    <a class="cz-thumblist-item  {{ $key == 0 ? 'active' : '' }} d-flex align-items-center justify-content-center"
                                                        id="preview-img{{ $key }}"
                                                        href="#image{{ $key }}">
                                                        <img onerror="this.src='{{ asset('public/assets/front-end/img/image-place-holder.png') }}'"
                                                            src="{{ asset("storage/app/public/product/$photo") }}"
                                                            alt="Product thumb">
                                                    </a>
                                                </div>
                                            @endforeach
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            @endforeach
            <!-- Product details-->
            <div class="col-lg-6 col-md-8 col-12 mt-md-0 mt-sm-3 p-3 productcardHead categoryHead"
                style="direction: {{ Session::get('direction') }}">
                <div class="details">

                    <div class="productNameDiv categoryName">
                        <span class="mb-2 __inline-24">{{ $product->name }}</span>
                        <div class="productNameDiv categoryRating">

                            <span class="text-info">{{ $reviewCount = count($reviews) }} Review</span>
                            <div class="star-rating"
                                style="{{ Session::get('direction') === 'rtl' ? '' : 'margin-right: 1px;' }}">
                                @for ($inc = 0; $inc < 1; $inc++)
                                    @if ($inc < $overallRating)
                                        <i class="sr-star czi-star-filled active"></i>
                                        <span>({{ $overallRating }})</span>
                                    @else
                                        <i class="sr-star czi-star"></i>
                                    @endif
                                @endfor
                            </div>
                        </div>
                    </div>
                    @foreach ($servicecategories as $category)
                        <div class="productNameDiv">
                            <span class="mb-2 __inline-24 categoryHeading">Category: {{ $category->name }}</span>
                        </div>
                    @endforeach
                    <div class="categoryPrice d-flex gap-5 {{ Session::get('direction') === 'rtl' ? 'ml-2' : 'mr-2' }}">
                        <span class="categoryPriceSpan">
                            <?php
                            if ($product->discount_type === 'percent') {
                                $purchase_price = $product->unit_price - ($product->unit_price * $product->discount) / 100;
                            } elseif ($product->discount_type === 'flat') {
                                $purchase_price = $product->unit_price - $product->discount;
                            } else {
                                $purchase_price = $product->unit_price;
                            }
                            ?>
                            {{ \App\CPU\Helpers::currency_converter($purchase_price) }}
                        </span>
                        {{-- totle price --}}
                        <strike class="categoryDelPrice" style="color: #E96A6A!important; padding-left: 15px">
                            {{ \App\CPU\Helpers::currency_converter($product->unit_price) }}
                        </strike>
                        {{-- discount percentage show it in same line as prices --}}
                        @if ($product->discount > 0)
                            <div class="d-flex">
                                <span class="text-danger categoryOffPrice">
                                    @if ($product->discount_type == 'percent')
                                        {{ round($product->discount, !empty($decimal_point_settings) ? $decimal_point_settings : 0) }}%
                                    @elseif($product->discount_type == 'flat')
                                        {{ \App\CPU\Helpers::currency_converter($product->discount) }}
                                    @endif
                                    {{ \App\CPU\translate('off') }}
                                </span>
                            </div>
                        @else
                            <div class="d-flex">
                                <span class=""></span>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="text-justify categoryDiscription">
                    "Delivering exceptional services with a commitment to quality and customer satisfaction."
                </div>

                <!-- Button trigger modal -->

                @if (auth('customer')->id())
                    <div class="categoryBtn">
                        <button type="button" class="btn btn-primary" id="modalButton" data-toggle="modal"
                            data-target="#exampleModalCenterBooking">
                            Book now
                        </button>
                    </div>
                @else
                    <div class="categoryBtn">
                        <button type="button" class="btn btn-primary" id="loginFirst">
                            Book now
                        </button>
                    </div>
                @endif

                <!-- Modal -->
                <div class="modal fade" id="exampleModalCenterBooking" tabindex="-1" role="dialog"
                    aria-labelledby="exampleModalCenterBookingTitle" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered serviceBookingModal" role="document">
                        <div class="modal-content">
                            <div class="modal-header" style="background-color: #0081FE;">
                                <h5 class="modal-title text-white" id="exampleModalLongTitle">Service Booking</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span style="color: #fff;" aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <form class="profileInputDiv" id="bookingForm" method="POST"
                                    enctype="multipart/form-data">
                                    @csrf
                                    <input type="hidden" value="{{ $product->id }}" name="service_id"
                                        id="service_id">
                                    <input type="hidden" value="{{ $product->user_id }}" name="seller_id">
                                    <input type="hidden" value="{{ $purchase_price }}" name="unit_price">

                                    <div class="inputWrapper1">
                                        <div class="form-group">
                                            <label for="booking_name">Name:</label>
                                            <div class="inputFeald">
                                                <input type="text" id="booking_name" name="booking_name" required
                                                    class="form-control" placeholder="Enter your full name">
                                            </div>
                                            <div class="error-message text-danger"></div>
                                        </div>

                                        <div class="form-group">
                                            <label for="booking_email">Email:</label>
                                            <div class="inputFeald">
                                                <input type="email" id="booking_email" name="booking_email" required
                                                    class="form-control" placeholder="Enter your email address">
                                            </div>
                                            <div class="error-message text-danger"></div>
                                        </div>
                                    </div>

                                    <div class="inputWrapper1">
                                        <div class="form-group">
                                            <label for="booking_mobile">Mobile:</label>
                                            <div class="inputFeald">
                                                <input type="tel" id="booking_mobile" name="booking_mobile" required 
                                                    class="form-control" placeholder="Enter your mobile number" maxlength="10">
                                            </div>
                                            
                                            <div class="error-message text-danger"></div>
                                        </div>

                                        <div class="form-group">
                                            <label for="alternate_mobile">Alternate Mobile (optional)</label>
                                            <div class="inputFeald">
                                                <input type="tel" id="alternate_mobile" name="alternate_mobile"
                                                    class="form-control"
                                                    placeholder="Alternate mobile number" maxlength="10">
                                            </div>
                                            
                                            <div class="error-message text-danger"></div>
                                        </div>
                                    </div>

                                    <div class="inputWrapper1">
                                        <div class="form-group">
                                            <label for="booking_address">{{ __('Address') }}</label>
                                            <textarea class="form-control" type="text" id="booking_address" name="booking_address"
                                                placeholder="{{ __('Address') }}" required></textarea>
                                        </div>
                                       
                                        <input type="hidden" id="bookingLat" name="latitude" value="">
                                        <input type="hidden" id="bookingLang" name="longitude" value="">
                                        <input type="hidden" id="bookingPincode" name="pincode">
                                        <input type="hidden" id="bookingArea" name="area">
                                        <input type="hidden" id="bookingCity" name="city">
                                        <input type="hidden" id="bookingState" name="state">

                                        <!-- Map container (hidden initially) -->
                                        {{-- <div id="map" style="width: 100%; height: 400px; display: none;"></div> --}}

                                        <div class="form-group">
                                            <label for="booking_datetime">Booking Date:</label>
                                            <div class="inputFeald">
                                                <input type="date" id="booking_datetime" 
                                                       value="{{ date('Y-m-d') }}"
                                                       name="booking_datetime" 
                                                       required class="form-control"
                                                       min="{{ date('Y-m-d') }}">
                                            </div>
                                            <!-- Error message for booking_datetime -->
                                            <div class="error-message text-danger"></div>
                                        </div>
                                        
                                    </div>

                                    <div id="slot_id_container">
                                        <!-- Radio buttons will be injected here -->
                                    </div>

                                    <div class="inputWrapper1">
                                        <div class="form-group">
                                            <label for="images">Upload Images:</label>
                                            <div class="inputFeald">
                                                <input type="file" id="images" name="images[]" multiple
                                                    class="form-control p-0 m-0 pt-1"
                                                    placeholder="Choose images to upload">
                                            </div>
                                            <!-- Error message for images -->
                                            <div class="error-message text-danger"></div>
                                        </div>

                                        <!-- <div class="form-group">
                                                                <label for="paid_amount">Paid Amount:</label>
                                                                <div class="inputFeald">
                                                                    <input type="number" id="paid_amount" name="paid_amount" required class="form-control" min="0" step="0.01" placeholder="Enter the amount paid">
                                                                </div>
                                                                <div class="error-message text-danger"></div>
                                                            </div> -->

                                        <div class="form-group">
                                            <label for="note">Note:</label>
                                            <div class="inputFeald">
                                                <textarea id="note" name="note"></textarea>
                                            </div>
                                            <div class="error-message text-danger"></div>
                                        </div>

                                    </div>

                                    <div class="categoryBtn">
                                        <button type="submit" class="btn btn-primary">Book Now</button>
                                    </div>
                                </form>

                            </div>

                        </div>
                    </div>
                </div>



                </form>

            </div>
        </div>
        <!-- Content  -->
        <section class="col-lg-12">
            <div class="d-flex flex-wrap align-items-center __inline-43  p-2">
                <div class="filter-show-btn btn btn--primary py-1 px-2">
                    <i class="tio-filter"></i>
                </div>
            </div>
            <div class="row">
                <div class="mt-4 rtl col-12"
                    style="text-align: {{ Session::get('direction') === 'rtl' ? 'right' : 'left' }};">
                    <div class="row">
                        <div class="col-12">
                            <div class=" mt-1">
                                <!-- Tabs-->
                                <ul class="nav nav-tabs d-flex justify-content-center __mt-35" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link __inline-27 active " href="#overview" data-toggle="tab"
                                            role="tab">
                                            {{ \App\CPU\translate('overview') }}
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link __inline-27" href="#reviews" data-toggle="tab"
                                            role="tab">
                                            {{ \App\CPU\translate('reviews') }}
                                        </a>
                                    </li>
                                </ul>
                                <div class="px-4 pt-lg-3 pb-3 mb-3 mr-0 mr-md-2 bg-white __review-overview __rounded-10">
                                    <div class="tab-content px-lg-3">
                                        <!-- Tech specs tab-->
                                        <div class="tab-pane fade show active" id="overview" role="tabpanel">
                                            <div class="row pt-2 specification">
                                                @if (isset($product->video_url) && $product->video_url != null)
                                                    <div class="col-12 mb-4">
                                                        <iframe width="420" height="315"
                                                            src="{{ $product->video_url }}">
                                                        </iframe>
                                                    </div>
                                                @endif

                                                <div class="text-body col-lg-12 col-md-12 overflow-scroll">
                                                    {!! isset($product['details']) ? $product['details'] : '' !!}
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Reviews tab-->
                                        <div class="tab-pane fade" id="reviews" role="tabpanel">
                                            <div class="row pt-2 pb-3">
                                                <div class="col-lg-4 col-md-5 reviewRatingDiv">
                                                    <div class="row d-flex justify-content-center align-items-center">
                                                        <div
                                                            class="col-12 d-flex justify-content-center align-items-center">
                                                            <h2 class="overall_review mb-2 __inline-28">
                                                                {{ $overallRating }}
                                                            </h2>
                                                        </div>
                                                        <div
                                                            class="d-flex justify-content-center align-items-center star-rating">
                                                            @for ($i = 1; $i <= 5; $i++)
                                                                @if ($i <= $overallRating)
                                                                    <i
                                                                        class="czi-star-filled font-size-sm startColor {{ Session::get('direction') === 'rtl' ? 'ml-1' : 'mr-1' }}"></i>
                                                                @else
                                                                    <i
                                                                        class="czi-star font-size-sm startColor {{ Session::get('direction') === 'rtl' ? 'ml-1' : 'mr-1' }}"></i>
                                                                @endif
                                                            @endfor
                                                        </div>
                                                        <div
                                                            class="col-12 d-flex justify-content-center align-items-center mt-2">
                                                            <span class="text-center">
                                                                {{ $review_count = count($reviews) }}
                                                                {{ \App\CPU\translate('ratings') }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-lg-8 col-md-7 pt-sm-3 pt-md-0 pl-4">
                                                    @foreach ([5, 4, 3, 2, 1] as $rating)
                                                        <?php
                                                        $ratingCount = $reviews->filter(fn($review) => $review->rating == $rating)->count();
                                                        $percentage = $overallRating != 0 ? ($ratingCount / $reviews->count()) * 100 : 0;
                                                        ?>
                                                        <div class="d-flex align-items-center mb-2 text-body font-size-sm">
                                                            <div class="__rev-txt"><span
                                                                    class="d-inline-block align-middle">
                                                                    {{ \App\CPU\translate(ucfirst(strtolower(['Poor', 'Below Average', 'Average', 'Good', 'Excellent'][$rating - 1]))) }}
                                                                </span></div>
                                                            <div class="w-0 flex-grow">
                                                                <div class="progress __h-5px">
                                                                    <div class="progress-bar" role="progressbar"
                                                                        style="background-color: {{ $web_config['primary_color'] }} !important;width: {{ $percentage }}%;"
                                                                        aria-valuenow="{{ $ratingCount }}"
                                                                        aria-valuemin="0" aria-valuemax="100"></div>
                                                                </div>
                                                            </div>
                                                            <div class="col-1">
                                                                <span
                                                                    class="{{ Session::get('direction') === 'rtl' ? 'mr-3 float-left' : 'ml-3 float-right' }}">
                                                                    {{ $ratingCount }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>

                                            <div class="row pb-4 mb-3">
                                                <div class="__inline-30 blueBg">
                                                    <span
                                                        class="text-capitalize">{{ \App\CPU\translate('Product Review') }}</span>
                                                </div>
                                            </div>
                                            <div class="row pb-4">
                                                <table class="review-table reviewTableDiv">
                                                    @if (isset($reviews) && count($reviews) > 0)
                                                        <?php $i = 0; ?>
                                                        @foreach ($reviews as $review)
                                                            <tr>
                                                                <td class="review-index">
                                                                    <p>{{ $i + 1 }}</p>
                                                                </td>
                                                                <td class="review-image">
                                                                    @if ($review->user_image)
                                                                        <img onerror="this.src='{{ asset('public/assets/front-end/img/image-place-holder.png') }}'"
                                                                            src="{{ asset("storage/app/public/users/$review->user_image") }}"
                                                                            alt="Product thumb" width="100px"
                                                                            class="userDpImg">
                                                                    @endif
                                                                </td>
                                                                <td class="review-info">
                                                                    <p>{{ $review->f_name }}</p>
                                                                    <div>
                                                                        <i class="sr-star czi-star-filled active"></i>
                                                                        <p>{{ $review->rating }}/5</p>
                                                                    </div>
                                                                </td>

                                                                <td class="review-comment">
                                                                    <p>{{ $review->comment }}</p>
                                                                    <?php
                                                                    $attachments = json_decode($review->attachment, true);
                                                                    $image = $attachments[0] ?? null;
                                                                    ?>
                                                                    @if ($image)
                                                                        <img onerror="this.src='{{ asset('public/assets/front-end/img/image-place-holder.png') }}'"
                                                                            src="{{ asset("storage/app/public/review/$image") }}"
                                                                            alt="Product thumb" width="100px">
                                                                    @endif
                                                                </td>

                                                                <td class="review-date">
                                                                    <p>{{ \Carbon\Carbon::parse($review->created_at)->format('M-d-Y') }}
                                                                    </p>
                                                                </td>
                                                            </tr>
                                                            <?php $i++; ?>
                                                        @endforeach
                                                    @elseif (isset($reviews) && count($reviews) == 0)
                                                        <tr>
                                                            <td colspan="5" class="no-reviews">
                                                                <div class="card">
                                                                    <div class="card-body">
                                                                        <h6 class="text-danger text-center m-0">
                                                                            {{ \App\CPU\translate('review_not_available') }}
                                                                        </h6>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endif
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
   
    <script>
        $(document).ready(function() {
            function fetchAvailableSlots() {
                let selectedDate = $('#booking_datetime').val();
                let serviceId = $('#service_id').val();

                $.ajax({
                    url: '{{ url('/get_timeslots') }}',
                    type: 'POST',
                    data: {
                        date: selectedDate,
                        service_id: serviceId
                    },
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    beforeSend: function() {
                        $('#slot_id_container').html('<p>Loading...</p>');
                    },
                    success: function(response) {
                        console.log(response);
                        let slotsHtml = '';

                        if (response.status) {
                            if (response.data && response.data.length > 0) {
                                response.data.forEach(slot => {
                                    let fromTimeFormatted = slot.from_time;
                                    let toTimeFormatted = slot.to_time;

                                    let slotClass = slot.class;

                                    // Set disabled if slot is booked
                                    let disabledAttribute = slot.is_booked === 1 ? 'disabled' :
                                        '';

                                    let slotLabel = `${fromTimeFormatted} - ${toTimeFormatted}`;

                                    slotsHtml += `
                                                <div class="customRadioBtn ${slotClass}">
                                                    <input type="radio" id="slot_${slot.slot_id}" name="slot_id" value="${slot.slot_id}" 
                                                        data-fromtime="${slot.from_time}" data-totime="${slot.to_time}" ${disabledAttribute} required>
                                                    <label for="slot_${slot.slot_id}">
                                                            ${slot.time}
                                                    </label>
                                                </div>
                                                `;
                                });
                            } else {
                                slotsHtml = '<p>No slots available for the selected date.</p>';
                            }
                        } else {
                            slotsHtml = `<p>${response.message}</p>`;
                        }

                        $('#slot_id_container').html(slotsHtml);

                        toggleSubmitButton();
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', error);
                        $('#slot_id_container').html('<p>Error loading slots. Please try again.</p>');
                    }
                });
            }

            function toggleSubmitButton() {
                const isSlotSelected = $('input[name="slot_id"]:checked').length >
                    $('#submit_button').prop('disabled', !isSlotSelected);
            }

            $('#booking_datetime').on('change', fetchAvailableSlots);

            $('#slot_id_container').on('change', 'input[name="slot_id"]', function() {
                toggleSubmitButton();
            });

            if ($('#booking_datetime').val()) {
                fetchAvailableSlots();
            }
        });
    </script>

    <script>
        $('#bookingForm').on('submit', function(e) {
            e.preventDefault();

            var formData = new FormData(this);

            $.ajax({
                url: "{{ route('services-booking') }}",
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(data) {
                    console.log(data)
                    // Clear previous error messages
                    $('.error-message').remove();

                    if (data.status === true) {
                        location.reload(); 
                        // Swal.fire({
                        //     title: 'Success!',
                        //     text: data.message,
                        //     icon: 'success',
                        //     confirmButtonText: 'Ok'
                        // });
                    } else if (data.status === false && data.errors) {
                        // Handle validation errors
                        let errorMessages = '';
                        $.each(data.errors, function(key, value) {
                            errorMessages += value[0] +
                                '<br>'; // Display the first error message
                        });

                        Swal.fire({
    html: errorMessages, 
    toast: true,
    position: 'top-end',
    icon: 'error',
    showConfirmButton: false,
    timer: 2000,
    timerProgressBar: true,
});

                    } else {
                        
                        Swal.fire({
    text: data.message || 'Something Went Wrong',
    toast: true,
    position: 'top-end',
    icon: 'error',
    showConfirmButton: false,
    timer: 2000, 
    timerProgressBar: true,
});

                    }
                },
                error: function(xhr, status, error) {
                    console.log('AJAX Error:', status, error);

                    // Clear previous error messages
                    $('.error-message').remove();

                    var errors = xhr.responseJSON.errors;

                    // Loop through each error and display it below the corresponding input field
                    $.each(errors, function(field, messages) {
                        var fieldElement = $('#' + field); // Get the input field by its ID
                        fieldElement.after('<div class="error-message text-danger">' + messages[
                            0] + '</div>'); // Append the error message below the input field
                    });

                    Swal.fire({
                        title: 'Error!',
                        text: 'An unexpected error occurred. Please try again later.',
                        icon: 'error',
                        confirmButtonText: 'Ok'
                    });
                }
            });
        });

        $('#loginFirst').on('click', function() {
            Swal.fire({
                    text: "Log in first",
                    toast: true,
                    position: "top-end",
    icon: 'error',
                    showConfirmButton: false,
                    timer: 1500,
                    timerProgressBar: true,
                });
        })
    </script>


    <script src="https://maps.google.com/maps/api/js?libraries=places&key=AIzaSyDp5WRm4NU2C0C6NeNkBY1uOUnpGl6ChKY"></script>

    <script type="text/javascript">
        

        document.addEventListener('DOMContentLoaded', function() {
            $('#modalButton').on('click', function() {
                const input = document.getElementById('booking_address');

                const autocomplete = new google.maps.places.Autocomplete(input);

                google.maps.event.addListener(autocomplete, 'place_changed', function() {
                    const place = autocomplete.getPlace();
                    if (place.geometry) {
                        const latitude = place.geometry.location.lat();
                        const longitude = place.geometry.location.lng();
                        const address = place.formatted_address;
                        let city = '';
                        let pincode = '';
                        let area = '';
                        let state = '';

                        place.address_components.forEach(function(component) {
                            const types = component.types;
                            if (types.includes("locality")) {
                                city = component.long_name;
                            }
                            if (types.includes("postal_code")) {
                                pincode = component.long_name;
                            }
                            if (types.includes("sublocality_level_1")) {
                                area = component.long_name;
                            }
                            if (types.includes("administrative_area_level_1")) {
                                state = component.long_name;
                            }
                        });

                        // console.log('Address:', address);
                        // console.log('Latitude:', latitude);
                        // console.log('Longitude:', longitude);
                        // console.log('City:', city);
                        // console.log('Pincode:', pincode);
                        // console.log('Area:', area);
                        // console.log('State:', state);

                        document.getElementById('bookingLat').value = latitude;
                        document.getElementById('bookingLang').value = longitude;
                        document.getElementById('bookingPincode').value = pincode;
                        document.getElementById('bookingArea').value = area;
                        document.getElementById('bookingCity').value = city;
                        document.getElementById('bookingState').value = state;
                    } else {
                        console.error('No geometry found for the selected place.');
                    }
                });
            });
        });
    </script>

@endsection
