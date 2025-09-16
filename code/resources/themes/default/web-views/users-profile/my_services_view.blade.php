@extends('layouts.front-end.app')

@section('title', \App\CPU\translate('My Bookings'))

@section('content')

    <div class="container text-center">
        <h3 class="headerTitle my-3">{{ \App\CPU\translate('my_bookings') }}</h3>
    </div>

    <!-- Page Content-->
    <div class="container pb-5 mb-2 mb-md-4 mt-3 rtl"
        style="text-align: {{ Session::get('direction') === 'rtl' ? 'right' : 'left' }};">
        <div class="row">
            <!-- Sidebar-->
            @include('web-views.partials._profile-aside')
            <!-- Content-->
            <section class="col-lg-9 col-md-9">
                <div class="card __card shadow-0">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table __table text-center">
                                <thead class="thead-light">
                                    <tr>
                                        <td class="tdBorder">
                                            <div class="py-2"><span
                                                    class="d-block spandHeadO">{{ \App\CPU\translate('Booking ID') }}</span>
                                            </div>
                                        </td>
                                        <td class="tdBorder">
                                            <div class="py-2"><span
                                                    class="d-block spandHeadO">{{ \App\CPU\translate('Service Name') }}</span>
                                            </div>
                                        </td>
                                        <td class="tdBorder">
                                            <div class="py-2"><span
                                                    class="d-block spandHeadO">{{ \App\CPU\translate('Booked By') }}</span>
                                            </div>
                                        </td>
                                        <td class="tdBorder">
                                            <div class="py-2"><span
                                                    class="d-block spandHeadO">{{ \App\CPU\translate('Booking Date') }}</span>
                                            </div>
                                        </td>
                                        <td class="tdBorder">
                                            <div class="py-2"><span
                                                    class="d-block spandHeadO">{{ \App\CPU\translate('Booking Time') }}</span>
                                            </div>
                                        </td>
                                        <td class="tdBorder">
                                            <div class="py-2"><span
                                                    class="d-block spandHeadO">{{ \App\CPU\translate('Status') }}</span>
                                            </div>
                                        </td>
                                        <!-- <td class="tdBorder">
                                            <div class="py-2"><span
                                                    class="d-block spandHeadO">{{ \App\CPU\translate('Paid Amount') }}</span>
                                            </div>
                                        </td> -->
                                        <td class="tdBorder">
                                            <div class="py-2"><span
                                                    class="d-block spandHeadO">{{ \App\CPU\translate('Action') }}</span>
                                            </div>
                                        </td>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($bookings as $booking)
                                        @php
                                            // Assuming each booking has a related service
                                            $service = $services->firstWhere('id', $booking->service_id); 
                                        @endphp
                                
                                        <tr>
                                            <td class="bodytr font-weight-bold">
                                                {{ $booking['booking_id'] }}
                                            </td>
                                            <td class="bodytr font-weight-bold">
                                                {{ $service ? $service['name'] : 'N/A' }}
                                            </td>
                                            <td class="bodytr">
                                                {{ $booking['patient_name'] }}
                                            </td>
                                            <td class="bodytr">
                                                {{ $booking['booking_datetime'] }}
                                            </td>
                                            <td class="bodytr">
                                                {{ $booking['booking_time'] }} - {{ $booking['till_time'] }}
                                            </td>
                                            <td class="bodytr">
                                                @if ($booking['status'] == 0)
                                                    <span class="badge badge-danger text-capitalize">
                                                        {{ \App\CPU\translate('Pending') }}
                                                    </span>
                                                @elseif($booking['status'] == 1)
                                                    <span class="badge badge-success text-capitalize">
                                                        {{ \App\CPU\translate('Confirmed') }}
                                                    </span>
                                                @elseif($booking['status'] == 2)
                                                    <span class="badge badge-success text-capitalize">
                                                        {{ \App\CPU\translate('Completed') }}
                                                    </span>
                                                @elseif($booking['status'] == 3)
                                                    <span class="badge badge-success text-capitalize">
                                                        {{ \App\CPU\translate('reschedule') }}
                                                    </span>
                                                @else
                                                    <span class="badge badge-info text-capitalize">
                                                        {{ \App\CPU\translate('cancelled') }}
                                                    </span>
                                                @endif
                                            </td>
                                
                                            <td class="bodytr d-flex">
                                                <div class="__btn-grp-sm flex-nowrap">
                                                    {{-- @dd($booking); --}}
                                                    <!-- Button trigger modal -->
                                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                                        data-bs-target="#exampleModal{{ $booking['id'] }}">
                                                        <i class="fa fa-eye"></i>
                                                    </button>
                                
                                                    <!-- Modal -->
                                                    <div class="modal fade" id="exampleModal{{ $booking['id'] }}"
                                                        tabindex="-1" aria-labelledby="exampleModalLabel"
                                                        aria-hidden="true">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <div class="modal-header serviceModal">
                                                                    <h1 class="modal-title fs-5" id="exampleModalLabel">
                                                                        My Bookings</h1>
                                                                    <button type="button" class="btn-close"
                                                                        data-bs-dismiss="modal" aria-label="Close">X</button>
                                                                </div>
                                                                <div class="modal-body serviceTableDiv">
                                                                    <table>
                                                                        <tr>
                                                                            <th>Booking ID</th>
                                                                            <td>{{ $booking['booking_id'] }}</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th>Service Name</th>
                                                                            <td>{{ $service ? $service['name'] : 'N/A' }}</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th>Booked By</th>
                                                                            <td>{{ $booking['patient_name'] }}</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th>Booking Date</th>
                                                                            <td>{{ $booking['booking_datetime'] }}</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th>Booking Time</th>
                                                                            <td>{{ $booking['booking_time'] }} to {{$booking['till_time']}}</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th>Paid Amount</th>
                                                                            <td>{{ $booking['paid_amount'] }}</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th>Booking Status</th>
                                                                            <td class="bodytr">
                                                                                @if ($booking['status'] == 0)
                                                                                    <span class="badge badge-danger text-capitalize">
                                                                                        {{ \App\CPU\translate('Pending') }}
                                                                                    </span>
                                                                                @elseif($booking['status'] == 1)
                                                                                    <span class="badge badge-success text-capitalize">
                                                                                        {{ \App\CPU\translate('Confirmed') }}
                                                                                    </span>
                                                                                @elseif($booking['status'] == 2)
                                                                                    <span class="badge badge-success text-capitalize">
                                                                                        {{ \App\CPU\translate('Completed') }}
                                                                                    </span>
                                                                                @else
                                                                                    <span class="badge badge-info text-capitalize">
                                                                                        {{ \App\CPU\translate('reschedule') }}
                                                                                    </span>
                                                                                @endif
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th>Order Note</th>
                                                                            <td>{{ $booking['order_note'] }}</td>
                                                                        </tr>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                
                                                <div class="__btn-grp-sm flex-nowrap">
                                                    <!-- Button trigger modal -->
                                                    @if($booking['status'] == 2)
                                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                                        data-bs-target="#exampleModal11{{ $booking['id'] }}">
                                                        <i class="fa fa-star"></i>
                                                    </button>
                                                    @endif
                                
                                                    <!-- Modal -->
                                                    <div class="modal fade" id="exampleModal11{{ $booking['id'] }}"
                                                        tabindex="-1" aria-labelledby="exampleModalLabel"
                                                        aria-hidden="true">
                                                        <div class="modal-dialog reviewServiceModal">
                                                            <div class="modal-content">
                                                                <div class="modal-header serviceModal">
                                                                    <h1 class="modal-title fs-5" id="exampleModalLabel">
                                                                        {{ \App\CPU\translate('submit_a_review') }}</h1>
                                                                    <button type="button" class="btn-close"
                                                                        data-bs-dismiss="modal" aria-label="Close">X</button>
                                                                </div>
                                                                <div class="modal-body serviceTableDiv">
                                                                    <section class="">
                                                                        <div class="card">
                                                                            <div class="card-body reviewFormModal">
                                                                                <form action="{{route('review.store_service')}}" method="post" enctype="multipart/form-data">
                                                                                    @csrf
                                                                                    <div class="modal-body p-0 reviewModalBody">
                                                                                        <div class="form-group">
                                                                                            <label for="rating">{{ \App\CPU\translate('rating') }}</label>
                                                                                            <select class="form-control" name="rating">
                                                                                                <option value="1">{{ \App\CPU\translate('1') }}</option>
                                                                                                <option value="2">{{ \App\CPU\translate('2') }}</option>
                                                                                                <option value="3">{{ \App\CPU\translate('3') }}</option>
                                                                                                <option value="4">{{ \App\CPU\translate('4') }}</option>
                                                                                                <option value="5">{{ \App\CPU\translate('5') }}</option>
                                                                                            </select>
                                                                                        </div>
                                                                                        <div class="form-group">
                                                                                            <label for="comment">{{ \App\CPU\translate('comment') }}</label>
                                                                                            <input name="service_id" value="{{ $booking->service_id }}" hidden>
                                                                                            <input name="order_id" value="{{ $booking->booking_id }}" hidden>
                                                                                            <textarea class="form-control" placeholder="Write a Review" name="comment"></textarea>
                                                                                        </div>
                                                                                        <div class="form-group">
                                                                                            <label for="attachment">{{ \App\CPU\translate('attachment') }}</label>
                                                                                            <div class="row coba"></div>
                                                                                            <div class="mt-1 text-info">{{ \App\CPU\translate('File type: jpg, jpeg, png. Maximum size: 2MB') }}</div>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="modal-footer reviewFooter">
                                                                                        <a href="{{ URL::previous() }}" class="btn btn-secondary">{{ \App\CPU\translate('back') }}</a>
                                                                                        <button type="submit" class="btn btn--primary">{{ \App\CPU\translate('submit') }}</button>
                                                                                    </div>
                                                                                </form>
                                                                            </div>
                                                                        </div>
                                                                    </section>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                
                            </table>

                            @if ($bookings->count() == 0)
                                <center class="mb-2 mt-3">{{ \App\CPU\translate('no_booking_found') }}</center>
                            @endif

                            {{-- <div class="card-footer border-0">
                                {{ $bookings->links() }}
                            </div> --}}
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

