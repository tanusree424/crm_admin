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
									<h4 class="page-title"><span class="font-weight-normal text-muted ms-2">{{lang('Stn', 'menu')}}</span></h4>
								</div>
							</div>
							<!--End Page header-->

							<!-- Project List-->
							<div class="col-xl-12 col-lg-12 col-md-12">
								<div class="card ">
									<div class="card-header border-0 d-md-max-block">
										<h4 class="card-title mb-md-max-2">{{lang('Stn', 'menu')}}</h4>
										
									</div>
									<div class="card-body" >
										<div class="table-responsive spruko-delete">
								
											<table class="table table-bordered border-bottom text-nowrap ticketdeleterow w-100" id="support-articlelists">
    <thead>
        <tr>
            <th>Sl.No</th>
            <th>Ticket ID</th>
            <th>Brand</th>
            <th>Product Type</th>
            <th>Material</th>
            <th>Transfer Quantity</th>
			<th>Balance Stock</th>
			<th>Transfer Date</th>
			<th>AWN No.</th>
            <th>Replacement Applicable</th>
            <th>Replacement Reason Type</th>
            <th>Replacement Reason</th>
            <th>Created At</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        @php $i = 1; @endphp
        @foreach($StockTransferList as $ints)
            <tr>
                <td>{{ $i++ }}</td>
                <td>{{ $ints->ticket_id }}</td>
                <td>{{ $ints->brand }}</td>
                <td>{{ $ints->product_type }}</td>
                <td>{{ $ints->material }}</td>
                <td>{{ $ints->transfer_qty }}</td>
				<td>{{ $ints->difference_qty }}</td>
				<td>{{ $ints->transfer_date }}</td>
				<td>{{ $ints->awb_no }}</td>
                <td>{{ $ints->replacement_applicable }}</td>
                <td>{{ $ints->replacement_reason_type }}</td>
                <td>{{ $ints->replacement_reason }}</td>
                <td>{{ $ints->created_at }}</td>
                <td><button class="btn btn-success btn-sm transfer-stn-modal-btn" data-ticket-id="{{ $ints->ticket_id }}" data-id="{{ $ints->id }}">Transfer</button></td>
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

    @include('admin.stn.model')
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


				/*  When user click add project button */
				$('#create-new-projects').on('click', function () {
					$('#btnsave').val("create-product");
					$('#projects_id').val('');
					$('#projects_form').trigger("reset");
					$('.modal-title').html("{{lang('Add New Inventory')}}");
					$('#addinventory').modal('show');
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


				$("#support-articlelists").on('click','.transfer-stn-modal-btn', function () {
					console.log('Transfer button clicked');
					$('#stnTransferModel').modal('show');
					var ticketId = $(this).data('ticket-id');
					var id = $(this).data('id');
					$('#ticker_id').val(ticketId);
					$('#stn_id').val(id);
				});



				$(document).on('click', '#btnsaveTransfer',function () {
					console.log('Transfer button clicked11');
					var transferDate = $('#transfer_date').val();
					var awbNo = $('#name').val();
					var tickerId = $('#ticker_id').val();
					var stnId = $('#stn_id').val();
					var token = $('meta[name="csrf-token"]').attr('content');
					// if(transferDate == '' || awbNo == ''){
					// 	$('#nameError').html('{{lang('Please fill all the fields', 'alerts')}}');
					// 	return false;
					// }
					// $('#btnsaveTransfer').html('{{lang('Sending..')}}');
					// $('#btnsaveTransfer').prop('disabled', true);

					let obj ={
						transfer_date: transferDate,
						awbNo: awbNo,
						ticker_id: tickerId,
						stn_id: stnId,
						_token: token
					}
					console.log(obj);

					var formData = new FormData();
					formData.append('transfer_date', transferDate);
					formData.append('awbNo', awbNo);
					formData.append('ticker_id', tickerId);
					formData.append('stn_id', stnId);
					formData.append('_token', token);


						$.ajax({
						type:'POST',
						url: SITEURL + "/admin/stn/update",
						data: formData,
						cache:false,
						contentType: false,
						processData: false,
						success: (data) => {
							console.log("Success:", data);
							toastr.success(data.message);
							location.reload();
						},
						error: function(data){
							console.log("Error:", data);
							toastr.error('{{lang('Something went wrong', 'alerts')}}');
						}
					});


				});
	


			})(jQuery);


		</script>

		@endsection
