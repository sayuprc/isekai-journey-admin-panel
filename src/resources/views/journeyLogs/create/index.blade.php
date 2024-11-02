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
    <form action="{{ route('journey-logs.create.handle') }}" method="post">
        @csrf
        <x-adminlte-textarea label="内容" name="story">
            {{ old('story') }}
        </x-adminlte-textarea>

        <div class="row m-0">
            <x-adminlte-input label="開始日" type="date" name="from_on" class="mr-3" value="{{ old('from_on') }}" id="from_on" />
            <x-adminlte-input label="終了日" type="date" name="to_on" value="{{ old('to_on') }}" id="to_on" />
        </div>

        <x-adminlte-button label="今日" type="button" id="copy_today_btn" />
        <x-adminlte-button label="開始日を終了日にコピー" id="copy_from_to_btn" />

        <div class="row m-0">
            <x-adminlte-input label="表示順" type="number" name="order_no" value="{{ old('order_no', 0) }}" />
        </div>

        <div class="form-group">
            <x-adminlte-button label="リンク追加" type="button" id="add_link_btn" />
            <div id="links">
            </div>
        </div>

        <x-adminlte-button label="登録" type="submit" theme="primary" />
    </form>

    <script>
        // TODO 正しいマスタ値にする
        window.linkTypes = [
            {"id": "", "name": "リンク種別"},
            {"id": "100", "name": "hoge"},
            {"id": "101", "name": "fuga"},
            {"id": "102", "name": "piyo"},
        ]

        window.oldLinks = @json(old('links'))
    </script>

    @vite(['resources/ts/journey-log.ts'])
@endsection
