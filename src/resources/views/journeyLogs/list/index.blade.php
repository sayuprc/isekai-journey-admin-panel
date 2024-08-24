@php use App\Http\ViewModel\ViewJourneyLog; @endphp

@extends('layout.page')

@section('title', 'あゆみ一覧')

@section('content_header')
    <h1>あゆみ一覧</h1>
@endsection

@section('content')
    <x-adminlte-datatable id="table" :heads="$heads">
        @php /** @var ViewJourneyLog $journeyLog */ @endphp
        @foreach($journeyLogs as $journeyLog)
            <tr>
                <td>{{ $journeyLog->summary }}</td>
                <td>{{ $journeyLog->story }}</td>
                <td>{{ $journeyLog->period() }}</td>
                <td><a href="">編集</a></td>
            </tr>
        @endforeach
    </x-adminlte-datatable>
@endsection
