@extends('layouts.back-end.app')
@section('title', \App\CPU\translate('Plan Edit'))
@push('css_or_js')
<link href="{{ asset('public/assets/select2/css/select2.min.css')}}" rel="stylesheet">
<link href="{{ asset('public/assets/back-end/css/custom.css')}}" rel="stylesheet">
@endpush

@section('content')
<div class="content container-fluid">
    <!-- Page Title -->
    <div class="mb-3">
        <h2 class="h1 mb-0 text-capitalize">
            <img src="{{asset('/public/assets/back-end/img/coupon_setup.png')}}" class="mb-1 mr-1" alt="">
            {{\App\CPU\translate('plan_update')}}
        </h2>
    </div>
    <!-- End Page Title -->

    <!-- Content Row -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">

                <div class="card-body">
                    <form action="{{route('admin.plan.update',[$plan['id']])}}" method="post">
                        @csrf
                        <div class="row">

                            <div class="col-md-6 col-lg-3 form-group">
                                <label for="name"
                                    class="title-color text-capitalize">{{\App\CPU\translate('plan_title')}}</label>
                                <input type="text" name="title" class="form-control" id="title"
                                    value="{{$plan['title']}}" placeholder="{{\App\CPU\translate('Title')}}" required>
                            </div>

                            <div class="col-md-6 col-lg-4 form-group first_order">
                                <label
                                    for="exampleFormControlInput1" class="title-color font-weight-medium d-flex">{{\App\CPU\translate('limit')}} {{\App\CPU\translate('for')}} {{\App\CPU\translate('same')}} {{\App\CPU\translate('user')}}</label>
                                <input type="number" name="limit" value="" min="0" id="coupon_limit" class="form-control"
                                        placeholder="{{\App\CPU\translate('EX')}}: 10">
                            </div>

                            <div class="col-md-6 col-lg-3 form-group free_delivery">
                                <label for="name"
                                    class="title-color font-weight-medium d-flex">{{\App\CPU\translate('Plan_Amount')}}
                                </label>
                                <input type="number" min="1" max="1000000" name="amount" value="{{$plan['amount']}}"
                                    class="form-control" id="discount" placeholder="{{\App\CPU\translate('Ex: 500')}}">
                            </div>

                            <div class="col-md-6 col-lg-3 form-group free_delivery">
                                <label for="name"
                                    class="title-color font-weight-medium d-flex">{{\App\CPU\translate('Plan_Discount_Amount')}}</label>
                                <input type="number" min="1" max="1000000" name="discount"
                                    value="{{$plan['discount_amount']}}" class="form-control" id="discount"
                                    placeholder="{{\App\CPU\translate('Ex: 500')}}">
                            </div>

                            <div class="col-md-6 col-lg-3 form-group">
                                <label for="name" class="title-color font-weight-medium d-flex">Plan Validity in No. of
                                    Days</label>
                                <input type="number" min="1" max="1000" name="days" value="{{$plan['days']}}"
                                    class="form-control" id="minimum purchase"
                                    placeholder="{{\App\CPU\translate('Ex: 100')}}" required>
                            </div>

                            <div class="col-md-6 col-lg-3 form-group">
                                <label for="name" class="title-color font-weight-medium d-flex">{{\App\CPU\translate('seller_type')}}</label>
                                <select class="form-control w-100" name="seller_type">
                                    <option value="goods" {{ $plan['seller_type'] == 'goods' ? 'selected' : ''}}>{{\App\CPU\translate('Goods')}}</option>
                                    <option value="service" {{ $plan['seller_type'] == 'service' ? 'selected' : ''}}>{{\App\CPU\translate('Service')}}</option>
                                    <option value="both" {{ $plan['seller_type'] == 'both' ? 'selected' : ''}}>{{\App\CPU\translate('Both')}}</option>
                                </select>
                            </div>
                             <!-- <input type="number" name="days" value="{{$plan['days']}}"
                                    class="form-control"> -->

                            <div class="col-md-12 col-lg-12 form-group">
                                <label for="name" class="title-color font-weight-medium d-flex">Description</label>
                                <textarea class="form-control" name="description" id="editor"
                                    rows="2">{{$plan['description']}}</textarea>

                            </div>

                            <!-- <div class="col-md-4 col-lg-3 form-group">
                                <label for="name" class="title-color font-weight-medium d-flex">{{\App\CPU\translate('Frenchise_bonus')}}</label>
                                <input id="daily_bonus" type="text" name="frenchise_bonus" value="{{ $plan['frenchise_bonus'] }}" class="form-control"
                                    placeholder="{{\App\CPU\translate('frenchise_bonus')}}" required>
                            </div>
                            <div class="col-md-4 col-lg-3 form-group">
                                <label for="name" class="title-color font-weight-medium d-flex">{{\App\CPU\translate('district_bonus')}}</label>
                                <input id="daily_bonus" type="text" name="district_bonus" value="{{ $plan['district_bonus'] }}" class="form-control"
                                    placeholder="{{\App\CPU\translate('district_bonus')}}" required>
                            </div>
                            <div class="col-md-4 col-lg-3 form-group">
                                <label for="name" class="title-color font-weight-medium d-flex">{{\App\CPU\translate('state_bonus')}}</label>
                                <input id="daily_bonus" type="text" name="state_bonus" value="{{ $plan['state_bonus'] }}" class="form-control"
                                    placeholder="{{\App\CPU\translate('state_bonus')}}" required>
                            </div>
                            <div class="col-md-4 col-lg-3 form-group">
                                <label for="name" class="title-color font-weight-medium d-flex">{{\App\CPU\translate('shop_bonus')}}</label>
                                <input id="daily_bonus" type="text" name="shop_bonus" value="{{ $plan['shop_bonus'] }}" class="form-control"
                                    placeholder="{{\App\CPU\translate('shop_bonus')}}" required>
                            </div>

                            <div class="col-md-4 col-lg-4 form-group">
                                <label for="name" class="title-color font-weight-medium d-flex">{{\App\CPU\translate('Re-purchase_Frenchise_bonus_(%)')}}</label>
                                <input id="daily_bonus" type="text" name="repurchase_frenchise_bonus" value="{{ $plan['repurchase_frenchise_bonus'] }}" class="form-control"
                                    placeholder="{{\App\CPU\translate('repurchase_frenchise_bonus')}}" required>
                            </div>
                            <div class="col-md-4 col-lg-4 form-group">
                                <label for="name" class="title-color font-weight-medium d-flex">{{\App\CPU\translate('Re-purchase_district_bonus_(%)')}}</label>
                                <input id="daily_bonus" type="text" name="repurchase_district_bonus" value="{{ $plan['repurchase_district_bonus'] }}" class="form-control"
                                    placeholder="{{\App\CPU\translate('repurchase_district_bonus')}}" required>
                            </div>
                            <div class="col-md-4 col-lg-4 form-group">
                                <label for="name" class="title-color font-weight-medium d-flex">{{\App\CPU\translate('Re-purchase_state_bonus_(%)')}}</label>
                                <input id="daily_bonus" type="text" name="repurchase_state_bonus" value="{{ $plan['repurchase_state_bonus'] }}" class="form-control"
                                    placeholder="{{\App\CPU\translate('repurchase_state_bonus')}}" required>
                            </div>

                            <div class="col-md-6 col-lg-3 form-group">
                                <label for="name" class="title-color font-weight-medium d-flex">{{\App\CPU\translate('Daily Bonus Till Days')}}</label>
                                <input id="daily_bonus" type="number" name="daily_bonus_till_days" value="{{$plan['daily_bonus_till_days']}}" class="form-control"
                                    placeholder="{{\App\CPU\translate('Daily Bonus till Days')}}" required>
                            </div>
                            <div class="col-md-6 col-lg-3 form-group">
                                <label for="name" class="title-color font-weight-medium d-flex">{{\App\CPU\translate('Daily Bonus Limit')}}</label>
                                <input id="daily_bonus_limit" type="number" name="daily_bonus_limit" value="{{$plan['daily_bonus_limit']}}" class="form-control"
                                    placeholder="{{\App\CPU\translate('daily_bonus_limit')}}" required>
                            </div>
                            <div class="col-md-6 col-lg-4 form-group">
                                <label for="name" class="title-color font-weight-medium d-flex">{{\App\CPU\translate('customer_self_purchase_bonus_in_(%)')}}</label>
                                <input id="daily_bonus_limit" type="number" name="self_purchase_bonus" value="{{ $plan['self_purchase_bonus'] }}" class="form-control"
                                    placeholder="{{\App\CPU\translate('self_purchase_bonus')}}" required>
                            </div> -->
                            <!-- <div class="col-md-6 col-lg-4 form-group" id="max-discount">
                                <label for="name" class="title-color font-weight-medium d-flex">{{\App\CPU\translate('Plan_levels')}}</label>
                                <input type="number" min="1" max="1000000" name="plan_level" value="{{$plan['level']}}"
                                    class="form-control" id="num_fields"
                                    placeholder="{{\App\CPU\translate('Ex: 10')}}" >
                            </div>

                            <div class="col-md-12 col-lg-8 form-group" id="input_fields">
                            </div> -->
                        </div>

                        <div class="d-flex align-items-center justify-content-end flex-wrap gap-10">

                            <button type="reset" class="btn btn-secondary px-4">{{\App\CPU\translate('reset')}}</button>
                            <button type="submit"
                                class="btn btn--primary px-4">{{\App\CPU\translate('Update')}}</button>
                        </div>
                    </form>
                </div>

                <div class="card-body d-none">
                    <h3 class="title" id="title">Levels</h3>
                    <div class="coupon-info-item">
                        @if(isset($level))
                        @foreach($level as $key=>$value)
                        <form action="{{route('admin.plan.level_update')}}" method="post" class="my-1">
                            @csrf
                        
                                    <div class=" row align-items-end justify-content-between">
                                        <!--<div class="col-lg-1 col-md-3">
                                            <input type="hidden" name="level_id" value={{$value->id}}>
                                            <label class="title-color font-weight-medium ">{{$value->level}}</label>
                                        </div>
                                        <div class="col-lg-3 col-md-3">
                                            <input type="text" class="form-control " name="amount"
                                                value={{$value->amount}}>
                                        </div>
                                        <div class="col-lg-1 col-md-3">
                                            <label class="title-color font-weight-medium ">Daily Bonus</label>
                                        </div>
                                        <div class="col-lg-3 col-md-3">
                                            <input type="text" class="form-control " name="daily_bonus"
                                                value={{$value->daily_bonus}}>
                                        </div>
                                        <div class="col-lg-4 col-md-3">
                                            <button class="btn  btn-outline-primary" type="submit">Update</button>
                                            <a class="btn btn-outline-danger delete"
                                                href="{{route('admin.plan.delete-level',[$value->id])}}"
                                                title="{{\App\CPU\translate('delete')}}">
                                                Delete
                                            </a>
                                        </div>-->
                                            <input type="hidden" name="level_id" value="{{$value->id}}">
                                            <div class="col-lg-2 col-md-4 col-6">
                                                <label class="title-color font-weight-medium ">{{$value->level}}</label>
                                                <input type="text" class="form-control" name="amount" value="{{$value->amount}}"/>
                                            </div>
                                            <div class="col-lg-2 col-md-4 col-6">
                                                <label class="title-color font-weight-medium "> Daily Bonus {{$value->level_id}} </label>
                                                <input type="text" class="form-control " name="daily_bonus"
                                                value="{{$value->daily_bonus}}" />
                                            </div>
                                            <div class="col-lg-2 col-md-4 col-6">
                                                <label class="title-color font-weight-medium "> Repurchase Level-{{$value->level_id}}</label>
                                                <input type="text" class="form-control " name="repurchase_income" value="{{$value->repurchase_income}}"/>
                                            </div>
                                            <div class="col-lg-2 col-md-4 col-6">
                                                <label class="title-color font-weight-medium "> Frenchise Level-{{$value->level_id}}</label>
                                                <input type="text" class="form-control " name="frenchise_income" value="{{$value->frenchise_income}}" />
                                            </div>
                                            <div class="col-lg-2 col-md-4 col-6">
                                                <label class="title-color font-weight-medium "> Withdrawal Level-{{$value->level_id}}</label>
                                                <input type="text" class="form-control " name="frenchise_withdrawal_income" value="{{$value->frenchise_withdrawal_income}}"/>
                                            </div>
                                            <div class="col-lg-2 col-md-3">
                                                <button class="btn  btn-outline-primary" type="submit">Update</button>
                                                <a class="btn btn-outline-danger delete"
                                                href="{{route('admin.plan.delete-level',[$value->id])}}"
                                                title="{{\App\CPU\translate('delete')}}">
                                                Delete
                                            </a>
                                        </div>
                                    </div>

                                </form>
                                @endforeach
                                @endif
                            </div>


                    <h3>Add More Levels</h3>
                    <form action="{{route('admin.plan.store-level')}}" method="post">
                        @csrf
                        <input type="hidden" name="plan_id" value="{{$plan['id']}}">
                        <div class="col-md-6 col-lg-4 form-group" id="max-discount">
                            <label for="name"
                                class="title-color font-weight-medium d-flex">{{\App\CPU\translate('Plan_levels')}}</label>
                            <input type="number" min="1" max="1000000" name="plan_level" class="form-control"
                                id="num_fields" placeholder="{{\App\CPU\translate('Ex: 10')}}" required>
                        </div>
                        <div class="col-12" id="heading_div">
                                    <div class="row justify-content-center">
                                        <div class="col-lg-2 col-md-4 col-6">
                                        <h5 class="mb-3"> Referral Income</h5>
                                        </div>
                                        <div class="col-lg-2 col-md-4 col-6">
                                        <h5 class="mb-3">Salary Income (Daily income)</h5>
                                        </div>
                                        <div class="col-lg-2 col-md-4 col-6">
                                        <h5 class="mb-3">Re-purchase income (%)</h5>
                                        </div>
                                        <div class="col-lg-2 col-md-4 col-6">
                                        <h5 class="mb-3">Frenchise Income</h5>
                                        </div>
                                        <div class="col-lg-2 col-md-4 col-6">
                                        <h5 class="mb-3">Frenchise withdrawal Income (%)</h5>
                                        </div>
                                    </div>
                                </div>
                        <div class="col-md-12 col-lg-12 form-group" id="input_fields">
                        </div>
                        <button class="btn btn-outline-primary" type="submit">Add levels</button>
                    </form>

                    <a href="{{route('admin.plan.add-new')}}" class="float-right"><button type="reset"
                            class="btn btn-danger px-4">{{\App\CPU\translate('back')}}</button></a>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
