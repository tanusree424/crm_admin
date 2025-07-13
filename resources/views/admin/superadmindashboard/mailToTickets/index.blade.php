@extends('layouts.adminmaster')

@section('styles')
    <!-- INTERNAL Data table css -->
    <link href="{{ asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/plugins/datatable/responsive.bootstrap5.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/plugins/datatable/buttonbootstrap.min.css') }}" rel="stylesheet" />

    <!-- INTERNAL Sweet-Alert css -->
    <link href="{{ asset('assets/plugins/sweet-alert/sweetalert.css') }}" rel="stylesheet" />

    <style>
        .uhelp-reply-badge {
            right: 14px;
            bottom: 10px;
            z-index: 1;
        }

        .pulse-badge {
            animation: pulse 1s linear infinite;
        }

        .pulse-badge.disabled {
            color: #b5c0df;
            animation: none;
        }

        @keyframes pulse {

            0%,
            100% {
                color: rgba(13, 205, 148, 0);
            }

            50% {
                color: rgba(13, 205, 148, 1);
            }
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid mt-4">
        <h4 class="mb-4">📥 Mail to Tickets</h4>

       <div class="mb-2 d-flex justify-content-end">
    <button class="btn btn-success" id="move-to-dashboard-btn">
        Move to Dashboard <i class="fe fe-arrow-right ms-1"></i>
    </button>
</div>


        <div class="table-responsive">
            <table id="mailTicketTable" class="table table-bordered w-100">
                <thead>
                    <tr>
                        <th>Serial</th>
                        <th><input type="checkbox" id="checkAll" /></th>
                        <th>Ticket Info</th>
                        <th>Customer</th>
                        <th>Mobile No</th>
                        <th>Status</th>
                        <th>Assigned To</th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection

@section('scripts')
    <!-- INTERNAL Data table js -->
    <script src="{{ asset('assets/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatable/js/dataTables.bootstrap5.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatable/dataTables.responsive.min.js') }}"></script>

    <!-- SweetAlert -->
    <script src="{{ asset('assets/plugins/sweet-alert/sweetalert.min.js') }}"></script>
    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            const table = $('#mailTicketTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('admin.mailtotickets.data') }}',
                columns: [{
                        data: null,
                        name: 'serial',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },


                    // ✅ Use a new column 'checkbox' for checkbox
                    {
                        data: 'checkbox',
                        name: 'checkbox',
                        orderable: false,
                        searchable: false
                    },

                    // ✅ 'id' will now only be used for subject & ticket info display
                    {
                        data: 'id',
                        name: 'id'
                    },

                    {
                        data: 'custname',
                        name: 'custname'
                    },
                    {
                        data: 'mobilenumber',
                        name: 'mobilenumber'
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },
                    {
                        data: 'assignedTo',
                        name: 'assignedTo'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],
                order: [],
                drawCallback: function() {
                    $('[data-bs-toggle="tooltip"]').tooltip();
                }
            });

            // Check/uncheck all
            $('#checkAll').on('change', function() {
                $('.row-checkbox').prop('checked', $(this).is(':checked'));
            });

            // Move to Dashboard button click
            $('#move-to-dashboard-btn').click(function() {
                const selectedIds = $('.row-checkbox:checked').map(function() {
                    console.log("Checkbox raw value:", this.value); // ✅ Confirm checkbox value
                    return parseInt(this.value);
                }).get();

                console.log("Selected Ticket IDs:", selectedIds); // ✅ Confirm parsed list

                if (selectedIds.length === 0 || selectedIds.includes(NaN)) {
                    Swal.fire('No Selection', 'Please select at least one valid ticket.', 'warning');
                    return;
                }

                Swal.fire({
                    title: 'Are you sure?',
                    text: "Selected tickets will be moved to dashboard.",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Move',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        let formData = new FormData();
                        formData.append('_token', '{{ csrf_token() }}');
                        selectedIds.forEach(id => formData.append('ticket_ids[]', id));

                        $.ajax({
                            url: "{{ route('admin.mailtotickets.move') }}",
                            method: "POST",
                            data: formData,
                            processData: false,
                            contentType: false,
                            success: function(response) {
                                Swal.fire('Success', 'Tickets moved successfully!',
                                    'success').then(() => {
                                    window.location.href =
                                        "{{ route('admin.dashboard') }}";
                                });
                            },
                            error: function(xhr) {
                                Swal.fire('Error', 'Something went wrong.\n' + xhr
                                    .responseText, 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
