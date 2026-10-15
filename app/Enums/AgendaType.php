<?php

namespace App\Enums;

enum AgendaType: string
{
    case RAPAT_INTERNAL = 'RAPAT_INTERNAL';
    case PERTEMUAN_EKSTERNAL = 'PERTEMUAN_EKSTERNAL';
    case UNDANGAN_KEDINASAN = 'UNDANGAN_KEDINASAN';
    case AGENDA_PRIBADI = 'AGENDA_PRIBADI';
    case HOBI_REKREASI = 'HOBI_REKREASI';
    case KUNJUNGAN_PERJALANAN_DINAS = 'KUNJUNGAN_PERJALANAN_DINAS';
    case PENDIDIKAN_PENGEMBANGAN_DIRI = 'PENDIDIKAN_PENGEMBANGAN_DIRI';

    public function label(): string
    {
        return match($this) {
            self::RAPAT_INTERNAL => 'Rapat Internal',
            self::PERTEMUAN_EKSTERNAL => 'Pertemuan Eksternal',
            self::UNDANGAN_KEDINASAN => 'Undangan Kedinasan',
            self::AGENDA_PRIBADI => 'Agenda Pribadi',
            self::HOBI_REKREASI => 'Hobi & Rekreasi',
            self::KUNJUNGAN_PERJALANAN_DINAS => 'Kunjungan & Perjalanan Dinas',
            self::PENDIDIKAN_PENGEMBANGAN_DIRI => 'Pendidikan & Pengembangan Diri',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::RAPAT_INTERNAL => '#DBEAFE', // Biru
            self::PERTEMUAN_EKSTERNAL => '#D1FAE5', // Hijau
            self::UNDANGAN_KEDINASAN => '#FFEDD5', // Oranye
            self::AGENDA_PRIBADI => '#F3E8FF', // Ungu
            self::HOBI_REKREASI => '#FCE7F3', // Pink
            self::KUNJUNGAN_PERJALANAN_DINAS => '#FEF3C7', // Kuning
            self::PENDIDIKAN_PENGEMBANGAN_DIRI => '#CCFBF1', // Toska
        };
    }

    public function textColor(): string
    {
        return match($this) {
            self::RAPAT_INTERNAL => '#1E40AF',
            self::PERTEMUAN_EKSTERNAL => '#065F46',
            self::UNDANGAN_KEDINASAN => '#9A3412',
            self::AGENDA_PRIBADI => '#6B21A8',
            self::HOBI_REKREASI => '#9D174D',
            self::KUNJUNGAN_PERJALANAN_DINAS => '#92400E',
            self::PENDIDIKAN_PENGEMBANGAN_DIRI => '#115E59',
        };
    }

    public function cssClass(): string
    {
        return 'type-' . strtolower($this->value);
    }

    public function colorDark(): string
    {
        return match($this) {
            self::RAPAT_INTERNAL => 'rgba(59, 130, 246, 0.18)',
            self::PERTEMUAN_EKSTERNAL => 'rgba(34, 197, 94, 0.18)',
            self::UNDANGAN_KEDINASAN => 'rgba(249, 115, 22, 0.18)',
            self::AGENDA_PRIBADI => 'rgba(168, 85, 247, 0.18)',
            self::HOBI_REKREASI => 'rgba(236, 72, 153, 0.18)',
            self::KUNJUNGAN_PERJALANAN_DINAS => 'rgba(234, 179, 8, 0.18)',
            self::PENDIDIKAN_PENGEMBANGAN_DIRI => 'rgba(20, 184, 166, 0.18)',
        };
    }

    public function textColorDark(): string
    {
        return match($this) {
            self::RAPAT_INTERNAL => '#93C5FD',
            self::PERTEMUAN_EKSTERNAL => '#86EFAC',
            self::UNDANGAN_KEDINASAN => '#FDBA74',
            self::AGENDA_PRIBADI => '#D8B4FE',
            self::HOBI_REKREASI => '#F9A8D4',
            self::KUNJUNGAN_PERJALANAN_DINAS => '#FDE047',
            self::PENDIDIKAN_PENGEMBANGAN_DIRI => '#5EEAD4',
        };
    }
}
