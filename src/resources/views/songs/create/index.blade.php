@php use App\Http\ViewModels\Web\Creator\CreatorListView;use App\Http\ViewModels\Web\Creator\CreatorView;use App\Http\ViewModels\Web\SongType\SongTypeListView;use App\Http\ViewModels\Web\SongType\SongTypeView;use Creator\Domain\Models\Creator;use Song\Domain\Models\Archives\ArchiveType; @endphp

@extends('layout.page')

@section('title', '楽曲登録')

@section('content_header')
    <h1>楽曲登録</h1>
@endsection

@section('content')
    @if($errors->any())
        <x-adminlte-alert theme="danger" title="Error">
            @foreach($errors->all() as $error)
                {{ $error }}<br>
            @endforeach
        </x-adminlte-alert>
    @endif
    <form action="" method="post">
        @csrf
        <x-adminlte-input label="タイトル" type="text" name="title" value="{{ old('title') }}"/>

        <x-adminlte-textarea label="説明" name="description">
            {{ old('description') }}
        </x-adminlte-textarea>

        <select name="song_type_id">
            @php /** @var array<SongTypeListView> $songTypes */ @endphp
            @foreach($songTypes as $songType)
                <option value="{{ $songType->songTypeId }}">{{ $songType->songTypeName }}</option>
            @endforeach
        </select>

        <div class="row m-0">
            <x-adminlte-input label="表示順" type="number" name="order_no" value="{{ old('order_no', 1) }}"/>
        </div>

        <div class="form-group">
            <label>作詞者</label>
            <x-adminlte-button label="追加" type="button" id="add_lyricist_btn"/>
            <div id="lyricists"></div>
        </div>

        <div class="form-group">
            <label>作曲者</label>
            <x-adminlte-button label="追加" type="button" id="add_composer_btn"/>
            <div id="composers"></div>
        </div>

        <div class="form-group">
            <label>編曲者</label>
            <x-adminlte-button label="追加" type="button" id="add_arranger_btn"/>
            <div id="arrangers"></div>
        </div>

        <div class="form-group">
            <label>アーカイブ</label>
            <x-adminlte-button label="追加" type="button" id="add_archive_btn"/>
            <div id="archives"></div>
        </div>

        <x-adminlte-button label="登録" type="submit" theme="primary"/>
    </form>

    @php
        /** @var array<CreatorListView> $creators */
        $data = array_map(function(CreatorListView $creator): array {
            return [
                'creator_id' => $creator->creatorId,
                'creator_name' => $creator->creatorName,
            ];
        }, $creators);

        $archiveTypes = array_map(function (ArchiveType $type): array {
            return [
                'archive_type' => (string)$type->value,
                'archive_name' => $type->name,
            ];
        }, ArchiveType::cases());
    @endphp
    <script>
        window.creators = @json($data);
        window.archiveTypes = @json($archiveTypes);

        window.oldLyricists = @json(old('lyricists'));
        window.oldComposers = @json(old('composers'));
        window.oldArrangers = @json(old('arrangers'));
        window.oldArchives = @json(old('archives'));
    </script>

    @vite(['resources/ts/song.ts'])
@endsection
