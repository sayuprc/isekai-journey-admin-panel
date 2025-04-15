@php use Support\Route\RouteMap; @endphp

@extends('layout.page')

@section('title', 'クリエイター登録')

@section('content_header')
    <h1>クリエイター登録</h1>
@endsection

@section('content')
    @if($errors->any())
        <x-adminlte-alert theme="danger" title="Error">
            @foreach($errors->all() as $error)
                {{ $error }}<br>
            @endforeach
        </x-adminlte-alert>
    @endif
    <form action="{{ route(RouteMap::CreateCreator) }}" method="post">
        @csrf
        <x-adminlte-input label="クリエイター名" type="text" name="creator_name" value="{{ old('creator_name') }}"/>

        <x-adminlte-button label="登録" type="submit" theme="primary"/>
    </form>
@endsection
