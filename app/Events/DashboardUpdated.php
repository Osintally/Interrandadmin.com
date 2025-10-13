<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DashboardUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $userId;
    public $dashboardData;
    public $updateType;
    public $timestamp;

    /**
     * Create a new event instance.
     */
    public function __construct(
        int $userId,
        array $dashboardData,
        string $updateType = 'general'
    ) {
        $this->userId = $userId;
        $this->dashboardData = $dashboardData;
        $this->updateType = $updateType;
        $this->timestamp = now();
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("dashboard-updates.{$this->userId}")
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'Dashboard.Updated';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'user_id' => $this->userId,
            'dashboard_data' => $this->dashboardData,
            'update_type' => $this->updateType,
            'timestamp' => $this->timestamp->toISOString()
        ];
    }
}
