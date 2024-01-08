<section class="users-list-wrapper">
	<div class="users-list-table">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-content">
                        <div class="card-header">
                            <div class="row">
                                <div class="col-12 col-sm-7">
                                    <h5 class="pt-2">View Customer Enquiry Details : {{ $customer_enquiry_id }}</h5>
                                </div>
                                <div class="col-12 col-sm-5 d-flex justify-content-end align-items-center">
                                    <a href="{{URL::previous()}}" class="btn btn-sm btn-primary px-3 py-1"><i class="fa fa-arrow-left"></i> Back</a>
                                </div>
                            </div>
                        </div>
                    	<div class="card-body">
                            <ul class="nav nav-tabs" role="tablist">
                                <li class="nav-item">
                                    <a href="#details" role="tab" id="details-tab" class="nav-link d-flex align-items-center active" data-toggle="tab" aria-controls="details" aria-selected="true">
                                        <i class="ft-info mr-1"></i>
                                        <span class="d-none d-sm-block">Enquiry Details</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="#masters" role="tab" id="page_description-tab" class="nav-link d-flex align-items-center" data-toggle="tab" aria-controls="page_description" aria-selected="false">
                                        <i class="ft-link mr-2"></i>
                                        <span class="d-none d-sm-block">Product Details</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="#map_to_vendor" role="tab" id="page_description-tab" class="nav-link d-flex align-items-center" data-toggle="tab" aria-controls="page_description" aria-selected="false">
                                        <i class="ft-link mr-2"></i>
                                        <span class="d-none d-sm-block">Mapped Vendor Details</span>
                                    </a>
                                </li>
                            </ul>
                            <div class="tab-content">
                                <div class="tab-pane fade mt-2 show active" id="details" role="tabpanel" aria-labelledby="details-tab">
                                     <div class="row">
                                        <div class="col-12">
                                            <div class="table-responsive">
                                                <table class="table table-striped table-bordered">
                                                    <tr>
                                                        <td><strong>User Name</strong></td>
                                                        <td>{{$data['user']->name;}}</td>
                                                    </tr>
                                                    {{-- <tr>
                                                        <td><strong>Order ID</strong></td>
                                                        <td>{{$data->order_id;}}</td>
                                                    </tr> --}}
                                                    {{-- <tr>
                                                        <td><strong>Description</strong></td>
                                                        <td>{{$data->description;}}</td>
                                                    </tr> --}}
                                                    <tr>
                                                        <td><strong>Enquiry Status</strong></td>
                                                        <td>{{customerEnquiryQuoteType($data->quote_type); }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Address Type</strong></td>
                                                        <td>{{ $data['user_address'] ? addressType($data['user_address']->type) : '-'}}</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>State</strong></td>
                                                        <td>{{ $data['state'] ? $data['state']->state_name : '-'}}</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>City</strong></td>
                                                        <td>{{ $data->city_name;}}</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Area</strong></td>
                                                        <td>{{ $data->area;}}</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Flat</strong></td>
                                                        <td>{{ $data->flat;}}</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Landmark</strong></td>
                                                        <td>{{ $data->land_mark;}}</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Pincode</strong></td>
                                                        <td>{{ $data->pincode;}}</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Status</strong></td>
                                                        <td>{{displayStatus($data->status)}}</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Enquiry Date Time</strong></td>
                                                        <td>{{date('d-m-Y h:i A', strtotime($data->updated_at)) }}</td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane fade mt-2" id="masters" role="tabpanel" aria-labelledby="page_description-tab">
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="table-responsive">
                                                <table class="table table-striped table-bordered">
                                                    <tr>
                                                        <td><strong>Category</strong></td>
                                                        <td>{{$data['category']->category_name;}}</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Sub Category</strong></td>
                                                        <td>{{$data['sub_category']->sub_category_name }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="col-sm-5"><strong>Product Name</strong></td>
                                                        <td>{{$data['product']->product_name;}}</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Shelf Life</strong></td>
                                                        <td>{{$data->shelf_life}} (Days)</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Entered Shelf Life</strong></td>
                                                        <td>{{$data->entered_shelf_life}} ({{ucfirst($data->entered_shelf_life_unit)}})</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Product Weight</strong></td>
                                                        <td>{{$data->product_weight.' '}}{{$data->measurement_unit_id!=0?$data->measurement_unit->unit_symbol:'NA';}}</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Product Quantity</strong></td>
                                                        <td>{{$data->product_quantity;}} {{ $data['recommendation_engine']->min_order_quantity_unit; }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Storage Condition</strong></td>
                                                        <td>{{$data->storage_condition_id!=0?$data['storage_condition']->storage_condition_title:'NA';}}</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Packaging Machine</strong></td>
                                                        <td>{{$data->packaging_machine_id!=0?$data['packaging_machine']->packaging_machine_name:'NA';}}</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Product Form</strong></td>
                                                        <td>{{$data->product_form_id!=0?$data['product_form']->product_form_name:'NA';}}</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Packing Type</strong></td>
                                                        <td>{{$data->packing_type_id!=0?$data['packing_type']->packing_name:'NA';}}</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Packaging Treatment</strong></td>
                                                        <td>{{$data->packaging_treatment_id!=0?$data['packaging_treatment']->packaging_treatment_name:'NA';}}</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Enquiry Date Time</strong></td>
                                                        <td>{{date('d-m-Y h:i A', strtotime($data->updated_at)) }}</td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane fade mt-2" id="map_to_vendor" role="tabpanel" aria-labelledby="page_description-tab">
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="table-responsive">
                                                <table class="table table-striped table-bordered">
                                                    <thead>
                                                        <th>Vendor Name</th>
                                                        <th>Email</th>
                                                        <th>Phone</th>
                                                        <th>Address</th>
                                                    </thead>
                                                    <tbody>
                                                        {{-- @foreach ($vendor_warehouse as $warehouses)
                                                            @php
                                                                $warehouse_name = $warehouses->warehouse_name;
                                                                $pincode = $warehouses->pincode;
                                                                $state_name = $warehouses->state->state_name;
                                                            @endphp
                                                        @endforeach --}}
                                                        @if ($vendors)
                                                            @foreach ($vendors as $vendor)
                                                                <tr>
                                                                    <td>{{ $vendor->vendor_name; }}</td>
                                                                    <td>{{ $vendor->vendor_email; }}</td>
                                                                    <td>{{ $vendor->phone; }}</td>
                                                                    @if ($vendor->warehouse_name)
                                                                        <td>{{ $vendor->warehouse_name ?? '' }}, {{ $vendor->state_name ?? '' }}, {{ $vendor->pincode ?? ''}}</td>                                                     </tr>
                                                                    @else
                                                                        <td>-</td>
                                                                    @endif
                                                                </tr>
                                                            @endforeach
                                                        @else
                                                            <tr><td colspan="4" class="text-center col">No vendor map found</td></tr>
                                                        @endif
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                    	</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
