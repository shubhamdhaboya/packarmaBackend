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
                                                <h5 class="pt-2">Manage Approval List</h5>
                                            </div>
                                            <div class="col-12 col-sm-5 d-flex justify-content-end align-items-center">
                                                <button class="btn btn-sm btn-outline-danger px-3 py-1 mr-2" id="listing-filter-toggle"><i class="fa fa-filter"></i>Filter</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="row mb-2" id="listing-filter-data" style="display: none;">
                                            <div class="col-md-4">
                                                <label>Name</label>
                                                <input class="form-control mb-3" type="text" id="search_name" name="search_name">
                                            </div>
                                            <div class="col-md-4">
                                                <label>Phone</label>
                                                <input class="form-control mb-3" type="text" id="search_phone" name="search_phone" onkeypress='return event.charCode >= 48 && event.charCode <= 57 || event.charCode ==46'>
                                            </div>
                                            <div class="col-md-4">
                                                <label>Email</label>
                                                <input class="form-control mb-3" type="email" id="search_email" name="search_email" onkeypress='return event.charCode >= 48 && event.charCode <= 57 || event.charCode ==46'>
                                            </div>
                                            <div class="col-md-4">
                                                <label>Approval Status</label>
                                                <select class="form-control mb-3 select2" name="search_approval_status" id="search_approval_status" style="width: 100% !important">
                                                    <option value="">Select</option>
                                                    @foreach ($data['approvalStatusArray'] as $key => $status)
                                                        @if ($key == 'accepted')
                                                            @php
                                                                continue;
                                                            @endphp
                                                        @endif
                                                        <option value="{{ $key }}">{{ $status }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label>&nbsp;</label><br/>
                                                <input class="btn btn-md btn-primary px-3 py-1 mb-3" id="clear-form-data" type="reset" value="Clear Search">
                                            </div>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-striped datatable" id="dataTable" width="100%" cellspacing="0" data-url="user_approval_list_data">
                                                <thead>
                                                    <tr>
                                                        <th class="sorting_disabled" id="id" data-orderable="false" data-searchable="false">Id</th>
                                                        <th id="name" data-orderable="false" data-searchable="false">Name</th>
                                                        <th id="email" data-orderable="false" data-searchable="false">Email</th>
                                                        <th id="phone" data-orderable="false" class="col-md-2" data-searchable="false">Phone</th>
                                                        <th id="gstin" data-orderable="false" class="col-md-2" data-searchable="false">GST Number</th>
                                                        {{-- <th id="gst_certificate" data-orderable="false" class="col-md-2" data-searchable="false">GST Certificate</th> --}}
                                                        <th id="approval_status" data-orderable="false" data-searchable="false">Approval Status</th>
                                                        <th id="created_at" data-orderable="false" data-searchable="false">Datetime</th>
                                                        @if ($data['user_approval_view'] || $data['user_approval_update'] )
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
