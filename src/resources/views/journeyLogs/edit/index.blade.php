@php
    use JourneyLog\Adapter\Web\Presenters\ViewJourneyLog;use JourneyLogLinkType\Adapter\Web\Presenters\ListViewJourneyLogLinkType;use Shared\Route\RouteMap;
    /** @var ViewJourneyLog $journeyLog */
@endphp

@extends('layout.page')

@section('title', '軌跡更新')

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1>軌跡更新</h1>

        <form action="{{ route(RouteMap::DELETE_JOURNEY_LOG) }}" method="post">
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
    <form action="{{ route(RouteMap::EDIT_JOURNEY_LOG) }}" method="post">
        @csrf
        <input name="journey_log_id" value="{{ $journeyLog->journeyLogId }}" type="hidden">

        <x-adminlte-textarea label="内容" name="story">
            {{ old('story', $journeyLog->story) }}
        </x-adminlte-textarea>

        <div class="row m-0">
            <x-adminlte-input label="開始日" type="date" name="from_on" class="mr-3"
                              value="{{ old('from_on', $journeyLog->fromOn->format()) }}" id="from_on"/>
            <x-adminlte-input label="終了日" type="date" name="to_on"
                              value="{{ old('to_on', $journeyLog->toOn->format()) }}" id="to_on"/>
        </div>

        <x-adminlte-button label="今日" type="button" id="copy_today_btn"/>
        <x-adminlte-button label="開始日を終了日にコピー" id="copy_from_to_btn"/>

        <div class="row m-0">
            <x-adminlte-input label="表示順" type="number" name="order_no"
                              value="{{ old('order_no', $journeyLog->orderNo) }}"/>
        </div>

        <div class="form-group">
            <x-adminlte-button label="リンク追加" type="button" id="add_link_btn"/>
            <div id="links">
            </div>
        </div>

        <x-adminlte-button label="更新" type="submit" theme="primary"/>
    </form>

    @php
        /** @var ListViewJourneyLogLinkType[] $journeyLogLinkTypes */
        $data = array_map(function(ListViewJourneyLogLinkType $journeyLogLinkType): array {
            return [
                'journey_log_link_type_id' => $journeyLogLinkType->journeyLogLinkTypeId(),
                'journey_log_link_type_name' => $journeyLogLinkType->journeyLogLinkTypeName(),
            ];
        }, $journeyLogLinkTypes);
    @endphp

    <script>
        window.journeyLogLinkTypes = @json($data);
        window.oldJourneyLogLinks = @json(old('journey_log_links', $journeyLog->journeyLogLinks));
    </script>

    @vite(['resources/ts/journey-log.ts'])
@endsection
