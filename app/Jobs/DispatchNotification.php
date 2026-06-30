<?php

namespace App\Jobs;

use App\Models\Doctor;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class DispatchNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    public int $tries = 3;
    public int $backoff = 3;

    protected string $targetType;
    protected mixed $targetId;
    protected string $type;
    protected string $title;
    protected string $message;
    protected array $data;
    protected ?string $link;

    public function __construct(
        string $targetType,
        mixed $target,
        string $type,
        string $title,
        string $message,
        array $data = [],
        ?string $link = null
    ) {
        $this->targetType = $targetType;
        $this->type = $type;
        $this->title = $title;
        $this->message = $message;
        $this->data = $data;
        $this->link = $link;

        if ($target instanceof Doctor) {
            $this->targetId = $target->id;
        } elseif ($target instanceof User) {
            $this->targetId = $target->id;
        } else {
            $this->targetId = $target;
        }
    }

    public function handle(NotificationService $service): void
    {
        match ($this->targetType) {
            'admins' => $service->notifyAdmins($this->type, $this->title, $this->message, $this->data, $this->link),
            'nurses' => $service->notifyNurses($this->type, $this->title, $this->message, $this->data, $this->link),
            'receptionists' => $service->notifyReceptionists($this->type, $this->title, $this->message, $this->data, $this->link),
            'doctor' => $this->notifyDoctor($service),
            default => $service->notifyUser(
                User::findOrFail($this->targetId),
                $this->type, $this->title, $this->message, $this->data, $this->link
            ),
        };
    }

    protected function notifyDoctor(NotificationService $service): void
    {
        $doctor = Doctor::find($this->targetId);
        if ($doctor) {
            $service->notifyDoctor($doctor, $this->type, $this->title, $this->message, $this->data, $this->link);
        }
    }

    public function failed(\Throwable $e): void
    {
        Log::error('Failed to dispatch notification', [
            'target_type' => $this->targetType,
            'target_id' => $this->targetId,
            'title' => $this->title,
            'error' => $e->getMessage(),
        ]);
    }
}
