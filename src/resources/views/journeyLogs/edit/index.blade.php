@php
    use App\Features\JourneyLog\Adapter\Web\Presenters\ViewJourneyLog;
    /** @var ViewJourneyLog $journeyLog */
@endphp

@extends('layout.page')

@section('title', 'あゆみ更新')

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1>あゆみ更新</h1>

        <form action="{{ route('journey-logs.delete.handle') }}" method="post">
            @csrf
            @method('DELETE')
            <input name="journey_log_id" value="{{ $journeyLog->journeyLogId }}" type="hidden">
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
    <form action="{{ route('journey-logs.edit.handle') }}" method="post">
        @csrf
        <input name="journey_log_id" value="{{ $journeyLog->journeyLogId }}" type="hidden">

        <x-adminlte-input label="概要" type="text" name="summary" value="{{ old('summary', $journeyLog->summary) }}"/>

        <x-adminlte-textarea label="内容" name="story">
            {{ old('story', $journeyLog->story) }}
        </x-adminlte-textarea>

        <div class="row m-0">
            <x-adminlte-input label="開始日" type="date" name="from_on" class="mr-3" value="{{ old('from_on', $journeyLog->fromOn->format()) }}" id="from_on"/>
            <x-adminlte-input label="終了日" type="date" name="to_on" value="{{ old('to_on', $journeyLog->toOn->format()) }}" id="to_on"/>
        </div>

        <x-adminlte-button label="今日" type="button" id="copy_today_btn"/>
        <x-adminlte-button label="開始日を終了日にコピー" id="copy_from_to_btn"/>

        <div class="row m-0">
            <x-adminlte-input label="表示順" type="number" name="order_no" value="{{ old('order_no', $journeyLog->orderNo) }}"/>
        </div>

        <x-adminlte-button label="更新" type="submit" theme="primary"/>
    </form>

    @vite(['resources/ts/journey-log.ts'])
@endsection
