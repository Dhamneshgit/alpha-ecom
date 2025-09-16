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

        .text-center.bookNowBtn {
            margin-top: 20px;
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
    </style>
@endpush

@section('content')

    @php($decimal_point_settings = \App\CPU\Helpers::get_business_settings('decimal_point_settings'))
    <!-- Page Title-->
    <div class="d-flex w-100 justify-content-center align-items-center mb-3 __min-h-70px __inline-35"
        style="background:{{ $web_config['primary_color'] }}10;">

        <div class="text-capitalize container text-center">
            <span class="__text-18px font-semibold">Service: {{ $servicecategories[0]->name }}</span>
        </div>

    </div>
    <!-- Page Content-->
    <div class="container pb-5 mb-2 mb-md-4 rtl __inline-35"
        style="text-align: {{ Session::get('direction') === 'rtl' ? 'right' : 'left' }};">
        <div class="row">

            <!-- Content  -->
            <section class="col-lg-12">
                <div class="d-flex flex-wrap align-items-center __inline-43 p-2">
                    <div class="filter-show-btn btn btn--primary py-1 px-2">
                        <i class="tio-filter"></i>
                    </div>

                    @foreach ($services as $product)
                        @if (!empty($product['product_id']))
                            @php($product = $product->product)
                        @endif
                        <div
                            class=" {{ Request::is('products*') ? 'col-md-4 col-sm-4 col-6' : 'col-lg-3 col-md-4 col-sm-6 col-12' }} {{ Request::is('shopView*') ? 'col-lg-3 col-md-4 col-sm-4 col-6' : '' }} mb-2 p-2">
                            @if (!empty($product))
                                <div class="product-single-hover">
                                    <div class="overflow-hidden position-relative">
                                        <div class=" inline_product d-flex justify-content-center"
                                            style="cursor: pointer;background:{{ $web_config['primary_color'] }}10;border-radius: 5px 5px 0px 0px;">
                                            @if ($product->discount > 0)
                                                <div class="d-flex" style="left:8px;top:8px;">
                                                    <span class="for-discoutn-value p-1 pl-2 pr-2">
                                                        @if ($product->discount_type == 'percent')
                                                            {{ round($product->discount, !empty($decimal_point_settings) ? $decimal_point_settings : 0) }}%
                                                        @elseif($product->discount_type == 'flat')
                                                            {{ \App\CPU\Helpers::currency_converter($product->discount) }}
                                                        @endif
                                                        {{ \App\CPU\translate('off') }}
                                                    </span>
                                                </div>
                                            @else
                                                <div class="d-flex justify-content-end for-dicount-div-null">
                                                    <span class="for-discoutn-value-null"></span>
                                                </div>
                                            @endif
                                            <a href="{{ route('singleservices', ['id' => $product['id']]) }}">
                                                <div class="d-flex d-block productImgDiv">
                                                    <img onerror="this.src='{{ asset('public/assets/front-end/img/image-place-holder.png') }}'"
                                                        src="{{ asset('storage/app/public/product/' . $product['thumbnail']) }}"
                                                        alt="{{ $product['name'] }}">
                                                </div>
                                            </a>
                                        </div>

                                        <div class="single-product-details">
                                            <div class="productHead">
                                                <a href="{{ route('singleservices', ['id' => $product['id']]) }}">
                                                    <div
                                                        class="text-{{ Session::get('direction') === 'rtl' ? 'right pr-0' : 'left pl-0' }}">
                                                        {{ Str::limit($product['name'], 23) }}
                                                    </div>
                                                </a>
                                                <div class="star-rating"
                                                    style="{{ Session::get('direction') === 'rtl' ? '' : 'margin-right: 1px;' }}">
                                                    @if ($product['average_rating'] && $product['average_rating'] > 0)
                                                        <i class="sr-star czi-star-filled active"></i>
                                                        <span>{{ number_format($product['average_rating'], 1) }}</span>
                                                    @else
                                                        <!-- Display empty star -->
                                                        <i class="sr-star czi-star"></i>
                                                    @endif
                                                </div>

                                            </div>
                                            <div>
                                                <?php
                                                if (preg_match('/<h[1-6]>.*?<\/h[1-6]>/', $product->details)) {
                                                    echo preg_replace('/(<h[1-6]>)(.*?)(<\/h[1-6]>)/', '<span style="font-size: 16px;">$2</span>', $product->details);
                                                } elseif (stripos($product->details, '<p>') !== false) {
                                                    echo substr($product->details, 0, 25);
                                                } else {
                                                    echo substr($product->details, 0, 25);
                                                }
                                                ?>


                                            </div>
                                            <div class="justify-content-between text-center">
                                                <div class="product-price text-center">

                                                    @if ($product->discount > 0)
                                                        <span class="cardMainPrice">
                                                            <?php
                                                            // Check discount type and calculate purchase price accordingly
                                                            if ($product->discount_type === 'percent') {
                                                                // Calculate the price after applying percentage discount
                                                                $purchase_price = $product->unit_price - ($product->unit_price * $product->discount) / 100;
                                                            } elseif ($product->discount_type === 'flat') {
                                                                // Calculate the price after applying flat discount
                                                                $purchase_price = $product->unit_price - $product->discount;
                                                            } else {
                                                                // No discount, purchase price is equal to unit price
                                                                $purchase_price = $product->unit_price;
                                                            }
                                                            ?>
                                                            {{ \App\CPU\Helpers::currency_converter($purchase_price) }}
                                                        </span><br>
                                                        <strike class="cardDelPrice" style="color: #E96A6A!important;">
                                                            {{ \App\CPU\Helpers::currency_converter($product->unit_price) }}
                                                        </strike><br>
                                                    @endif
                                                    {{-- discount percentage show it in same line as prices --}}
                                                    @if ($product->discount > 0)
                                                        <div class="d-flex">
                                                            <span class="text-danger cardOffPrice">
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

                                            @if (auth('customer')->id())
                                                <div class="text-center bookNowBtn">
                                                    <a href="{{ route('singleservices', ['id' => $product['id']]) }}"> Book
                                                        now</a>
                                                </div>
                                            @else
                                                <div class="categoryBtn">
                                                    <button type="button" class="btn btn-primary loginFirst">
                                                        Book now
                                                    </button>
                                                </div>
                                            @endif

                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach

                </div>

            </section>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Change from id selector to class selector
            $('.loginFirst').on('click', function() {
                Swal.fire({
                    text: "Log in first",
                    toast: true,
                    icon: 'error',
                    position: "top-end",
                    showConfirmButton: false,
                    timer: 1500,
                    timerProgressBar: true,
                });
            });
        });
    </script>
@endsection
