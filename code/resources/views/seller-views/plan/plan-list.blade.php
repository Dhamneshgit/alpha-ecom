@extends('layouts.back-end.app-seller')

@section('title', \App\CPU\translate('Plan List'))

@push('css_or_js')
    <link href="{{ asset('public/assets/select2/css/select2.min.css')}}" rel="stylesheet">
    <link href="{{ asset('public/assets/back-end/css/custom.css')}}" rel="stylesheet">

    {{-- Razorpay integration --}}
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
@endpush

@section('content')
    <div class="content container-fluid">
        <div class="mb-3">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                <img src="{{asset('/public/assets/back-end/img/coupon_setup.png')}}" alt="">
                {{\App\CPU\translate('plans')}}
            </h2>
        </div>

        <div class="row mt-20">
            <div class="col-md-12">
                <div class="card">
                    <div class="px-3 py-4">
                        <div class="row justify-content-between align-items-center flex-grow-1">
                            <div class="col-sm-4 col-md-6 col-lg-8 mb-2 mb-sm-0">
                                <h5 class="mb-0 text-capitalize d-flex gap-2">
                                    {{\App\CPU\translate('coupon_list')}}
                                    <span class="badge badge-soft-dark radius-50 fz-12 ml-1">{{ $cou->total() }}</span>
                                </h5>
                            </div>
                            <div class="col-sm-8 col-md-6 col-lg-4">
                                <!-- Search -->
                                <form action="{{ url()->current() }}" method="GET">
                                    <div class="input-group input-group-merge input-group-custom">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text">
                                                <i class="tio-search"></i>
                                            </div>
                                        </div>
                                        <input id="datatableSearch_" type="search" name="search" class="form-control"
                                               placeholder="{{\App\CPU\translate('Search by Title or Code or Discount Type')}}"
                                               value="{{ $search ?? '' }}" aria-label="Search orders" required>
                                        <button type="submit" class="btn btn--primary">{{\App\CPU\translate('search')}}</button>
                                    </div>
                                </form>
                                <!-- End Search -->
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table id="datatable" class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                            <thead class="thead-light thead-50 text-capitalize">
                                <tr>
                                    <th>{{\App\CPU\translate('SL')}}</th>
                                    <th>{{\App\CPU\translate('plan_title')}}</th>
                                    <th>{{\App\CPU\translate('plan_amount')}}</th>
                                    <th>{{\App\CPU\translate('discounted_amount')}}</th>
                                    <th>{{\App\CPU\translate('no_of_days')}}</th>
                                    <th class="text-center">{{\App\CPU\translate('Action')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($cou as $k=>$c)
                                <tr>
                                    <td>{{$cou->firstItem() + $k}}</td>
                                    <td>{{substr($c['title'],0,20)}}</td>
                                    <td>{{($c['amount'])}}</td>
                                    <td>{{ $c['discount_amount'] ?? $c['amount'] }}</td> <!-- If discount_amount is null, show amount -->
                                    <td>{{($c['days'])}}</td>
                                    <td>
                                        <div class="d-flex gap-10 justify-content-center">
                                            <!-- Purchase Plan Button -->
                                            <button class="btn btn-primary plan_purchase" data-plan_id="{{$c['id']}}" data-amount="{{ $c['discount_amount'] ?? $c['amount'] }}">Purchase Plan</button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            
                            </tbody>
                        </table>
                    </div>

                    <div class="table-responsive mt-4">
                        <div class="px-4 d-flex justify-content-lg-end">
                            <!-- Pagination -->
                            {{$cou->links()}}
                        </div>
                    </div>

                    @if(count($cou)==0)
                        <div class="text-center p-4">
                            <img class="mb-3 w-160" src="{{asset('public/assets/back-end')}}/svg/illustrations/sorry.svg" alt="Image Description">
                            <p class="mb-0">{{\App\CPU\translate('No data to show')}}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
<script>
    $(document).ready(function() {
        $('.plan_purchase').on('click', function() {
            var planId = $(this).data('plan_id');
            var amount = $(this).data('amount');
            
            Swal.fire({
                title: 'Are you sure?',
                text: "Do you want to purchase this plan?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, purchase it!',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {

                    var options = {
                        "key": "{{ \Illuminate\Support\Facades\Config::get('razor.razor_key') }}",  
                        "amount": amount * 100, 
                        "currency": "INR",
                        "name": "Your Company Name", 
                        "description": "Plan Purchase", 
                        "image": "YOUR_LOGO_URL", 
                        "handler": function (response) {
                            $.ajax({
                                url: '{{route('seller.plan.purchase_plan')}}', 
                                type: 'POST',
                                data: {
                                    plan_id: planId,
                                    amount: amount,
                                    razorpay_payment_id: response.razorpay_payment_id,
                                    _token: '{{ csrf_token() }}' 
                                },
                                success: function(response) {
                                    Swal.fire({
                                        title: 'Success!',
                                        text: response.message || 'Your plan has been successfully purchased.',
                                        icon: 'success',
                                        confirmButtonText: 'Ok'
                                    }).then(() => {
                                        location.reload();
                                    });
                                },
                                error: function(xhr, status, error) {
                                    Swal.fire({
                                        title: 'Error!',
                                        text: 'There was an issue with your purchase. Please try again.',
                                        icon: 'error',
                                        confirmButtonText: 'Ok'
                                    });
                                }
                            });
                        },
                        "prefill": {
                            "name": "Customer Name", 
                            "email": "customer@example.com",
                            "contact": "9999999999"
                        },
                        "notes": {
                            "address": "Razorpay Payment"
                        },
                        "theme": {
                            "color": "#F37254"
                        }
                    };
                    
                    var rzp1 = new Razorpay(options);
                    rzp1.open(); 
                }
            });
        });
    });
</script>
@endpush
