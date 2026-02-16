<?php

declare(strict_types=1);

namespace AdminUser\Domain\Models;

enum Permission: string
{
    case ReadAdminUser = 'read_admin_user';

    case WriteAdminUser = 'write_admin_user';

    case ReadCreator = 'read_creator';

    case WriteCreator = 'write_creator';

    case ReadPerformer = 'read_performer';

    case WritePerformer = 'write_performer';

    case ReadSong = 'read_song';

    case WriteSong = 'write_song';

    case ReadSongType = 'read_song_type';

    public function getName(): string
    {
        return match ($this) {
            self::ReadAdminUser => '管理ユーザー閲覧',
            self::WriteAdminUser => '管理ユーザー編集',
            self::ReadCreator => 'クリエイター閲覧',
            self::WriteCreator => 'クリエイター編集',
            self::ReadPerformer => '共演者閲覧',
            self::WritePerformer => '共演者編集',
            self::ReadSong => '楽曲閲覧',
            self::WriteSong => '楽曲編集',
            self::ReadSongType => '楽曲種別閲覧',
        };
    }
}
