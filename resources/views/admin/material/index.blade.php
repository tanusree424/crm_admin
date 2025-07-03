@extends('layouts.adminmaster')

@section('styles')
<!-- INTERNAL Data table css -->
<link href="{{ asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}?v={{ time() }}" rel="stylesheet" />
<link href="{{ asset('assets/plugins/datatable/responsive.bootstrap5.css') }}?v={{ time() }}" rel="stylesheet" />

<!-- INTERNAL Sweet-Alert css -->
<link href="{{ asset('assets/plugins/sweet-alert/sweetalert.css') }}?v={{ time() }}" rel="stylesheet" />
@endsection

@section('content')
<!-- Page header -->
<div class="page-header d-xl-flex d-block">
    <div class="page-leftheader">
        <h4 class="page-title"><span class="font-weight-normal text-muted ms-2">{{ lang('Materials', 'menu') }}</span></h4>
    </div>
</div>
<!-- End Page header -->

<!-- Material List -->
<div class="col-xl-12 col-lg-12 col-md-12">
    <div class="card">
        <div class="card-header border-0 d-md-max-block">
            <h4 class="card-title">{{ lang('Materials List') }}</h4>
            <div class="card-options mt-sm-max-2 d-md-max-block">
                @can('Materials Create')
                    <a href="{{ url('admin/material/create') }}" class="btn btn-success mb-md-max-2 me-3">
                        <i class="feather feather-plus"></i> {{ lang('Add Material') }}
                    </a>
                @endcan
                @can('Materials Importlist')
                    <a href="" class="btn btn-info mb-md-max-2 me-3">
                        <i class="feather feather-download"></i> {{ lang('Import Materials List') }}
                    </a>
                @endcan
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive spruko-delete">
                @can('Materials Delete')
                    <button id="massdelete" class="btn btn-outline-light btn-sm mb-4 data-table-btn">
                        <i class="fe fe-trash"></i> {{ lang('Delete') }}
                    </button>
                @endcan

                <table class="table table-bordered border-bottom text-nowrap ticketdeleterow w-100" id="support-materiallist">
                    <thead>
                        <tr>
                            <th width="10">{{ lang('Sl.No') }}</th>
                            @can('Materials Delete')
                                <th width="10">
                                    <input type="checkbox" id="customCheckAll">
                                    <label for="customCheckAll"></label>
                                </th>
                            @endcan
                            @cannot('Materials Delete')
                                <th width="10">
                                    <input type="checkbox" id="customCheckAll" disabled>
                                    <label for="customCheckAll"></label>
                                </th>
                            @endcannot
                            <th>{{ lang('Material Code') }}</th>
                            <th>{{ lang('Material Name') }}</th>
                            <th>{{ lang('Description') }}</th>
                            <th>{{ lang('Group Code 1') }}</th>
                            <th>{{ lang('Group Code 2') }}</th>
                            <th>{{ lang('Group Code 3') }}</th>
                            <th>{{ lang('MRP') }}</th>
                            <th>{{ lang('Division Code') }}</th>
                            <th>{{ lang('Serialized') }}</th>
                            <th>{{ lang('Repairable') }}</th>
                            <th>{{ lang('On-Site Allowed') }}</th>
                            <th>{{ lang('Actions') }}</th>
                            <th>{{ lang('is Active') }}</th>
                            <th>{{ lang('Warranty Years') }}</th>
                            <th>{{ lang('Warranty Days') }}</th>
                            <th>{{ lang('Number of Repairs') }}</th>
                            <th>{{ lang('Is Service Charge Applicable') }}</th>

                        </tr>
                    </thead>
                    <tbody>
                        @php $i = 1; @endphp
                        @foreach($materials as $material)
                            <tr>
                                <td>{{ $i++ }}</td>
                                <td>
                                    @can('Materials Delete')
                                        <input type="checkbox" name="material_checkbox[]" class="checkall" value="{{ $material->id }}" />
                                    @else
                                        <input type="checkbox" name="material_checkbox[]" class="checkall" value="{{ $material->id }}" disabled />
                                    @endcan
                                </td>
                                <td>{{ $material->material_code }}</td>
                                <td>{{ $material->material_name }}</td>
                                <td>{{ $material->material_description }}</td>
                                <td>{{ optional($material->group1)->name ?? 'N/A' }}</td>
                                <td>{{ optional($material->group2)->name ?? 'N/A' }}</td>
                                <td>{{ optional($material->group3)->name ?? 'N/A' }}</td>
                                <td>{{ $material->mrp }}</td>
                                <td>{{ $material->division_code }}</td>
                                <td>{{ $material->isserialized ? 'Yes' : 'No' }}</td>
                                <td>{{ $material->isrepairable ? 'Yes' : 'No' }}</td>
                                <td>{{ $material->isonsiteallowed ? 'Yes' : 'No' }}</td>
                                <td>{{ $material->is_active ? lang('Yes') : lang('No') }}</td>
                                <td>{{ $material->warranty_years }}</td>
                                <td>{{ $material->warrant_days }}</td>
                                <td>{{ $material->numberofrepair }}</td>
                                <td>{{ $material->is_servicecharge_applicable ? lang('Yes') : lang('No') }}</td>
                                <td>
                                    <div class="d-flex">
                                        @can('Materials Edit')
                                            <a href="{{ url('/admin/material/' . $material->id) }}" class="action-btns1" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ lang('Edit') }}">
                                                <i class="feather feather-edit text-primary"></i>
                                            </a>
                                        @endcan
                                        @can('Materials Delete')
                                            <a href="javascript:void(0)" class="action-btns1" data-id="{{ $material->id }}" id="show-delete" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ lang('Delete') }}">
                                                <i class="feather feather-trash-2 text-danger"></i>
                                            </a>
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
<!-- End Material List -->
@endsection

