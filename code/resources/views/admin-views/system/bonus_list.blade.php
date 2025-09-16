@extends('layouts.back-end.app')

@section('title', \App\CPU\translate('Bonus Earning'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Title -->
        <div class="mb-3">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                <img width="20" src="{{asset('/public/assets/back-end/img/withdraw-icon.png')}}" alt="">
                {{\App\CPU\translate('Bonus Earning ')}}
            </h2>
        </div>
        <!-- End Page Title -->

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header flex-wrap gap-2">
                        <h5 class="mb-0 text-capitalize">{{ \App\CPU\translate('Withdraw Request Table')}}
                            <span class="badge badge-soft-dark radius-50 fz-12 ml-1">{{ $cou->total() }}</span>
                        </h5>
                        <select name="withdraw_status_filter" onchange="status_filter(this.value)" class="custom-select max-w-200  d-none">
                            <option value="all" {{session()->has('withdraw_status_filter') && session('withdraw_status_filter') == 'all'?'selected':''}}>{{\App\CPU\translate('All')}}</option>
                            <option value="approved" {{session()->has('withdraw_status_filter') && session('withdraw_status_filter') == 'approved'?'selected':''}}>{{\App\CPU\translate('Approved')}}</option>
                            <option value="denied" {{session()->has('withdraw_status_filter') && session('withdraw_status_filter') == 'denied'?'selected':''}}>{{\App\CPU\translate('Denied')}}</option>
                            <option value="pending" {{session()->has('withdraw_status_filter') && session('withdraw_status_filter') == 'pending'?'selected':''}}>{{\App\CPU\translate('Pending')}}</option>
                        </select>
                    </div>

                    <div class="table-responsive">
                        <table id="datatable"
                                class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table {{ Session::get('direction') === 'rtl' ? 'text-right' : 'text-left' }}">
                            <thead class="thead-light thead-50 text-capitalize">
                            <tr>
                                <th>{{\App\CPU\translate('SL')}}</th>
                                <th>{{\App\CPU\translate('Shop Name')}}</th>
                                <th class="d-none">{{\App\CPU\translate('Parent Type')}}</th>
                                <th>{{\App\CPU\translate('Referral Name')}}</th>
                                <th>{{\App\CPU\translate('Bonus Type')}}</th>
                                <th>{{\App\CPU\translate('Amount')}}</th>
                                <th class="d-none">{{\App\CPU\translate('Referral Level')}}</th>
                                <th>{{\App\CPU\translate('Transaction')}}</th>
                                <th>{{\App\CPU\translate('transaction_date')}}</th>
                                <!-- <th>{{\App\CPU\translate('expire_date')}}</th> -->
                                <!-- <th>{{\App\CPU\translate('Status')}}</th>
                                <th class="text-center">{{\App\CPU\translate('Action')}}</th> -->
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($cou as $k=>$c)
                                <tr>
                                    <td >{{$cou->firstItem() + $k}}</td>
                                    @if($c['parent_type'] == 'user')
                                        @php
                                        $users = \DB::table('users')->select('f_name','l_name')->where('id',$c['parent_id'])->first();
                                        @endphp
                                         <td>
                                            <div>{{substr($users->f_name,0,20)}}  {{substr($users->l_name,0,20)}}</div>
                                        </td>
                                    @elseif($c['parent_type'] == 'shop')
                                        @php
                                        $users = \DB::table('shops')->select('name')->where('id',$c['parent_id'])->first();
                                        @endphp
                                         <td>
                                            <div>{{substr($users->name,0,20)}} </div>
                                        </td>
                                    @else
                                        @php
                                        $users = \DB::table('admins')->select('name')->where('id',$c['parent_id'])->first();
                                        @endphp
                                         <td>
                                            <div>{{substr($users->name ?? '',0,20)}} </div>
                                        </td>
                                    @endif
                                    <!-- <td>
                                        <div>{{substr($c['parent_f_name'],0,20)}}  {{substr($c['parent_l_name'],0,20)}}</div>
                                    </td> -->
                                    <td class="d-none">
                                        <div>{{ucfirst($c['parent_type'])}}</div>
                                    </td>
                                    <td>
                                        <div>{{substr($c['referral_f_name'],0,20)}}  {{substr($c['referral_l_name'],0,20)}}</div>
                                    </td>
                                    <td>
                                        <div>{{ucfirst(str_replace("_"," ",$c['type']))}}</div>
                                    </td>
                                    <td>
                                        <div>{{($c['amount'])}}</div>
                                        
                                    </td>
                                    <td class="d-none">
                                        <div>{{($c['level']) ?? '--'}}</div>
                                        
                                    </td>
                                    <td>
                                        <div>{{($c['transaction']) ?? '--'}}</div>
                                        
                                    </td>
                                    <!-- <td>
                                        <div>{{($c['discount_amount'])}}</div>
                                    </td> -->
                                    <!-- <td>
                                        <div>{{($c['days'])}}</div>
                                        
                                    </td> -->
                                    <td>
                                        <div>{{ \Carbon\Carbon::parse($c['created_at'])->format('Y-m-d') }}</div>
                                    </td>
                                    <!-- <td>
                                        <div>{{($c['expire_date'])}}</div>
                                        
                                    </td> -->
                                    <!-- <td>
                                        <div>{{($c['level'])}}</div>
                                        
                                    </td> -->
                                    
                                    <!-- <td>
                                        <label class="switcher">
                                            <input type="checkbox" class="switcher_input"
                                                    onclick="location.href='{{route('admin.plan.status',[$c['id'],$c->status?0:1])}}'"
                                                    class="toggle-switch-input" {{$c->status?'checked':''}}>
                                            <span class="switcher_control"></span>
                                        </label>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-10 justify-content-center">
                                            <button class="btn btn-outline--primary square-btn btn-sm mr-1" onclick="get_details(this)" data-id="{{ $c['id'] }}" data-toggle="modal" data-target="#exampleModalCenter">
                                                <img src="{{asset('/public/assets/back-end/img/eye.svg')}}" class="svg" alt="">
                                            </button>
                                            <a class="btn btn-outline--primary btn-sm edit"
                                            href="{{route('admin.plan.update',[$c['id']])}}"
                                            title="{{ \App\CPU\translate('Edit')}}"
                                            >
                                                <i class="tio-edit"></i>
                                            </a>
                                            <a class="btn btn-outline-danger btn-sm delete"
                                                href="javascript:"
                                                onclick="form_alert('coupon-{{$c['id']}}','Want to delete this coupon ?')"
                                                title="{{\App\CPU\translate('delete')}}"
                                                >
                                                <i class="tio-delete"></i>
                                            </a>
                                            <form action="{{route('admin.coupon.delete',[$c['id']])}}"
                                                method="post" id="coupon-{{$c['id']}}">
                                                @csrf @method('delete')
                                            </form> 
                                        </div>

                                    </td> -->
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
                </div>
            </div>

        </div>

    </div>
@endsection


@push('script_2')
  <script>
      function status_filter(type) {
          $.ajaxSetup({
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              }
          });
          $.post({
              url: '{{route('seller.business-settings.withdraw.status-filter')}}',
              data: {
                  withdraw_status_filter: type
              },
              beforeSend: function () {
                  $('#loading').show()
              },
              success: function (data) {
                 location.reload();
              },
              complete: function () {
                  $('#loading').hide()
              }
          });
      }
  </script>

  <script>
      function close_request(route_name) {
          swal({
              title: "{{\App\CPU\translate('Are you sure?')}}",
              text: "{{\App\CPU\translate('Once deleted, you will not be able to recover this')}}",
              icon: "{{\App\CPU\translate('warning')}}",
              buttons: true,
              dangerMode: true,
              confirmButtonText: "{{\App\CPU\translate('OK')}}",
          })
              .then((willDelete) => {
                  if (willDelete.value) {
                      window.location.href = (route_name);
                  }
              });
      }
  </script>
@endpush
