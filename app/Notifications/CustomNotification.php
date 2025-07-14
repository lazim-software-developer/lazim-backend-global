<?php

namespace App\Notifications;

use Illuminate\Support\Str;
use App\Models\NotificationType;
use Filament\Notifications\Notification as BaseNotification;

class CustomNotification extends BaseNotification
{
    protected string $type = '';
    protected string $priority = '';
    protected int $building = 0;


    public function toArray(): array
    {
        return [
            ...parent::toArray(),
            'type' => $this->type, //  raw key (e.g. 'access_card')
            'type_lable' => $this->getType(), // optional UI label
            'priority' => $this->getPriority(),
            'building' => $this->getBuilding(),
        ];
    }

    public static function fromArray(array $data): static
    {
        return parent::fromArray($data)
            ->type($data['type'] ?? '')
            ->priority($data['priority'] ?? '')
            ->building($data['building'] ?? 0);
    }

    public function type(string $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getType(): ?string
    {
        return NotificationType::where('key', $this->type)->value('name')
            ?? Str::of($this->type)->replace('_', ' ')->title();
    }

    public function priority(string $priority): static
    {
        $this->priority = $priority;
        return $this;
    }

    public function getPriority(): string
    {
        return $this->priority;
    }
    public function building(int $building): static
    {
        $this->building = $building;
        return $this;
    }

    public function getBuilding(): int
    {
        return $this->building;
    }
}
