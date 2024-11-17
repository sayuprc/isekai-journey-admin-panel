<?php

declare(strict_types=1);

namespace App\Shared\Route;

enum RouteMap: string
{
    // システム関連
    case SHOW_LOGIN_FORM = 'login';
    case LOGIN = 'login.handle';

    // 軌跡関連
    case LIST_JOURNEY_LOGS = 'journey-logs.index';
    case SHOW_CREATE_JOURNEY_LOG_FORM = 'journey-logs.create.index';
    case CREATE_JOURNEY_LOG = 'journey-logs.create.handle';
    case SHOW_EDIT_JOURNEY_LOG_FORM = 'journey-logs.edit.index';
    case EDIT_JOURNEY_LOG = 'journey-logs.edit.handle';
    case DELETE_JOURNEY_LOG = 'journey-logs.delete.handle';

    // 軌跡リンク種別関連
    case LIST_JOURNEY_LOG_LINK_TYPE = 'journey-log-link-types.index';
    case SHOW_CREATE_JOURNEY_LOG_LINK_TYPE_FORM = 'journey-log-link-types.create.index';
    case CREATE_JOURNEY_LOG_LINK_TYPE = 'journey-log-link-types.create.handle';
    case SHOW_EDIT_JOURNEY_LOG_LINK_TYPE_FORM = 'journey-log-link-types.edit.index';
    case EDIT_JOURNEY_LOG_LINK_TYPE = 'journey-log-link-types.edit.handle';
    case DELETE_JOURNEY_LOG_LINK_TYPE = 'journey-log-link-types.delete.handle';

    // 楽曲関連
    case LIST_SONGS = 'songs.index';
}
