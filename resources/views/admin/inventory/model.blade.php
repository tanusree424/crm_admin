  		<!-- Add Project-->
          <div class="modal fade"  id="addinventory" aria-hidden="true">
			<div class="modal-dialog modal-xl" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" ></h5>
						<button  class="close" data-bs-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">×</span>
						</button>
					</div>
					<form method="POST" enctype="multipart/form-data" id="material1_form" name="material1_form">
                        <input type="hidden" name="material1_id" id="material1_id">
                        @csrf
                        @honeypot
                        <div class="modal-body">
                           
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Product Code<span class="text-red">*</span></label>
                                    <input type="text" class="form-control" name="name" id="name">
                                    <span id="nameError" class="text-danger alert-message"></span>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Country<span class="text-red">*</span></label>
                                    <input type="text" class="form-control" name="name" id="name">
                                    <span id="nameError" class="text-danger alert-message"></span>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Location<span class="text-red">*</span></label>
                                    <input type="text" class="form-control" name="name" id="name">
                                    <span id="nameError" class="text-danger alert-message"></span>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Stock Quantity<span class="text-red">*</span></label>
                                    <input type="text" class="form-control" name="name" id="name">
                                    <span id="nameError" class="text-danger alert-message"></span>
                                </div>
                            </div>

                        </div>
                        </div>
                      
                        <div class="modal-footer">
                            <a href="#" class="btn btn-outline-danger" data-bs-dismiss="modal">{{lang('Close')}}</a>
                            <button type="submit" class="btn btn-secondary" id="btnsave"  >{{lang('Save')}}</button>
                        </div>
                    </form>
				</div>
			</div>
		</div>
		<!-- End  Add Project  -->