@extends('layout.page')

@section('title', '軌跡リンク種別登録')

@section('content_header')
    <h1>軌跡リンク種別登録</h1>
@endsection

@section('content')
    @if($errors->any())
        <x-adminlte-alert theme="danger" title="Error">
            @foreach($errors->all() as $error)
                {{ $error }}<br>
            @endforeach
        </x-adminlte-alert>
    @endif
    <form action="{{ route('journey-log-link-types.create.handle') }}" method="post">
        @csrf
        <x-adminlte-textarea label="名前" name="journey_log_link_type_name">
            {{ old('journey_log_link_type_name') }}
        </x-adminlte-textarea>

        <div class="row m-0">
            <x-adminlte-input label="表示順" type="number" name="order_no" value="{{ old('order_no', 0) }}" />
        </div>

        <x-adminlte-button label="登録" type="submit" theme="primary" />
    </form>
@endsection
