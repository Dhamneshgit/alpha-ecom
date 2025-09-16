@extends('layouts.back-end.app')

@section('title', \App\CPU\translate('Kyc Info'))

@push('css_or_js')
    <!-- Custom styles for this page -->
    <link href="{{asset('public/assets/back-end')}}/vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <!-- Custom styles for this page -->
    <link href="{{asset('public/assets/back-end/css/croppie.css')}}" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
    <div class="content container-fluid" style="text-align: {{Session::get('direction') === "rtl" ? 'right' : 'left'}};">
        <!-- Page Title -->
        <div class="mb-3">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                <img width="20" src="{{asset('/public/assets/back-end/img/my-bank-info.png')}}" alt="">
                {{\App\CPU\translate('Upadte_KYC_info')}}
            </h2>
        </div>
        <!-- End Page Title -->

        <!-- Content Row -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0 ">{{\App\CPU\translate('Upadte_KYC_info')}}</h4>

                        @if(isset($data))
                            @if($data->status == 1)
                            <span class="badge badge-success">Approved</span>
                            @elseif($data->status == 2)
                            <span class="badge badge-danger">Reject</span>
                            @else
                            <span class="badge badge-warning">Pending</span>
                            @endif
                        @else   
                        <span class="badge badge-warning">Pending</span> 
                        @endif
                        <span class="badge"></span>
                    </div>
                    <div class="card-body">
                        <form action="{{route('admin.dashboard.update_kyc')}}" method="post"
                              enctype="multipart/form-data">
                            @csrf
                            <div class="form-group">
                                <div class="row">
                                    <input type="hidden" name="kyc_id" value="{{@$data->id ?? ''}}"/>
                                    <input type="hidden" name="user_id" value="{{auth('admin')->id()}}"/>
                                    <div class="col-md-6 mb-3">
                                        <label for="name" class="title-color">{{\App\CPU\translate('Bank Name')}} <span class="text-danger">*</span></label>
                                        <input type="text" name="bank_name" value="{{@$data->bank_name ?? ''}}"
                                               class="form-control" id="name"
                                               required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="account_no" class="title-color">{{\App\CPU\translate('Holder Name')}} <span class="text-danger">*</span></label>
                                        <input type="text" name="holder_name" value="{{@$data->holder_name ?? ''}}"
                                        class="form-control" id="account_no"
                                        required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="account_no" class="title-color">{{\App\CPU\translate('Account Number')}} <span class="text-danger">*</span></label>
                                        <input type="number" name="account_number" value="{{@$data->account_number ?? ''}}"
                                        class="form-control" id="account_no"
                                        required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="name" class="title-color">{{\App\CPU\translate('IFSC')}} <span class="text-danger">*</span></label>
                                        <input type="text" name="ifsc" value="{{@$data->ifsc ?? ''}}" class="form-control"
                                               id="name"
                                               required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="name" class="title-color">{{\App\CPU\translate('Nomini Name')}}</label>
                                        <input type="text" name="nomini_name" value="{{@$data->nomini_name ?? ''}}" class="form-control" id="name"
                                               >
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="name" class="title-color">{{\App\CPU\translate('Nomini Relation')}}</label>
                                        <input type="text" name="nomini_relation" value="{{@$data->nomini_relation ?? ''}}" class="form-control"
                                               id="name"
                                               >
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="name" class="title-color">{{\App\CPU\translate('Aadhar Number')}} <span class="text-danger">*</span></label>
                                        <input type="text" name="adhar_number" value="{{@$data->adhar_number ?? ''}}" class="form-control"
                                               id="name"
                                               required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="name" class="title-color">{{\App\CPU\translate('Pan Number')}} <span class="text-danger">*</span></label>
                                        <input type="text" name="pan_number" value="{{@$data->pan_number ?? ''}}" class="form-control"
                                               id="name"
                                               required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="name" class="title-color">{{\App\CPU\translate('Pan Image')}} </label>
                                        <input type="file" name="pan_image" class="form-control" >
                                        <a href="{{ @$data->pan_image ? asset('public/images/' . @$data->pan_image) : asset('public/assets/front-end/img/image-place-holder.png') }}" target="_blank">
                                            <img class="avatar rounded-circle avatar-70" onerror="this.src='{{asset('public/assets/front-end/img/image-place-holder.png')}}'"
                                            src="{{asset('public/images/'.@$data->pan_image ??'')}}" alt="Image">
                                        </a>   
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="name" class="title-color">{{\App\CPU\translate('Aadhar Front')}} </label>
                                        <input type="file" name="adhar_front" class="form-control" >
                                        <a href="{{ @$data->adhar_front ? asset('public/images/' . @$data->adhar_front) : asset('public/assets/front-end/img/image-place-holder.png') }}" target="_blank">
                                            <img class="avatar rounded-circle avatar-70" onerror="this.src='{{asset('public/assets/front-end/img/image-place-holder.png')}}'"
                                            src="{{asset('public/images/'.@$data->adhar_front ??'')}}" alt="Image">
                                        </a>  
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="name" class="title-color">{{\App\CPU\translate('Aadhar Back')}} </label>
                                        <input type="file" name="adhar_back" class="form-control" >
                                        <a href="{{ @$data->adhar_back ? asset('public/images/' . @$data->adhar_back) : asset('public/assets/front-end/img/image-place-holder.png') }}" target="_blank">
                                            <img class="avatar rounded-circle avatar-70" onerror="this.src='{{asset('public/assets/front-end/img/image-place-holder.png')}}'"
                                            src="{{asset('public/images/'.@$data->adhar_back ??'')}}" alt="Image">
                                        </a> 
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="name" class="title-color">{{\App\CPU\translate('Passbook/Chaque Image')}} </label>
                                        <input type="file" name="passbook_image" class="form-control" >
                                        <a href="{{ @$data->passbook_image ? asset('public/images/' . @$data->passbook_image) : asset('public/assets/front-end/img/image-place-holder.png') }}" target="_blank">
                                            <img class="avatar rounded-circle avatar-70" onerror="this.src='{{asset('public/assets/front-end/img/image-place-holder.png')}}'"
                                            src="{{asset('public/images/'.@$data->passbook_image ??'')}}" alt="Image">
                                        </a>
                                    </div>
                                    
                                    
                                </div>

                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <!-- <a class="btn btn-danger" href="{{route('seller.profile.view')}}">{{\App\CPU\translate('Cancel')}}</a> -->
                                <button type="submit" class="btn btn--primary" id="btn_update">{{\App\CPU\translate('Update')}}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')

@endpush
