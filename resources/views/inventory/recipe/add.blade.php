@extends('layouts.app')

@php
    $title = isset($recipe) ? 'Edit recipe' : 'Create recipe';
    $action = isset($recipe) ? route('inventory.recipe.update', $recipe->id) : route('inventory.recipe.store');
    $btnName = isset($recipe) ? 'UPDATE' : 'SAVE';

    $name = isset($recipe) ? $recipe->name : '';
    $code = isset($recipe) ? $recipe->code : getCodeFromID($lastindex, 6);
    $category_id = isset($recipe) ? $recipe->category_id : 0;
    $serve_size = isset($recipe) ? $recipe->serve_size : 1;
    $number_of_serving = isset($recipe) ? $recipe->number_of_serving : 1;
    $serve_unit_id = isset($recipe) ? $recipe->serve_unit_id : 0;
    $selected_items = isset($selected_items) ? $selected_items : [];
    $total_cost = isset($recipe) ? number_format($recipe->total_cost, 2, '.', '') : 0;
@endphp

@section('title', $title)

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">{{$title}}</div>
                    <div class="card-body">
                        <form id="recipe-form" action="{{$action}}" method="post" novalidate="novalidate">
                            @csrf
                            @if(isset($recipe)) @method('PUT') @endif
                            <input type="hidden" name="items" id="items" />
                            @include('partials.message')
                            <div class="row">
                                <div class="col-lg-8">
                                    <div class="row">
                                        <div class="form-group col-lg-4">
                                            <label for="code" class="control-label mb-1">Recipe Code</label>
                                            <input id="code" name="code" type="text" class="form-control" aria-required="true" aria-invalid="false" value="{{$code}}" readonly>
                                        </div>
                                        <div class="form-group col-lg-4">
                                            <label for="recipe-name" class="control-label mb-1">Recipe Name</label>
                                            <input id="recipe-name" name="name" type="text" class="form-control" aria-required="true" aria-invalid="false" value="{{$name}}">
                                            <span id="recipe-name-error-msg" class="error-msg">*You must input the recipe name.</span>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="form-group col-lg-4">
                                            <label for="category" class="control-label mb-1">Category</label>
                                            <select class="form-control" name="category_id" id="category">
                                                <option value="0">-- select --</option>
                                                @foreach($categories as $category)
                                                    <option value="{{$category->id}}" @if($category->id == $category_id) selected @endif>{{$category->name}}</option>
                                                @endforeach
                                            </select>
                                            <span id="recipe-category-error-msg" class="error-msg">*You must choose category.</span>
                                        </div>
                                        <div class="form-group col-lg-4">
                                            <label for="serve_size" class="control-label mb-1">Serve Size</label>
                                            <div class="input-group">
                                                <input id="serve_size" name="serve_size" type="number" class="form-control" aria-required="true" aria-invalid="false" value="{{$serve_size}}">
                                                <div class="input-group-btn">
                                                    <div class="btn-group">
                                                        <select class="form-control" id="unit" name="serve_unit_id">
                                                            <option value="0" selected>-- Select --</option>
                                                            @foreach($units as $unit)
                                                            <option value="{{$unit->id}}" @if($serve_unit_id == $unit->id) selected @endif>{{$unit->name}}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <span id="recipe-unit-error-msg" class="error-msg">*You must input serve size and choose the unit measure.</span>
                                            </div>
                                        </div>
                                        <div class="form-group col-lg-4">
                                            <label for="number_of_serving" class="control-label mb-1">Number of Servings</label>
                                            <input id="number_of_serving" name="number_of_serving" type="number" class="form-control" aria-required="true" aria-invalid="false" value="{{$number_of_serving}}">
                                            <span id="recipe-number-of-serving-error-msg" class="error-msg">*You must input number of servings.</span>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="form-group col-lg-4">
                                            <label for="total_cost" class="control-label mb-1">Total Cost</label>
                                            <input id="total_cost" name="total_cost" type="text" class="form-control" aria-required="true" aria-invalid="false" readonly value="{{$total_cost}}">
                                            <span id="recipe-total-cost-error-msg" class="error-msg">*You must add the items.</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-lg-12">
                                    <button type="button" class="btn btn-primary btn-sm" id="addItem" data-toggle="modal" data-target="#production-recipe-item-modal"><i class="fas fa-plus"></i> Add Item</button>
                                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#recipe-item-modal"><i class="fas fa-plus"></i> Add Production Recipe</button>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="table-responsive m-b-30 m-t-20">
                                        <table id="recipe-item-table" class="table table-striped table-earning table-addable">
                                            <thead>
                                                <tr>
                                                    <th>Type</th>
                                                    <th>Item Code</th>
                                                    <th>Item Name</th>
                                                    <th>Unit Measure</th>
                                                    <th>Quantity</th>
                                                    <th>Waste Percentage</th>
                                                    <th>Cost</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($selected_items as $item)
                                                @if($item->type == 'Item')
                                                <tr class="real-row" id="tr_item_{{$item->item_code}}">
                                                @else
                                                <tr class="real-row" id="tr_recipe_{{$item->item_code}}">
                                                @endif
                                                    <td>{{$item->type}}</td>
                                                    <td>{{$item->item_code}}</td>
                                                    <td>{{$item->item_name}}</td>
                                                    <td>{{$item->uom->name}}</td>
                                                    <td>{{$item->quantity}}</td>
                                                    <td>{{$item->item_waste_percentage}}</td>
                                                    @if($item->type == 'Item')
                                                    <td>{{number_format($item->item_unit_cost * $item->quantity, 2, '.', '')}}</td>
                                                    @else
                                                    <td>{{number_format($item->item_unit_cost * $item->quantity * $item->item_number_of_serving, 2, '.', '')}}</td>
                                                    @endif
                                                    <td>
                                                        <div class="table-data-feature text-center">
                                                            @if($item->type == 'Item')
                                                            <button class="item" type="button" title="Edit"><i class="zmdi zmdi-edit" onclick="editRow('{{$item->item_code}}', '{{$item->item_unit_cost}}', '{{$item->item_name}}', '{{$item->quantity}}', '{{$item->uom->name}}', '{{$item->item_waste_percentage}}', '{{number_format($item->item_unit_cost * $item->quantity, 2, '.', '')}}');"></i></button>
                                                            <button class="item" type="button" title="Delete"><i class="zmdi zmdi-delete" onclick="removeRow('item', '{{$item->item_code}}');"></i></button>
                                                            @else
                                                            <button class="item" type="button" title="Edit"><i class="zmdi zmdi-edit" onclick="editRecipeRow('recipe', '{{$item->item_code}}', '{{$item->item_unit_cost}}', '{{$item->item_name}}', '{{$item->quantity}}', '{{$item->uom->name}}', '{{$item->item_waste_percentage}}', '{{$item->item_number_of_serving}}', '{{number_format($item->item_unit_cost * $item->quantity * $item->item_number_of_serving, 2, '.', '')}}');"></i></button>
                                                            <button class="item" type="button" title="Delete"><i class="zmdi zmdi-delete" onclick="removeRow('recipe', '{{$item->item_code}}');"></i></button>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr id="empty-tr">
                                                    <td colspan="8" class="text-center">There are no items selected.</td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="form-group col-lg-6">
                                    <button id="confirm-button" type="button" class="btn btn-md btn-primary pull-right">
                                        <i class="fa fa-save fa-md"></i>&nbsp;
                                        <span>{{$btnName}}</span>
                                    </button>
                                </div>
                                <div class="form-group col-lg-6">
                                    <a href="{{route('inventory.recipe.index')}}" class="btn btn-md btn-secondary pull-left">
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
    </div>
