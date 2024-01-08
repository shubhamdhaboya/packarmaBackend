<section class="users-list-wrapper">
    <div class="users-list-table">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-content">
                        <div class="card-header">
                            <div class="row">
                                <div class="col-12 col-sm-7">
                                    <h5 class="pt-2">Map Vendors To Customer Enquiry : {{ $customer_enquiry_id }}</h5>
                                </div>
                                <div class="col-12 col-sm-5 d-flex justify-content-end align-items-center">
                                    <a href="{{ URL::previous() }}" class="btn btn-sm btn-primary px-3 py-1"><i class="fa fa-arrow-left"></i> Back</a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="card-text">
                                <div class="col-md-12 row">
                                    <div class="col-md-6">
                                        <dl class="row">
                                            <dt class="col-sm-4 text-left">Product Name:</dt>
                                            <dd class="col-sm-8">{{ $data['product']->product_name }}</dd>
                                        </dl>
                                    </div>
                                    <div class="col-md-6">
                                        <dl class="row">
                                            <dt class="col-sm-5 text-left">Packaging Material:</dt>
                                            <dd class="col-sm-7">{{$data['packaging_material']->packaging_material_name}}</dd>
                                        </dl>
                                    </div>
                                </div>
                                <div class="col-md-12 row">
                                    <div class="col-md-6">
                                        <dl class="row">
                                            <dt class="col-sm-4 text-left">User Name:</dt>
                                            <dd class="col-sm-8">{{ $data['user']->name }}</dd>
                                        </dl>
                                    </div>
                                    <div class="col-md-6">
                                        <dl class="row">
                                            <dt class="col-sm-5 text-left">Recommendation Engine:</dt>
                                            <dd class="col-sm-7">{{$data['recommendation_engine']->engine_name}}</dd>
                                        </dl>
                                    </div>
                                </div>
                                <div class="col-md-12 row">
                                    <div class="col-md-6">
                                        <dl class="row">                                                                        
                                            <dt class="col-sm-4 text-left">User Address:</dt>
                                            <dd class="col-sm-8">{{$data->flat}}, {{$data->land_mark}}, {{$data->area}}, {{$data->city_name}}, {{$data->state->state_name}}</dd>
                                        </dl>
                                    </div>
                                    <div class="col-md-6">
                                        <dl class="row">
                                            <dt class="col-sm-5 text-left">Entered Product Quantity:</dt>
                                            <dd class="col-sm-7">{{$data->product_quantity}} {{ $data['recommendation_engine']->min_order_quantity_unit }}</dd>
                                        </dl>
                                    </div>
                                </div>   
                                <div class="col-md-12 row">
                                <!-- Outline variants section start -->
                                    <div class="col-md-3 col-12">
                                        <div class="card card-outline-secondary box-shadow-0 text-center" style="height: 90%;">
                                            <div class="card-content">
                                                <div class="card-body modal_src_data" data-size="extra-large" data-title="Map Vendor To Enquiry" href="map_vendor_form/-1/{{ $data->id }}">
                                                    <h1><i class="fa fa-user fa-2x text-secondary" style="color: grey;"></i></h1>
                                                    <h4 class="card-title text-secondary">Map Vendors</h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @php
                                        $i=1;
                                    @endphp
                                    @foreach ($mapped_vendor as $vendors)
                                    <div class="col-md-3 col-12 map_vendor_section">
                                        <div class="card card-outline-secondary box-shadow-0 h6" style="height: 90%;">
                                            <div class="card-content">
                                                <div class="card-body pb-0">
                                                    <h6>{{ $vendors->vendor_name ?? ''; }} @if($vendors->enquiry_status == 'accept')<i class="fa fa-check-circle success pull-right"></i>@endif @if($vendors->enquiry_status == 'reject')<i class="fa fa-close danger pull-right"></i>@endif</h6>
                                                    <p class="text-secondary small">Rate: {{ $vendors->vendor_price ??''; }}/{{ $vendors->min_order_quantity_unit ??''; }}</p>
                                                    <p class="text-secondary small">Delivery in: {{ $vendors->delivery_in_days ??''; }} Days</p>
                                                    <p class="text-secondary small">Commission Rate: {{ $vendors->commission_amt ??''; }}/{{ $vendors->min_order_quantity_unit ??''; }}</p>
                                                    <p class="text-secondary small">Mapped On: {{date('d-m-Y h:i A', strtotime($vendors->created_at)) ?? ''; }}</p>
                                                </div>
                                                <div class="card-footer">
                                                <a href="map_vendor_form/{{$vendors->id}}/{{ $data->id }}" class="modal_src_data" data-size="extra-large" data-title="Edit Mapped Vendor" style="color: #975AFF;">Edit</a> @if($vendors->enquiry_status != 'quoted' && $vendors->enquiry_status != 'accept' && $vendors->enquiry_status != 'reject') | <a style="color: red;" class="delete_map_vendor" data-id="{{$vendors->id}}" data-url="delete_map_vendor" id="delete{{$i}}" >Remove</a> @endif
                                            </div>
                                            </div>
                                        </div>
                                    </div>
                                    @php
                                        $i++;
                                    @endphp
                                    @endforeach
                                    <!-- Outline variants section end -->
                                </div>
                            </div>                                
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<script>

