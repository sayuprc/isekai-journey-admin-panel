@php
    use App\Features\JourneyLogLinkType\Adapter\Web\Presenters\ListViewJourneyLogLinkType;
    use App\Shared\Route\RouteMap;
@endphp

@extends('layout.page')

@section('title', '軌跡登録')

@section('content_header')
    <h1>軌跡登録</h1>
@endsection

@section('content')
    @if($errors->any())
        <x-adminlte-alert theme="danger" title="Error">
            @foreach($errors->all() as $error)
                {{ $error }}<br>
            @endforeach
        </x-adminlte-alert>
    @endif
    <form action="{{ route(RouteMap::CREATE_JOURNEY_LOG) }}" method="post">
        @csrf
        <x-adminlte-textarea label="内容" name="story">
            {{ old('story') }}
        </x-adminlte-textarea>

        <div class="row m-0">
            <x-adminlte-input label="開始日" type="date" name="from_on" class="mr-3" value="{{ old('from_on') }}"
                              id="from_on"/>
            <x-adminlte-input label="終了日" type="date" name="to_on" value="{{ old('to_on') }}" id="to_on"/>
        </div>

        <x-adminlte-button label="今日" type="button" id="copy_today_btn"/>
        <x-adminlte-button label="開始日を終了日にコピー" id="copy_from_to_btn"/>

        <div class="row m-0">
            <x-adminlte-input label="表示順" type="number" name="order_no" value="{{ old('order_no', 0) }}"/>
        </div>

        <div class="form-group">
            <x-adminlte-button label="リンク追加" type="button" id="add_link_btn"/>
            <div id="links">
            </div>
        </div>

        <x-adminlte-button label="登録" type="submit" theme="primary"/>
    </form>

    @php
        /** @var ListViewJourneyLogLinkType[] $journeyLogLinkTypes */
        $data = array_map(function(ListViewJourneyLogLinkType $journeyLogLinkType):array{
            return [
                'journey_log_link_type_id' => $journeyLogLinkType->journeyLogLinkTypeId,
                'journey_log_link_type_name' => $journeyLogLinkType->journeyLogLinkTypeName,
            ];
        }, $journeyLogLinkTypes);
    @endphp
    <script>
        window.journeyLogLinkTypes = @json($data);

        window.oldJourneyLogLinks = @json(old('journey_log_links'));
    </script>

    @vite(['resources/ts/journey-log.ts'])
@endsection
