@extends('layouts.app')

@php
    $title = 'Monthly Thruput Report';
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
                                <select name="salesArea[]" id="salesArea" class="form-control" multiple="multiple">
                                    @foreach($salesAreas as $salesArea)
                                        <option value="{{$salesArea->id}}">{{$salesArea->name}}</option>
                                    @endforeach
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
                                <button id="downloadReport" type="button" class="btn btn-md btn-primary pull-right">
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
        $(document).ready(function(){
            $('#salesArea').select2();

            $("#downloadReport").on("click", function() {
                var salesArea = $("#salesArea").val();
                var month = $("#month").val();

                $.ajax({
                    type: "POST",
                    url: "{{route('reports.downloadThrupt')}}",
                    data: {
                        '_token': '{{csrf_token()}}',
                        'salesArea' : salesArea,
                        'month' : month,
                        },
                    success:function(data) {

                    }
                });
            });

        });
    </script>
@endsection
