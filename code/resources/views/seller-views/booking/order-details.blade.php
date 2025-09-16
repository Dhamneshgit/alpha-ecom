@extends('layouts.back-end.app-seller')

@section('title', \App\CPU\translate('Booking_Details'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush
<style>
    .service-image{
        max-width: 100px;
    }
    .service-image{
        width: 100%;
    }
</style>

@section('content')
    <div class="content container-fluid">
        <!-- Page Title -->
        <div class="mb-4">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                <img src="{{ asset('/public/assets/back-end/img/all-orders.png') }}" alt="">
                {{ \App\CPU\translate('Booking_details') }}
            </h2>
        </div>
        <!-- End Page Title -->

        <div class="row gy-3" id="printableArea">
            <div class="col-lg-8 col-xl-9">
                <!-- Card -->
                <div class="card h-100">
                    <!-- Body -->
                    <div class="card-body">
                        <div class="d-flex flex-wrap gap-10 justify-content-between mb-4">
                            <div class="d-flex flex-column gap-10">
                                <h4 class="text-capitalize">{{ \App\CPU\translate('Booking_ID') }} #{{ $order['booking_id'] }}</h4>
                                <div class="bookingDate">
                                    <i class="tio-date-range"></i>
                                    {{ date('d M Y H:i:s', strtotime($order['created_at'])) }}
                                </div>
                                <div class="bookingDate">
                                    <span>Booking Date And Time</span>
                                    <i class="tio-date-range"></i>
                                    {{ date('d M Y H:i:s', strtotime($order['booking_datetime'])) }}
                                </div>
                                @if($order['alternate_datetime'])
                                <div class="bookingDate">
                                    <span>Alternate Booking Date And Time</span>
                                    <i class="tio-date-range"></i>
                                    {{ date('d M Y H:i:s', strtotime($order['alternate_datetime'])) }}
                                </div>
                                @endif
                            </div>
                            <div class="service-image">
                                <img onerror="this.src='{{ asset('public/assets/front-end/img/image-place-holder.png') }}'"
                                                        src="{{ asset('storage/app/public/product/' . $service->thumbnail) }}"
                                                        alt="{{ $service->name }}">
                            </div>
                            <div class="text-sm-right">
                                <div class="d-flex flex-wrap gap-10">
                                    <div class="d-none">
                                        @if (isset($shipping_address['latitude']) && isset($shipping_address['longitude']))
                                            <button class="btn btn--primary px-4" data-toggle="modal"
                                                data-target="#locationModal"><i class="tio-map"></i>
                                                {{ \App\CPU\translate('show_locations_on_map') }}</button>
                                        @else
                                            <button class="btn btn-warning px-4">
                                                <i class="tio-map"></i>
                                                {{ \App\CPU\translate('shipping_address_has_been_given_below') }}
                                            </button>
                                        @endif
                                    </div>
                                    <a class="d-none btn btn--primary px-4" target="_blank"
                                        href={{ route('admin.orders.generate-invoice', [$order['id']]) }}>
                                        <i class="tio-print mr-1"></i> {{ \App\CPU\translate('Print') }}
                                        {{ \App\CPU\translate('invoice') }}
                                    </a>
                                </div>
                                <div class="d-flex flex-column gap-2 mt-3">
                                    <!-- Order status -->
                                    <div class="order-status d-flex justify-content-sm-end gap-10 text-capitalize">
                                        <span class="title-color">{{ \App\CPU\translate('Status') }}: </span>
                                        @if ($order['status'] == 0)
                                            <span
                                                class="badge badge-soft-info font-weight-bold radius-50 d-flex align-items-center py-1 px-2">{{ str_replace('_', ' ', 'Pending') }}</span>
                                        @elseif($order['status'] == 1)
                                            <span
                                                class="badge badge-soft-warning font-weight-bold radius-50 d-flex align-items-center py-1 px-2">
                                                {{ str_replace('_', ' ', 'Confirmed') }}
                                            </span>
                                        @elseif($order['status'] == 2)
                                            <span
                                                class="badge badge-soft-success font-weight-bold radius-50 d-flex align-items-center py-1 px-2">
                                                {{ str_replace('_', ' ', 'Completed') }}
                                            </span>
                                        @elseif($order['status'] == 3)
                                        <span
                                            class="badge badge-soft-warning font-weight-bold radius-50 d-flex align-items-center py-1 px-2">
                                            {{ str_replace('_', ' ', 'Re-Scheduled') }}
                                        </span>
                                        @else
                                            <span
                                                class="badge badge-soft-danger font-weight-bold radius-50 d-flex align-items-center py-1 px-2">
                                                {{ str_replace('_', ' ', 'Cancelled') }}
                                            </span>
                                        @endif
                                    </div>

                                    <!-- Payment Method -->
                                    <div class="payment-method d-flex justify-content-sm-end gap-10 text-capitalize">
                                        <span class="title-color">{{ \App\CPU\translate('Payment') }}
                                            {{ \App\CPU\translate('Method') }} :</span>
                                        <strong>{{ \App\CPU\translate('Online') }}</strong>
                                    </div>

                                    <!-- reference-code -->
                                  

                                    <!-- Payment Status -->
                                    <div class="payment-status d-flex justify-content-sm-end gap-10">
                                        <span class="title-color">{{ \App\CPU\translate('Payment_Status') }}:</span>
                                            @if($order['is_paid'] == 0)
                                            <span class="text-warning font-weight-bold">
                                                {{ \App\CPU\translate('unPaid') }}
                                            </span>
                                            @else
                                            <span class="text-success font-weight-bold">
                                                {{ \App\CPU\translate('Paid') }}
                                            </span>
                                            @endif
                                        
                                    </div>
                                   

                                </div>
                            </div>
                        </div>

                        <!-- Order Note -->
                    
                        <div class="table-responsive datatable-custom">
                            
                        </div>

                        @php($shipping = $order['shipping_cost'])
                        @php($coupon_discount = $order['discount_amount'])
                        @php($total = 0)
                        <hr />
                        <div class="row justify-content-md-end mb-3 detailsContainer">
                            <div class="patient_detail">
                                <label>Booking Name : </label>
                                <span>{{ucfirst($order['patient_name']) ?? '--'}}</span>
                            </div>
                            <div class="patient_detail">
                                <label>Booking Mobile : </label>
                                <span>{{$order['patient_mobile'] ?? '--'}}</span>
                            </div>
                            <div class="patient_detail">
                                <label>Booking Email : </label>
                                <span>{{$order['patient_email'] ?? '--'}}</span>
                            </div>
                            
                            <div class="patient_detail">
                                <label>Pincode : </label>
                                <span>{{$order['pincode'] ?? '--'}}</span>
                            </div>
                            <div class="patient_detail">
                                <label>Address : </label>
                                <span>{{$order['landmark'] ?? '--'}}</span>
                            </div>
                            <div class="patient_detail">
                                <label>Area : </label>
                                <span>{{$order['area'] ?? '--'}}</span>
                            </div>
                            <div class="patient_detail">
                                <label>City : </label>
                                <span>{{$order['city'] ?? '--'}}</span>
                            </div>
                            <div class="patient_detail">
                                <label>Amount : </label>
                                <span>{{$order['paid_amount'] ?? '--'}}</span>
                            </div>
                            <div class="patient_detail complaint_details">
                                <label>Complaint : </label>
                                <span>{{$order['complaint'] ?? '--'}}</span>
                            </div>
                             <!-- order note -->
                             <div class="payment-status d-flex justify-content-sm-end gap-10">
                                        <span class="title-color">{{ \App\CPU\translate('order_note') }}:</span>
                                            <span class="text-warning font-weight-bold">
                                                {{ \App\CPU\translate($order['order_note']) }}
                                            </span>
                                    </div>
                        </div>
                        
                        <!-- End Row -->
                    </div>
                    <!-- End Body -->
                </div>
                <!-- End Card -->
            </div>

            <div class="col-lg-4 col-xl-3 d-flex flex-column gap-3">
                <div class="card">
                    <div class="card-body text-capitalize d-flex flex-column gap-4">
                        <h4 class="mb-0 text-center">{{ \App\CPU\translate('Booking_Info') }}</h4>
                        <div class="">
                            <label
                                class="font-weight-bold title-color fz-14">{{ \App\CPU\translate('Order Status') }}</label>
                            <select name="order_status" onchange="order_status(this.value)" class="status form-control"
                                data-id="{{ $order['id'] }}">

                                <option value="0" {{ $order->status == 0 ? 'selected' : '' }}>
                                    {{ \App\CPU\translate('Pending') }}</option>
                                <option value="1" {{ $order->status == 1 ? 'selected' : '' }}>
                                    {{ \App\CPU\translate('Confirm') }}</option>
                                <option value="2" {{ $order->status == 2 ? 'selected' : '' }}>
                                    {{ \App\CPU\translate('Completed') }}</option>
                                <option value="3" {{ $order->status == 3 ? 'selected' : '' }}>
                                    {{ \App\CPU\translate('Re-schedule') }}</option>
                                <option value="4" {{ $order->status == 4 ? 'selected' : '' }}>
                                    {{ \App\CPU\translate('Cancelled') }}</option>
                               
                            </select>
                        </div>


                        <div class="">
                            <label
                                class="font-weight-bold title-color fz-14">{{ \App\CPU\translate('Payment_Status') }}</label>
                            <select name="payment_status" class="payment_status form-control"
                                data-id="{{ $order['id'] }}">
                                <!-- <option
                                    onclick="route_alert('{{ route('admin.orders.payment-status', ['id' => $order['id'], 'payment_status' => 'paid']) }}','Change status to paid ?')"
                                    href="javascript:" value="paid"  selected>
                                    {{ \App\CPU\translate('Paid') }}
                                </option> -->
                                <option  value="1" @if($order->is_paid == 1) selected @endif>Paid</option>
                                <option value="0" @if($order->is_paid == 0) selected @endif>Unpaid</option>
                                
                            </select>
                        </div>

                        @if ($physical_product)
                            @if (auth('seller')->user()->id)
                                <ul class="d-none list-unstyled list-unstyled-py-4">
                                    <li class="">
                                        <label class="font-weight-bold title-color fz-14">
                                            {{ \App\CPU\translate('choose_aggregator') }}
                                        </label>
                                        <select class="form-control text-capitalize js-select2-custom"
                                            name="delivery_man_id" onchange="addDeliveryMan1(this.value)">
                                            <option value="0">{{ \App\CPU\translate('select') }}</option>
                                            @foreach ($aggregator as $deliveryMan)
                                                <option value="{{ $deliveryMan->id }}"
                                                    {{ $order->aggregator_id == $deliveryMan->id ? 'selected' : '' }}>
                                                    {{ ucfirst($deliveryMan->name) . ' (' . $deliveryMan->phone . ' )' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </li>
                                </ul>

                                <ul class=" d-none list-unstyled list-unstyled-py-4">
                                    <li class="">
                                        <label class="font-weight-bold title-color fz-14">
                                            {{ \App\CPU\translate('choose_manufacturer') }}
                                        </label>
                                        <select class="form-control text-capitalize js-select2-custom"
                                            name="delivery_man_id1" onchange="addDeliveryMan(this.value)">
                                            <option value="0">{{ \App\CPU\translate('select') }}</option>
                                            @foreach ($manufacturer as $deliveryMan)
                                                <option value="{{ $deliveryMan->id }}"
                                                    {{ $order->manufacturer_id == $deliveryMan->id ? 'selected' : '' }}>
                                                    {{ ucfirst($deliveryMan->name) . ' (' . $deliveryMan->phone . ' )' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </li>
                                </ul>
                                @if($order['booking_type'] == 'doctor')
                                    <ul class="list-unstyled list-unstyled-py-4 d-none">
                                        <li class="">
                                            <label class="font-weight-bold title-color fz-14">
                                                {{ \App\CPU\translate('choose_doctor') }}
                                            </label>
                                            <select class="form-control text-capitalize js-select2-custom"
                                                name="delivery_man_id1" onchange="assignDoctor(this.value)">
                                                <option value="0">{{ \App\CPU\translate('select') }}</option>
                                                @foreach ($doctor as $deliveryMan)
                                                    <option value="{{ $deliveryMan->id }}"
                                                        {{ $order->employee_id == $deliveryMan->id ? 'selected' : '' }}>
                                                        {{ ucfirst($deliveryMan->name) . ' (' . $deliveryMan->phone . ' )' }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </li>
                                    </ul>
                                    {{-- <ul class="list-unstyled list-unstyled-py-4">
                                        <li class="">
                                            <label class="font-weight-bold title-color fz-14">
                                                {{ \App\CPU\translate('choose_seller') }}
                                            </label>
                                            <select class="form-control text-capitalize js-select2-custom"
                                                name="delivery_man_id1" onchange="assignSeller(this.value)">
                                                <option value="0">{{ \App\CPU\translate('select') }}</option>
                                                @foreach ($seller as $deliveryMan)
                                                    <option value="{{ $deliveryMan->id }}"
                                                        {{ $order->seller_id == $deliveryMan->id ? 'selected' : '' }}>
                                                        {{ ucfirst($deliveryMan->f_name.' '.$deliveryMan->l_name ) . ' (' . $deliveryMan->phone . ' )' }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </li>
                                    </ul> --}}
                                @else
                                    <ul class="list-unstyled list-unstyled-py-4">
                                        <li class="">
                                            <label class="font-weight-bold title-color fz-14">
                                                {{ \App\CPU\translate('choose_home_visitor') }}
                                            </label>
                                            <select class="form-control text-capitalize js-select2-custom"
                                                name="delivery_man_id1" onchange="assignHomeVisit(this.value)">
                                                <option value="0">{{ \App\CPU\translate('select') }}</option>
                                                @foreach ($homevisit as $deliveryMan)
                                                    <option value="{{ $deliveryMan->id }}"
                                                        {{ $order->employee_id == $deliveryMan->id ? 'selected' : '' }}>
                                                        {{ ucfirst($deliveryMan->name) . ' (' . $deliveryMan->phone . ' )' }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </li>
                                    </ul>
                                @endif
                            @elseif(auth('seller')->user()->role->name == 'Manufacturer')
                                <ul class="list-unstyled list-unstyled-py-4">
                                    <li class="">
                                        <label class="font-weight-bold title-color fz-14">
                                            {{ \App\CPU\translate('choose_aggregator') }}
                                        </label>
                                        <select class="form-control text-capitalize js-select2-custom"
                                            name="delivery_man_id" onchange="addDeliveryMan2(this.value)">
                                            <option value="0">{{ \App\CPU\translate('select') }}</option>
                                            @foreach ($aggregator as $deliveryMan)
                                                <option value="{{ $deliveryMan->id }}"
                                                    {{ $order->aggregator_id_assign_by_manufacturer == $deliveryMan->id ? 'selected' : '' }}>
                                                    {{ $deliveryMan->name . ' (' . $deliveryMan->phone . ' )' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </li>
                                </ul>
                            @endif
                            <ul class=" d-none list-unstyled list-unstyled-py-4">
                                <li>
                                    <label class="font-weight-bold title-color fz-14">
                                        {{ \App\CPU\translate('shipping_type') }}
                                        ({{ \App\CPU\translate(str_replace('_', ' ', $order->shipping_type)) }})
                                    </label>
                                    @if ($order->shipping_type == 'order_wise')
                                        <label class="font-weight-bold title-color fz-14">
                                            {{ \App\CPU\translate('shipping') }} {{ \App\CPU\translate('method') }}
                                            ({{ $order->shipping ? \App\CPU\translate(str_replace('_', ' ', $order->shipping->title)) : \App\CPU\translate('no_shipping_method_selected') }})
                                        </label>
                                    @endif

                                    <select class="form-control text-capitalize" name="delivery_type"
                                        onchange="choose_delivery_type(this.value)">
                                        <option value="0">
                                            {{ \App\CPU\translate('choose_delivery_type') }}
                                        </option>

                                        <option value="self_delivery"
                                            {{ $order->delivery_type == 'self_delivery' ? 'selected' : '' }}>
                                            {{ \App\CPU\translate('by_self_delivery_man') }}
                                        </option>
                                        <option value="third_party_delivery"
                                            {{ $order->delivery_type == 'third_party_delivery' ? 'selected' : '' }}>
                                            {{ \App\CPU\translate('by_third_party_delivery_service') }}
                                        </option>
                                    </select>
                                </li>
                                
                                <li class="choose_delivery_man">
                                    <label class="font-weight-bold title-color fz-14">
                                        {{ \App\CPU\translate('deliveryman_will_get') }}
                                        ({{ session('currency_symbol') }})
                                    </label>
                                    <input type="number" id="deliveryman_charge" onkeyup="amountDateUpdate(this, event)"
                                        value="{{ $order->deliveryman_charge }}" name="deliveryman_charge"
                                        class="form-control" placeholder="Ex: 20" required>
                                </li>
                                <li class="choose_delivery_man">
                                    <label class="font-weight-bold title-color fz-14">
                                        {{ \App\CPU\translate('expected_delivery_date') }}
                                    </label>
                                    <input type="date" onchange="amountDateUpdate(this, event)"
                                        value="{{ $order->expected_delivery_date }}" name="expected_delivery_date"
                                        id="expected_delivery_date" class="form-control" required>
                                </li>
                                <li class=" mt-2" id="by_third_party_delivery_service_info">
                                    <span>
                                        {{ \App\CPU\translate('delivery_service_name') }} :
                                        {{ $order->delivery_service_name }}
                                    </span>
                                    <span class="float-right">
                                        <a href="javascript:" onclick="choose_delivery_type('third_party_delivery')">
                                            <i class="tio-edit"></i>
                                        </a>
                                    </span>
                                    <br>
                                    <span>
                                        {{ \App\CPU\translate('tracking_id') }} :
                                        {{ $order->third_party_delivery_tracking_id }}
                                    </span>
                                </li>
                            </ul>
                        @endif
                    </div>
                </div>

                <!-- Card -->
                <div class="card">
                    <!-- Body -->
                    @if ($order->user)
                        <div class="card-body">
                            <h4 class="mb-4 d-flex gap-2">
                                <img src="{{ asset('/public/assets/back-end/img/seller-information.png') }}"
                                    alt="">
                                {{ \App\CPU\translate('Customer_information') }}
                            </h4>
                            <div class="media flex-wrap gap-3">
                                <div class="">
                                    <img class="avatar rounded-circle avatar-70"
                                        onerror="this.src='{{ asset('public/assets/front-end/img/image-place-holder.png') }}'"
                                        src="{{ asset('storage/app/public/profile/' . $order->user->image) }}"
                                        alt="Image">
                                </div>
                                <div class="media-body d-flex flex-column gap-1">
                                    <span class="title-color"><strong>{{ $order->user['f_name'] . ' ' . $order->user['l_name'] }}
                                        </strong></span>
                                    <span
                                        class="title-color break-all"><strong>{{ $order->user['phone'] }}</strong></span>
                                    <span class="title-color break-all">{{ $order->user['email'] }}</span>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="card-body">
                            <div class="media">
                                <span>{{ \App\CPU\translate('no_customer_found') }}</span>
                            </div>
                        </div>
                    @endif
                    <!-- End Body -->
                </div>
                <!-- End Card -->

                <!-- Card -->
             
                <!-- End Card -->

                <!-- Card -->
               
                <!-- End Card -->

                <!-- Card -->
                <div class="card">
                    <div class="card-body">
                        <h4 class="mb-4 d-flex gap-2">
                            <img src="{{ asset('/public/assets/back-end/img/shop-information.png') }}" alt="">
                            {{ \App\CPU\translate('Company_Information') }}
                        </h4>


                        <div class="media">
                            

                                @if ($order->seller->shop)
                                    <div class="mr-3">
                                        <img class="avatar rounded avatar-70"
                                            onerror="this.src='https://6valley.6amtech.com/public/assets/front-end/img/image-place-holder.png'"
                                            src="{{ asset('storage/app/public/shop') }}/{{ $order->seller->shop->image }}"
                                            alt="">
                                    </div>
                                    <div class="media-body d-flex flex-column gap-2">
                                        <h5>{{ $order->seller->shop->name }}</h5>
                                        
                                        <span class="title-color">
                                            <strong>{{ $order->seller->shop->contact }}</strong></span>
                                        <div class="d-flex align-items-start gap-2">
                                            <img src="{{ asset('/public/assets/back-end/img/location.png') }}"
                                                class="mt-1" alt="">
                                            {{ $order->seller->shop->address }}
                                        </div>
                                    </div>
                                @else
                                    <div class="card-body">
                                        <div class="media align-items-center">
                                            <span>{{ \App\CPU\translate('no_data_found') }}</span>
                                        </div>
                                    </div>
                                @endif
                        </div>
                    </div>
                </div>
                <!-- End Card -->
            </div>
        </div>
        <!-- End Row -->
    </div>

    <!--Show locations on map Modal -->
    <div class="modal fade" id="locationModal" tabindex="-1" role="dialog" aria-labelledby="locationModalLabel">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="locationModalLabel">{{ \App\CPU\translate('location') }}
                        {{ \App\CPU\translate('data') }}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 modal_body_map">
                            <div class="location-map" id="location-map">
                                <div class="w-100 __h-400px" id="location_map_canvas"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Modal -->

    <!--Show delivery info Modal -->
    <div class="modal" id="shipping_chose" role="dialog" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ \App\CPU\translate('update_third_party_delivery_info') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12">
                            <form action="{{ route('admin.orders.update-deliver-info') }}" method="POST">
                                @csrf
                                <input type="hidden" name="order_id" value="{{ $order['id'] }}">
                                <div class="card-body">
                                    <div class="form-group">
                                        <label for="">{{ \App\CPU\translate('delivery_service_name') }}</label>
                                        <input class="form-control" type="text" name="delivery_service_name"
                                            value="{{ $order['delivery_service_name'] }}" id="" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="">{{ \App\CPU\translate('tracking_id') }}
                                            ({{ \App\CPU\translate('optional') }})</label>
                                        <input class="form-control" type="text"
                                            name="third_party_delivery_tracking_id"
                                            value="{{ $order['third_party_delivery_tracking_id'] }}" id="">
                                    </div>
                                    <button class="btn btn--primary"
                                        type="submit">{{ \App\CPU\translate('update') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>


                </div>
            </div>
        </div>
    </div>
    <!-- End Modal -->
@endsection

@push('script_2')
    <script>
        $(document).on('change', '.payment_status', function() {
            var id = $(this).attr("data-id");
            var value = $(this).val();
            Swal.fire({
                title: '{{ \App\CPU\translate('Are you sure Change this') }}?',
                text: "{{ \App\CPU\translate('You will not be able to revert this') }}!",
                showCancelButton: true,
                confirmButtonColor: '#377dff',
                cancelButtonColor: 'secondary',
                confirmButtonText: '{{ \App\CPU\translate('Yes, Change it') }}!'
            }).then((result) => {
                if (result.value) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                        }
                    });
                    $.ajax({
                        url: "{{ route('seller.bookings.payment-status') }}",
                        method: 'POST',
                        data: {
                            "id": id,
                            "payment_status": value
                        },
                        success: function(data) {
                            if (data.customer_status == 0) {
                                toastr.warning(
                                    '{{ \App\CPU\translate('Account has been deleted, you can not change the status!') }}!'
                                );
                                // location.reload();
                            } else {
                                toastr.success(
                                    '{{ \App\CPU\translate('Status Change successfully') }}'
                                );
                                location.reload();
                            }
                        }
                    });
                }
            })
        });

        function order_status(status) {
           
                Swal.fire({
                    title: '{{ \App\CPU\translate('Are you sure Change this') }}?',
                    text: "{{ \App\CPU\translate('You will not be able to revert this') }}!",
                    showCancelButton: true,
                    confirmButtonColor: '#377dff',
                    cancelButtonColor: 'secondary',
                    confirmButtonText: '{{ \App\CPU\translate('Yes, Change it') }}!'
                }).then((result) => {
                    if (result.value) {
                        $.ajaxSetup({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                            }
                        });
                        $.ajax({
                            url: "{{ route('seller.bookings.status') }}",
                            method: 'POST',
                            data: {
                                "id": '{{ $order['id'] }}',
                                "order_status": status
                            },
                            success: function(data) {
                                if (data.complete_status == 0) {
                                    toastr.danger(
                                        '{{ \App\CPU\translate('Booking is already Completed, You can not change it') }} !!'
                                    );
                                    location.reload();
                                } else {
                                    if (data.payment_status == 0) {
                                        toastr.danger(
                                            '{{ \App\CPU\translate('Before Change the Status you need to assign the booking!') }}!'
                                        );
                                        // location.reload();
                                    } else if (data.customer_status == 0) {
                                        toastr.danger(
                                            '{{ \App\CPU\translate('User Account has been deleted, you can not change the status!') }}!'
                                        );
                                        // location.reload();
                                    } else {
                                        toastr.success(
                                            '{{ \App\CPU\translate('Status Change successfully') }}!'
                                        );
                                        // location.reload();
                                    }
                                }

                            }
                        });
                    }
                })
        }
    </script>
    <script>
        $(document).ready(function() {
            let delivery_type = '{{ $order->delivery_type }}';


            if (delivery_type === 'self_delivery') {
                $('.choose_delivery_man').show();
                $('#by_third_party_delivery_service_info').hide();
            } else if (delivery_type === 'third_party_delivery') {
                $('.choose_delivery_man').hide();
                $('#by_third_party_delivery_service_info').show();
            } else {
                $('.choose_delivery_man').hide();
                $('#by_third_party_delivery_service_info').hide();
            }
        });
    </script>
    <script>
        function choose_delivery_type(val) {

            if (val === 'self_delivery') {
                $('.choose_delivery_man').show();
                $('#by_third_party_delivery_service_info').hide();
            } else if (val === 'third_party_delivery') {
                $('.choose_delivery_man').hide();
                $('#deliveryman_charge').val(null);
                $('#expected_delivery_date').val(null);
                $('#by_third_party_delivery_service_info').show();
                $('#shipping_chose').modal("show");
            } else {
                $('.choose_delivery_man').hide();
                $('#by_third_party_delivery_service_info').hide();
            }

        }
    </script>
    <script>
        function addDeliveryMan(id) {
            $.ajax({
                type: "GET",
                url: '{{ url('/') }}/admin/orders/add-delivery-man/{{ $order['id'] }}/' + id +
                    '/manufacturer',
                data: {
                    'order_id': '{{ $order['id'] }}',
                    'delivery_man_id1': id,
                    'type': 'manufacturer',

                },
                success: function(data) {
                    if (data.status == true) {
                        toastr.success('Manufacturer successfully assigned/changed', {
                            CloseButton: true,
                            ProgressBar: true
                        });
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    } else {
                        toastr.error('Manufacturer can not assign/change in that status', {
                            CloseButton: true,
                            ProgressBar: true
                        });
                    }
                },
                error: function() {
                    toastr.error('Add valid data', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                }
            });
        }
        function assignDoctor(id) {
            $.ajax({
                type: "GET",
                url: '{{ url('/') }}/admin/bookings/add-doctor/{{ $order['id'] }}/' + id +
                    '/doctor',
                data: {
                    'order_id': '{{ $order['id'] }}',
                    'delivery_man_id1': id,
                    'type': 'doctor',

                },
                success: function(data) {
                    if (data.status == true) {
                        toastr.success('Doctor successfully assigned/changed', {
                            CloseButton: true,
                            ProgressBar: true
                        });
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    } else {
                        toastr.error('Doctor can not assign/change in that status', {
                            CloseButton: true,
                            ProgressBar: true
                        });
                    }
                },
                error: function() {
                    toastr.error('Add valid data', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                }
            });
        }
        function assignSeller(id) {
            $.ajax({
                type: "GET",
                url: '{{ url('/') }}/admin/bookings/add-doctor/{{ $order['id'] }}/' + id +
                    '/doctor',
                data: {
                    'order_id': '{{ $order['id'] }}',
                    'delivery_man_id1': id,
                    'type': 'doctor',

                },
                success: function(data) {
                    if (data.status == true) {
                        toastr.success('Seller successfully assigned/changed', {
                            CloseButton: true,
                            ProgressBar: true
                        });
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    } else {
                        toastr.error('Seller can not assign/change in that status', {
                            CloseButton: true,
                            ProgressBar: true
                        });
                    }
                },
                error: function() {
                    toastr.error('Add valid data', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                }
            });
        }
        function assignHomeVisit(id) {
            $.ajax({
                type: "GET",
                url: '{{ url('/') }}/admin/bookings/add-doctor/{{ $order['id'] }}/' + id +
                    '/home_visit',
                data: {
                    'order_id': '{{ $order['id'] }}',
                    'delivery_man_id1': id,
                    'type': 'home_visit',

                },
                success: function(data) {
                    if (data.status == true) {
                        toastr.success('Home Visit successfully assigned/changed', {
                            CloseButton: true,
                            ProgressBar: true
                        });
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    } else {
                        toastr.error('Home Visit can not assign/change in that status', {
                            CloseButton: true,
                            ProgressBar: true
                        });
                    }
                },
                error: function() {
                    toastr.error('Add valid data', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                }
            });
        }

        function addDeliveryMan1(id) {
            $.ajax({
                type: "GET",
                url: '{{ url('/') }}/admin/orders/add-delivery-man/{{ $order['id'] }}/' + id +
                    '/aggregator',
                data: {
                    'order_id': '{{ $order['id'] }}',
                    'delivery_man_id': id,
                    'type': 'aggregator',
                },
                success: function(data) {
                    if (data.status == true) {
                        toastr.success('Aggregator successfully assigned/changed', {
                            CloseButton: true,
                            ProgressBar: true
                        });
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    } else {
                        toastr.error('Aggregator can not assign/change in that status', {
                            CloseButton: true,
                            ProgressBar: true
                        });
                    }
                },
                error: function() {
                    toastr.error('Add valid data', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                }
            });
        }

        function addDeliveryMan2(id) {
            $.ajax({
                type: "GET",
                url: '{{ url('/') }}/admin/orders/add-delivery-man/{{ $order['id'] }}/' + id +
                    '/m_aggregator',
                //m_aggregator = the aggregator assign by manufacturer
                data: {
                    'order_id': '{{ $order['id'] }}',
                    'delivery_man_id': id,
                    'type': 'm_aggregator',
                },
                success: function(data) {
                    if (data.status == true) {
                        toastr.success('Aggregator successfully assigned/changed', {
                            CloseButton: true,
                            ProgressBar: true
                        });
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    } else {
                        toastr.error('Aggregator can not assign/change in that status', {
                            CloseButton: true,
                            ProgressBar: true
                        });
                    }
                },
                error: function() {
                    toastr.error('Add valid data', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                }
            });
        }

        function last_location_view() {
            toastr.warning('Only available when order is out for delivery!', {
                CloseButton: true,
                ProgressBar: true
            });
        }

        $(function() {
            $('[data-toggle="tooltip"]').tooltip()
        })

        function waiting_for_location() {
            toastr.warning('{{ \App\CPU\translate('waiting_for_location') }}', {
                CloseButton: true,
                ProgressBar: true
            });
        }

        function amountDateUpdate(t, e) {
            let field_name = $(t).attr('name');
            let field_val = $(t).val();

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                }
            });
            $.ajax({
                url: "{{ route('admin.orders.amount-date-update') }}",
                method: 'POST',
                data: {
                    'order_id': '{{ $order['id'] }}',
                    'field_name': field_name,
                    'field_val': field_val
                },
                success: function(data) {
                    if (data.status == true) {
                        toastr.success('Deliveryman charge add successfully', {
                            CloseButton: true,
                            ProgressBar: true
                        });
                    } else {
                        toastr.error('Failed to add deliveryman charge', {
                            CloseButton: true,
                            ProgressBar: true
                        });
                    }
                },
                error: function() {
                    toastr.error('Add valid data', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                }
            });
        }
    </script>
    <script
        src="https://maps.googleapis.com/maps/api/js?key={{ \App\CPU\Helpers::get_business_settings('map_api_key') }}&v=3.49">
    </script>
    <script>
        function initializegLocationMap() {
            var map = null;
            var myLatlng = new google.maps.LatLng({{ $shipping_address->latitude ?? null }},
                {{ $shipping_address->longitude ?? null }});
            var dmbounds = new google.maps.LatLngBounds(null);
            var locationbounds = new google.maps.LatLngBounds(null);
            var dmMarkers = [];
            dmbounds.extend(myLatlng);
            locationbounds.extend(myLatlng);

            var myOptions = {
                center: myLatlng,
                zoom: 13,
                mapTypeId: google.maps.MapTypeId.ROADMAP,

                panControl: true,
                mapTypeControl: false,
                panControlOptions: {
                    position: google.maps.ControlPosition.RIGHT_CENTER
                },
                zoomControl: true,
                zoomControlOptions: {
                    style: google.maps.ZoomControlStyle.LARGE,
                    position: google.maps.ControlPosition.RIGHT_CENTER
                },
                scaleControl: false,
                streetViewControl: false,
                streetViewControlOptions: {
                    position: google.maps.ControlPosition.RIGHT_CENTER
                }
            };
            map = new google.maps.Map(document.getElementById("location_map_canvas"), myOptions);
            console.log(map);
            var infowindow = new google.maps.InfoWindow();

            

            google.maps.event.addListenerOnce(map, 'idle', function() {
                map.fitBounds(locationbounds);
            });
        }

        // Re-init map before show modal
        $('#locationModal').on('shown.bs.modal', function(event) {
            initializegLocationMap();
        });
    </script>
@endpush
