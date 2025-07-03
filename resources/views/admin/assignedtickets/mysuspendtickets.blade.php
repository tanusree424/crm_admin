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
									<h4 class="page-title"><span class="font-weight-normal text-muted ms-2">{{lang('My Suspend Tickets', 'menu')}}</span></h4>
								</div>
							</div>
							<!--End Page header-->


							<div class="col-xl-12 col-lg-12 col-md-12">
								<div class="card ">
									<div class="card-header border-0">
										<h4 class="card-title">{{lang('My Suspend Tickets', 'menu')}}</h4>
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
															<th>Ticket Details</th>
															<th>User</th>
															<th>Mobile No.</th>
															<th>Status</th>
															<th>Assign To</th>
															<th>Action</th>
													</tr>
												</thead>
											</table>
										</div>
									</div>
								</div>
							</div>
							</div>

							@endsection


		@section('scripts')


		<!-- INTERNAL Data tables -->
		<script src="{{asset('assets/plugins/datatable/js/jquery.dataTables.min.js')}}?v=<?php echo time(); ?>"></script>
		<script src="{{asset('assets/plugins/datatable/js/dataTables.bootstrap5.js')}}?v=<?php echo time(); ?>"></script>
		<script src="{{asset('assets/plugins/datatable/dataTables.responsive.min.js')}}?v=<?php echo time(); ?>"></script>
		<script src="{{asset('assets/plugins/datatable/responsive.bootstrap5.min.js')}}?v=<?php echo time(); ?>"></script>


		<!-- INTERNAL Index js-->
		<script src="{{asset('assets/js/support/support-sidemenu.js')}}?v=<?php echo time(); ?>"></script>
		<script src="{{asset('assets/js/support/support-admindash.js')}}?v=<?php echo time(); ?>"></script>
		<script src="{{asset('assets/js/select2.js')}}?v=<?php echo time(); ?>"></script>

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

				// DataTable
				// $('#overduetickets').DataTable({
				// 	language: {
				// 		searchPlaceholder: search,

				// 		sSearch: '',
				// 	},
				// 	order:[],
				// 	columnDefs: [
				// 		{ "orderable": false, "targets":[ 0,1,6] }
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
						url: '{{ route("admin.allmysuspendedTickets") }}',
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
						{ data: 'status', name: 'status' , orderable: false, searchable: false },
						{ data: 'assignedTo', name: 'assignedTo' , orderable: false, searchable: false },
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
                $('#overduetickets').dataTable({
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

				$('.form-select').select2({
					minimumResultsForSearch: Infinity,
					width: '100%'
				});

				// TICKET DELETE SCRIPT
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
								url: SITEURL + "/admin/delete-ticket/"+_id,
								success: function (data) {
									toastr.success(data.success);
									var oTable = $('#overduetickets').dataTable();
									oTable.fnDraw(false);
									location.reload();
								},
								error: function (data) {
									console.log('Error:', data);
								}
							});
						}
					});

				});
				// TICKET DELETE SCRIPT END

				//Mass Delete
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
									url:"{{ url('admin/ticket/delete/tickets')}}",
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

				// when user click its get modal popup to assigned the ticket
				$('body').on('click', '#assigned', function () {
					var assigned_id = $(this).data('id');

					$('.select2_modalassign').select2({
						dropdownParent: ".sprukosearch",
						minimumResultsForSearch: '',
						placeholder: "Search",
						width: '100%'
					});

					$.get('assigned/' + assigned_id , function (data) {
						$('#AssignError').html('');
						$('#assigned_id').val(data.assign_data.id);
						$(".modal-title").text('{{lang('Assign To Agent')}}');
						$('#username').html(data.table_data);
						$('#addassigned').modal('show');
					});
				});

				// Assigned Submit button
				$('body').on('submit', '#assigned_form', function (e) {
					e.preventDefault();
					var actionType = $('#btnsave').val();
					var fewSeconds = 2;
					$('#btnsave').html('Sending..');
					$('#btnsave').prop('disabled', true);
						setTimeout(function(){
							$('#btnsave').prop('disabled', false);
						}, fewSeconds*1000);
					var formData = new FormData(this);

					$.ajax({
						type:'POST',
						url: SITEURL + "/admin/assigned/create",
						data: formData,
						cache:false,
						contentType: false,
						processData: false,
						success: (data) => {
							$('#AssignError').html('');
							$('#assigned_form').trigger("reset");
							$('#addassigned').modal('hide');
							$('#btnsave').html('{{lang('Save Changes')}}');
							var oTable = $('#overduetickets').dataTable();
							oTable.fnDraw(false);
							toastr.success(data.success);
							location.reload();
						},
						error: function(data){
							$('#AssignError').html('');
							$('#AssignError').html(data.responseJSON.errors.assigned_user_id);
							$('#btnsave').html('{{lang('Save Changes')}}');
						}
					});
				});

				// Remove the assigned from the ticket
				$('body').on('click', '#btnremove', function () {
					var asid = $(this).data("id");
					swal({
						title: `{{lang('Are you sure you want to unassign this agent?', 'alerts')}}`,
						text: "{{lang('This agent may no longer exist for this ticket.', 'alerts')}}",
						icon: "warning",
						buttons: true,
						dangerMode: true,
					})
					.then((willDelete) => {
						if (willDelete) {

							$.ajax({
								type: "get",
								url: SITEURL + "/admin/assigned/update/"+asid,
								success: function (data) {
								var oTable = $('#overduetickets').dataTable();
								oTable.fnDraw(false);
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

				// Checkbox checkall
				$('#customCheckAll').on('click', function() {
					$('.checkall').prop('checked', this.checked);
				});

				$('body').on('click','#selfassigid', function(e){

					e.preventDefault();

					let id = $(this).data('id');

					$.ajax({
						method:'POST',
						url: '{{route('admin.selfassign')}}',
						data: {
							id : id,
						},
						success: (data) => {
							toastr.success(data.success);
							location.reload();
						},
						error: function(data){

						}
					});
				})

			})(jQuery);

		</script>

		@endsection

			@section('modal')

			@include('admin.modalpopup.assignmodal')
			@endsection
