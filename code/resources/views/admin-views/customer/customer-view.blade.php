@extends('layouts.back-end.app')
{{--@section('title','Customer')--}}
@section('title', \App\CPU\translate('Customer Details'))

@push('css_or_js')

@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="d-print-none pb-2">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">

                    <!-- Page Title -->
                    <div class="mb-3">
                        <h2 class="h1 mb-0 text-capitalize d-flex gap-2">
                            <img width="20" src="{{asset('/public/assets/back-end/img/customer.png')}}" alt="">
                            {{\App\CPU\translate('customer_details')}}
                        </h2>
                    </div>
                    <!-- End Page Title -->

                    <div class="d-sm-flex align-items-sm-center">
                        <h3 class="page-header-title">{{\App\CPU\translate('Customer ID')}} #{{$customer['id']}}</h3>
                        <span class="{{Session::get('direction') === "rtl" ? 'mr-2 mr-sm-3' : 'ml-2 ml-sm-3'}}">
                        <i class="tio-date-range">
                        </i> {{\App\CPU\translate('Joined At')}} : {{date('d M Y H:i:s',strtotime($customer['created_at']))}}
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Page Header -->

        <div class="row" id="printableArea">
            <div class="col-lg-8 mb-3 mb-lg-0">
                <div class="card">
                    <div class="p-3">
                        <div class="row justify-content-end">
                            <div class="col-auto">
                                <form action="{{ url()->current() }}" method="GET">
                                    <!-- Search -->
                                    <div class="input-group input-group-merge input-group-custom">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text">
                                                <i class="tio-search"></i>
                                            </div>
                                        </div>
                                        <input id="datatableSearch_" type="search" name="search" class="form-control"
                                            placeholder="{{\App\CPU\translate('Search orders')}}" aria-label="Search orders" value="{{ $search }}"
                                            required>
                                        <button type="submit" class="btn btn--primary">{{\App\CPU\translate('search')}}</button>
                                    </div>
                                    <!-- End Search -->
                                </form>
                            </div>
                        </div>
                    </div>
                    <!-- Table -->
                    <div class="table-responsive datatable-custom">
                        <table
                               class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100">
                            <thead class="thead-light thead-50 text-capitalize">
                            <tr>
                                <th>{{\App\CPU\translate('sl')}}</th>
                                <th>{{\App\CPU\translate('Order ID')}}</th>
                                <th>{{\App\CPU\translate('Total')}}</th>
                                <th class="text-center">{{\App\CPU\translate('Action')}}</th>
                            </tr>

                            </thead>

                            <tbody>
                            @foreach($orders as $key=>$order)
                                <tr>
                                    <td>{{$orders->firstItem()+$key}}</td>
                                    <td>
                                        <a href="{{route('admin.orders.details',['id'=>$order['id']])}}" class="title-color hover-c1">{{$order['id']}}</a>
                                    </td>
                                    <td> {{\App\CPU\BackEndHelper::set_symbol(\App\CPU\BackEndHelper::usd_to_currency($order['order_amount']))}}</td>

                                    <td>
                                        <div class="d-flex justify-content-center gap-10">
                                            <a class="btn btn-outline--primary btn-sm edit square-btn"
                                                title="{{\App\CPU\translate('View')}}"
                                                href="{{route('admin.orders.details',['id'=>$order['id']])}}"><i
                                                    class="tio-invisible"></i> </a>
                                            <a class="btn btn-outline-info btn-sm square-btn"
                                                title="{{\App\CPU\translate('Invoice')}}"
                                                target="_blank"
                                                href="{{route('admin.orders.generate-invoice',[$order['id']])}}"><i
                                                    class="tio-download"></i> </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        @if(count($orders)==0)
                            <div class="text-center p-4">
                                <img class="mb-3 w-160" src="{{asset('public/assets/back-end')}}/svg/illustrations/sorry.svg" alt="Image Description">
                                <p class="mb-0">{{ \App\CPU\translate('No_data_to_show')}}</p>
                            </div>
                        @endif
                        <!-- Footer -->
                        <div class="card-footer">
                            <!-- Pagination -->
                        {!! $orders->links() !!}
                        <!-- End Pagination -->
                        </div>
                        <!-- End Footer -->
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <!-- Card -->
                <div class="card">
                    @if($customer)
                        <div class="card-body">
                            <h4 class="mb-4 d-flex align-items-center gap-2">
                                <img src="{{asset('/public/assets/back-end/img/seller-information.png')}}" alt="">
                                {{\App\CPU\translate('Customer')}}
                            </h4>

                            <div class="media">
                                <div class="mr-3">
                                    <img
                                        class="avatar rounded-circle avatar-70"
                                        onerror="this.src='{{asset('public/assets/front-end/img/image-place-holder.png')}}'"
                                        src="{{asset('storage/app/public/profile/'.$customer->image??'')}}"
                                        alt="Image">
                                </div>
                                <div class="media-body d-flex flex-column gap-1">
                                    <span class="title-color hover-c1"><strong>{{$customer['f_name'].' '.$customer['l_name']}}</strong></span>
                                    <span class="title-color">
                                        <strong>{{\App\Model\Order::where('customer_id',$customer['id'])->count()}} </strong>{{\App\CPU\translate('orders')}}
                                    </span>
                                    <span class="title-color"><strong>{{$customer['phone']}}</strong></span>
                                    <span class="title-color">{{$customer['email']}}</span>
                                    <span class="title-color d-none">Fund Wallet :- Rs.{{$customer['fund_wallet'] ?? 0}} /-</span>
                                    <button type="button" class="btn btn-primary d-none" data-toggle="modal" data-target="#exampleModal">
                                     Add Money
                                    </button>

                                    <div class="modal fade d-none" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                        <form action="{{route('admin.customer.add_money')}}" method="POST">
                                            @csrf
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="exampleModalLabel">Add Money</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row">
                                                        <input type="hidden" name="user_id" value="{{$customer['id']}}">
                                                        <div class="col-4">
                                                            <lable>
                                                                Amount : 
                                                            </lable>         
                                                        </div>          
                                                        <div class="col-8">
                                                            <input name="amount" class="form-control" value="">         
                                                        </div>          
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                    <button type="submit" class="btn btn-primary">Send Money</button>
                                                </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>

                                </div>
                                <div class="media-body text-right">
                                    {{--<i class="tio-chevron-right text-body"></i>--}}
                                </div>
                            </div>
                        </div>
                @endif

                <!-- End Body -->
                </div>
                <div class="card mt-2 d-none">
                    @if($kyc)
                        <div class="card-body">
                            <h4 class="mb-4 d-flex align-items-center gap-2">
                                <img src="{{asset('/public/assets/back-end/img/seller-information.png')}}" alt="">
                                {{\App\CPU\translate('Customer Kyc')}}
                            </h4>
                            <div class="d-flex mb-2">
                             @if($kyc->status == 0)
                                <a href="{{asset('admin/customer/kycstatus/'.$customer['id'].'/1')}}" class="btn btn-primary">Approve</a>
                                <a href="{{asset('admin/customer/kycstatus/'.$customer['id'].'/2')}}" class="btn btn-warning">Reject</a>
                                @elseif($kyc->status == 1)
                                <span class="title-color hover-c1"><strong>Approved</strong></span>
                                @else
                                <span class="title-color hover-c1"><strong>Rejected</strong></span>
                                @endif
                            </div>

                            <div class="media d-flex flex-column gap-1">
                                <span class="title-color hover-c1"><strong>Account Holder Name : {{ $kyc->holder_name}}</strong></span>
                                <span class="title-color hover-c1"><strong>Account Number : {{ $kyc->account_number}}</strong></span>
                                <span class="title-color hover-c1"><strong>Bank Name : {{ $kyc->bank_name}}</strong></span>
                                <span class="title-color hover-c1"><strong>IFSC : {{ $kyc->ifsc}}</strong></span>
                                <span class="title-color hover-c1"><strong>Pan Number : {{ $kyc->pan_number}}</strong></span>
                                <span class="title-color hover-c1"><strong>Aadhar Number : {{ $kyc->adhar_number}}</strong></span>
                                <span class="title-color hover-c1"><strong>Nomini Name : {{ $kyc->nomini_name}}</strong></span>
                                <span class="title-color hover-c1"><strong>Nomini Relation : {{ $kyc->nomini_relation}}</strong></span>
                                
                                <span class="title-color"><strong> Pan Image : </span>
                                    <a href="{{ $kyc->pan_image ? asset('public/images/' . $kyc->pan_image) : asset('public/assets/front-end/img/image-place-holder.png') }}" target="_blank">
                                        <img class="avatar rounded-circle avatar-70" onerror="this.src='{{asset('public/assets/front-end/img/image-place-holder.png')}}'"
                                        src="{{asset('public/images/'.$kyc->pan_image ??'')}}" alt="Image">
                                    </a>    
                                <span class="title-color"><strong> Aadhar Front Image : </span>
                                    <a href="{{ $kyc->adhar_front ? asset('public/images/' . $kyc->adhar_front) : asset('public/assets/front-end/img/image-place-holder.png') }}" target="_blank">
                                        <img class="avatar rounded-circle avatar-70" onerror="this.src='{{asset('public/assets/front-end/img/image-place-holder.png')}}'"
                                        src="{{asset('public/images/'.$kyc->adhar_front ??'')}}" alt="Image">
                                    </a>    
                                <span class="title-color"><strong> Aadhar Back Image : </span>
                                    <a href="{{ $kyc->adhar_back ? asset('public/images/' . $kyc->adhar_back) : asset('public/assets/front-end/img/image-place-holder.png') }}" target="_blank">
                                        <img class="avatar rounded-circle avatar-70" onerror="this.src='{{asset('public/assets/front-end/img/image-place-holder.png')}}'"
                                        src="{{asset('public/images/'.$kyc->adhar_back ??'')}}" alt="Image">
                                    </a>    
                                <span class="title-color"><strong> Passbook/Cheque Image : </span>
                                    <a href="{{ $kyc->passbook_image ? asset('public/images/' . $kyc->passbook_image) : asset('public/assets/front-end/img/image-place-holder.png') }}" target="_blank">
                                        <img class="avatar rounded-circle avatar-70" onerror="this.src='{{asset('public/assets/front-end/img/image-place-holder.png')}}'"
                                        src="{{asset('public/images/'.$kyc->passbook_image ??'')}}" alt="Image">
                                    </a>
                            </div>
                        </div>
                    @else 
                        <div class="card-body">
                                <h4 class="mb-4 d-flex align-items-center gap-2">
                                    <img src="{{asset('/public/assets/back-end/img/seller-information.png')}}" alt="">
                                    {{\App\CPU\translate('Customer Kyc')}}
                                </h4>

                                <div class="media d-flex flex-column gap-1">
                                    <span class="title-color hover-c1"><strong> No Kyc Found </span>
                                </div>
                            </div>   
                    @endif

                <!-- End Body -->
                </div>
                <!-- End Card -->
            </div>
        </div>
        <!-- End Row -->
    </div>
@endsection

@push('script_2')

@endpush