@section('scripts')
<!-- INTERNAL Vertical-scroll js -->
<script src="{{ asset('assets/plugins/vertical-scroll/jquery.bootstrap.newsbox.js') }}?v={{ time() }}"></script>

<!-- INTERNAL Data tables -->
<script src="{{ asset('assets/plugins/datatable/js/jquery.dataTables.min.js') }}?v={{ time() }}"></script>
<script src="{{ asset('assets/plugins/datatable/js/dataTables.bootstrap5.js') }}?v={{ time() }}"></script>
<script src="{{ asset('assets/plugins/datatable/dataTables.responsive.min.js') }}?v={{ time() }}"></script>
<script src="{{ asset('assets/plugins/datatable/responsive.bootstrap5.min.js') }}?v={{ time() }}"></script>

<!-- INTERNAL Index js -->
<script src="{{ asset('assets/js/support/support-sidemenu.js') }}?v={{ time() }}"></script>

<!-- INTERNAL Sweet-Alert js -->
<script src="{{ asset('assets/plugins/sweet-alert/sweetalert.min.js') }}?v={{ time() }}"></script>

<script type="text/javascript">
    "use strict";
    (function($) {
        // Variables
        var SITEURL = '{{ url('') }}';

        // Csrf Field
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Datatable
        $('#support-materiallist').dataTable({
            language: {
                searchPlaceholder: '{{ lang("Search...") }}',
                scrollX: "100%",
                sSearch: '',
                paginate: {
                    previous: '{{ lang("Previous") }}',
                    next: '{{ lang("Next") }}'
                },
                emptyTable: '{{ lang("No data available in table") }}',
                infoFiltered: '{{ lang("filtered from") }} _MAX_ {{ lang("records") }}',
                info: '{{ lang("showing page") }} _PAGE_ {{ lang("of") }} _PAGES_',
                infoEmpty: '{{ lang("No entries to show") }}',
                lengthMenu: '{{ lang("Show") }} _MENU_ {{ lang("entries") }} ',
            },
            order: [],
            columnDefs: [
                { "orderable": false, "targets": [0, 1, 12] }
            ],
        });

        // Delete the material
        $('body').on('click', '#show-delete', function () {
            var _id = $(this).data("id");
            swal({
                title: '{{ lang("Are you sure you want to continue?", "alerts") }}',
                text: '{{ lang("This might erase your records permanently", "alerts") }}',
                icon: "warning",
                buttons: true,
                dangerMode: true,
            })
            .then((willDelete) => {
                if (willDelete) {
                    $.ajax({
                        type: "get",
                        url: SITEURL + "/admin/material/delete/" + _id,
                        success: function (data) {
                            toastr.success(data.success);
                            location.reload();
                        },
                        error: function (data) {
                            console.log('Error:', data);
                        }
                    });
                }
            });
        });

        // Mass Delete
        $('body').on('click', '#massdelete', function () {
            var id = [];
            $('.checkall:checked').each(function(){
                id.push($(this).val());
            });
            if (id.length > 0) {
                swal({
                    title: '{{ lang("Are you sure you want to continue?", "alerts") }}',
                    text: '{{ lang("This might erase your records permanently", "alerts") }}',
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                })
                .then((willDelete) => {
                    if (willDelete) {
                        $.ajax({
                            url: "{{ url('admin/massmaterial/delete') }}",
                            method: "GET",
                            data: { id: id },
                            success: function(data) {
                                toastr.success(data.success);
                                location.reload();
                            },
                            error: function(data) {
                                console.log('Error:', data);
                            }
                        });
                    }
                });
            } else {
                toastr.error('{{ lang("Please select at least one check box.", "alerts") }}');
            }
        });

        // Checkbox check all
        $('#customCheckAll').on('click', function() {
            $('.checkall').prop('checked', this.checked);
        });

        $('.form-select').select2({
            minimumResultsForSearch: Infinity,
            width: '100%'
        });

        $('#customCheckAll').prop('checked', false);

        $('.checkall').on('click', function(){
            if($('.checkall:checked').length == $('.checkall').length){
                $('#customCheckAll').prop('checked', true);
            } else {
                $('#customCheckAll').prop('checked', false);
            }
        });
    })(jQuery);
</script>
@endsection
