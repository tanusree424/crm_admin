@extends('layouts.adminmaster')

@section('styles')

<!-- INTERNAL Data table css -->
<link href="{{asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css')}}?v=<?php echo time(); ?>" rel="stylesheet" />
<link href="{{asset('assets/plugins/datatable/responsive.bootstrap5.css')}}?v=<?php echo time(); ?>" rel="stylesheet" />

<!-- INTERNAL Sweet-Alert css -->
<link href="{{asset('assets/plugins/sweet-alert/sweetalert.css')}}?v=<?php echo time(); ?>" rel="stylesheet" />

@endsection

@section('content')

<!--Page header-->
<div class="page-header d-xl-flex d-block">
	<div class="page-leftheader">
		<h4 class="page-title"><span class="font-weight-normal text-muted ms-2">{{lang('Customers', 'menu')}}</span></h4>
	</div>
</div>
<!--End Page header-->

<!-- Customer List -->
<div class="col-xl-12 col-lg-12 col-md-12">
	<div class="card ">
		<div class="card-header border-0 d-md-max-block">
			<h4 class="card-title">{{lang('Customers List')}}</h4>
			<div class="card-options mt-sm-max-2 d-md-max-block">
				@can('Customers Create')

				<a href="{{url('admin/customer/create')}}" class="btn btn-success mb-md-max-2 me-3"><i class="feather feather-user-plus"></i> {{lang('Add Customer')}}</a>
				@endcan
                @can('Customers Importlist')
				<a href="{{route('admin.customer.import')}}" class="btn btn-info mb-md-max-2 me-3"><i class="feather feather-download"></i> {{lang('Import Customer List')}}</a>
                @endcan
			</div>
		</div>
		<div class="card-body overflow-scroll" >
			<div class=" spruko-delete">
				<!-- @can('Ticket Delete')

				<button id="massdelete" class="btn btn-outline-light btn-sm mb-4 ticketdeleterow data-table-btn"><i class="fe fe-trash"></i> {{lang('Delete')}}</button>
				@endcan -->
				<button id="refreshdata" class="btn btn-outline-light btn-sm mb-4 "><i class="fe fe-refresh-cw"></i> </button>
				<div class="container mt-5">
					<table class="table table-bordered border-bottom text-nowrap w-100" id="itemsTable" class="display" style="width:100%">
					<thead>
						<tr>
								<th>Sl.No</th>
								<th >Name</th>
								<th >User Type</th>
								<th >Mobile Number</th>
								<th >Verification</th>
								<th >Register Date</th>
								<th class="w-5">Status</th>
								<th >Actions</th>
							</tr>
						</thead>
				</table>
			</div>
		</div>
	</div>
</div>
</div>
<!-- End Customer List -->
@endsection

@section('scripts')

<!-- INTERNAL Vertical-scroll js-->
<script src="{{asset('assets/plugins/vertical-scroll/jquery.bootstrap.newsbox.js')}}?v=<?php echo time(); ?>"></script>

<!-- INTERNAL Data tables -->
<script src="{{asset('assets/plugins/datatable/js/jquery.dataTables.min.js')}}?v=<?php echo time(); ?>"></script>
<script src="{{asset('assets/plugins/datatable/js/dataTables.bootstrap5.js')}}?v=<?php echo time(); ?>"></script>
<script src="{{asset('assets/plugins/datatable/dataTables.responsive.min.js')}}?v=<?php echo time(); ?>"></script>
<script src="{{asset('assets/plugins/datatable/responsive.bootstrap5.min.js')}}?v=<?php echo time(); ?>"></script>

<!-- INTERNAL Index js-->
<script src="{{asset('assets/js/support/support-sidemenu.js')}}?v=<?php echo time(); ?>"></script>

<!-- INTERNAL Sweet-Alert js-->
<script src="{{asset('assets/plugins/sweet-alert/sweetalert.min.js')}}?v=<?php echo time(); ?>"></script>

