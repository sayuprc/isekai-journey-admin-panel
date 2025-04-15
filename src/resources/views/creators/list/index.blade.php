@php use Support\Route\RouteMap; @endphp

@extends('layout.page')

@section('title', 'クリエイター一覧')

@section('content_header')
    <h1>クリエイター一覧</h1>
@endsection

@section('content')
    @if(session('message'))
        <x-adminlte-alert theme="info" title="Info">
            {{ session('message') }}<br>
        </x-adminlte-alert>
    @endif
    @if($errors->any())
        <x-adminlte-alert theme="danger" title="Error">
            @foreach($errors->all() as $error)
                {{ $error }}<br>
            @endforeach
        </x-adminlte-alert>
    @endif
    <x-adminlte-datatable id="table" :heads="$heads" :config="$config">
        @php /** @var \App\Http\ViewModels\Web\Creator\CreatorListView $creator */ @endphp
        @foreach($creators as $creator)
            <tr>
                <td>{{ $creator->creatorName }}</td>
                <td><a href="">編集</a>
                </td>
            </tr>
        @endforeach
    </x-adminlte-datatable>
@endsection
