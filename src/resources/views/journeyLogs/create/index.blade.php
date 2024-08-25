@extends('layout.page')

@section('title', 'あゆみ登録')

@section('content_header')
    <h1>あゆみ登録</h1>
@endsection

@section('content')
    <form action="" method="post">
        @csrf
        <x-adminlte-input label="概要" type="text" name="summary" />

        <x-adminlte-textarea label="内容" name="story" />

        <div class="row m-0">
            <x-adminlte-input label="開始日" type="date" name="from_on" class="mr-3" />
            <x-adminlte-input label="終了日" type="date" name="to_on" />
        </div>

        <x-adminlte-button label="登録" type="submit" theme="primary" />
    </form>
@endsection
