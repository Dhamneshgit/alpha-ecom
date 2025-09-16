<div class="feature_header mb-2">
    <span>{{ \App\CPU\translate('shopping_cart')}}</span>
</div>

@php($shippingMethod=\App\CPU\Helpers::get_business_settings('shipping_method'))
@php($cart=\App\Model\Cart::where(['customer_id' => auth('customer')->id()])->get()->groupBy('cart_group_id'))

<div class="row g-3">
    <!-- List of items-->
    <section class="col-lg-8">
            @if(count($cart)==0)
                @php($physical_product = false)
            @endif

            @foreach($cart as $group_key=>$group)
            <div class="card __card cart_information mb-3">
                @foreach($group as $cart_key=>$cartItem)
                    @if ($shippingMethod=='inhouse_shipping')
                            <?php

                            $admin_shipping = \App\Model\ShippingType::where('seller_id', 0)->first();
                            $shipping_type = isset($admin_shipping) == true ? $admin_shipping->shipping_type : 'order_wise';

                            ?>
                    @else
                            <?php
                            if ($cartItem->seller_is == 'admin') {
                                $admin_shipping = \App\Model\ShippingType::where('seller_id', 0)->first();
                                $shipping_type = isset($admin_shipping) == true ? $admin_shipping->shipping_type : 'order_wise';
                            } else {
                                $seller_shipping = \App\Model\ShippingType::where('seller_id', $cartItem->seller_id)->first();
                                $shipping_type = isset($seller_shipping) == true ? $seller_shipping->shipping_type : 'order_wise';
                            }
                            ?>
                    @endif

                    @if($cart_key==0)
                        <div class="card-header">
                            @if($cartItem->seller_is=='admin')
                            <b>
                                <span>{{ \App\CPU\translate('shop_name')}} : </span>
                                <a href="{{route('shopView',['id'=>0])}}">{{\App\CPU\Helpers::get_business_settings('company_name')}}</a>
                            </b>
                        @else
                            <b>
                                <span>{{ \App\CPU\translate('shop_name')}}:</span>
                                <a href="{{route('shopView',['id'=>$cartItem->seller_id])}}">
                                    {{\App\Model\Shop::where(['seller_id'=>$cartItem['seller_id']])->first()->name}}
                                </a>
                            </b>
                        @endif
                        </div>
                    @endif
                @endforeach
                <div class="table-responsive mt-3">
                    <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table __cart-table">
                        <thead class="thead-light">
                            <tr class="">
                                <th class="font-weight-bold __w-5p">{{\App\CPU\translate('SL#')}}</th>
                                @if ( $shipping_type != 'order_wise')
                                <th class="font-weight-bold __w-30p">{{\App\CPU\translate('product_details')}}</th>
                                @else
                                <th class="font-weight-bold __w-45">{{\App\CPU\translate('product_details')}}</th>
                                @endif
                                <th class="font-weight-bold __w-15p">{{\App\CPU\translate('unit_price')}}</th>
                                <th class="font-weight-bold __w-15p">{{\App\CPU\translate('qty')}}</th>
                                <th class="font-weight-bold __w-15p">{{\App\CPU\translate('total_price')}}</th>
                                @if ( $shipping_type != 'order_wise')
                                    <th class="font-weight-bold __w-15p">{{\App\CPU\translate('shipping_cost')}} </th>
                                @endif
                                <!-- <th class="font-weight-bold __w-5p"></th> -->
                                <th class="font-weight-bold __w-5p">Remove</th>
                            </tr>
                        </thead>

                        <tbody>
                        <?php
                            $physical_product = false;
                            foreach ($group as $row) {
                                if ($row->product_type == 'physical') {
                                    $physical_product = true;
                                }
                            }
                        ?>
                        @foreach($group as $cart_key=>$cartItem)
                            <tr>
                                <td>{{$cart_key+1}}</td>
                                <td>
                                    <div class="d-flex">
                                        <div class="__w-30p">
                                            <a href="{{route('product',$cartItem['slug'])}}">
                                                <img class="rounded __img-62"
                                                        onerror="this.src='{{asset('public/assets/front-end/img/image-place-holder.png')}}'"
                                                        src="{{\App\CPU\ProductManager::product_image_path('thumbnail')}}/{{$cartItem['thumbnail']}}"
                                                        alt="Product">
                                            </a>
                                        </div>
                                        <div class="ml-2 text-break __line-2 __w-70p">
                                            <a href="{{route('product',$cartItem['slug'])}}">{{$cartItem['name']}}</a>

                                        </div>

                                    </div>
                                    <div class="d-flex">

                                        @foreach(json_decode($cartItem['variations'],true) as $key1 =>$variation)
                                            <div class="text-muted mr-2">
                                                <span class="{{Session::get('direction') === "rtl" ? 'ml-2' : 'mr-2'}} __text-12px">
                                                    {{$key1}} : {{$variation}}</span>

                                            </div>
                                        @endforeach
                                    </div>
                                </td>
                                <td>
    <div class="text-accent" id="price{{$cartItem['id']}}">
        {{ \App\CPU\Helpers::currency_converter($cartItem['price']-$cartItem['discount']) }}
    </div>
    @if($cartItem['discount'] > 0)
        <strike class="__inline-18">
            {{\App\CPU\Helpers::currency_converter($cartItem['price'])}}
        </strike>
    @endif
