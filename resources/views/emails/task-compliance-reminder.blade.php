<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Reminder</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            padding: 30px 20px;
        }
        .task-info {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .task-info h3 {
            margin-top: 0;
            color: #667eea;
        }
        .issues-list {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .issues-list ul {
            margin: 10px 0;
            padding-left: 20px;
        }
        .issues-list li {
            margin: 5px 0;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            font-weight: 600;
        }
        .btn:hover {
            background: #5568d3;
        }
        .footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #6c757d;
        }
        .priority-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .priority-urgent { background: #fee; color: #c00; }
        .priority-high { background: #ffe; color: #f60; }
        .priority-medium { background: #ffa; color: #960; }
        .priority-low { background: #eee; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            @if($reminderType === 'time_log')
                <h1>⏰ Time Log Reminder</h1>
            @elseif($reminderType === 'daily_comment')
                <h1>📝 Daily Update Required</h1>
            @elseif($reminderType === 'overdue')
                <h1>🚨 Overdue Task Alert</h1>
            @elseif($reminderType === 'approval_pending')
                <h1>✅ Task Awaiting Approval</h1>
            @else
                <h1>⚠️ Task Compliance Reminder</h1>
            @endif
        </div>

        <!-- Content -->
        <div class="content">
            <p>Hi <strong>{{ $user->full_name }}</strong>,</p>

            @if($reminderType === 'time_log')
                <p>You haven't logged any time today for your active task. Time tracking is mandatory for all active tasks.</p>
            @elseif($reminderType === 'daily_comment')
                <p>Your active task requires a daily progress update. Please add a comment describing what you accomplished today.</p>
            @elseif($reminderType === 'overdue')
                <p>This task is past its due date. Please update the status or request an extension.</p>
            @elseif($reminderType === 'approval_pending')
                <p>A task has been submitted for your review and approval.</p>
            @else
                <p>Your active task has compliance issues that need attention.</p>
            @endif

            <!-- Task Info -->
            <div class="task-info">
                <h3>📋 {{ $task->card_title }}</h3>
                <p><strong>Task ID:</strong> #{{ $task->card_id }}</p>
                <p>
                    <strong>Priority:</strong>
                    <span class="priority-badge priority-{{ $task->priority }}">
                        {{ ucfirst($task->priority) }}
                    </span>
                </p>
                <p><strong>Status:</strong> {{ ucfirst(str_replace('_', ' ', $task->status)) }}</p>
                @if($task->due_date)
                    <p><strong>Due Date:</strong> {{ $task->due_date->format('M d, Y') }}</p>
                @endif
                <p><strong>Time Tracked:</strong> {{ $task->actual_hours ?? 0 }}h / {{ $task->estimated_hours ?? 0 }}h</p>
            </div>

            <!-- Issues List -->
            @if(!empty($issues))
                <div class="issues-list">
                    <strong>⚠️ Issues to Address:</strong>
                    <ul>
                        @foreach($issues as $issue)
                            <li>{{ $issue }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Reminder Messages -->
            @if($reminderType === 'time_log')
                <p>Please log your time before continuing work on this task. Accurate time tracking helps us:</p>
                <ul>
                    <li>Provide better estimates for future projects</li>
                    <li>Ensure accurate billing for clients</li>
                    <li>Track productivity and workload balance</li>
                </ul>
            @elseif($reminderType === 'daily_comment')
                <p>Daily updates help the team stay informed about:</p>
                <ul>
                    <li>Progress made on the task</li>
                    <li>Any blockers or challenges encountered</li>
                    <li>Next steps and estimated completion</li>
                </ul>
            @elseif($reminderType === 'approval_pending')
                <p>Please review the work and either:</p>
                <ul>
                    <li>✅ Approve the task if it meets requirements</li>
                    <li>❌ Reject with feedback if changes are needed</li>
                </ul>
            @endif

            <!-- CTA Button -->
            <p style="text-align: center;">
                <a href="{{ $taskUrl }}" class="btn">
                    @if($reminderType === 'approval_pending')
                        Review Task Now
                    @else
                        Update Task Now
                    @endif
                </a>
            </p>

            <p style="font-size: 14px; color: #666;">
                <strong>Need help?</strong> Contact your team leader or check the 
                <a href="{{ $dashboardUrl }}">dashboard</a> for more details.
            </p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>This is an automated reminder from the Project Management System.</p>
            <p>© {{ date('Y') }} Project Management System. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
