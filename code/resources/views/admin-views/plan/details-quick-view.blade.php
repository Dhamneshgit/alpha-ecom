<button type="button" class="close" data-dismiss="modal" aria-label="Close">
    <i class="tio-clear"></i>
</button>
<div class="coupon__details">
    <div class="coupon__details-left">
        <div class="text-center">
            <h6 class="title" id="title">{{ $coupon->title }}</h6>
            <!-- <h6 class="subtitle">{{\App\CPU\translate('code')}} : <span id="coupon_code">{{ $coupon->code }}</span></h6> -->
            <div class="text-capitalize">
                <!-- <span>{{\App\CPU\translate(str_replace('_',' ',$coupon->coupon_type))}}</span> -->
            </div>
        </div>
        <div class="coupon-info">
            <div class="coupon-info-item">
                <span>{{\App\CPU\translate('Amount')}} :</span>
                <strong id="min_purchase">{{\App\CPU\BackEndHelper::set_symbol(\App\CPU\BackEndHelper::usd_to_currency($coupon->amount))}}</strong>
            </div>
            <div class="coupon-info-item">
                <span>{{\App\CPU\translate('Day')}} :</span>
                <strong id="min_purchase">{{$coupon->days}}</strong>
            </div>
            <div class="coupon-info-item">
                <span>{{\App\CPU\translate('lavel')}} :</span>
                <strong id="min_purchase">{{$coupon->level}}</strong>
            </div>
            <div class="coupon-info-item">
                <span>{{\App\CPU\translate('plan_created_date')}} : </span>
                <span id="expire_date">{{ \Carbon\Carbon::parse($coupon->created_at)->format('dS M Y') }}</span>
            </div>

           <div class="row row-for-lables">
            <div class="col-lg-2">
            <h6 class="title" id="title">Levels</h6>
                @if(isset($level))
                @foreach($level as $key=>$value)
                <div class="coupon-info-item">
                    <span>{{$value->level}} : </span>
                    <span id="expire_date">{{$value->amount}}</span>
                </div>
                @endforeach
                @endif
            </div>
            <div class="col-lg-2">
            <h6 class="title" id="title">Levels</h6>
                @if(isset($level))
                @foreach($level as $key=>$value)
                <div class="coupon-info-item">
                    <span>{{$value->level}} : </span>
                    <span id="expire_date">{{$value->amount}}</span>
                </div>
                @endforeach
                @endif
            </div>
            <div class="col-lg-2">
            <h6 class="title" id="title">Levels</h6>
                @if(isset($level))
                @foreach($level as $key=>$value)
                <div class="coupon-info-item">
                    <span>{{$value->level}} : </span>
                    <span id="expire_date">{{$value->amount}}</span>
                </div>
                @endforeach
                @endif
            </div>
            <div class="col-lg-2">
            <h6 class="title" id="title">Levels</h6>
                @if(isset($level))
                @foreach($level as $key=>$value)
                <div class="coupon-info-item">
                    <span>{{$value->level}} : </span>
                    <span id="expire_date">{{$value->amount}}</span>
                </div>
                @endforeach
                @endif
            </div>
            <div class="col-lg-2">
            <h6 class="title" id="title">Levels</h6>
                @if(isset($level))
                @foreach($level as $key=>$value)
                <div class="coupon-info-item">
                    <span>{{$value->level}} : </span>
                    <span id="expire_date">{{$value->amount}}</span>
                </div>
                @endforeach
                @endif
            </div>
           </div>
        </div>

        <div class="coupon-info">
            <!--  -->
            <!-- <h6 class="title" id="title">Add more Levels</h6>
            <div class="col-md-6 col-lg-4 form-group" id="max-discount">
                <label for="name" class="title-color font-weight-medium d-flex">{{\App\CPU\translate('Plan_levels')}}</label>
                <input type="number" min="1" max="1000000" name="plan_level" value=""
                    class="form-control" id="num_fields"
                    placeholder="{{\App\CPU\translate('Ex: 10')}}" >
            </div>

            <div class="col-md-12 col-lg-8 form-group" id="input_fields">
            </div> -->
           
        </div>

        
    </div>
    <div class="coupon__details-right">
        <div class="coupon">
            
            <div class="d-flex">
                <h4 id="discount">
                {{\App\CPU\BackEndHelper::set_symbol(\App\CPU\BackEndHelper::usd_to_currency($coupon->discount_amount))}}
                </h4>
            </div>

            <!-- <span>{{\App\CPU\translate('rs')}}</span> -->
        </div>
    </div>
</div>

<style>
    .row-for-lables{
        max-height: 300px;
        overflow-y: auto;
    }
</style>
