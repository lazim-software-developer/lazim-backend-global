<?php

namespace App\Notifications;

use Filament\Notifications\Notification as BaseNotification;

class CustomNotification extends BaseNotification
{
    protected string $type = '';
    protected string $priority = '';


    public function toArray(): array
    {
        return [
            ...parent::toArray(),
            'type' => $this->getType(),
            'priority' => $this->getPriority(),
        ];
    }

    public static function fromArray(array $data): static
    {
        return parent::fromArray($data)
            ->type($data['type'] ?? '')
            ->priority($data['priority'] ?? '');
    }

    public function type(string $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getType(): string
    {
        return $this->type;
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
}
