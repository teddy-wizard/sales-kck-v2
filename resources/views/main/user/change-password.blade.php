@extends('layouts.app')

@php
    $title = 'Change Password';
    // $action = route('user.changePassword');
    $btnName = 'SAVE';

@endphp

@section('title', $title)

@section('styles')
    <style>
        .form-check-inline .radio {
            margin: 0 10px;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">{{$title}}</div>
                    <div class="card-body">
                        <div class="row">
                            <div class="form-group col-lg-12">
                                <label for="oldPassword" class="control-label mb-1">Old Password*</label>
                                <input id="oldPassword" name="oldPassword" type="text" class="form-control" aria-required="true" aria-invalid="false">
                            </div>
                            <div class="form-group col-lg-12">
                                <label for="newPassword" class="control-label mb-1">New Password*</label>
                                <input id="newPassword" name="newPassword" type="text" class="form-control" aria-required="true" aria-invalid="false">
                            </div>
                            <div class="form-group col-lg-12">
                                <label for="confirmNewPassword" class="control-label mb-1">Confirm New Password*</label>
                                <input id="confirmNewPassword" name="confirmNewPassword" type="text" class="form-control" aria-required="true" aria-invalid="false">
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-lg-6">
                                <button id="confirm-button" type="button" class="btn btn-md btn-primary pull-right">
                                    <i class="fa fa-save fa-md"></i>&nbsp;
                                    <span>{{$btnName}}</span>
                                </button>
                            </div>
                            <div class="form-group col-lg-6">
                                <button id="reset-button" type="button" class="btn btn-md btn-secondary pull-left">
                                    <i class="fa fa-ban fa-md"></i>&nbsp;
                                    <span>Reset</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>

    </script>
@endsection
