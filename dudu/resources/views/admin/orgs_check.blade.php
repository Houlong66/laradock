@extends('adminlte::page')

@section('title', '机构注册审核')

@section('content_header')
    <center><h1>待审核机构列表</h1></center>
@stop

@section('content')

<table class="table table-bordered table-hover data-table">
    <thead>
        <tr>
            <th>机构编号</th>
            <th>机构代码</th>
            <th>机构名</th>
            <th>地区</th>
            <th>创建时间</th>
            <th>更新时间</th>
            <th>操作</th>
        </tr>
    </thead>

    <tbody>
        @foreach ($orgs as $org)
        <tr>
            <td>{{ $org->id }}</td>
            <td>{{ $org->code }}</td>
            <td>{{ $org->name }}</td>
            <td>{{ $org->region }}</td>
            <td>{{ $org->created_at }}</td>
            <td>{{ $org->updated_at }}</td>
            <td>
                <center>
                    <div class="btn-group">
                        <button type="button" class="btn btn-success" onclick="toggleOrg('{{ route('admin.orgs.enable', $org->id) }}', '{{$org->name}}')">审核通过</button>
                        <button type="button" class="btn btn-danger" onclick="toggleOrg('{{ route('admin.orgs.disable', $org->id) }}', '{{$org->name}}', false)">拒绝申请</button>
                    </div>
                </center>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@stop

@section('js')
    <script>
        function toggleOrg(link, name, enable =true) {
            var msg =
                '是否确定' + (enable ? '通过' : '拒绝') +
                '机构 「' + name + '」 的申请请求？\n申请者将收到一条申请结果消息';

            if (confirm(msg)) {
                location.href = link;
            }
        }
    </script>
@endsection
