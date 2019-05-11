@extends('adminlte::page')

@section('title', '机构账号列表')

@section('content_header')
    <center><h1>所有机构列表</h1></center>
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
            <th>状态</th>
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
                @switch($org->status)
                    @case(ORG_ST_DISABLED)
                        <button type="button" class="btn btn-danger"
                            onclick="toggleOrg('{{ route('admin.orgs.enable', $org->id) }}', '{{$org->name}}')">
                            {{orgStatus($org->status)}}</button>
                        @break

                        @case(ORG_ST_ENABLED)
                        <button type="button" class="btn btn-success"
                            onclick="toggleOrg('{{ route('admin.orgs.disable', $org->id) }}', '{{$org->name}}', false)">
                            {{orgStatus($org->status)}}</button>
                        @break

                        @case(ORG_ST_PENDING)
                        <button type="button" class="btn btn-warning"
                            onclick="location.href='{{route('admin.orgs.check')}}'">{{orgStatus($org->status)}}</button>
                        @break
                    @endswitch
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
                '是否确定要' + (enable ? '启用' : '禁用') +
                '机构 「' + name + '」？\n机构管理员将收到状态变更消息提醒';

            if (confirm(msg)) {
                location.href = link;
            }
        }
    </script>
@endsection