<script type="text/javascript">

	"use strict";

	(function($)  {

		// Variables
		var SITEURL = '{{url('')}}';

		// Csrf Field
		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			}
		});

		// Datatable
		// $('#support-customerlist').DataTable({
		// 	responsive: true,
		// 	language: {
		// 		searchPlaceholder: search,
		// 		scrollX: "100%",
		// 		sSearch: '',
		// 	},
		// 	order:[],
		// 	columnDefs: [
		// 		{ "orderable": false, "targets":[ 0,1,7] }
		// 	],
		// });

        let prev = {!! json_encode(lang("Previous")) !!};
        let next = {!! json_encode(lang("Next")) !!};
        let nodata = {!! json_encode(lang("No data available in table")) !!};
        let noentries = {!! json_encode(lang("No entries to show")) !!};
        let showing = {!! json_encode(lang("showing page")) !!};
        let ofval = {!! json_encode(lang("of")) !!};
        let maxRecordfilter = {!! json_encode(lang("- filtered from ")) !!};
        let maxRecords = {!! json_encode(lang("records")) !!};
        let entries = {!! json_encode(lang("entries")) !!};
        let show = {!! json_encode(lang("Show")) !!};
        let search = {!! json_encode(lang("Search...")) !!};

		var itemsTable = $('#itemsTable').DataTable({
					processing: true,
					serverSide: true,
					ajax: {
						url: '{{ route("admin.allcustomerdata") }}',
						error: function(xhr, error, code) {
							console.error("DataTables AJAX request failed:", xhr.responseText);
						}
					},
					// ajax: '{{ route("admin.data") }}',
					columns: [
						{ data: 'serial', name: 'serial' , orderable: false, searchable: false },
						{ data: 'id', name: 'id' , orderable: false, searchable: false },
						{ data: 'custname', name: 'custname' , orderable: false, searchable: false },
						{ data: 'mobilenumber', name: 'mobilenumber' , orderable: false, searchable: false },
						{ data: 'verification', name: 'verification' , orderable: false, searchable: false },
						{ data: 'registerdate', name: 'registerdate' , orderable: false, searchable: false },
						{ data: 'status', name: 'status' , orderable: false, searchable: false },
						{ data: 'action', name: 'action', orderable: false, searchable: false },
					],
					"drawCallback": function(settings) {
						// Reset serial number on each page draw
						var pageInfo = itemsTable.page.info();  // Get current page info
						var start = pageInfo.start;         // Starting index for the current page
						itemsTable.column(0).nodes().each(function(cell, index) {
							// Set the serial number based on the row index
							$(cell).html(start + index + 1);
						});
					},
					pageLength: 10, // Number of records per page
					lengthMenu: [5, 10, 20, 50], // Options for "Show X entries"
				});

				$('#refreshdata').on('click', function(e){
					e.preventDefault();;
					itemsTable.ajax.reload(null, true);
				}); 

        // Datatable
        $('#support-customerlist').dataTable({
            language: {
                searchPlaceholder: search,
                scrollX: "100%",
                sSearch: '',
                paginate: {
                previous: prev,
                next: next
                },
                emptyTable : nodata,
                infoFiltered: `${maxRecordfilter} _MAX_ ${maxRecords}`,
                info: `${showing} _PAGE_ ${ofval} _PAGES_`,
                infoEmpty: noentries,
                lengthMenu: `${show} _MENU_ ${entries} `,
            },
            order:[],
            columnDefs: [
                { "orderable": false, "targets":[ 0,1,4] }
            ],
        });

		// Delete the customer
		$('body').on('click', '#show-delete', function () {
			var _id = $(this).data("id");
			swal({
				title: `{{lang('Are you sure you want to continue?', 'alerts')}}`,
				text: "{{lang('This might erase your records permanently', 'alerts')}}",
				icon: "warning",
				buttons: true,
				dangerMode: true,
			})
			.then((willDelete) => {
				if (willDelete) {
					$.ajax({
						type: "get",
						url: SITEURL + "/admin/customer/delete/"+_id,
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
			if(id.length > 0){
				swal({
					title: `{{lang('Are you sure you want to continue?', 'alerts')}}`,
					text: "{{lang('This might erase your records permanently', 'alerts')}}",
					icon: "warning",
					buttons: true,
					dangerMode: true,
				})
				.then((willDelete) => {
					if (willDelete) {
						$.ajax({
							url:"{{ url('admin/masscustomer/delete')}}",
							method:"GET",
							data:{id:id},
							success:function(data)
							{
								toastr.success(data.success);
								location.reload();
							},
							error:function(data){

							}
						});
					}
				});
			}else{
				toastr.error('{{lang('Please select at least one check box.', 'alerts')}}');
			}

		});


		// resend verification code to the customer
		$('body').on('click', '#resendverification', function () {
			var _id = $(this).data("id");

			swal({
				title: `{{lang('Are you sure you want to continue?', 'alerts')}}`,
				text: "{{lang('This is to resend email verification link to the customer', 'alerts')}}",
				icon: "warning",
				buttons: true,
				dangerMode: true,
			})
			.then((willDelete) => {
				if (willDelete) {
					$.ajax({
						type: "get",
						url: SITEURL + "/admin/customer/resendverification/"+_id,
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
			}else{
				$('#customCheckAll').prop('checked', false);
			}
		});
	})(jQuery);

</script>
@endsection


