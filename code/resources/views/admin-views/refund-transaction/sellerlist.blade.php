@extends('layouts.back-end.app')

@section('title',\App\CPU\translate('refund_transactions'))

@section('content')
    <div class="content container-fluid ">
        <!-- Page Title -->
        <div class="mb-3">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                <img width="20" src="{{asset('/public/assets/back-end/img/order_report.png')}}" alt="">
                {{ \App\CPU\translate('transaction_report')}}
            </h2>
        </div>
        <!-- End Page Title -->

  
        <div class="card">
            <div class="card-header border-0 px-3 py-4">
                <div class="w-100 d-flex flex-wrap gap-3 align-items-center">
                    <h4 class="mb-0 mr-auto">
                        {{ \App\CPU\translate('total_transaction')}}
                        <span class="badge badge-soft-dark radius-50 fz-14">{{$refund_transactions->total()}}</span>
                    </h4>
                    <!-- <form action="{{ url()->current() }}" method="GET" class="mb-0">
                        <div class="input-group input-group-merge input-group-custom">
                            <div class="input-group-prepend">
                                <div class="input-group-text">
                                    <i class="tio-search"></i>
                                </div>
                            </div>
                            <input id="datatableSearch_" type="search" name="search" class="form-control"
                                   placeholder="{{ \App\CPU\translate('Search by orders id _or_refund_id')}}" aria-label="Search orders"
                                   value="{{ $search }}">
                            <button type="submit" class="btn btn--primary">{{ \App\CPU\translate('search')}}</button>
                        </div>
                    </form> -->
                    <form action="#" id="form-data" method="GET">
                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            <select class="form-control __form-control w-auto" name="payment_method" id="payment_method">
                                <option value="all" {{ $payment_method=='all' ? 'selected': '' }}>{{\App\CPU\translate('all')}}</option>
                                <!-- <option value="cash" {{ $payment_method=='cash' ? 'selected': '' }}>{{\App\CPU\translate('cash')}}</option> -->
                                <option value="customer_wallet" {{ $payment_method=='customer_wallet' ? 'selected': '' }}>{{\App\CPU\translate('customer_wallet')}}</option>
                                <option value="digitally_paid" {{ $payment_method=='digitally_paid' ? 'selected': '' }}>{{\App\CPU\translate('digitally_paid')}}</option>
                            </select>
                            <button type="submit" class="btn btn--primary px-4 min-w-120 __h-45px" onclick="formUrlChange(this)"
                                    data-action="{{ url()->current() }}">
                                {{\App\CPU\translate('filter')}}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="table-responsive">
                <table id="datatable"
                       style="text-align: {{Session::get('direction') === "rtl" ? 'right' : 'left'}};"
                       class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100 __table-refund">
                    <thead class="thead-light thead-50 text-capitalize">
                    <tr>
                        <th>{{\App\CPU\translate('SL')}}</th>
                        <th>{{\App\CPU\translate('Seller')}}</th>
                        <th>{{\App\CPU\translate('Shop')}}</th>
                        <th>{{\App\CPU\translate('Total Refund')}}</th>
                        <th>{{\App\CPU\translate('Total Amount')}}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($refund_transactions as $key=>$refund_transaction)
                        <tr class="text-capitalize">
                            <td>
                                 #
                            </td>
                            <td>
                                <a href="{{ route('admin.sellers.view', [$refund_transaction->payer_id]) }}">
                                    {{ $refund_transaction->f_name }} {{ $refund_transaction->l_name }} <br> 
                                    {{ $refund_transaction->unique_code }}
                                </a>
                            </td>
                            <td>
                                {{ $refund_transaction->shop_name }}
                            </td>
                            <td>
                                {{ $refund_transaction->total_refunds }}
                            </td>
                            <td>
                                {{ $refund_transaction->total_amount }}
                            </td>
                            
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                @if(count($refund_transactions)==0)
                    <div class="text-center p-4">
                        <img class="mb-3 w-160" src="{{asset('public/assets/back-end')}}/svg/illustrations/sorry.svg"
                             alt="Image Description">
                        <p class="mb-0">{{ \App\CPU\translate('No_data_to_show')}}</p>
                    </div>
                @endif
            </div>

            <div class="table-responsive mt-4">
                <div class="px-4 d-flex justify-content-lg-end">
                    <!-- Pagination -->
                    {{$refund_transactions->links()}}
                </div>
            </div>
        </div>
    </div>
@endsection