@endsection

@section('scripts')
    <script>
        var unit_cost = 0, cost = 0, quantity = 1, real_quantity = 1, waste_percentage = 0, item_id = 0, item_name = '', item_code = '', total_cost = 0, unit_name = '', unit_number_of_serving = 1, unit_serve_size = 1;
        $(document).ready(function(){
            total_cost = $("#total_cost").val();
            $('#production-recipe-item').on('change', function(e){
                // get item's main properties
                waste_percentage = $('option:selected', this).attr('data-waste-percentage');
                unit_cost = $('option:selected', this).attr('data-cost');
                if(waste_percentage != 0){
                    real_amount = 1 - waste_percentage / 100;
                    unit_cost = unit_cost / real_amount;
                }
                $('#production-recipe-item-unit-cost').val(unit_cost);
                item_id = $('option:selected', this).val();
                item_name = $('option:selected', this).text();
                item_code = $('option:selected', this).attr('data-code');
                unit_name = $('option:selected', this).attr('data-unit');

                var _this = this;
                getUOM("", _this);

                cost = unit_cost;
                $('#production-recipe-item-quantity').attr('disabled', false);
                $('#production-recipe-item-quantity').val('1');
                $('#production-recipe-item-cost').val(normalizeCost(cost));
            });

            $('#production-recipe-item-unit-measure').change(function(){
               $('#uom-error-msg').hide();
               unit_cost = $(this).val();
               unit_name = $('option:selected', this).text();
               $('#production-recipe-item-unit-cost').val(unit_cost);
               quantity = $('#production-recipe-item-quantity').val();
               cost = parseFloat(unit_cost) * parseFloat(quantity);
               $('#production-recipe-item-cost').val(normalizeCost(cost));
            });

            $('#production-recipe-item-quantity').keyup(function(){
                quantity = $(this).val();
                unit_cost = $('#production-recipe-item-unit-cost').val();
                cost = parseFloat(unit_cost) * parseFloat(quantity);
                $('#production-recipe-item-cost').val(normalizeCost(cost));
            });
            $('#production-recipe-item-quantity').change(function(){
                quantity = $(this).val();
                unit_cost = $('#production-recipe-item-unit-cost').val();
                cost = parseFloat(unit_cost) * parseFloat(quantity);
                $('#production-recipe-item-cost').val(normalizeCost(cost));
            });
            $('#production-recipe-item-confirm-button').on('click', function(){
                //form validator
                if($('#production-recipe-item').val() == 0){
                    $('#production-recipe-item-select-error-msg').text('*You must choose one item.');
                    $('#production-recipe-item-select-error-msg').show();
                    return false;
                }

                if($(`#tr_item_${item_code}`))
                    $(`#tr_item_${item_code}`).remove();

                addItemRow(item_code);

                // Modal hide, form data init
                $('#production-recipe-item-modal').modal('hide');
                $('#production-recipe-item').val(0);
                $('#production-recipe-item-quantity').val(0);
                $('#production-recipe-item-unit-measure').val(0);
                $('#production-recipe-item-unit-measure').empty();
                $('#production-recipe-item-unit-measure').append('<option value="0">-- select --</option>');
                $('#production-recipe-item-cost').val('0.00');

                total_cost = Number(total_cost) + Number(cost);
                $('#total_cost').val(normalizeCost(total_cost));
                $('#recipe-total-cost-error-msg').hide();
            });

            /* ----------- Production Recipe --------------- */
            $('#recipe-item').on('change', function(e){
                $('#production-recipe-select-error-msg').hide();

                // set as default
                unit_cost = $('option:selected', this).attr('data-cost');
                waste_percentage = $('option:selected', this).attr('data-waste-percentage');
                item_id = $('option:selected', this).val();
                item_name = $('option:selected', this).text();
                item_code = $('option:selected', this).attr('data-code');
                unit_name = $('option:selected', this).attr('data-unit');
                unit_number_of_serving = $('option:selected', this).attr('data-number-of-serving');
                unit_serve_size = $('option:selected', this).attr('data-unit-size');
                real_quantity = 1 / unit_number_of_serving;

                // cost calculate, set it into the cost field
                cost = 0;
                $('#recipe-item-quantity').attr('disabled', true);
                if(unit_cost !== undefined){
                    cost = parseFloat(unit_cost) * parseFloat(unit_serve_size);
                    $('#recipe-item-quantity').attr('disabled', false);
                }
                $('#recipe-item-quantity').val('1');
                $('#recipe-item-real-quantity').val(real_quantity);
                $('#recipe-item-cost').val(normalizeCost(cost));
            });
            $('#recipe-item-quantity').keyup(function(){
                quantity = $(this).val();
                cost = parseFloat(unit_cost) * parseFloat(unit_serve_size) * parseFloat(quantity);
                real_quantity = parseFloat(quantity) / parseFloat(unit_number_of_serving);
                real_quantity = normalizeCost(real_quantity);
                $('#recipe-item-real-quantity').val(real_quantity);
                $('#recipe-item-cost').val(normalizeCost(cost));
            });
            $('#recipe-item-quantity').change(function(){
                quantity = $(this).val();
                cost = parseFloat(unit_cost) * parseFloat(unit_serve_size) * parseFloat(quantity);
                real_quantity = parseFloat(quantity) / parseFloat(unit_number_of_serving);
                real_quantity = normalizeCost(real_quantity);
                $('#recipe-item-real-quantity').val(real_quantity);
                $('#recipe-item-cost').val(normalizeCost(cost));
            });
            // Click add button in Production Recipe form
            $('#recipe-item-confirm-button').on('click', function(){
                //form validator
                if($('#recipe-item').val() == 0){
                    $('#production-recipe-select-error-msg').text('*You must choose one production recipe.');
                    $('#production-recipe-select-error-msg').show();
                    return false;
                }

                if($(`#tr_recipe_${item_code}`))
                    $(`#tr_recipe_${item_code}`).remove();

                // add new production recipe to the table
                addRecipeRow(item_code);

                // Modal hide, form data init
                $('#recipe-item-modal').modal('hide');
                $('#recipe-item').val(0);
                $('#recipe-item-quantity').attr('disabled', true);
                $('#recipe-item-quantity').val('1');
                $('#recipe-item-cost').val(normalizeCost(0));
                unit_cost = 0;

                total_cost = Number(total_cost) + Number(normalizeCost(cost));
                $('#total_cost').val(normalizeCost(total_cost));
                $('#recipe-total-cost-error-msg').hide();
            });
            $('#confirm-button').on('click', function(){
                var tbl = $('table#recipe-item-table tr').get().map(function(row) {
                    return $(row).find('td').get().map(function(cell) {
                        return $(cell).html();
                    });
                });
                $('#items').val(JSON.stringify(tbl));
                if(form_validator()){
                    $('#recipe-form').submit();
                }
            });
            $('#recipe-name').on('change', function(){
                $('#recipe-name-error-msg').hide();
            });
            $('#category').on('change', function(){
                $('#recipe-category-error-msg').hide();
            });
            $('#unit').on('change', function(){
                $('#recipe-unit-error-msg').hide();
            });
            $('#number_of_serving').on('change', function(){
                $('#recipe-number-of-serving-error-msg').hide();
            });
        });

        function addItemRow(item_code){
            $('#empty-tr').remove();
            var tr_row = "tr#tr_item_" + item_code;
            if($(tr_row).length){
                old_quantity = $(tr_row).find('td')[4].innerText;
                old_cost = $(tr_row).find('td')[6].innerText;

                new_quantity = Number(old_quantity) + Number(quantity);
                new_cost = Number(old_cost) + Number(cost);

                $(tr_row + " td:nth-child(5)").html(new_quantity);
                $(tr_row + " td:nth-child(7)").html(new_cost);
            }else {
                var action = `<td>
                        <div class="table-data-feature text-center">
                            <button class="item" type="button" data-toggle="tooltip" data-placement="top" title="Edit"><i class="zmdi zmdi-edit" onclick="editRow('${item_code}', '${unit_cost}', '${item_name}', '${quantity}', '${unit_name}', '${waste_percentage}', '${normalizeCost(cost)}');"></i></button>
                            <button class="item" type="button" data-toggle="tooltip" data-placement="top" title="Delete"><i class="zmdi zmdi-delete" onclick="removeRow('item', '${item_code}');"></i></button>
                        </div>
                    </td>`;
                $('#recipe-item-table tr:last').after(`<tr class="real-row" id="tr_item_${item_code}"><td>Item</td><td>${item_code}</td><td>${item_name}</td><td>${unit_name}</td><td>${quantity}</td><td>${waste_percentage}</td><td>${normalizeCost(cost)}</td>${action}</tr>`);
            }
        }

        function addRecipeRow(item_code){
            $('#empty-tr').remove();
            var tr_row = "tr#tr_recipe_" + item_code;
            if($(tr_row).length){
                old_quantity = $(tr_row).find('td')[4].innerText;
                old_cost = $(tr_row).find('td')[6].innerText;

                new_quantity = Number(old_quantity) + Number(quantity);
                new_cost = Number(old_cost) + Number(cost);

                $(tr_row + " td:nth-child(5)").html(new_quantity);
                $(tr_row + " td:nth-child(7)").html(new_cost);

            }else {
                var action = `
                <td>
                    <div class="table-data-feature text-center">
                        <button class="item" type="button" data-toggle="tooltip" data-placement="top" title="Edit"><i class="zmdi zmdi-edit" onclick="editRecipeRow('recipe', '${item_code}', '${unit_cost}', '${item_name}', '${real_quantity}', '${unit_name}', '${waste_percentage}', '${unit_number_of_serving}', '${normalizeCost(cost)}');"></i></button>
                        <button class="item" type="button" data-toggle="tooltip" data-placement="top" title="Delete"><i class="zmdi zmdi-delete" onclick="removeRow('recipe', '${item_code}');"></i></button>
                    </div>
                </td>`;
                $('#recipe-item-table tr:last').after(`<tr class="real-row" id="tr_recipe_${item_code}"><td>Production Recipe</td><td>${item_code}</td><td>${item_name}</td><td>${unit_name}</td><td>${real_quantity}</td><td>${waste_percentage}</td><td>${normalizeCost(cost)}</td>${action}</tr>`);
            }
        }

        function editRow(code, cost, name, qty, uom_name, waste, price){
            total_cost = Number(total_cost) - Number(price);
            item_name = name;
            waste_percentage = waste;
            unit_cost = cost;
            unit_name = uom_name;
            item_code = code;
            quantity = qty;
            $('#production-recipe-item-unit-cost').val(cost);
            $('#production-recipe-item-modal').modal('show');
            $('#production-recipe-item-quantity').val(qty);
            $('#production-recipe-item option').filter(function () { return $(this).html() == item_name; }).prop('selected', true);
            getUOM(unit_name, $('#production-recipe-item'));
            $('#production-recipe-item-cost').val(normalizeCost(price));
        }

        function editRecipeRow(type, code, cost, name, real_qty, uom_name, waste, number_of_serving, price){
            total_cost = Number(total_cost) - Number(price);
            item_code = code;
            item_name = name;
            unit_cost = cost;
            waste_percentage = waste;
            unit_name = uom_name;
            unit_number_of_serving = number_of_serving;
            quantity = parseFloat(real_qty) * parseFloat(number_of_serving);
            real_quantity = real_qty;
            waste_percentage = waste;
            $('#recipe-item-modal').modal('show');
            $('#recipe-item-quantity').attr('disabled', false);
            $('#recipe-item-quantity').val(quantity);
            $('#recipe-item-real-quantity').val(real_quantity);
            $('#recipe-item option').filter(function () { return $(this).html() == item_name; }).prop('selected', true);
            $('#recipe-item-cost').val(normalizeCost(price));
        }

        function removeRow(type, item_code){
            old_cost = $('#tr_'+type+'_'+item_code).find('td')[6].innerText;
            total_cost = Number(total_cost) - Number(old_cost);
            $('#tr_'+type+'_'+item_code).remove();
            $('#total_cost').val(normalizeCost(total_cost));
            if($('.real-row').length == 0)
                $('#recipe-item-table tr:last').after(`<tr id="empty-tr"><td colspan="8" class="text-center">There are no items selected.</td></tr>`);
        }
        function form_validator(){
            //recipe-name
            if($('#recipe-name').val() == ''){
                $('#recipe-name-error-msg').show();
                return false;
            }
            if($('#category').val() == 0){
                $('#recipe-category-error-msg').show();
                return false;
            }
            if($('#unit').val() == 0){
                $('#recipe-unit-error-msg').show();
                return false;
            }
            if($('#total_cost').val() == 0){
                $('#recipe-total-cost-error-msg').show();
                return false;
            }
            if($('#number_of_serving').val() == 0){
                $('#recipe-number-of-serving-error-msg').show();
                return false;
            }
            return true;
        }
        function configureUOM(opt){
            $('#production-recipe-item-unit-measure').empty();
            $('#production-recipe-item-unit-measure').append(opt);
        }
        function getUOM(unit, _this){
            // get item's UOM
            //unit_cost = $('option:selected', _this).attr('data-cost');
            waste_percentage = $('option:selected', _this).attr('data-waste-percentage');
            unit_cost = $('option:selected', _this).attr('data-cost');
            if(waste_percentage != 0){
                real_amount = 1 - waste_percentage / 100;
                unit_cost = unit_cost / real_amount;
            }
            product_uom = $('option:selected', _this).attr('data-product-uom');
            product_size_uom = $('option:selected', _this).attr('data-product-size-uom');
            product_size = $('option:selected', _this).attr('data-product-size');
            recipe_uom = $('option:selected', _this).attr('data-recipe-uom');
            // configure uom list
            if(product_size_uom == recipe_uom){
                if(unit == product_uom)
                    opt = `<option value="0">-- select --</option><option value="${unit_cost}" selected>${product_uom}</option><option value="${parseFloat(unit_cost)/parseFloat(product_size)}">${recipe_uom}</option>`;
                if(unit == product_size_uom)
                    opt = `<option value="0">-- select --</option><option value="${unit_cost}">${product_uom}</option><option value="${parseFloat(unit_cost)/parseFloat(product_size)}" selected>${recipe_uom}</option>`;
                if(unit == "")
                    opt = `<option value="0">-- select --</option><option value="${unit_cost}">${product_uom}</option><option value="${parseFloat(unit_cost)/parseFloat(product_size)}">${recipe_uom}</option>`;
                configureUOM(opt);
            }else{
                /*
                * get conversion rate
                * from: product_size_uom
                * to: recipe_uom
                */
                $.ajax({
                    type: "GET",
                    url: "{{route('inventory.getConversion')}}",
                    data: {'from' : product_size_uom,'to' : recipe_uom},
                    success:
                        function(data) {
                            if(data.status == 'fail'){
                                $('#uom-error-msg').text(`The conversion between ${product_size_uom} and ${recipe_uom} is not define`);
                                $('#uom-error-msg').show();
                                if(unit == product_uom)
                                    opt = `<option value="0">-- select --</option><option value="${unit_cost}" selected>${product_uom}</option><option value="${parseFloat(unit_cost)/parseFloat(product_size)}">${product_size_uom}</option>`;
                                if(unit == product_size_uom)
                                    opt = `<option value="0">-- select --</option><option value="${unit_cost}">${product_uom}</option><option value="${parseFloat(unit_cost)/parseFloat(product_size)}" selected>${product_size_uom}</option>`;
                                if(unit == "")
                                    opt = `<option value="0">-- select --</option><option value="${unit_cost}">${product_uom}</option><option value="${parseFloat(unit_cost)/parseFloat(product_size)}">${product_size_uom}</option>`;
                            }else{
                                conversion = data.rate;
                                if(unit == product_uom)
                                    opt = `<option value="0">-- select --</option><option value="${unit_cost}" selected>${product_uom}</option><option value="${parseFloat(unit_cost)/parseFloat(product_size)}">${product_size_uom}</option><option value="${parseFloat(unit_cost)/parseFloat(product_size)/parseFloat(conversion)}">${recipe_uom}</option>`;
                                if(unit == product_size_uom)
                                    opt = `<option value="0">-- select --</option><option value="${unit_cost}">${product_uom}</option><option value="${parseFloat(unit_cost)/parseFloat(product_size)}" selected>${product_size_uom}</option><option value="${parseFloat(unit_cost)/parseFloat(product_size)/parseFloat(conversion)}">${recipe_uom}</option>`;
                                if(unit == recipe_uom)
                                    opt = `<option value="0">-- select --</option><option value="${unit_cost}">${product_uom}</option><option value="${parseFloat(unit_cost)/parseFloat(product_size)}">${product_size_uom}</option><option value="${parseFloat(unit_cost)/parseFloat(product_size)/parseFloat(conversion)}" selected>${recipe_uom}</option>`;
                                if(unit == "")
                                    opt = `<option value="0">-- select --</option><option value="${unit_cost}">${product_uom}</option><option value="${parseFloat(unit_cost)/parseFloat(product_size)}">${product_size_uom}</option><option value="${parseFloat(unit_cost)/parseFloat(product_size)/parseFloat(conversion)}">${recipe_uom}</option>`;
                            }
                            configureUOM(opt);
                        }
                });
            }
        }
    </script>
@endsection
