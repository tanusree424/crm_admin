@extends('layouts.adminmaster')

@section('styles')
    <link href="{{ asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}?v={{ time() }}"
        rel="stylesheet" />
    <link href="{{ asset('assets/plugins/datatable/responsive.bootstrap5.css') }}?v={{ time() }}" rel="stylesheet" />
    <link href="{{ asset('assets/plugins/sweet-alert/sweetalert.css') }}?v={{ time() }}" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endsection

@section('content')
    <div class="page-header d-xl-flex d-block">
        <div class="page-leftheader">
            <h4 class="page-title"><span class="font-weight-normal text-muted ms-2">{{ lang('Mapping', 'menu') }}</span>
            </h4>
        </div>
    </div>

    <div class="col-xl-12 col-lg-12 col-md-12">
        <div class="card">
            <div class="card-header border-0 d-sm-max-flex">
                <h4 class="card-title">{{ lang('Mapping', 'menu') }}</h4>
                <div class="card-options mt-sm-max-2">
                    @can('Mapping Create')
                        <a href="{{ route('admin.mapping.create') }}" class="btn btn-secondary me-3"
                            id="create-new-Mapping">{{ lang('Add Mapping') }}</a>
                    @endcan
                </div>
            </div>

            <div class="card-body">
                <div class="table-responsive spruko-delete">

                    @can('Mapping Delete')
                        <button id="massdeletenotify" class="btn btn-outline-light btn-sm mb-4 data-table-btn">
                            <i class="fe fe-trash"></i> {{ lang('Delete') }}
                        </button>
                    @endcan

                    @can('Mapping Importlist')
                        <a href="{{ route('mapping.import') }}"
                            class="btn btn-info me-3 mb-3 align-right  mb-md-max-2 mw-12"><i
                                class="feather feather-download"></i> {{ lang('Import User Mapping List') }}</a>
                    @endcan

                   <table class="table table-bordered border-bottom text-nowrap ticketdeleterow w-100" id="mapping_table">
    <thead>
        <tr>
            <th width="10">{{ lang('Sl.No') }}</th>
            <th width="10">
                <input type="checkbox" id="customCheckAll">
            </th>
            <th>{{ lang('Customer Name') }}</th>

            <th>{{ lang('Agent Name') }}</th>
            <th>{{ lang('Modules Name') }}</th>
            <th class="w-5">{{ lang('Status') }}</th>
            <th class="w-5">{{ lang('Actions') }}</th>
        </tr>
    </thead>
    <tbody>
        @php $i = 1; @endphp
       @php $i = 1; @endphp
@foreach ($mapping as $map)
    <tr>
        <td>{{ $i++ }}</td>
        <td><input type="checkbox" class="checkall" value="{{ $map->id }}" /></td>

        {{-- Customer Name --}}
        <td>{{ $map->customer_name ?? 'N/A' }}</td>

        {{-- Employee Name (user) --}}
        <td>{{ $map->user->name ?? $map->empid ?? 'N/A' }}</td>

        {{-- Department Name --}}
        <td>{{ $map->modules ?? 'N/A' }}</td>

        {{-- Status Switch --}}
        <td>
            <label class="custom-switch form-switch mb-0">
                <input type="checkbox"
                       data-id="{{ $map->id }}"
                       class="custom-switch-input tswitch"
                       {{ $map->status == 'active' ? 'checked' : '' }}>
                <span class="custom-switch-indicator"></span>
            </label>
        </td>

        {{-- Action Buttons --}}
        <td>
            <div class="d-flex">
                @can('Mapping Edit')
                    <a href="javascript:void(0)" data-id="{{ $map->id }}"
                        onclick="editMapping(this)" class="action-btns1">
                        <i class="feather feather-edit text-primary" title="{{ lang('Edit') }}"></i>
                    </a>
                @else
                    ~
                @endcan

                @can('Mapping Delete')
                    <a href="javascript:void(0)" data-id="{{ $map->id }}"
                        class="action-btns1 delete-mapping">
                        <i class="feather feather-trash-2 text-danger" title="{{ lang('Delete') }}"></i>
                    </a>
                @else
                    ~
                @endcan
            </div>
        </td>
    </tr>
@endforeach


    </tbody>
