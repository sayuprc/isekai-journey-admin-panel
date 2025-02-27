@php use Shared\Route\RouteMap; @endphp

@extends('layout.page')

@section('title', '軌跡一覧')

@section('content_header')
    <h1>軌跡一覧</h1>
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
        @php /** @var \App\Http\ViewModels\JourneyLog\JourneyLogListView $journeyLog */ @endphp
        @foreach($journeyLogs as $journeyLog)
            <tr>
                <td>{{ $journeyLog->period }}</td>
                <td>{{ $journeyLog->story }}</td>
                <td>{{ $journeyLog->orderNo }}</td>
                <td><a href="{{ route(RouteMap::SHOW_EDIT_JOURNEY_LOG_FORM, $journeyLog->journeyLogId) }}">編集</a>
                </td>
            </tr>
        @endforeach
    </x-adminlte-datatable>
@endsection
