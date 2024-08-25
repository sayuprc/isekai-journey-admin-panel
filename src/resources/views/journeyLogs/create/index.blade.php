@extends('layout.page')

@section('title', 'あゆみ登録')

@section('content_header')
    <h1>あゆみ登録</h1>
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
        <x-adminlte-input label="概要" type="text" name="summary" value="{{ old('summary') }}" />

        <x-adminlte-textarea label="内容" name="story">
            {{ old('story') }}
        </x-adminlte-textarea>

        <div class="row m-0">
            <x-adminlte-input label="開始日" type="date" name="from_on" class="mr-3" value="{{ old('from_on') }}" />
            <x-adminlte-input label="終了日" type="date" name="to_on" value="{{ old('to_on') }}" />
        </div>

        <x-adminlte-button label="登録" type="submit" theme="primary" />
    </form>
@endsection
