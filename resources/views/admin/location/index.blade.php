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
									<h4 class="page-title"><span class="font-weight-normal text-muted ms-2">{{lang('Location', 'menu')}}</span></h4>
								</div>
							</div>
							<!--End Page header-->

							<!-- Project List-->
							<div class="col-xl-12 col-lg-12 col-md-12">
								<div class="card ">
									<div class="card-header border-0 d-md-max-block">
										<h4 class="card-title mb-md-max-2">{{lang('Location', 'menu')}}</h4>
										<div class="card-options d-md-max-block">
										</div>
									</div>
									<div class="card-body" >
										<div class="table-responsive spruko-delete">
							
										<table class="table table-bordered border-bottom text-nowrap ticketdeleterow w-100 locationTble" id="support-articlelists">
											<thead>
												<tr>
													<th width="10">{{ lang('Sl.No') }}</th>
													@cannot('Location Delete')
													<th width="10">
														<input type="checkbox" id="customCheckAll" disabled>
														<label for="customCheckAll"></label>
													</th>
													@endcannot
													<th>{{ lang('Name') }}</th>
													<th>{{ lang('Code') }}</th>
													<th>{{ lang('Actions') }}</th>
												</tr>
											</thead>
											<tbody>
												@php $i = 1; @endphp
												@foreach($countries as $country)
													<tr>
														<td>{{ $i++ }}</td>
														@cannot('Location Delete')
														<td>
															<input type="checkbox" name="country_checkbox[]" class="checkall" value="{{ $country->id }}" disabled />
														</td>
														@endcannot
														<td>{{ $country->name }}</td>
														<td>{{ $country->code }}</td>
														<td>
															<div class="d-flex">
																@can('Location Edit')
																	<a  data-url="{{ url('/admin/country/' . $country->id) }}" data-name="{{ $country->name }}" data-code="{{ $country->code }}" data-id="{{$country->id}}" class="action-btns1 addLocationBtn" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Edit') }}">
																		<i class="feather feather-map text-primary"></i>
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
							<!-- End Project List-->

							@endsection
	@section('modal')

    @include('admin.location.model')
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



				

				// Project submit button
				$('.locationTble').on('click', '.addLocationBtn', function (e) {
					console.log('addLocationBtn');
					var id = $(this).data('id');
					let url = $(this).data('url');
					let code = $(this).data('code');
					let name = $(this).data('name');
					console.log(id, url, name, code);
					$("#addLocation").find('input[name="name"]').val(name+" "+code);
					$("#addLocation").find('input[name="name"]').attr('data-id', id);
					$('#addLocation').modal('show');

					$.ajax({
						type:'GET',
						url: SITEURL + "/admin/location/"+id,
						cache:false,
						contentType: false,
						processData: false,
						success: (data) => {
						$('#country_list').html('');
							let country_list = '<ul class="list-group">';
							data.forEach(function (item) {
								country_list+=`
									<li class="list-group-item d-flex justify-content-between align-items-center">
										<label>${item.location}</label>
										 <span class="badge text-dark rounded-pill">
										 <a  class="delete-location-btn" data-id="${item.id}"  data-bs-toggle="tooltip" data-bs-placement="top" title="Delete">
																		<i class="feather feather-trash-2 text-danger"></i>
																	</a>
										 </span>
									</li>
								`;
							});
							country_list+='</ul>';
							$('#country_list').html(country_list);
						
						},
						error: function(data){
							$('#nameError').html('');
							toastr.error(data.responseJSON.error);
							$('#btnsaveLocation').html('{{lang('Save Changes')}}');
						}
					});

				});

				$(document).on('click',"#btnsaveLocation", function (e) {
					console.log('btnsaveLocation');
					var id = $("#addLocation").find('input[name="name"]').attr('data-id');
					var name = $("#addLocation").find('input[name="name"]').val();
					var location = $("#addLocation").find('input[name="location"]').val();
					console.log(id, name, location);

					if(location == ''){
						$('#nameError').html('');
						$('#nameError').html('Please enter location');
						return false;
					}
					$('#nameError').html('');

					var formData = new FormData();
					formData.append('name', name);
					formData.append('location', location);
					formData.append('id', id);
				let newUrl = SITEURL + "/admin/country/update";
					$.ajax({
						type:'POST',
						url: SITEURL + "/admin/location/create",
						data: formData,
						cache:false,
						contentType: false,
						processData: false,
						success: (data) => {
							toastr.success('{{lang('Location Added Successfully')}}');
							if(data.locations){
								$('#country_list').html('');
								let country_list = '<ul class="list-group">';
								data.locations.forEach(function (item) {
									console.log(item);
									country_list+=`
										<li class="list-group-item d-flex justify-content-between align-items-center">
										<label>${item.location}</label>
										 <span class="badge text-dark rounded-pill">
										 <a  class="delete-location-btn" data-id="${item.id}"  data-bs-toggle="tooltip" data-bs-placement="top" title="Delete">
																		<i class="feather feather-trash-2 text-danger"></i>
																	</a>
										</span>
									</li>
									`;
								});
								country_list+='</ul>';
								$('#country_list').html(country_list);
							}
							$("#addLocation").find('input[name="location"]').val('');

							if(data.errors){
								$('#nameError').html('');
								$('#nameError').html(data.errors.name);
								$('#btnsaveLocation').html('{{lang('Save Changes')}}');
							}
							if(data.success){
								$('#nameError').html('');
								$('#material1_form').trigger("reset");
								$('#addLocation').modal('hide');
								$('#btnsaveLocation').html('{{lang('Save Changes')}}');
								toastr.success(data.success);
								location.reload();
							}
						},
						error: function(data){
							console.log(data);
							$('#nameError').html('');
							toastr.error(data.responseJSON.error);
							$('#btnsaveLocation').html('{{lang('Save Changes')}}');
						}
					});
				});

				

				//Mass Delete
				$(document).on('click', '.delete-location-btn', function () {
					console.log('delete-location-btn');
					var id = $(this).data('id');
					let currentList = $(this).closest('li');
					console.log('id:::::::::::::::>>',id);
					if(id){
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
									url:"{{ url('/admin/location/delete')}}",
									method:"POST",
									data:{location_id:id},
									success:function(data)
									{	
										currentList.remove();
										toastr.success(data.success);
										// location.reload();
									},
									error:function(data){
										toastr.error(data.responseJSON.error);
									}
								});
							}
						});
					}else{
						toastr.error('{{lang('Please select at least one check box.', 'alerts')}}');
					}
				});

			

			})(jQuery);


		</script>

		@endsection