@endsection

@push('script')

<script type="text/javascript">
</script>
<script type="text/javascript">
    $(document).ready(function () {
        $('#heading_div').hide();
        $("#num_fields").keyup(function (e) {
            e.preventDefault();
            var numFields = parseInt($(this).val());
            if (!isNaN(numFields) && numFields > 0) {
                $("#input_fields").empty(); // Clear previous fields
                $('#heading_div').show();
                for (var i = 1; i <= numFields; i++) {
                    $("#input_fields").append(`<div class="row justify-content-center">    
                    <div class="col-lg-2 col-md-4 col-6">
                    <label class="title-color font-weight-medium ">Level-${i}</label>
                    <input type="text" class="form-control " name="level[]" required/>
                    </div>
                    <div class="col-lg-2 col-md-4 col-6">
                    <label class="title-color font-weight-medium "> Daily Bonus-${i}</label>
                    <input type="text" class="form-control " name="daily_bonus[]"/>
                    </div>
                    <div class="col-lg-2 col-md-4 col-6">
                    <label class="title-color font-weight-medium "> Repurchase Level-${i}</label>
                    <input type="text" class="form-control " name="repurchase_income[]"/>
                    </div>
                    <div class="col-lg-2 col-md-4 col-6">
                    <label class="title-color font-weight-medium "> Frenchise Level-${i}</label>
                    <input type="text" class="form-control " name="frenchise_income[]"/>
                    </div>
                    <div class="col-lg-2 col-md-4 col-6">
                    <label class="title-color font-weight-medium "> Withdrawal Level-${i}</label>
                    <input type="text" class="form-control " name="frenchise_withdrawal_income[]"/>
                    </div>
                    <a href="#" class="remove_field d-none">Remove</a></div>`);
                }
            } else {
                $("#input_fields").empty(); // Clear previous fields
                $('#heading_div').hide();
                alert("Please enter a valid number greater than 0.");
            }
        });

        $("#input_fields").on("click", ".remove_field", function (e) {
            e.preventDefault();
            $(this).parent('div').remove();
        });
    });
</script>
@endpush