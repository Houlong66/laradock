@extends('adminlte::page')

@section('title', '部门列表')

@section('content_header')
    <center><h1>部门列表</h1></center>
@stop

@section('content')

<table class="table table-bordered table-hover data-table">
    <thead>
        <tr>
            <th>部门编号</th>
            <th>部门名称</th>
            <th>隶属机构(代码)</th>
            <th>部门级别</th>
            <th>状态</th>
            <th>创建时间</th>
            <th>更新时间</th>
            <th>操作</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($depts as $dept)
        <tr>
            <td>{{ $dept->id }}</td>
            <td>{{ $dept->name }}</td>
            <td>{{ $dept->org->name }}({{$dept->org->code}})</td>
            <td>{{ deptLevel($dept->level) }}</td>
            <td>{{ deptStatus($dept->status) }}</td>
            <td>{{ $dept->created_at }}</td>
            <td>{{ $dept->updated_at }}</td>
            <td>
                <center>
                    <button type="button" class="btn btn-primary" onclick="location.href = '{{route('admin.dept.users', $dept->id)}}'">查看部门内人员</button>
                </center>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@stop

