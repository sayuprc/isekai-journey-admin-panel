@php use App\Http\ViewModel\ViewJourneyLog; @endphp

@extends('layout.page')

@section('title', 'あゆみ一覧')

@section('content_header')
    <h1>あゆみ一覧</h1>
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
        @php /** @var ViewJourneyLog $journeyLog */ @endphp
        @foreach($journeyLogs as $journeyLog)
            <tr>
                <td>{{ $journeyLog->summary }}</td>
                <td>{{ $journeyLog->story }}</td>
                <td>{{ $journeyLog->period() }}</td>
                <td>{{ $journeyLog->order_no }}</td>
                <td><a href="{{ route('journey-logs.edit.index', $journeyLog->journeyLogId) }}">編集</a></td>
            </tr>
        @endforeach
    </x-adminlte-datatable>
@endsection
