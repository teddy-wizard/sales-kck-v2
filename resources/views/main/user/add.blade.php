@extends('layouts.app')

@php
    $title = isset($user) ? 'Edit user' : 'New user';
    $action = isset($user) ? route('user.update', $user->id) : route('user.store');
    $btnName = isset($user) ? 'UPDATE' : 'CREATE';

    $id = isset($user) ? $user->id : '';
    $username = isset($user) ? $user->username : old('username');
    $name = isset($user) ? $user->name : old('name');
    $email = isset($user) ? $user->email : old('email');
    $role = isset($user) ? $user->role : [];
    $selected_companies = isset($user) ? mb_split(',', $user->company_ids) : [];

    $branch_id = '';
    $manager_id = '';
    $month_target = '';
    $code = '';
    $showSalesPart = 0;
    if(isset($user)) {
        if(isset($user->sales_info)) {
            $showSalesPart = 1;
            $branch_id = $user->sales_info->salesArea;
            $manager_id = $user->sales_info->managerId;
            $month_target = $user->sales_info->monthTarget;
            $code = $user->sales_info->code;
        }
    }

@endphp

@section('title', $title)

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <h1>{{$title}}</h1>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">User Information</div>
                    <div class="card-body">
                        <form id="user-form" action="{{$action}}" method="post" novalidate="novalidate">
                            @csrf
                            @if(isset($user)) @method('PUT') @endif
                            @include('partials.message')
                            <input type="hidden" id="id" name="id" value="{{$id}}"/>
                            <div class="row">
                                <div class="form-group col-lg-6">
                                    <label for="username" class="control-label mb-1">Login ID*</label>
                                    <input id="username" name="username" type="text" class="form-control" aria-required="true" aria-invalid="false" value="{{$username}}">
                                </div>
                                <div class="form-group col-lg-6">
                                    <label for="fullname" class="control-label mb-1">Display Name*</label>
                                    <input id="fullname" name="name" type="text" class="form-control" aria-required="true" aria-invalid="false" value="{{$name}}">
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-lg-6">
                                    <label for="email" class="control-label mb-1">Email*</label>
                                    <input id="email" name="email" type="email" class="form-control" aria-required="true" aria-invalid="false" value="{{$email}}">
                                </div>
                                <div class="form-group col-lg-6">
                                    <label for="company-id" class="control-label mb-1">Company*</label>
                                    <select name="company[]" id="company-id" class="form-control" multiple="multiple">
                                        @foreach($companies as $company)
                                            <option value="{{$company->id}}" @if(in_array($company->id, $selected_companies)) selected @endif>{{$company->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-lg-12">
                                    <label for="role" class="control-label">Role*</label>
                                    <div class="form-check">
                                        <div class="checkbox mb-2">
                                            <label for="checkbox1" class="form-check-label ">
                                                <input type="checkbox" id="checkbox1" name="role[]" value="1" class="form-check-input" @if(in_array(1, $role)) checked @endif> System Admin
                                            </label>
                                        </div>
                                        <div class="checkbox mb-2">
                                            <label for="checkbox2" class="form-check-label ">
                                                <input type="checkbox" id="checkbox2" name="role[]" value="2" class="form-check-input" @if(in_array(2, $role)) checked @endif> User Admin
                                            </label>
                                        </div>
                                        <div class="checkbox mb-2">
                                            <label for="checkbox3" class="form-check-label ">
                                                <input type="checkbox" id="checkbox3" name="role[]" value="3" class="form-check-input" @if(in_array(3, $role)) checked @endif> Ops
                                            </label>
                                        </div>
                                        <div class="checkbox mb-2">
                                            <label for="checkbox4" class="form-check-label ">
                                                <input type="checkbox" id="checkbox4" name="role[]" value="4" class="form-check-input" @if(in_array(4, $role)) checked @endif> Salesman
                                            </label>
                                        </div>
                                        <div class="checkbox">
                                            <label for="checkbox5" class="form-check-label ">
                                                <input type="checkbox" id="checkbox5" name="role[]" value="5" class="form-check-input" @if(in_array(5, $role)) checked @endif> Sales Manager
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div id="salesPart" class="row" @if($showSalesPart == 0)style="display:none"@endif>
                                <div class="form-group col-lg-6">
                                    <label for="branch" class="control-label mb-1">Branch*</label>
                                    <select id="salesArea" name="salesArea" class="form-control branch">
                                        <option value="0">-- select --</option>
                                        @foreach($branches as $branch)
                                        <option value="{{$branch->id}}" @if($branch->id == $branch_id) selected @endif>{{$branch->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-lg-6">
                                    <label for="manager" class="control-label mb-1">Manager*</label>
                                    <select id="managerId" name="managerId" class="form-control manager">
                                        <option value="0">-- select --</option>
                                        @foreach($managers as $manager)
                                        <option value="{{$manager->user_id}}" @if($manager->user_id == $manager_id) selected @endif>{{$manager->user_name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-lg-6">
                                    <label for="monthTarget" class="control-label mb-1">Manthly Sales Target*</label>
                                    <input id="monthTarget" name="monthTarget" type="number" min="0" class="form-control" aria-required="true" aria-invalid="false" value="{{$month_target}}">
                                </div>
                                <div class="form-group col-lg-6">
                                    <label for="documentCode" class="control-label mb-1">Document Code</label>
                                    <input id="documentCode" name="documentCode" type="text" class="form-control" aria-required="true" aria-invalid="false" value="{{$code}}" readonly>
                                </div>
                            </div>
                            @if(isset($user))
                            <div class="row">
                                <div class="form-group col-lg-6">
                                    <label for="status" class="control-label mb-1">Status*</label>
                                    <select id="status" name="status" class="form-control status">
                                        <option value="1" @if($user->status == 1) selected @endif>Active</option>
                                        <option value="0" @if($user->status == 0) selected @endif>Inactive</option>
                                        <option value="2" @if($user->status == 2) selected @endif>Locked</option>
                                        <option value="3" @if($user->status == 3) selected @endif>Deactivated</option>
                                    </select>
                                </div>
                            </div>
                            @endif

                            <div class="row">
                                <div class="form-group col-lg-6">
                                    <button id="confirm-button" type="submit" class="btn btn-md btn-primary pull-right">
                                        <i class="fa fa-save fa-md"></i>&nbsp;
                                        <span>{{$btnName}}</span>
                                    </button>
                                </div>
                                <div class="form-group col-lg-6">
                                    <a href="{{route('user.index')}}" class="btn btn-md btn-secondary pull-left">
                                        <i class="fa fa-ban fa-md"></i>&nbsp;
                                        <span>Cancel</span>
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @if(isset($user) && ($showSalesPart == 1))
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">Sale Agent Mapping</div>
                    <div class="card-body">
                        <div class="row m-b-30">
                            <div class="col-lg-12">
                                <button id="btn-add-saleAgent" class="btn btn-primary pull-right"><i class="fa fa-plus"></i>&nbsp; Add</button>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <table id="sale-agent-mapping-table" class="table table-borderless table-striped table-earning js-exportable">
                                    <thead>
                                        <tr>
                                            <th>Company</th>
                                            <th>Sales Agent</th>
                                            <th width="15%">Remove</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @isset($salesPersonMappings)
                                            @foreach($salesPersonMappings as $salesPersonMapping)
                                            <tr>
                                                <td>{{ $salesPersonMapping->companyInfo->name }}</td>
                                                <td>{{ $salesPersonMapping->acSalesAgentInfo->salesAgent }}</td>
                                                <td>
                                                    <div class="table-data-feature text-center">
                                                        <button href="#" class="sale-agent-mapping-remove" data-mappingId="{{ $salesPersonMapping->id }}" data-toggle="tooltip" data-placement="top" title="Remove">
                                                            <i class="zmdi zmdi-delete"></i>&nbsp; REMOVE
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                            @endforeach
                                        @endisset
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <div>
            </div>
        </div>
        @endif
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function(){
            $('#company-id').select2();
            $("#checkbox4, #checkbox5").on("change", function(){
                if(($("#checkbox4").is(':checked')) || ($("#checkbox5").is(':checked')) )
                    $("#salesPart").show();
                else
                    $("#salesPart").hide();
            });

            $("#btn-add-saleAgent").on("click", function() {
                var user_id = "{{ $id }}";
                console.log(user_id);
                $.ajax({
                    type: "GET",
                    url: "{{route('getCompanyByUser')}}",
                    data: {'user_id' : user_id},
                    success:function(data) {
                        $("#sale-agent-mapping-company").html(data);
                        $("#sale-agent-mapping-modal").modal("show");
                    }
                });
            });

            $("#sale-agent-mapping-company").on("change", function(){
                var company_id = $(this).children("option:selected").val();
                $.ajax({
                    type: "GET",
                    url: "{{route('getSaleAgentsByCompany')}}",
                    data: {'company_id' : company_id},
                    success:function(data) {
                        $("#sale-agent-mapping-agent").append(data);
                    }
                });
            });

            $("#sale-agent-mapping-company").on("change", function(){
                var company_id = $(this).children("option:selected").val();
                $.ajax({
                    type: "GET",
                    url: "{{route('getSaleAgentsByCompany')}}",
                    data: {'company_id' : company_id},
                    success:function(data) {
                        $("#sale-agent-mapping-agent").append(data);
                    }
                });
            });

            $("#sale-agent-mapping-confirm-button").on("click", function(){
                var user_id = "{{ $id }}";
                var company_id = $("#sale-agent-mapping-company").children("option:selected").val();
                var agent_id = $("#sale-agent-mapping-agent").children("option:selected").val();
                $.ajax({
                    type: "POST",
                    url: "{{route('addSaleAgentMapping')}}",
                    data: {
                        'user_id' : user_id,
                        'company_id' : company_id,
                        'agent_id' : agent_id,
                        '_token': '{{csrf_token()}}'
                    },
                    success:function(data) {
                        if(data.status) {
                            if($("#sale-agent-mapping-table tbody tr:first td").hasClass("dataTables_empty"))
                                $("#sale-agent-mapping-table tbody").html("");
                            $("#sale-agent-mapping-table tbody").append(data.row);
                            $("#sale-agent-mapping-modal").modal("hide");
                        } else {
                            console.log("add error");
                        }
                    }
                });
            });

            $(".sale-agent-mapping-remove").on("click", function(){
                var mapping_id = $(this).data("mappingid");
                var tr_row = $(this).closest("tr");

                $.ajax({
                    type: "POST",
                    url: "{{route('removeSaleAgentMapping')}}",
                    data: {
                        'mapping_id' : mapping_id,
                        '_token': '{{csrf_token()}}'
                    },
                    success:function(data) {
                        if(data.status) {
                            tr_row.remove();
                            swal({
                                title: "The sale agent was removed from this user."
                            });
                        } else {
                            console.log("add error");
                        }
                    }
                });
            });
        });


    </script>
@endsection
