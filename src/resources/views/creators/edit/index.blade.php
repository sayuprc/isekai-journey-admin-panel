@php
    use Support\Route\RouteMap;

    /** @var \App\Http\ViewModels\Web\Creator\CreatorView $creator */
@endphp

@extends('layout.page')

@section('title', 'クリエイター更新')

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1>クリエイター更新</h1>

        <form action="{{ route(RouteMap::DeleteCreator, $creator->creatorId) }}" method="post">
            @csrf
            @method('DELETE')
            <input name="creator_id" value="{{ $creator->creatorId }}" type="hidden">
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
    <form action="{{ route(RouteMap::EditCreator) }}" method="post">
        @csrf
        <input name="creator_id" value="{{ $creator->creatorId }}" type="hidden">

        <x-adminlte-input label="クリエイター名" type="text" name="creator_name" value="{{ old('creator_name', $creator->creatorName) }}"/>

        <x-adminlte-button label="更新" type="submit" theme="primary"/>
    </form>
@endsection