//getVendorWarehouse function with Ajax to get warehouse drop down of selected vendor in customer enquiry map to vendor
function getVendorWarehouse(vendor,i)
{
    var product_id ='<?php echo $data->product_id; ?>';
    var product_quantity = '<?php echo $data->product_quantity; ?>';
    var packaging_material_id = '<?php echo $data->packaging_material_id; ?>';

    $("#vendor_price_bulk").val('');
    $("#commission_rate_bulk").val('');
    $.ajax({
        url:"getVendorWarehouseDropdown",
        type: "POST",
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        data: {
            vendor_id: vendor, product_id: product_id, packaging_material_id:packaging_material_id
        },
        success:function(result)
        {
            response = JSON.parse(result);
            if(response['data']['vendorMaterialMapData'].length !== 0){
                var vendor_price = response['data']['vendorMaterialMapData'][0]['vendor_price']; 
                var commission_rate = response['data']['vendorMaterialMapData'][0]['min_amt_profit'];
            }
            if(commission_rate){
                // $("#vendor_price_bulk").val(vendor_price);
                // $("#commission_rate_bulk").val(commission_rate);
                $("#vendor_price").val(vendor_price);
                $("#commission_rate").val(commission_rate);
                
                $("#vendor_price_per_kg_div").show();
                $("#commission_price_per_kg_div").show();
                // showVendorPricePerUnit(vendor_price);
                // showCommissionPerUnit(commission_rate);
                vend_price = vendor_price * product_quantity;
                comm_rate = commission_rate * product_quantity;
                $("#vendor_price_bulk").val(vend_price);
                $("#commission_rate_bulk").val(comm_rate);
                showVendorPricePerUnit(vend_price);
                showCommissionPerUnit(comm_rate);
                calcGrandTotal();
            }else{
                $("#vendor_price_bulk").val('');
                $("#commission_rate_bulk").val('');
                $("#vendor_price_per_kg_div").hide();
                $("#commission_price_per_kg_div").hide();
            }
            $("#warehouse").empty();
            $("#warehouse").append('<option value="">Select</option>');
            for(var j=0; j<response['data']['vendor_warehouse'].length; j++)
            {
                var warehouse_id = response['data']['vendor_warehouse'][j]['id'];
                var warehouse_name = response['data']['vendor_warehouse'][j]['warehouse_name'];
                var warehouse_state_id = response['data']['vendor_warehouse'][j]['state_id'];
                $("#warehouse").append('<option value="'+warehouse_id+"|"+warehouse_state_id+'" warehouse_state_id ="'+warehouse_state_id+'">'+warehouse_name+'</option>');
            }
        },
    });  
}

function taxValueToggle(gst_type,i){
    if(gst_type == 'not_applicable'){
    $('#gst_percentage'+i).hide('slow');
    }else{
        $('#gst_percentage'+i).show('slow');

    }  
}
</script>
