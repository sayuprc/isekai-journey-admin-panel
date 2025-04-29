<?php

declare(strict_types=1);

namespace Support\Route;

enum RouteMap: string
{
    // システム関連
    case ShowLoginForm = 'login';
    case Login = 'login.handle';

    // 軌跡関連
    case ListJourneyLogs = 'journey-logs.index';
    case ShowCreateJourneyLogForm = 'journey-logs.create.index';
    case CreateJourneyLog = 'journey-logs.create.handle';
    case ShowEditJourneyLogForm = 'journey-logs.edit.index';
    case EditJourneyLog = 'journey-logs.edit.handle';
    case DeleteJourneyLog = 'journey-logs.delete.handle';

    // 軌跡リンク種別関連
    case ListJourneyLogLinkType = 'journey-log-link-types.index';
    case ShowCreateJourneyLogLinkTypeForm = 'journey-log-link-types.create.index';
    case CreateJourneyLogLinkType = 'journey-log-link-types.create.handle';
    case ShowEditJourneyLogLinkTypeForm = 'journey-log-link-types.edit.index';
    case EditJourneyLogLinkType = 'journey-log-link-types.edit.handle';
    case DeleteJourneyLogLinkType = 'journey-log-link-types.delete.handle';

    // 楽曲関連
    case ListSongs = 'songs.index';

    // 楽曲種別関連
    case ListSongTypes = 'song-types.index';
    case ShowCreateSongTypeForm = 'song-types.create.index';
    case CreateSongType = 'song-types.create.handle';
    case ShowEditSongTypeForm = 'song-types.edit.index';
    case EditSongType = 'song-types.edit.handle';
    case DeleteSongType = 'song-types.delete.handle';

    // クリエイター関連
    case ListCreators = 'creators.index';
    case ShowCreateCreatorForm = 'creators.create.index';
    case CreateCreator = 'creators.create.handle';
    case ShowEditCreatorForm = 'creators.edit.index';
    case EditCreator = 'creators.edit.handle';
    case DeleteCreator = 'creators.delete.handle';
}
