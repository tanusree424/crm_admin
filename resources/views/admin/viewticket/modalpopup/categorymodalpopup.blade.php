  		<!-- Category List-->
          <div class="modal fade sprukosearchcategory"  id="addcategory" aria-hidden="true">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" ></h5>
						<button  class="close" data-bs-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">x</span>
						</button>
					</div>
					<form method="POST" enctype="multipart/form-data" id="sprukocategory_form" name="sprukocategory_form">
                        <input type="hidden" name="ticket_id" class="ticket_id">
                        @csrf
                        @honeypot
                        <div class="modal-body">
                            <div class="form-group">
                                <label class="form-label">{{lang('Select Category')}}</label>
                                <div class="custom-controls-stacked d-md-flex" >
									<select class="form-control select4-show-search" data-placeholder="{{lang('Select category')}}" name="category" id="sprukocategorylist" >

									</select>
								</div>
								<span id="CategoryError" class="text-danger"></span>
                            </div>
							<div class="form-group" id="envatopurchase">
							</div>
							<div class="form-group" id="selectssSubCategory" style="display: none;">

								<label class="form-label mb-0 mt-2">{{lang('Subcategory')}}</label>
								<select  class="form-control subcategoryselect"  data-placeholder="{{lang('Select SubCategory')}}" name="subscategory" id="subscategory">

								</select>
								<span id="subsCategoryError" class="text-danger alert-message"></span>

							</div>
							<div class="form-group" id="selectSubCategory">
							</div>
                        </div>
                        <div class="modal-footer">
                            <a href="#" class="btn btn-outline-danger" data-bs-dismiss="modal">{{lang('Close')}}</a>
                            <button type="submit" class="btn btn-secondary sprukoapiblock" id="btnsave" >{{lang('Save')}}</button>
                        </div>
                    </form>
				</div>
			</div>
		</div>
		<!-- End Category List  -->

		<!-- AWB changes-->
          <div class="modal fade sprukoawb"  id="addawb" aria-hidden="true">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="modal-awb-title"></h5>
						<button  class="close" data-bs-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">x</span>
						</button>
					</div>
					<form method="POST" enctype="multipart/form-data" id="sprukoawb_form" name="sprukoawb_form">
                        <input type="hidden" name="cust_ticket_id" id="cust_ticket_id">
						<input type="hidden" name="cust_ticket_fieldname" id="cust_ticket_fieldname">
                        @csrf
                        @honeypot
                        <div class="modal-body">
                            <div class="form-group">
                                <label class="form-label" id="awb_from_lable"></label>
                                <div class="custom-controls-stacked d-md-flex" >
									<input type="text" class="form-control" placeholder="Enter Here" name="editawbvalue" id="editawbvalue" required>	
								</div>
								<span id="AwbError" class="text-danger"></span>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <a href="#" class="btn btn-outline-danger" data-bs-dismiss="modal">{{lang('Close')}}</a>
                            <button type="submit" class="btn btn-secondary sprukoapiblock" id="btnnsave" >{{lang('Save')}}</button>
                        </div>
                    </form>
				</div>
			</div>
		</div>
		<!-- End Category List  -->


