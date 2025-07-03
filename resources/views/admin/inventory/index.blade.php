@extends('layouts.adminmaster')

		@section('styles')

		<!-- INTERNAL Data table css -->
		<link href="{{asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css')}}?v=<?php echo time(); ?>" rel="stylesheet" />
		<link href="{{asset('assets/plugins/datatable/responsive.bootstrap5.css')}}?v=<?php echo time(); ?>" rel="stylesheet" />

		<!-- INTERNAL Sweet-Alert css -->
		<link href="{{asset('assets/plugins/sweet-alert/sweetalert.css')}}?v=<?php echo time(); ?>" rel="stylesheet" />

		<!-- INTERNAL Multiselect css -->
		<link href="{{asset('assets/plugins/multipleselect/multiple-select.css')}}?v=<?php echo time(); ?>" rel="stylesheet" />
		<link href="{{asset('assets/plugins/multi/multi.min.css')}}?v=<?php echo time(); ?>" rel="stylesheet" />

		@endsection

							@section('content')

							<!--Page header-->
							<div class="page-header d-xl-flex d-block">
								<div class="page-leftheader">
									<h4 class="page-title"><span class="font-weight-normal text-muted ms-2">{{lang('Inventory', 'menu')}}</span></h4>
								</div>
							</div>
							<!--End Page header-->

							<!-- Project List-->
							<div class="col-xl-12 col-lg-12 col-md-12">
								<div class="card ">
									<div class="card-header border-0 d-md-max-block">
										<h4 class="card-title mb-md-max-2">{{lang('Inventory', 'menu')}}</h4>
										<div class="card-options d-md-max-block">
											@can('Inventory Create')
											<a href="{{ url('admin/inventory/create') }}" class="btn btn-success me-3 mb-md-max-2 mw-10">{{lang('Add Inventory')}}</a>
											@endcan


											@can('Export Inventory')
												<a  class="btn btn-danger mb-md-max-2 me-3 exportInventory" href="{{ url('admin/inventory/export') }}">
													<i class="feather feather-download"></i> {{ lang('Export Inventory') }}
												</a>
											@endcan

											  @can('Import Inventory')
												<a  class="btn btn-primary mb-md-max-2 me-3 importInventory">
													<i class="feather feather-upload"></i> {{ lang('Import Inventory') }}
												</a>
											@endcan
										</div>
									</div>
									<div class="card-body" >
										<div class="table-responsive spruko-delete">
											<table class="table table-bordered border-bottom text-nowrap ticketdeleterow w-100" id="support-articlelists">
												<thead>
													<tr>
														<th>Sl.No	</th>
														<th>Location</th>
														<th>Country Name</th>
														<th>Spare Code</th>
														<th>Spare Name</th>
														<th>Quantity</th>
														<th>Date</th>
													</tr>
												</thead>
												<tbody>
														@php $i = 1; @endphp
												@foreach($inventory as $ints)
												<tr>
													<td>{{ $i++ }}</td>
													<td>{{ $ints->location }}</td>
													<td>{{ $ints->country_name }}</td>
													<td>{{ $ints->material_code }}</td>
													<td>{{ $ints->material_name }}</td>
													<td>{{ $ints->quantity }}</td>
													<td>{{ $ints->created_at }}</td>
												</tr>
												@endforeach
												</tbody>
											</table>
										</div>
									</div>
								</div>
							</div>
							<!-- End Project List-->

							@endsection
	@section('modal')

    @include('admin.inventory.model')
	@include('admin.inventory.import')



			<!-- INTERNAL Multiselect Js -->
            <script src="{{asset('assets/plugins/multipleselect/multiple-select.js')}}?v=<?php echo time(); ?>"></script>
            <script src="{{asset('assets/plugins/multipleselect/multi-select.js')}}?v=<?php echo time(); ?>"></script>
	@endsection

		@section('scripts')


		<!-- INTERNAL Vertical-scroll js-->
		<script src="{{asset('assets/plugins/vertical-scroll/jquery.bootstrap.newsbox.js')}}?v=<?php echo time(); ?>"></script>

		<!-- INTERNAL Data tables -->
		<script src="{{asset('assets/plugins/datatable/js/jquery.dataTables.min.js')}}?v=<?php echo time(); ?>"></script>
		<script src="{{asset('assets/plugins/datatable/js/dataTables.bootstrap5.js')}}?v=<?php echo time(); ?>"></script>
		<script src="{{asset('assets/plugins/datatable/dataTables.responsive.min.js')}}?v=<?php echo time(); ?>"></script>
		<script src="{{asset('assets/plugins/datatable/responsive.bootstrap5.min.js')}}?v=<?php echo time(); ?>"></script>
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

				// Datatable
				// $('#support-articlelists').DataTable({

				// 	responsive: true,
				// 	language: {
				// 		searchPlaceholder: search,
				// 		scrollX: "100%",
				// 		sSearch: '',
				// 	},
				// 	order:[],
				// 	columnDefs: [
				// 		{ "orderable": false, "targets":[ 0,1,3] }
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
                // Datatable
                $('#support-articlelists').DataTable({
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
                        { "orderable": false, "targets":[ 0,1,3] }
                    ],
                });


				/*  When user click add project button */
				$('#create-new-projects').on('click', function () {
					$('#btnsave').val("create-product");
					$('#projects_id').val('');
					$('#projects_form').trigger("reset");
					$('.modal-title').html("{{lang('Add New Inventory')}}");
					$('#addinventory').modal('show');
				});


				/* When click edit project */
				$('body').on('click', '.edit-testimonial', function () {
					var projects_id = $(this).data('id');
					$.get('projects/' + projects_id , function (data) {
						$('#nameError').html('');
						$('.modal-title').html("{{lang('Edit Inventory')}}");
						$('#btnsave').val("edit-project");
						$('#addinventory').modal('show');
						$('#projects_id').val(data.id);
						$('#name').val(data.name);
					})
				});

				// Delete Project
				$('body').on('click', '#delete-testimonial', function () {
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
									url: SITEURL + "/admin/projects/delete/"+_id,
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

				//projects assign
				$('#projectsassign').on('click', function () {

					document.getElementById('projectdisable').style.pointerEvents = 'none';
					document.getElementById('projectdisable').style.opacity = '0.6';

					$('.select2_modal').select2({
						minimumResultsForSearch: '',
						placeholder: "Search",
						width: '100%'
					});

					$('#btnsave').val("create-project");
					$('#projects_id').val('');
					$('#projects_form').trigger("reset");
					$('.modal-title').html("{{lang('Assign Projects')}}");
					$('#projectsassigned').modal('show');
					$('#projects').hide();
					$.get('projects/' , function (data) {

					});

				});

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
									url:"{{ url('admin/massproject/delete')}}",
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

				// Project submit button
				$('body').on('submit', '#projects_form', function (e) {
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
						url: SITEURL + "/admin/projects/create",
						data: formData,
						cache:false,
						contentType: false,
						processData: false,
						success: (data) => {

							if(data.errors){
								$('#nameError').html('');
								$('#nameError').html(data.errors.name);
								$('#btnsave').html('{{lang('Save Changes')}}');
							}
							if(data.success){
								$('#nameError').html('');
								$('#projects_form').trigger("reset");
								$('#addinventory').modal('hide');
								$('#btnsave').html('{{lang('Save Changes')}}');
								toastr.success(data.success);
								location.reload();
							}
						},
						error: function(data){
							$('#nameError').html('');
							toastr.error('{{lang('Project Name is Already Exists', 'alerts')}}');
							$('#btnsave').html('{{lang('Save Changes')}}');
						}
					});
				});


				$("body").on("click", ".importInventory", function(e) {
					e.preventDefault();
					$('#importInventory').modal('show');
				});

				//Checkbox checkall
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


				// $("body").on("click", "#uploadInventory", function(e) {
				// 	var formData = new FormData();
				// 	var file = $('#formFile')[0].files[0];
				// 	if(!file) {
				// 		toastr.error('{{lang('Please select a file to upload.', 'alerts')}}');
				// 		return false;
				// 	}
				// 	formData.append('file', file);
				// 	$.ajax({
				// 		type: 'POST',
				// 		url: SITEURL + "/admin/inventory/import",
				// 		data: formData,
				// 		cache: false,
				// 		contentType: false,
				// 		processData: false,
				// 		success: function(data) {
				// 			console.log(data);
				// 				toastr.success(data.message);
				// 				location.reload();
				// 		},
				// 		error: function(data) {
				// 			toastr.error(data.responseJSON.error);
				// 		}
				// 	});
				// });



				$("body").on("click", "#uploadInventory", function(e) {
    var formData = new FormData();
    var file = $('#formFile')[0].files[0];

    if (!file) {
        toastr.error('Please select a file to upload.');
        return false;
    }

    formData.append('file', file);

    $.ajax({
        type: 'POST',
        url: SITEURL + "/admin/inventory/import",
        data: formData,
        cache: false,
        contentType: false,
        processData: false,
        success: function(data) {
            toastr.success(data.message);
            location.reload();
        },
        error: function(xhr) {
            if (xhr.status === 422) {
                // Handle validation errors
                if (xhr.responseJSON.validation_errors) {
                    xhr.responseJSON.validation_errors.forEach(function(error) {
                        var row = error.row;
                        error.errors.forEach(function(msg) {
                            toastr.error("Row " + row + ": " + msg);
                        });
                    });
                } else if (xhr.responseJSON.errors) {
                    // Laravel's default validator (if used outside loop)
                    Object.values(xhr.responseJSON.errors).forEach(function(msgArray) {
                        msgArray.forEach(function(msg) {
                            toastr.error(msg);
                        });
                    });
                }
            } else if (xhr.responseJSON && xhr.responseJSON.error) {
                toastr.error(xhr.responseJSON.error);
            } else {
                toastr.error('An unknown error occurred.');
            }
        }
    });
});

			})(jQuery);


		</script>

		@endsection
