<meta name="csrf-token" content="{{ csrf_token() }}">
@extends('adminlte::page')
{{--<meta name="csrf-token" content="{{ csrf_token() }}">--}}
@section('title', '都督 · 管理页面')

@section('content_header')
@stop

{{-- 目前首页暂时默认跳转至所有机构列表页 --}}
<script>location.href = '/admin/orgs';</script>
