<?php

namespace App\Mail;

use App\Models\Card;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TaskComplianceReminder extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $user;
    public $task;
    public $issues;
    public $reminderType;

    /**
     * Create a new message instance.
     * 
     * @param User $user
     * @param Card $task
     * @param array $issues
     * @param string $reminderType ('time_log', 'daily_comment', 'overdue', 'approval_pending')
     */
    public function __construct(User $user, Card $task, array $issues = [], string $reminderType = 'compliance')
    {
        $this->user = $user;
        $this->task = $task;
        $this->issues = $issues;
        $this->reminderType = $reminderType;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subjects = [
            'time_log' => '⏰ Time Log Reminder - ' . $this->task->card_title,
            'daily_comment' => '📝 Daily Update Required - ' . $this->task->card_title,
            'overdue' => '🚨 Overdue Task Alert - ' . $this->task->card_title,
            'approval_pending' => '✅ Task Ready for Your Approval',
            'compliance' => '⚠️ Task Compliance Reminder',
        ];

        return new Envelope(
            subject: $subjects[$this->reminderType] ?? $subjects['compliance'],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.task-compliance-reminder',
            with: [
                'user' => $this->user,
                'task' => $this->task,
                'issues' => $this->issues,
                'reminderType' => $this->reminderType,
                'dashboardUrl' => route('dashboard'),
                'taskUrl' => route('tasks.show', $this->task->card_id),
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
