@php use Support\Route\RouteMap; @endphp

@extends('layout.page')

@section('title', '楽曲種別登録')

@section('content_header')
    <h1>楽曲種別登録</h1>
@endsection

@section('content')
    @if($errors->any())
        <x-adminlte-alert theme="danger" title="Error">
            @foreach($errors->all() as $error)
                {{ $error }}<br>
            @endforeach
        </x-adminlte-alert>
    @endif
    <form action="{{ route(RouteMap::CreateSongType) }}" method="post">
        @csrf
        <x-adminlte-textarea label="楽曲種別名" name="song_type_name">
            {{ old('song_type_name') }}
        </x-adminlte-textarea>

        <div class="row m-0">
            <x-adminlte-input label="表示順" type="number" name="order_no" value="{{ old('order_no', 1) }}"/>
        </div>

        <x-adminlte-button label="登録" type="submit" theme="primary"/>
    </form>
@endsection
