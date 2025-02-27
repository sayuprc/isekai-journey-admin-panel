@php use Shared\Route\RouteMap; @endphp

@extends('layout.page')

@section('title', '軌跡リンク種別一覧')

@section('content_header')
    <h1>軌跡リンク種別一覧</h1>
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
        @php /** @var \App\Http\ViewModels\JourneyLogLink\JourneyLogLinkTypeListView $journeyLogLinkType */ @endphp
        @foreach($journeyLogLinkTypes as $journeyLogLinkType)
            <tr>
                <td>{{ $journeyLogLinkType->journeyLogLinkTypeName }}</td>
                <td>{{ $journeyLogLinkType->orderNo }}</td>
                <td>
                    <a href="{{ route(RouteMap::SHOW_EDIT_JOURNEY_LOG_LINK_TYPE_FORM, $journeyLogLinkType->journeyLogLinkTypeId) }}">編集</a>
                </td>
            </tr>
        @endforeach
    </x-adminlte-datatable>
@endsection
