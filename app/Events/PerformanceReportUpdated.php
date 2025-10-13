<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PerformanceReportUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $reportId;
    public $reportData;
    public $userId;
    public $timestamp;

    /**
     * Create a new event instance.
     */
    public function __construct(
        int $reportId,
        array $reportData,
        int $userId
    ) {
        $this->reportId = $reportId;
        $this->reportData = $reportData;
        $this->userId = $userId;
        $this->timestamp = now();
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("performance-reports.{$this->reportId}"),
            new PrivateChannel("user.{$this->userId}")
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'PerformanceReport.Updated';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'report_id' => $this->reportId,
            'report_data' => $this->reportData,
            'user_id' => $this->userId,
            'timestamp' => $this->timestamp->toISOString()
        ];
    }
}