</td>
<td>
    <div>
        @php($minimum_order=\App\Model\Product::select('minimum_order_qty','current_stock','qty_step_size','product_type','tax','discount','discount_type','unit_price')->find($cartItem['product_id']))
        <span class="input-group-btn">
                                                        <button class="btn btn-number1 __p-10" type="button"
                                                            data-type="minus" data-field="quantity"
                                                            style="color: {{ $web_config['primary_color'] }}">
                                                            -
                                                        </button>
                                                    </span>
        <input class="__cart-input form-control input-number1 text-center cart-qty-field" type="text" name="quantity[{{ $cartItem['id'] }}]" id="cartQuantity{{$cartItem['id']}}" product-type="{{ $minimum_order->product_type }}"
               onchange="updateCartQuantity('{{ $minimum_order->minimum_order_qty }}', '{{ $cartItem['id'] }}')" 
               min="{{ $minimum_order->minimum_order_qty ?? 1 }}" max="{{ $minimum_order->current_stock ?? 1 }}" step-size="{{ $minimum_order->qty_step_size ?? 1 }}" value="{{$cartItem['quantity']}}">
               <span class="input-group-btn">
                                                        <button class="btn btn-number1 __p-10" type="button"
                                                            product-type="{{ $minimum_order->product_type }}" data-type="plus"
                                                            data-field="quantity"
                                                            style="color: {{ $web_config['primary_color'] }}">
                                                            +
                                                        </button>
                                                    </span>
    </div>
</td>
<td>
    <?php
    $discount = 0;
    $tax = ($minimum_order->unit_price / 100) * $minimum_order->tax;
        if ($minimum_order->discount_type == 'percent') {
            $discount = ($minimum_order->unit_price * $minimum_order->discount) / 100;
        } elseif ($minimum_order->discount_type == 'flat') {
            $discount = $minimum_order->discount;
        }
    $net_price = $minimum_order->unit_price - $discount + $tax;
    ?>
    <input type="hidden" id="variantPrice{{$cartItem['id']}}" value="{{\App\CPU\Helpers::currency_converter($net_price)}}">
    <div id="cal_price{{$cartItem['id']}}">
        {{ \App\CPU\Helpers::currency_converter(($cartItem['price'] - $cartItem['discount']) * $cartItem['quantity']) }}
    </div>
