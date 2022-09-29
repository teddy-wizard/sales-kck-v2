@extends('layouts.app')

@section('title', 'Users')

@section('content')
    <div class="container-fluid">
        <div class="row m-b-30">
            <div class="col-lg-12">
                <a class="btn btn-primary pull-right" href="{{route('user.create')}}"><i class="fa fa-plus"></i>&nbsp; Create User</a>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <table class="table table-borderless table-striped table-earning js-exportable">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Login ID</th>
                            <th>Name</th>
                            <th>Email Address</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                        <tr>
                            <td>{{$loop->index + 1}}</td>
                            <td>{{$user->username}}</td>
                            <td>
                                {{$user->name}} ***
                            </td>
                            <td>{{$user->email}}</td>
                            <td>
                                @if($user->status == 0) Inactive @endif
                                @if($user->status == 1) Active @endif
                                @if($user->status == 2) Locked @endif
                                @if($user->status == 3) Deactivated @endif
                            </td>
                            <td>
                                <div class="table-data-feature text-center">
                                    <a href="{{route('user.edit', $user->id)}}" class="item" data-toggle="tooltip" data-placement="top" title="Edit">
                                        <i class="zmdi zmdi-edit"></i>
                                    </a>
                                    @if($isAdmin)
                                    <a href="{{route('resetPasswordByAdmin', $user->id)}}" class="item" data-toggle="tooltip" data-placement="top" title="Reset Password">
                                        <i class="fa fa-gear"></i>
                                    </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
