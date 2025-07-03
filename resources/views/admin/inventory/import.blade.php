  		<!-- Add Project-->
          <div class="modal fade"  id="importInventory" aria-hidden="true">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" ></h5>
						<button  class="close" data-bs-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">×</span>
						</button>
					</div>
					<div  id="material1_form" name="material1_form">
                        <input type="hidden" name="material1_id" id="material1_id">
                        <div class="modal-body">
                           <div class="mb-3">
                            <input class="form-control" type="file" id="formFile">
                            </div>
                            @can('Download Template')
                            <a href="{{ url('admin/inventory/downloadtemplate') }}" class="btn btn-outline-secondary" download>
                                <i class="fa fa-download"></i> {{lang('Download Template')}}
                            </a>
                            @endcan
                        </div>
                      
                        <div class="modal-footer">
                            <a href="#" class="btn btn-outline-danger" data-bs-dismiss="modal">{{lang('Close')}}</a>
                            <button type="submit" class="btn btn-secondary" id="uploadInventory"  >{{lang('Save')}}</button>
                        </div>
    </div>
				</div>
			</div>
		</div>
		<!-- End  Add Project  -->