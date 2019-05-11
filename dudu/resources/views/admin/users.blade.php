@extends('adminlte::page')

@section('title', '用户列表')

@section('content_header')
    <center>
        <h1>
            @switch(\Request::route()->getName())
                @case('admin.users')
                    所有用户列表
                    @break
                @case('admin.dept.users')
                    「{{$dept_name}}」 部门用户列表
                    @break
                @case('admin.group.users')
                    「{{$group_name}}」 群组用户列表
                    @break
            @endswitch
        </h1>
    </center>
@stop

@section('content')

<table class="table table-bordered table-hover data-table">
    <thead>
        <tr>
            <th>用户编号</th>
            <th>姓名</th>
            <th>性别</th>
            <th>昵称</th>
            <th>手机号</th>
            <th>通信地址</th>
            <th>固定电话</th>
            <th>邮箱</th>
            <th>QQ</th>
            <th>微信号</th>
            <th>创建时间</th>
            <th>更新时间</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($users as $user)
        <tr>
            <td>{{ $user->id }}</td>
            <td>{{ $user->real_name }}</td>
            <td>{{ userSex($user->sex) }}</td>
            <td>{{ $user->name }}</td>
            <td>{{ $user->tel }}</td>
            <td>{{ $user->address }}</td>
            <td>{{ $user->fixed_tel }}</td>
            <td>{{ $user->email }}</td>
            <td>{{ $user->qq }}</td>
            <td>{{ $user->wechat }}</td>
            <td>{{ $user->created_at }}</td>
            <td>{{ $user->updated_at }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@stop
