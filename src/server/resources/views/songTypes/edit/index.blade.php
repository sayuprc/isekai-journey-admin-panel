@php
    use SongType\Route\SongTypeRouteMap;

    /** @var \App\Http\ViewModels\Web\SongType\SongTypeView $songType */
@endphp

@extends('layout.page')

@section('title', '楽曲種別更新')

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1>楽曲種別更新</h1>

        <form action="{{ route(SongTypeRouteMap::Delete, $songType->songTypeId) }}" method="post">
            @csrf
            @method('DELETE')
            <input name="song_type_id" value="{{ $songType->songTypeId }}" type="hidden">
            <x-adminlte-button label="削除" type="submit" theme="danger"/>
        </form>
    </div>
@endsection

@section('content')
    @if($errors->any())
        <x-adminlte-alert theme="danger" title="Error">
            @foreach($errors->all() as $error)
                {{ $error }}<br>
            @endforeach
        </x-adminlte-alert>
    @endif
    <form action="{{ route(SongTypeRouteMap::Edit, $songType->songTypeId) }}" method="post">
        @csrf
        <input name="song_type_id" value="{{ $songType->songTypeId }}" type="hidden">

        <x-adminlte-input label="楽曲種別" type="text" name="song_type_name" value="{{ old('song_type_name', $songType->songTypeName) }}"/>

        <div class="row m-0">
            <x-adminlte-input label="表示順" type="number" name="order_no" value="{{ old('order_no', $songType->orderNo) }}"/>
        </div>

        <x-adminlte-button label="更新" type="submit" theme="primary"/>
    </form>
@endsection
