<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'message',
        'is_active',
        'type',
        'status',
    ];

    /**
     * Get the active announcement.
     *
     * @return Announcement|null
     */
    public static function getActiveAnnouncement()
    {
        return self::where('type', 'announcement')
            ->where('status', 'active')
            ->where('is_active', true)
            ->latest()
            ->first();
    }
}
