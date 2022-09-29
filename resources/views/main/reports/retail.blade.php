@extends('layouts.app')

@php
    $title = 'Monthly Sales Report';
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
            $("#downloadReport").on("click", function() {
                var month = $("#month").val();
                var export_filename = "Monthly-Sales-Report (" + month + ").xlsx";

                $.ajax({
                    xhrFields: {
                        responseType: 'blob',
                    },
                    type: "POST",
                    url: "{{route('reports.downloadRetail')}}",
                    data: {
                        '_token': '{{csrf_token()}}',
                        'month' : month,
                        },
                    success:function(result, status, xhr) {
                        var disposition = xhr.getResponseHeader('content-disposition');
                        var matches = /"([^"]*)"/.exec(disposition);

                        var filename = export_filename;

                        // The actual download
                        var blob = new Blob([result], {
                            type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                        });
                        var link = document.createElement('a');
                        link.href = window.URL.createObjectURL(blob);
                        link.target = '_blank'
                        link.download = filename;

                        document.body.appendChild(link);

                        link.click();
                        document.body.removeChild(link);
                    }
                });
            });

        });
    </script>
@endsection
