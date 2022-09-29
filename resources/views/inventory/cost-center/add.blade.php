@extends('layouts.app')


@php
    $title = isset($costcenter) ? 'Edit cost center' : 'Create cost center';
    $action = isset($costcenter) ? route('inventory.cost-center.update', $costcenter->id) : route('inventory.cost-center.store');
    $btnName = isset($costcenter) ? 'UPDATE' : 'SAVE';

    $username = isset($costcenter) ? $costcenter->name : '';
    $branch_id = isset($costcenter) ? $costcenter->branch_id : 0;

@endphp

@section('title', $title)

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">{{$title}}</div>
                    <div class="card-body">
                        <form action="{{$action}}" method="post" novalidate="novalidate">
                            @csrf
                            @if(isset($costcenter)) @method('PUT') @endif
                            @include('partials.message')
                            <div class="row">
                                <div class="form-group col-lg-12">
                                    <label for="name" class="control-label mb-1">Cost Center Name</label>
                                    <input id="name" name="name" type="text" class="form-control" aria-required="true" aria-invalid="false" value="{{$username}}">
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-lg-12">
                                    <label for="branch" class="control-label mb-1">Branch</label>
                                    <select name="branch_id" id="branch" class="form-control">
                                        <option value="0">-- Select --</option>
                                        @foreach($branches as $branch)
                                            <option value="{{$branch->id}}" @if($branch_id == $branch->id) selected @endif>{{$branch->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-lg-6">
                                    <button id="confirm-button" type="submit" class="btn btn-md btn-primary pull-right">
                                        <i class="fa fa-save fa-md"></i>&nbsp;
                                        <span>{{$btnName}}</span>
                                    </button>
                                </div>
                                <div class="form-group col-lg-6">
                                    <a href="{{route('inventory.cost-center.index')}}" class="btn btn-md btn-secondary pull-left">
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
