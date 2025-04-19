<?php
namespace Moinul\LinkScanner\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Database\Factories\BrokenLinkFactory;

class BrokenLink extends Model
{
    use HasFactory;

    protected $table = 'broken_links';
    protected $fillable = [
        'url', 'status_code', 'reason', 'checked_at',
    ];
    protected $casts = [
        'checked_at' => 'datetime',
    ];

    /**
     * Tell Laravel exactly which factory to use.
     */
    protected static function newFactory()
    {
        return BrokenLinkFactory::new();
    }
}
