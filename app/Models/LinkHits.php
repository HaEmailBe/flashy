<?php

namespace App\Models;

use App\Models\Links;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LinkHits extends Model
{
    /** @use HasFactory<\Database\Factories\LinkHitsFactory> */
    use HasFactory;

    const UPDATED_AT = null;

    protected $fillable = [
        "link_id",
        "ip",
        "user_agent",
    ];

    public function links()
    {
        return $this->belongsTo(Links::class);
    }
}
