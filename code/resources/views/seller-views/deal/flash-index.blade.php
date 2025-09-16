@extends('layouts.back-end.app-seller')

@section('title', \App\CPU\translate('Flash Deal'))

@push('css_or_js')
    <link href="{{asset('public/assets/back-end/css/tags-input.min.css')}}" rel="stylesheet">
    <link href="{{ asset('public/assets/select2/css/select2.min.css')}}" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
<div class="content container-fluid">
    <!-- Page Title -->
    <div class="mb-3">
        <h2 class="h1 mb-0 text-capitalize d-flex gap-2">
            <img width="20" src="{{asset('/public/assets/back-end/img/flash_deal.png')}}" alt="">
            {{\App\CPU\translate('flash_deals')}}
        </h2>
    </div>
    <!-- End Page Title -->

    <!-- Content Row -->
    

    <div class="row mt-20">
        <div class="col-md-12">
            <div class="card">
                <div class="px-3 py-4">
                    <div class="row align-items-center">
                        <div class="col-sm-4 col-md-6 col-lg-8 mb-2 mb-sm-0">
                            <h5 class="mb-0 text-capitalize d-flex gap-2">
                                {{ \App\CPU\translate('flash_deal_table')}}
                                <span class="badge badge-soft-dark radius-50 fz-12">{{ $flash_deal->total() }}</span>
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
                                        placeholder="{{\App\CPU\translate('Search by Title')}}" aria-label="Search orders" value="{{ $search }}" required>
                                    <button type="submit" class="btn btn--primary">{{\App\CPU\translate('search')}}</button>
                                </div>
                            </form>
                            <!-- End Search -->
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="datatable"
                            style="text-align: {{Session::get('direction') === "rtl" ? 'right' : 'left'}};"
                            class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                        <thead class="thead-light thead-50 text-capitalize">
                        <tr>
                            <th>{{ \App\CPU\translate('SL')}}</th>
                            <th>{{ \App\CPU\translate('Title')}}</th>
                            <th>{{ \App\CPU\translate('Duration')}}</th>
                            <th>{{ \App\CPU\translate('status')}}</th>
                            <th>{{ \App\CPU\translate('total_deal_products')}}</th>
                            <th class="text-center">{{ \App\CPU\translate('action')}}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($flash_deal as $k=>$deal)
                            <tr>
                                <td>{{$flash_deal->firstItem()+ $k}}</td>
                                <td><span class="font-weight-semibold">{{$deal['title']}}</span></td>
                                <td>{{date('d-M-y',strtotime($deal['start_date']))}} - {{date('d-M-y',strtotime($deal['end_date']))}}</td>
                                <!-- <td>{{date('d-M-y',strtotime($deal['end_date']))}}</td> -->
                                <td>
                                    @if(\Carbon\Carbon::parse($deal['end_date'])->endOfDay()->isPast())
                                        <span class="badge badge-soft-danger">{{ \App\CPU\translate('expired')}} </span>
                                    @else
                                        <span class="badge badge-soft-success"> {{ \App\CPU\translate('active')}} </span>
                                    @endif
                                </td>
                                <td>{{ $deal->products_count }}</td>
                                
                                <td class="text-center">
                                    <div class="d-flex align-items-center justify-content-center gap-10">
                                        <a class="h-30 d-flex gap-2 align-items-center btn btn-soft-info btn-sm border-info" href="{{route('seller.deal.add-product',[$deal['id']])}}">
                                            <img src="{{asset('/public/assets/back-end/img/plus.svg')}}" class="svg" alt="">
                                            {{\App\CPU\translate('Add Product')}}
                                        </a>

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
                        {{$flash_deal->links()}}
                    </div>
                </div>

                @if(count($flash_deal)==0)
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
    <!-- Page level plugins -->
    <script src="{{asset('public/assets/back-end')}}/vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="{{asset('public/assets/back-end')}}/vendor/datatables/dataTables.bootstrap4.min.js"></script>
    <!-- Page level custom scripts -->

    <script src="{{asset('public/assets/back-end')}}/js/select2.min.js"></script>
    <script>
        function readURL(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();

                reader.onload = function (e) {
                    $('#viewer').attr('src', e.target.result);
                }

                reader.readAsDataURL(input.files[0]);
            }
        }

        $("#customFileUpload").change(function () {
            readURL(this);
        });

        $(".js-example-theme-single").select2({
            theme: "classic"
        });

        $(".js-example-responsive").select2({
            width: 'resolve'
        });

        // Call the dataTables jQuery plugin
        $(document).ready(function () {
            $('#dataTable').DataTable();
        });

        $(document).on('change', '.status', function () {
            var id = $(this).attr("id");
            if ($(this).prop("checked") == true) {
                var status = 1;
            } else if ($(this).prop("checked") == false) {
                var status = 0;
            }
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                }
            });
            $.ajax({
                url: "{{route('admin.deal.status-update')}}",
                method: 'POST',
                data: {
                    id: id,
                    status: status
                },
                success: function () {
                    toastr.success('{{\App\CPU\translate('Status updated successfully')}}');
                    setTimeout(function (){
                        location.reload()
                    },1000);
                }
            });
        });

    </script>

    <!-- Page level custom scripts -->

    <script>
        $(document).ready(function () {
            // color select select2
            $('.color-var-select').select2({
                templateResult: colorCodeSelect,
                templateSelection: colorCodeSelect,
                escapeMarkup: function (m) {
                    return m;
                }
            });

            function colorCodeSelect(state) {
                var colorCode = $(state.element).val();
                if (!colorCode) return state.text;
                return "<span class='color-preview' style='background-color:" + colorCode + ";'></span>" + state.text;
            }
        });
    </script>

   
@endpush
