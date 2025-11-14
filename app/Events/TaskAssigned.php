<?php

namespace App\Events;

use App\Models\Task;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TaskAssigned implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $task;
    public $user;
    public $assigner;

    /**
     * Create a new event instance.
     */
    public function __construct(Task $task, User $user, User $assigner)
    {
        $this->task = $task;
        $this->user = $user;
        $this->assigner = $assigner;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->user->user_id),
        ];
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith(): array
    {
        return [
            'task_id' => $this->task->task_id,
            'task_title' => $this->task->title,
            'task_priority' => $this->task->priority,
            'project_name' => $this->task->project->name ?? 'Unknown',
            'assigner_name' => $this->assigner->name,
            'message' => "{$this->assigner->name} assigned you a new task: {$this->task->title}",
        ];
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs(): string
    {
        return 'task.assigned';
    }
}
