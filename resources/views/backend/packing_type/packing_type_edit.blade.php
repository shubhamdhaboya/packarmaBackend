<section class="users-list-wrapper">
	<div class="users-list-table">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-content">
                        <div class="card-header">
                            <div class="row">
                                <div class="col-12 col-sm-7">
                                    <h5 class="pt-2">Edit Packing Type : {{$data->packing_name}}</h5>
                                </div>
                                <div class="col-12 col-sm-5 d-flex justify-content-end align-items-center">
                                    <a href="{{URL::previous()}}" class="btn btn-sm btn-primary px-3 py-1"><i class="fa fa-arrow-left"></i> Back</a>
                                </div>
                            </div>
                        </div>
                    	<div class="card-body">
                    		<form id="editPackingType" method="post" action="savePackingType?id={{$data->id}}">
                                <h4 class="form-section"><i class="ft-info"></i> Details</h4>
                    			@csrf
                        		<div class="row">
                        			<div class="col-sm-6">
                        				<label>Packing Name<span style="color:#ff0000">*</span></label>
                        				<input class="form-control required" type="text" id="packing_name" name="packing_name" value="{{$data->packing_name}}"><br/>
                        			</div>
                                    <div class="col-sm-6">
                        				<label>Packing Description</label>
                        				<input class="form-control" type="text" id="packing_description" name="packing_description" value="{{$data->packing_description}}"><br/>
                        			</div>
                        		</div>
                        		<hr>
                        		<div class="row">
                        			<div class="col-sm-12">
                        				<div class="pull-right">
                        					<button type="button" class="btn btn-success" onclick="submitForm('editPackingType','post')">Update</button>
                                            <a href="{{URL::previous()}}" class="btn btn-danger px-3 py-1"></i>Cancel</a>
                        				</div>
                        			</div>
                        		</div>
                        	</form>
                    	</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>