<?php

declare(strict_types=1);

namespace AdminUser\Domain\Models;

enum Permission: string
{
    case ReadAdminUser = 'read_admin_user';

    case WriteAdminUser = 'write_admin_user';

    case ReadPerson = 'read_person';

    case WritePerson = 'write_person';

    case ReadSong = 'read_song';

    case WriteSong = 'write_song';

    case ReadMedia = 'read_media';

    case WriteMedia = 'write_media';

    public function getName(): string
    {
        return match ($this) {
            self::ReadAdminUser => '管理ユーザー閲覧',
            self::WriteAdminUser => '管理ユーザー編集',
            self::ReadPerson => '人物閲覧',
            self::WritePerson => '人物編集',
            self::ReadSong => '楽曲閲覧',
            self::WriteSong => '楽曲編集',
            self::ReadMedia => 'Media閲覧',
            self::WriteMedia => 'Media編集',
        };
    }
}
