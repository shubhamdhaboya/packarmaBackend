@extends('backend.layouts.app')
@section('content')
<div class="main-content">
    <div class="content-overlay"></div>
    <div class="content-wrapper">
        <section class="users-list-wrapper">
        	<div class="users-list-table">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-content">
                                <div class="card-header">
                                    <div class="row">
                                        <div class="col-12 col-sm-7">
                                            <h5 class="pt-2">Manage Vendor List</h5>
                                        </div>
                                        <div class="col-12 col-sm-5 d-flex justify-content-end align-items-center">
                                            <button class="btn btn-sm btn-outline-danger px-3 py-1 mr-2" id="listing-filter-toggle"><i class="fa fa-filter"></i> Filter</button>
                                            @if($data['vendor_add'])
                                                <a href="vendor_add" class="btn btn-sm btn-outline-primary px-3 py-1 src_data"><i class="fa fa-plus"></i> Add Vendor</a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            	<div class="card-body">
                                    <div class="row mb-2" id="listing-filter-data" style="display: none;">
                                        <div class="col-md-4">
                                            <label>Vendor Name</label>
                                            <input class="form-control mb-3" type="text" id="search_vendor_name" name="search_vendor_name">
                                        </div>
                                        <div class="col-md-4">
                                            <label>Phone</label>
                                            <input class="form-control mb-3" type="text" id="search_vendor_phone" name="search_vendor_phone">
                                        </div>
                                        <div class="col-md-4">
                                            <label>Email</label>
                                            <input class="form-control mb-3" type="text" id="search_vendor_email" name="search_vendor_email">
                                        </div>
                                        <div class="col-md-4">
                                            <label>Company Name</label>
                                            <input class="form-control mb-3" type="text" id="search_vendor_company" name="search_vendor_company">
                                        </div>
                                        <div class="col-md-4">
                                            <label>Vendor Type</label>
                                            <select class="form-control mb-3 select2" id="search_vendor_type" name="search_vendor_type" style="width: 100% !important;">
                                                <option value="">Select</option>
                                                    <option value="not-deleted">Approved</option>                                              
                                                    <option value="deleted">Deleted</option>                                              
                                            </select><br/>
                                        </div>
                                        <div class="col-md-4">
                                            <label>&nbsp;</label><br/>
                                            <input class="btn btn-md btn-primary px-3 py-1 mb-3" id="clear-form-data" type="reset" value="Clear Search">
                                        </div>
                                    </div>
                            		<div class="table-responsive">
                                        <table class="table table-bordered table-striped datatable" id="dataTable" width="100%" cellspacing="0" data-url="vendor_data">
				                            <thead >
				                                <tr>
				                                    <th class="sorting_disabled" id="id" data-orderable="false" data-searchable="false">Id</th>
                                                    <th id="vendor_name" data-orderable="false" data-searchable="false">Vendor Name</th>
                                                    <th id="vendor_company_name" data-orderable="false" data-searchable="false">Vendor Company Name</th>
                                                    <th id="gstin" data-orderable="false" data-searchable="false">Gst No</th>
                                                    {{-- <th id="gst_certificate" data-orderable="false" data-searchable="false">Gst Certificate</th> --}}
                                                    <th id="mark_featured" data-orderable="false" data-searchable="false">Mark Featured</th>
                                                    @if($data['vendor_status'])
                                                        <th id="vendor_status" data-orderable="false" data-searchable="false">Status</th>
                                                    @endif
                                                    @if($data['vendor_edit'] || $data['vendor_material_map']) 
                                                        <th id="action" data-orderable="false" data-searchable="false" width="130px">Action</th>
                                                    @endif
				                                </tr>
				                            </thead>
				                        </table>
                                    </div>
                            	</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
@endsection