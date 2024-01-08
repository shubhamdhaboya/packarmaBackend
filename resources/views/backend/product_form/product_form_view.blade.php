<section class="users-list-wrapper">
	<div class="users-list-table">
        <div class="row">
            <div class="col-12">
                <div>
                    <div class="card-content">
                    	<div class="card-body">
                            <div class="col-12">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped">
                                        <tr>
                                            <td class="col-sm-5"><strong>Product Form Name</strong></td>
                                            <td>{{$data->product_form_name}}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Short Description</strong></td>
                                            <td>{{$data->short_description}}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Product Form Status</strong></td>
                                            <td>{{displayStatus($data->status)}}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Date Time</strong></td>
                                            <td>{{date('d-m-Y h:i A', strtotime($data->updated_at)) }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Product Form Image</strong></td>
                                            <td><img src="{{ListingImageUrl('product_form',$data->product_form_image)}}" width="150px" height="auto"/></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                    	</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
