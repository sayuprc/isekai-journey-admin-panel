@php use SongType\Route\SongTypeRouteMap; @endphp
@extends('layout.page')

@section('title', '楽曲種別一覧')

@section('content_header')
    <h1>楽曲種別一覧</h1>
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
    <x-adminlte-datatable id="table" :heads="$heads">
        @php /** @var \App\Http\ViewModels\Web\SongType\SongTypeListView $songType */ @endphp
        @foreach($songTypes as $songType)
            <tr>
                <td>{{ $songType->songTypeName }}</td>
                <td>{{ $songType->orderNo }}</td>
                <td>
                    <a href="{{ route(SongTypeRouteMap::ShowEditForm, $songType->songTypeId) }}">編集</a>
                </td>
            </tr>
        @endforeach
    </x-adminlte-datatable>
@endsection