@endsection

@push('script')
    <script src="{{asset('public/assets/front-end/js/spartan-multi-image-picker.js')}}"></script>
    <script type="text/javascript">
        $(function () {
            $(".coba").spartanMultiImagePicker({
                fieldName: 'fileUpload[]',
                maxCount: 5,
                rowHeight: '150px',
                groupClassName: 'col-md-4',
                placeholderImage: {
                    image: '{{asset('public/assets/front-end/img/image-place-holder.png')}}',
                    width: '100%'
                },
                dropFileLabel: "{{\App\CPU\translate('drop_here')}}",
                onAddRow: function (index, file) {

                },
                onRenderedPreview: function (index) {

                },
                onRemoveRow: function (index) {

                },
                onExtensionErr: function (index, file) {
                    toastr.error('{{\App\CPU\translate('input_png_or_jpg')}}', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                },
                onSizeErr: function (index, file) {
                    toastr.error('{{\App\CPU\translate('file_size_too_big')}}', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                }
            });
        });
    </script>
@endpush

{{-- @push('script')
    <script>
        function cancel_message() {
            toastr.info('{{ \App\CPU\translate('booking_can_be_canceled_only_when_pending.') }}', {
                CloseButton: true,
                ProgressBar: true
            });
        }
    </script>
@endpush --}}

<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
    integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous">
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"
    integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous">
</script>
<style>
    table {
        width: 100%;
        border-collapse: collapse;
    }

    th,
    td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
    }

    th {
        background-color: #f2f2f2;
    }
    .btn-primary {
        color: #fff;
        background-color: #0081FE !important;
        border-color: #0081FE !important;
        box-shadow: none;
    }
    .btn-primary:focus, .btn-primary.focus {
    color: #fff;
    background-color: #0081FE !important;
    border-color: #0081FE !important;
    box-shadow: 0 0 0 0 #0081FE !important;
}
</style>
