@php
    use Song\Adapter\Web\Presenters\ListViewSong;
@endphp

@extends('layout.page')

@section('title', '楽曲一覧')

@section('content_header')
    <h1>楽曲一覧</h1>
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
        @php /** @var ListViewSong $song */ @endphp
        @foreach($songs as $song)
            <tr>
                <td>{{ $song->title }}</td>
                <td>{{ $song->description }}</td>
                <td>{{ $song->releasedOn }}</td>
                <td>{{ $song->orderNo }}</td>
                <td><a href="">編集</a></td>
            </tr>
        @endforeach
    </x-adminlte-datatable>
@endsection
