@php
    use App\Shared\Route\RouteMap;use JourneyLogLinkType\Adapter\Web\Presenters\ViewJourneyLogLinkType;
    /** @var ViewJourneyLogLinkType $journeyLogLinkType */
@endphp

@extends('layout.page')

@section('title', '軌跡リンク種別更新')

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1>軌跡リンク種別更新</h1>

        <form action="{{ route(RouteMap::DELETE_JOURNEY_LOG_LINK_TYPE) }}" method="post">
            @csrf
            @method('DELETE')
            <input name="journey_log_link_type_id" value="{{ $journeyLogLinkType->journeyLogLinkTypeId }}"
                   type="hidden">
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
    <form action="{{ route(RouteMap::EDIT_JOURNEY_LOG_LINK_TYPE) }}" method="post">
        @csrf
        <input name="journey_log_link_type_id" value="{{ $journeyLogLinkType->journeyLogLinkTypeId }}" type="hidden">

        <x-adminlte-textarea label="名前" name="journey_log_link_type_name">
            {{ old('journey_log_link_type_name', $journeyLogLinkType->journeyLogLinkTypeName) }}
        </x-adminlte-textarea>

        <div class="row m-0">
            <x-adminlte-input label="表示順" type="number" name="order_no"
                              value="{{ old('order_no', $journeyLogLinkType->orderNo) }}"/>
        </div>

        <x-adminlte-button label="更新" type="submit" theme="primary"/>
    </form>
@endsection
