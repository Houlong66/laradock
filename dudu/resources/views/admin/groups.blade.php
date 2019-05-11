@extends('adminlte::page')

@section('title', '群组列表')

@section('content_header')
    <center><h1>群组列表</h1></center>
@stop

@section('content')

<table class="table table-bordered table-hover data-table">
    <thead>
        <tr>
            <th>群组编号</th>
            <th>群名</th>
            <th>隶属机构(代码)</th>
            <th>群类型</th>
            <th>状态</th>
            <th>创建时间</th>
            <th>更新时间</th>
            <th>操作</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($groups as $group)
        <tr>
            <td>{{ $group->id }}</td>
            <td>{{ $group->name }}</td>
            <td>{{ $group->org->name }}({{$group->org->code}})</td>
            <td>{{ groupType($group->type) }}</td>
            <td>{{ groupStatus($group->status) }}</td>
            <td>{{ $group->created_at }}</td>
            <td>{{ $group->updated_at }}</td>
            <td>
                <center>
                    <button type="button" class="btn btn-primary" onclick="location.href = '{{route('admin.group.users', $group->id)}}'">查看群组内人员</button>
                </center>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@stop