</table>

                </div>
            </div>
        </div>
    </div>

    <!-- Edit Mapping Modal -->
    <div class="modal fade" id="editMappingModal" tabindex="-1" aria-labelledby="editMappingModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <form id="editMappingForm">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Mapping</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <input type="hidden" name="id" id="edit_mapping_id">

                        <div class="mb-3">
                            <label for="edit_empid" class="form-label">Employee</label>
                            <select name="empid" id="edit_empid" class="form-control select2"></select>
                        </div>

                        <div class="mb-3">
                            <label for="edit_modules" class="form-label">Modules (Departments)</label>
                            <select name="modules" id="edit_modules" class="form-control select2"></select>
                        </div>

                        <div class="mb-3">
                            <label for="edit_customer" class="form-label">Customers</label>
                            <select name="customer" id="edit_customer" class="form-control select2"></select>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="edit_status" name="status">
                            <label class="form-check-label" for="edit_status">Active</label>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Update Mapping</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <!-- Required plugins -->
    <script src="{{ asset('assets/plugins/vertical-scroll/jquery.bootstrap.newsbox.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatable/js/dataTables.bootstrap5.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatable/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatable/responsive.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/sweet-alert/sweetalert.min.js') }}"></script>

    <!-- Select2 and SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            $('#mapping_table').DataTable();

            $('.select2').select2({
                placeholder: 'Search and select',
                allowClear: true,
                dropdownParent: $('#editMappingModal')
            });

            $('#customCheckAll').on('click', function() {
                $('.checkall').prop('checked', this.checked);
            });

            $('#massdeletenotify').on('click', function() {
                var ids = $('.checkall:checked').map(function() {
                    return this.value;
                }).get();

                if (ids.length === 0) {
                    Swal.fire("Warning", "Please select at least one row.", "warning");
                    return;
                }

                Swal.fire({
                    title: "Are you sure?",
                    text: "Selected records will be deleted permanently.",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#dc3545",
                    cancelButtonColor: "#6c757d",
                    confirmButtonText: "Yes, delete!"
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('admin.mapping.massdelete') }}",
                            method: "POST",
                            data: {
                                ids: ids,
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(response) {
                                Swal.fire("Deleted!", response.message, "success").then(
                                    () => location.reload());
                            },
                            error: function() {
                                Swal.fire("Error!", "Internal server error.", "error");
                            }
                        });
                    }
                });
            });

            $('#editMappingForm').on('submit', function(e) {
                e.preventDefault();
                var id = $('#edit_mapping_id').val();

                var data = {
                    _token: '{{ csrf_token() }}',
                    empid: $('#edit_empid').val(),
                    modules: $('#edit_modules').val(),
                    customer: $('#edit_customer').val(),
                    status: $('#edit_status').is(':checked') ? 'active' : 'inactive'
                };

                console.log("Form Data Being Sent:", data);

                $.ajax({
                    url: '/admin/mapping/' + id,
                    method: 'PUT',
                    data: data,
                    success: function(response) {
                        console.log("Server Response:", response); // ✅ log full response

                        if (response.success) {
                            Swal.fire("Updated!", response.message, "success").then(() =>
                                location.reload());
                            $('#editMappingModal').modal('hide');
                        } else {
                            Swal.fire("Error!", response.message ?? "Update failed", "error");
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX Error:", error);
                        console.error("Status:", status);
                        console.error("Response Text:", xhr.responseText);

                        // Optionally show it with SweetAlert as well
                        Swal.fire("Error!", "Server error: " + xhr.responseText, "error");
                    }

                });
            });

        });

        // Edit Mapping Function
        function editMapping(el) {
            var id = $(el).data('id');

            $.ajax({
                url: '/admin/mapping/' + id + '/edit',
                method: 'GET',
                success: function(res) {
                    console.log("Full Response:", res);
                    console.log("Mapping Record:", res.mapping);
                    console.log("Departments:", res.departments);
                    console.log("Customers:", res.customers);
                    console.log("Users:", res.users);

                    // Set hidden input
                    $('#edit_mapping_id').val(res.mapping.id);

                    // Populate empid dropdown
                    $('#edit_empid').empty();

                    $.each(res.users, function(index, user) {
                        const optionText = `${user.name} (${user.empid})`; // Display: Name (Code)
                        const optionValue = user.id; // Value: user ID (primary key)

                        const isSelected = user.id == res.mapping.empid;

                        const option = new Option(optionText, optionValue, isSelected, isSelected);
                        $('#edit_empid').append(option);
                    });

                    $('#edit_empid').trigger('change');



                    // Populate modules dropdown (assuming departments)
                    let selectedDept = res.mapping.modules; // assuming 'modules' holds department name

                    $.each(res.departments, function(index, dept) {
                        $('#edit_modules').append(
                            new Option(dept.departmentname, dept.departmentname, dept
                                .departmentname === selectedDept, dept.departmentname ===
                                selectedDept)
                        );
                    });


                    // Populate customer dropdown
                    //let selectedCustomer = res.mapping.customer; // assuming this is the customer's first name

                    let selectedCustomer = res.mapping.customer_name; // e.g., customer.id

                    $('#edit_customer').empty();
                    $.each(res.customers, function(index, customer) {
                        let fullName = customer.firstname + ' ' + customer.lastname;
                        let customer_id = customer.id;

                        $('#edit_customer').append(
                            new Option(fullName, customer_id, false, customer_id ==
                                selectedCustomer)
                        );
                    });



                    // Set status
                    $('#edit_status').prop('checked', res.mapping.status == "active");

                    // Show modal
                    $('#editMappingModal').modal('show');
                },
                error: function() {
                    Swal.fire("Error", "Could not load data.", "error");
                }
            });
        }

        // Delete Mapping
        $(document).on('click', '.delete-mapping', function() {
            const id = $(this).data('id');

            Swal.fire({
                title: "Are you sure?",
                text: "This record will be permanently deleted.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#e3342f",
                cancelButtonColor: "#6c757d",
                confirmButtonText: "Yes, delete it!"
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/admin/mapping/' + id,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire("Deleted!", response.message, "success").then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire("Error!", response.message ?? "Delete failed",
                                    "error");
                            }
                        },
                        error: function(xhr) {
                            console.error(xhr.responseText);
                            Swal.fire("Error!", "Server error", "error");
                        }
                    });
                }
            });
        });
        $(document).on('change', '.tswitch', function() {
            let mappingId = $(this).data('id');
            let newStatus = $(this).is(':checked') ? 'active' : 'inactive';

            $.ajax({
                url: '/admin/mapping/' + mappingId + '/toggle-status',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    status: newStatus
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire("Success", response.message, "success");
                    } else {
                        Swal.fire("Error", response.message ?? "Update failed", "error");
                    }
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    Swal.fire("Error", "Server error occurred.", "error");
                }
            });
        });
    </script>
@endsection
