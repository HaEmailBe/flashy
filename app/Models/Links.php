<?php

namespace App\Models;

use App\Models\LinkHits;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Collection;

class Links extends Model
{
    /** @use HasFactory<\Database\Factories\LinksFactory> */
    use HasFactory;

    protected $fillable = [
        "slug",
        "target_url",
        "is_active",
    ];

    public function hits()
    {
        return $this->hasMany(LinkHits::class, 'link_id');
    }

    public function lastHits()
    {
        return $this->hasMany(LinkHits::class, 'link_id')->latest()->limit(5);
    }

    public function getFormattedLastHits(): Collection
    {
        $last_hits = $this->lastHits()
            ->get()
            ->map(function ($hit) {
                return [
                    'ip' => $this->truncateIp($hit->ip),
                    'created_at' => $hit->created_at->format('Y-m-d H:i:s'),
                ];
            });

        return $last_hits;
    }

    private function truncateIp(string $ip): string
    {
        // For IPv4: 192.168.1.100 -> 192.168.1.xxx
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $parts = explode('.', $ip);
            $parts[3] = 'xxx';
            return implode('.', $parts);
        }

        // For IPv6: 2001:0db8:85a3:0000:0000:8a2e:0370:7334 -> 2001:0db8:85a3:xxxx:xxxx:xxxx:xxxx:xxxx
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $parts = explode(':', $ip);
            for ($i = 3; $i < count($parts); $i++) {
                $parts[$i] = 'xxxx';
            }
            return implode(':', $parts);
        }

        return 'xxx.xxx.xxx.xxx';
    }
}
