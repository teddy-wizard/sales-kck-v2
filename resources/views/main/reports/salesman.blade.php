@extends('layouts.app')

@php
    $title = 'Product Salesman Sales Report';
    // $action = route('user.changePassword');
    $btnName = 'SAVE';

@endphp

@section('title', $title)

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">{{$title}}</div>
                    <div class="card-body">
                        <div class="row">
                            <div class="form-group col-lg-8">
                                <label for="salesArea" class="control-label mb-1"> Sales Area*:</label>
                                <select class="form-control salesArea">
                                    <option value="0">-- select --</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-lg-4">
                                <label for="month" class="control-label mb-1"> * Month:</label>
                                <input id="month" name="month" type="month" class="form-control datepicker" aria-required="true" aria-invalid="false" value="{{ date('Y-m') }}">
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-lg-6">
                                <button id="confirm-button" type="button" class="btn btn-md btn-primary pull-right">
                                    <i class="fa fa-save fa-md"></i>&nbsp;
                                    <span>Download as Excel</span>
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
