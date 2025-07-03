  		<!-- Add Project-->
          <div class="modal fade"  id="stnTransferModel" aria-hidden="true">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" ></h5>
						<button  class="close" data-bs-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">×</span>
						</button>
					</div>
					<div >
                        @csrf
                        @honeypot
                        <div class="modal-body">
                           
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Date<span class="text-red">*</span></label>
                                    <input type="date" class="form-control" name="transfer_date" id="transfer_date">
                                    <input type="text" class="form-control" name="ticker_id" id="ticker_id" hidden>
                                    <input type="text" class="form-control" name="stn_id" id="stn_id" hidden>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">AWB No.<span class="text-red">*</span></label>
                                    <input type="text" class="form-control" name="name" id="name">
                                    <span id="nameError" class="text-danger alert-message"></span>
                                </div>
                            </div>
                        </div>
                        </div>
                      
                        <div class="modal-footer">
                            <a href="#" class="btn btn-outline-danger" data-bs-dismiss="modal">{{lang('Close')}}</a>
                            <button type="submit" class="btn btn-secondary" id="btnsaveTransfer"  >{{lang('Save')}}</button>
                        </div>
                    </div>
				</div>
			</div>
		</div>
		<!-- End  Add Project  -->