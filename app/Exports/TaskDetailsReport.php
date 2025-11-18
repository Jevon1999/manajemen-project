<?php

namespace App\Exports;

use App\Models\Card;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class TaskDetailsReport implements 
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    ShouldAutoSize,
    WithTitle,
    WithColumnFormatting
{
    protected $projectId;
    protected $status;

    public function __construct($projectId = null, $status = null)
    {
        $this->projectId = $projectId;
        $this->status = $status;
    }

    public function collection()
    {
        $query = Card::with(['project', 'assignedUser']);

        if ($this->projectId) {
            $query->where('project_id', $this->projectId);
        }

        if ($this->status) {
            $query->where('status', $this->status);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'Task ID',
            'Project Name',
            'Task Title',
            'Description',
            'Assigned To',
            'Assignee Email',
            'Role',
            'Status',
            'Priority',
            'Created Date',
            'Start Time',
            'Deadline',
            'Completed At',
            'Duration (Hours)',
            'Is Overdue',
            'Delay Days',
            'Notes',
        ];
    }

    public function map($task): array
    {
        // Calculate duration if completed
        $duration = 0;
        if ($task->completed_at && $task->created_at) {
            $duration = round($task->created_at->diffInHours($task->completed_at), 2);
        }

        // Calculate overdue status
        $isOverdue = 'NO';
        $delayDays = 0;
        
        if ($task->deadline) {
            if ($task->completed_at && $task->completed_at > $task->deadline) {
                $isOverdue = 'YES';
                $delayDays = $task->completed_at->diffInDays($task->deadline);
            } elseif (!$task->completed_at && now() > $task->deadline) {
                $isOverdue = 'ONGOING';
                $delayDays = now()->diffInDays($task->deadline);
            }
        }

        return [
            $task->card_id,
            $task->project ? $task->project->project_name : 'N/A',
            $task->title,
            $this->cleanText($task->description),
            $task->assignedUser ? $task->assignedUser->full_name : 'Unassigned',
            $task->assignedUser ? $task->assignedUser->email : 'N/A',
            $task->assignedUser ? strtoupper($task->assignedUser->role) : 'N/A',
            strtoupper($task->status),
            strtoupper($task->priority ?? 'MEDIUM'),
            $task->created_at->format('Y-m-d H:i:s'),
            $task->start_time ? $task->start_time->format('Y-m-d H:i:s') : 'N/A',
            $task->deadline ? $task->deadline->format('Y-m-d H:i:s') : 'N/A',
            $task->completed_at ? $task->completed_at->format('Y-m-d H:i:s') : 'N/A',
            $duration,
            $isOverdue,
            $delayDays,
            $this->cleanText($task->notes ?? ''),
        ];
    }

    private function cleanText($text)
    {
        if (!$text) return 'N/A';
        
        $cleaned = strip_tags($text);
        $cleaned = preg_replace('/\s+/', ' ', $cleaned);
        $cleaned = trim($cleaned);
        
        return $cleaned ?: 'N/A';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 12,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '10B981'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    public function columnFormats(): array
    {
        return [
            'J' => NumberFormat::FORMAT_DATE_DATETIME,
            'K' => NumberFormat::FORMAT_DATE_DATETIME,
            'L' => NumberFormat::FORMAT_DATE_DATETIME,
            'M' => NumberFormat::FORMAT_DATE_DATETIME,
            'N' => NumberFormat::FORMAT_NUMBER_00,
        ];
    }

    public function title(): string
    {
        return 'Task Details';
    }
}
