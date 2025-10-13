<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class KPICalculated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $templateId;
    public $fieldId;
    public $value;
    public $kpiConfig;
    public $calculatedScore;
    public $timestamp;

    /**
     * Create a new event instance.
     */
    public function __construct(
        string $templateId,
        string $fieldId,
        $value,
        array $kpiConfig,
        float $calculatedScore
    ) {
        $this->templateId = $templateId;
        $this->fieldId = $fieldId;
        $this->value = $value;
        $this->kpiConfig = $kpiConfig;
        $this->calculatedScore = $calculatedScore;
        $this->timestamp = now();
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new Channel("kpi-calculations.{$this->templateId}")
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'KPI.Calculated';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'template_id' => $this->templateId,
            'field_id' => $this->fieldId,
            'value' => $this->value,
            'kpi_config' => $this->kpiConfig,
            'calculated_score' => $this->calculatedScore,
            'timestamp' => $this->timestamp->toISOString()
        ];
    }
}
