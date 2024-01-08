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
                                                <h5 class="pt-2">Manage User List</h5>
                                            </div>
                                            <div class="col-12 col-sm-5 d-flex justify-content-end align-items-center">
                                                <button class="btn btn-sm btn-outline-danger px-3 py-1 mr-2"
                                                    id="listing-filter-toggle"><i class="fa fa-filter"></i>Filter</button>
                                                {{-- @if ($data['user_add'])
                                                    <a href="user_list_add" class="btn btn-sm btn-outline-primary px-3 py-1 src_data"><i class="fa fa-plus"></i> Add User</a>
                                                @endif --}}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="row mb-2" id="listing-filter-data" style="display: none;">
                                            <div class="col-md-4">
                                                <label>Name</label>
                                                <input class="form-control mb-3" type="text" id="search_name"
                                                    name="search_name">
                                            </div>
                                            <div class="col-md-4">
                                                <label>Phone</label>
                                                <input class="form-control mb-3" type="text" id="search_phone"
                                                    name="search_phone"
                                                    onkeypress='return event.charCode >= 48 && event.charCode <= 57 || event.charCode ==46'>
                                            </div>
                                            <div class="col-md-4">
                                                <label>Email</label>
                                                <input class="form-control mb-3" type="email" id="search_email"
                                                    name="search_email"
                                                    onkeypress='return event.charCode >= 48 && event.charCode <= 57 || event.charCode ==46'>
                                            </div>
                                            <div class="col-md-4">
                                                <label>User Type</label>
                                                <select class="form-control mb-3 select2" id="search_user_type"
                                                    name="search_user_type" style="width: 100% !important;">
                                                    <option value="">Select</option>
                                                    <option value="not-deleted">Approved</option>
                                                    <option value="deleted">Deleted</option>
                                                </select><br />
                                            </div>
                                            <div class="col-md-4">
                                                <label>&nbsp;</label><br />
                                                <input class="btn btn-md btn-primary px-3 py-1 mb-3" id="clear-form-data"
                                                    type="reset" value="Clear Search">
                                            </div>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-striped datatable" id="dataTable"
                                                width="100%" cellspacing="0" data-url="user_list_data">
                                                <thead>
                                                    <tr>
                                                        <th class="sorting_disabled" id="id" data-orderable="false"
                                                            data-searchable="false">Id</th>
                                                        <th id="name" data-orderable="false" data-searchable="false">
                                                            Name</th>
                                                        <th id="email" data-orderable="false" data-searchable="false">
                                                            Email</th>
                                                        <th id="phone" data-orderable="false" data-searchable="false">
                                                            Phone</th>
                                                        <th id="gstin" data-orderable="false" class="col-md-2"
                                                            data-searchable="false">GST Number</th>
                                                        <th id="current_credit_amount" data-orderable="false"
                                                            class="col-md-2" data-searchable="false">Credits</th>
                                                        <th id="created_at" data-orderable="false" data-searchable="false">
                                                            DateTime</th>
                                                        @if ($data['user_status'] || $data['user_edit'] || $data['user_view'] || $data['user_add_address'])
                                                            <th id="action" data-orderable="false"
                                                                data-searchable="false" width="130px">Action</th>
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