</td>
                                @if ( $shipping_type != 'order_wise')
                                <td>
                                    {{ \App\CPU\Helpers::currency_converter($cartItem['shipping_cost']) }}
                                </td>
                                @endif
                                <td class="text-center">
                                    <button class="btn btn-link px-0 text-danger"
                                            onclick="removeFromCart({{ $cartItem['id'] }})" type="button"><i
                                            class="czi-close-circle {{Session::get('direction') === "rtl" ? 'ml-2' : 'mr-2'}}"></i>
                                    </button>
                                </td>
                                </tr>

                                @if($physical_product && $shippingMethod=='sellerwise_shipping' && $shipping_type == 'order_wise')
                                    @php($choosen_shipping=\App\Model\CartShipping::where(['cart_group_id'=>$cartItem['cart_group_id']])->first())

                                    @if(isset($choosen_shipping)==false)
                                        @php($choosen_shipping['shipping_method_id']=0)
                                    @endif

                                    @php($shippings=\App\CPU\Helpers::get_shipping_methods($cartItem['seller_id'],$cartItem['seller_is']))
                                    <tr>
                                        <td colspan="4">

                                            @if($cart_key==$group->count()-1)

                                                <!-- choosen shipping method-->

                                                <div class="row">

                                                    <div class="col-12">
                                                        <select class="form-control"
                                                                onchange="set_shipping_id(this.value,'{{$cartItem['cart_group_id']}}')">
                                                            <option>{{\App\CPU\translate('choose_shipping_method')}}</option>
                                                            @foreach($shippings as $shipping)
                                                                <option
                                                                    value="{{$shipping['id']}}" {{$choosen_shipping['shipping_method_id']==$shipping['id']?'selected':''}}>
                                                                    {{$shipping['title'].' ( '.$shipping['duration'].' ) '.\App\CPU\Helpers::currency_converter($shipping['cost'])}}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>

                                    @endif
                                </td>
                                <td colspan="3">
                                    @if($cart_key==$group->count()-1)
                                    <div class="row">
                                        <div class="col-12">
                                            <span>
                                                <b>{{\App\CPU\translate('shipping_cost')}} : </b>
                                            </span>
                                            {{\App\CPU\Helpers::currency_converter($choosen_shipping['shipping_method_id']!= 0?$choosen_shipping->shipping_cost:0)}}
                                        </div>
                                    </div>
                                    @endif
                                </td>
                            </tr>
                            @endif
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endforeach

@if($shippingMethod=='inhouse_shipping')
        <?php
            $admin_shipping = \App\Model\ShippingType::where('seller_id', 0)->first();
            $shipping_type = isset($admin_shipping) == true ? $admin_shipping->shipping_type : 'order_wise';
        ?>
    @if ($shipping_type == 'order_wise' && $physical_product)
        @php($shippings=\App\CPU\Helpers::get_shipping_methods(1,'admin'))
        @php($choosen_shipping=\App\Model\CartShipping::where(['cart_group_id'=>$cartItem['cart_group_id']])->first())

        @if(isset($choosen_shipping)==false)
            @php($choosen_shipping['shipping_method_id']=0)
        @endif
        <div class="row">
            <div class="col-12">
                <select class="form-control" onchange="set_shipping_id(this.value,'all_cart_group')">
                    <option>{{\App\CPU\translate('choose_shipping_method')}}</option>
                    @foreach($shippings as $shipping)
                        <option
                            value="{{$shipping['id']}}" {{$choosen_shipping['shipping_method_id']==$shipping['id']?'selected':''}}>
                            {{$shipping['title'].' ( '.$shipping['duration'].' ) '.\App\CPU\Helpers::currency_converter($shipping['cost'])}}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    @endif
@endif

@if( $cart->count() == 0)
    <div class="d-flex justify-content-center align-items-center">
        <h4 class="text-danger text-capitalize">{{\App\CPU\translate('cart_empty')}}</h4>
    </div>
