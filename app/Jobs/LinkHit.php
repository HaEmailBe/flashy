<?php

namespace App\Jobs;

use Exception;
use App\Events\LinkHitEvent;
use App\Models\LinkHits;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Symfony\Component\Console\Attribute\Argument;

class LinkHit implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $linkId;
    private $ip;
    private $userAgent;
    private $slug;

    /**
     * Create a new job instance.
     */
    public function __construct($data)
    {
        $this->slug = $data["slug"];
        $this->linkId = $data['link_id'];
        $this->ip = $data['ip'];
        $this->userAgent = $data['user_agent'];
    }

    public function getLinkId()
    {
        return $this->linkId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $linkHists = LinkHits::create([
                'link_id' => $this->linkId,
                'ip' => $this->ip,
                'user_agent' => $this->userAgent,
            ]);

            event(new LinkHitEvent($this->slug));
        } catch (Exception $e) {
            // For testing
            dump($e->getMessage());
            Log::error('Failed to record link hit', [
                'link_id' => $this->linkId,
                'ip' => $this->ip,
                'user_agent' => $this->userAgent,
                'error' => $e->getMessage()
            ]);
        }
    }
}
