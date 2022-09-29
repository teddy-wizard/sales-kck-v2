@extends('layouts.app')

@section('title', 'Users')

@section('content')
    <div class="container-fluid">
        <div class="row m-b-30">
            <div class="col-lg-12">
                <div class="card p-5">
                    <div class="form-group col-md-6">
                        <label for="status" class="control-label mb-1">Data*</label>
                        <select id="type" name="type" class="form-control">
                            <option value=""> ----- Select ----- </option>
                            <option value="SalesAgent">SalesAgent</option>
                            <option value="TaxType">TaxType</option>
                            <option value="Term">Term</option>
                            <option value="StockItem">StockItem</option>
                            <option value="Debtor">Debtor</option>
                            <option value="SalesOrder">SalesOrder</option>
                            <option value="Invoice">Invoice</option>
                            <option value="DebitNote">DebitNote</option>
                            <option value="SalesInvoice">SalesInvoice</option>
                            <option value="SalesCreditNote">SalesCreditNote</option>
                            <option value="SalesDebitNote">SalesDebitNote</option>
                        </select>
                    </div>
                    <button id="btnSync" class="btn btn-primary pull-left"><i class="fa fa-cloud"></i>&nbsp; Sync Data</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function(){
            $("#btnSync").on("click", function(){

                if($("#type").val() == "")
                    return;


                $.ajax({
                    type: "POST",
                    url: "{{route('syncData')}}",
                    data: {
                        'option' : $("#type").val(),
                        '_token': '{{csrf_token()}}'
                    },
                    success:function(data) {
                        console.log(data.result);
                    }
                });
            });
        });
    </script>
@endsection