@endif


        <form  method="get">
            <div class="form-group">
                <div class="row">
                    <div class="col-12">
                        <label for="phoneLabel" class="form-label input-label">{{\App\CPU\translate('order_note')}} <span
                                            class="input-label-secondary">({{\App\CPU\translate('Optional')}})</span></label>
                        <textarea class="form-control w-100" id="order_note" name="order_note">{{ session('order_note')}}</textarea>
                    </div>
                </div>
            </div>
        </form>


        <div class="d-flex btn-full-max-sm align-items-center __gap-6px flex-wrap justify-content-between">
            <a href="{{route('home')}}" class="btn btn--primary">
                <i class="fa fa-{{Session::get('direction') === "rtl" ? 'forward' : 'backward'}} px-1"></i> {{\App\CPU\translate('continue_shopping')}}
            </a>
            <a onclick="checkout()"
            class="btn btn--primary pull-{{Session::get('direction') === "rtl" ? 'left' : 'right'}}">
                {{\App\CPU\translate('checkout')}}
                <i class="fa fa-{{Session::get('direction') === "rtl" ? 'backward' : 'forward'}} px-1"></i>
            </a>
        </div>
</section>
<!-- Sidebar-->
@include('web-views.partials._order-summary')
</div>


<script>
    cartQuantityInitialize();

    function set_shipping_id(id, cart_group_id) {
        $.get({
            url: '{{url('/')}}/customer/set-shipping-method',
            dataType: 'json',
            data: {
                id: id,
                cart_group_id: cart_group_id
            },
            beforeSend: function () {
                $('#loading').show();
            },
            success: function (data) {
                location.reload();
            },
            complete: function () {
                $('#loading').hide();
            },
        });
    }
</script>
<script>
    function checkout() {
        let order_note = $('#order_note').val();
        //console.log(order_note);
        $.post({
            url: "{{route('order_note')}}",
            data: {
                _token: '{{csrf_token()}}',
                order_note: order_note,

            },
            beforeSend: function () {
                $('#loading').show();
            },
            success: function (data) {
                let url = "{{ route('checkout-details') }}";
                location.href = url;

            },
            complete: function () {
                $('#loading').hide();
            },
        });
    }

function handleQuantityChange(cartItemId, operation) {
    var quantityInput = document.getElementById('cartQuantity' + cartItemId);
    var currentQuantity = parseInt(quantityInput.value);
    var minimumOrderQty = parseInt(quantityInput.getAttribute('min'));
    var stepSize = parseInt(quantityInput.getAttribute('step-size'));
    var maxQuantity = parseInt(quantityInput.getAttribute('max'));
    if (operation === 'plus') {
        if (currentQuantity + stepSize <= maxQuantity) {
            if(currentQuantity==1 && stepSize>1){
                currentQuantity = currentQuantity+stepSize-1;
            }else{
                currentQuantity += stepSize;
            }
        } else {
            currentQuantity = maxQuantity;
        }
    } else if (operation === 'minus') {
        if (currentQuantity - stepSize >= minimumOrderQty) {
            currentQuantity -= stepSize;
        } else {
            currentQuantity = minimumOrderQty;
        }
    }
    quantityInput.value = currentQuantity;
    upCartQty(minimumOrderQty, cartItemId);
}

function upCartQty(minimumOrderQty, cartItemId) {
    var quantityInput = document.getElementById('cartQuantity' + cartItemId);
    var quantity = parseInt(quantityInput.value);
    if (quantity < minimumOrderQty) {
        alert('Minimum order quantity is ' + minimumOrderQty);
        quantityInput.value = minimumOrderQty;
        quantity = minimumOrderQty;
    }
    var priceElement = document.getElementById('variantPrice' + cartItemId);
    var pricePerItem = parseFloat(priceElement.value.replace(/[^\d.-]/g, ''));
    var discountedPrice = pricePerItem * quantity;
    var formattedPrice = new Intl.NumberFormat().format(discountedPrice);
    var calPriceElement = document.getElementById('cal_price' + cartItemId);
    calPriceElement.innerText = formattedPrice+'₹';
updateCartQuantity(minimumOrderQty, cartItemId);
}

document.querySelectorAll('.btn-number1').forEach(function(button) {
    button.addEventListener('click', function() {
        var cartItemId = button.closest('div').querySelector('input').getAttribute('id').replace('cartQuantity', '');
        var operation = button.getAttribute('data-type');
        handleQuantityChange(cartItemId, operation);
    });
});

</script>
