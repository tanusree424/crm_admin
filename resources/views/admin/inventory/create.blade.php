@extends('layouts.adminmaster')
@section('styles')
    <!-- INTERNAL Tag css -->
    <link href="{{ asset('assets/plugins/taginput/bootstrap-tagsinput.css') }}?v={{ time() }}" rel="stylesheet" />
@endsection

							@section('content')

							<!--Page header-->
							<div class="page-header d-xl-flex d-block">
								<div class="page-leftheader">
									<h4 class="page-title"><span class="font-weight-normal text-muted ms-2">{{lang('Create Inventory', 'menu')}}</span></h4>
								</div>
							</div>
							<!--End Page header-->

							<!-- Project List-->
							<div class="col-xl-12 col-lg-12 col-md-12">
								<div class="card ">
									<div class="card-header border-0 d-md-max-block">
										<h4 class="card-title mb-md-max-2">{{lang('Create Inventory', 'menu')}}</h4>
										
									</div>
									<div class="card-body" >
						<div class="row">
                        <div class="col-sm-6 col-md-3">
                            <div class="form-group">
                                <label class="form-label">Spare Code <span class="text-red">*</span></label>
						<select class="form-select select2  form-select" name="spare_code" id="spare_code">
							<option value="">Select Country</option>
							@foreach($materials as $material)
								<option value="{{ $material->material_code }}" data-material-id="{{ $material->id }}" data-material-code="{{ $material->material_code }}" data-material-name="{{ $material->material_name }}" data-material-description="{{ $material->material_description }}">{{ $material->material_code }}</option>
							@endforeach
						</select>
                                </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="form-group">
                                <label class="form-label">Spare Name <span class="text-red">*</span></label>
                                <input type="text" class="form-control " name="spare_name" value="" autocomplete="off">
                                                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="form-group">
                                <label class="form-label">Description</label>
                                <textarea class="form-control " name="spare_description"></textarea>
                            </div>
                        </div>
                      
                        
                        <div class="col-sm-6 col-md-3">
                        <label class="form-label">Country</label>
						<select class="form-select select2 select2_modal form-select" name="country_id" id="country_id">
							<option value="">Select Country</option>
							@foreach($countries as $country)
								<option value="{{ $country->id }}">{{ $country->name }}</option>
							@endforeach
						</select>
                        </div>
                        <div class="col-sm-6 col-md-3">
                                <label class="form-label">Location</label>
							<select class="form-select select2 select2_modal form-select" name="location_id" id="location_id">
							</select>
                        </div>
                     

                        <div class="col-sm-6 col-md-3">
                            <div class="form-group">
                                <label class="form-label">Quantity</label>
                                <input type="number" class="form-control " name="quantity" id="quantity" value="" autocomplete="off">
                                                            </div>
                        </div>


                    </div>

					<div class="col-md-12 card-footer">
                    <div class="form-group float-end">
						<button type="button" id="inventory_form" class="btn btn-danger me-2 mb-md-max-2">Create Inventory</button>
                    </div>
                </div>

									</div>
								</div>
							</div>
							<!-- End Project List-->

							@endsection
	@section('modal')

    @include('admin.inventory.model')
			<!-- INTERNAL Multiselect Js -->
            <script src="{{asset('assets/plugins/multipleselect/multiple-select.js')}}?v=<?php echo time(); ?>"></script>
            <script src="{{asset('assets/plugins/multipleselect/multi-select.js')}}?v=<?php echo time(); ?>"></script>
	@endsection

		@section('scripts')
		   <script src="{{ asset('assets/js/form-browser.js') }}?v={{ time() }}"></script>
		<!-- INTERNAL Vertical-scroll js-->
		<script src="{{asset('assets/plugins/vertical-scroll/jquery.bootstrap.newsbox.js')}}?v=<?php echo time(); ?>"></script>
		<!-- INTERNAL Data tables -->
		<script src="{{asset('assets/plugins/datatable/js/jquery.dataTables.min.js')}}?v=<?php echo time(); ?>"></script>
		<script src="{{asset('assets/plugins/datatable/js/dataTables.bootstrap5.js')}}?v=<?php echo time(); ?>"></script>
		<script src="{{asset('assets/plugins/datatable/dataTables.responsive.min.js')}}?v=<?php echo time(); ?>"></script>
		<script src="{{asset('assets/plugins/datatable/responsive.bootstrap5.min.js')}}?v=<?php echo time(); ?>"></script>
 		<script src="{{ asset('assets/js/select2.js') }}?v={{ time() }}"></script>

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


				

				$("#spare_code").on('change', function() {
					var selectedOption = $(this).find('option:selected');
					var materialId = selectedOption.data('material-id');
					var materialCode = selectedOption.data('material-code');
					var materialName = selectedOption.data('material-name');
					var materialDescription = selectedOption.data('material-description');

					// Set the values in the respective input fields
					$('input[name="spare_name"]').val(materialName);
					$('textarea[name="spare_description"]').val(materialDescription);
				});
			

				// Project submit button
				$('#inventory_form').on('click', function (e) {
					var formData = new FormData();
					formData.append('spare_code', $('#spare_code').find('option:selected').data('material-code'));
					// Get the selected option's value
					formData.append('material_id', $('#spare_code').find('option:selected').data('material-id'));
					formData.append('spare_name', $('input[name="spare_name"]').val());
					formData.append('spare_description', $('textarea[name="spare_description"]').val());
					formData.append('country_id', $('select[name="country_id"]').val());
					formData.append('location_id', $('select[name="location_id"]').val());
					formData.append('quantity', $('input[name="quantity"]').val());

					formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
					e.preventDefault();
					$.ajax({
						type:'POST',
						url: SITEURL + "/admin/inventory/create",
						data: formData,
						cache:false,
						contentType: false,
						processData: false,
						success: (data) => {
							toastr.success(data.message);
							location.reload();
						},
						error: function(data){
							toastr.error('{{lang('Something went wrong', 'alerts')}}');
						}
					});
				});


				$('.form-select').select2({
					minimumResultsForSearch: Infinity,
					width: '100%'
				});

				$("#country_id").on('change', function() {
					var countryId = $(this).val();
					if(countryId) {
						$.ajax({
							url: SITEURL + '/admin/location/' + countryId,
							type: 'GET',
							dataType: 'json',
							success: function(data) {
								console.log(data);
								var options = '<option value="">Select Location</option>';
								$.each(data, function(key, value) {
									options += '<option value="' + value.id + '">' + value.location + '</option>';
								});
								$('#location_id').html(options);
							},
							error: function(xhr, status, error) {
								console.error('Error fetching locations:', error);
									toastr.success('{{lang('Error fetching locations')}}');
								
							}
						});
					} else {
						$('input[name="location_id"]').html('<option value="">Select Location</option>');
					}
				});
			

			})(jQuery);


		</script>

		@endsection
