<?php

namespace App\Exports;

use App\Models\Card;
use App\Models\WorkSession;
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
use Carbon\Carbon;

class PerProjectReport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    ShouldAutoSize,
    WithTitle,
    WithColumnFormatting
{
    protected $projectId;

    public function __construct($projectId)
    {
        $this->projectId = $projectId;
    }

    public function collection()
    {
        return Card::with(['user', 'project', 'workSessions'])
            ->where('project_id', $this->projectId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Task ID',
            'Task Title',
            'Description',
            'Assigned To',
            'Assignee Email',
            'Role',
            'Status',
            'Priority',
            'Board',
            'Created Date',
            'Start Time',
            'Deadline',
            'Completed At',
            'Duration (Hours)',
            'Total Work Sessions',
            'Total Work Hours',
            'Is Overdue',
            'Delay Days',
            'Subtasks Total',
            'Subtasks Completed',
            'Notes',
        ];
    }

    public function map($card): array
    {
        $createdAt = Carbon::parse($card->created_at);
        $completedAt = $card->completed_at ? Carbon::parse($card->completed_at) : null;
        $deadline = $card->deadline ? Carbon::parse($card->deadline) : null;
        $startTime = $card->start_time ? Carbon::parse($card->start_time) : null;
        
        // Calculate duration in hours
        $durationHours = 0;
        if ($startTime && $completedAt) {
            $durationHours = round($startTime->diffInHours($completedAt), 2);
        } elseif ($startTime && !$completedAt) {
            $durationHours = round($startTime->diffInHours(now()), 2);
        }

        // Calculate total work hours from sessions
        $totalWorkHours = 0;
        foreach ($card->workSessions as $session) {
            if ($session->start_time && $session->stop_time) {
                $start = Carbon::parse($session->start_time);
                $stop = Carbon::parse($session->stop_time);
                $totalWorkHours += $start->diffInHours($stop);
            }
        }
        $totalWorkHours = round($totalWorkHours, 2);

        // Overdue calculation
        $isOverdue = 'NO';
        $delayDays = 0;
        if ($deadline) {
            if ($completedAt && $completedAt->greaterThan($deadline)) {
                $isOverdue = 'YES';
                $delayDays = $deadline->diffInDays($completedAt);
            } elseif (!$completedAt && now()->greaterThan($deadline)) {
                $isOverdue = 'ONGOING';
                $delayDays = $deadline->diffInDays(now());
            }
        }

        // Subtasks count
        $subtasksTotal = $card->subtasks ? $card->subtasks->count() : 0;
        $subtasksCompleted = $card->subtasks ? $card->subtasks->where('is_completed', true)->count() : 0;

        return [
            $card->id,
            $this->cleanText($card->title),
            $this->cleanText($card->description),
            $card->user ? $card->user->name : 'Unassigned',
            $card->user ? $card->user->email : 'N/A',
            $card->user ? strtoupper($card->user->role) : 'N/A',
            strtoupper($card->status),
            strtoupper($card->priority ?? 'MEDIUM'),
            $card->board ? strtoupper($card->board) : 'BACKLOG',
            $createdAt->format('Y-m-d'),
            $startTime ? $startTime->format('Y-m-d H:i:s') : 'Not Started',
            $deadline ? $deadline->format('Y-m-d') : 'N/A',
            $completedAt ? $completedAt->format('Y-m-d H:i:s') : 'Not Completed',
            $durationHours,
            $card->workSessions->count(),
            $totalWorkHours,
            $isOverdue,
            $delayDays,
            $subtasksTotal,
            $subtasksCompleted,
            $this->cleanText($card->notes),
        ];
    }

    protected function cleanText($text)
    {
        if (!$text) return 'N/A';
        $cleaned = strip_tags($text);
        $cleaned = preg_replace('/\s+/', ' ', $cleaned);
        return trim($cleaned);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '3B82F6'], // Blue color
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
            'J' => NumberFormat::FORMAT_DATE_YYYYMMDD2, // Created Date
            'K' => NumberFormat::FORMAT_DATE_DATETIME, // Start Time
            'L' => NumberFormat::FORMAT_DATE_YYYYMMDD2, // Deadline
            'M' => NumberFormat::FORMAT_DATE_DATETIME, // Completed At
            'N' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // Duration
            'O' => NumberFormat::FORMAT_NUMBER, // Sessions
            'P' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // Work Hours
            'R' => NumberFormat::FORMAT_NUMBER, // Delay Days
            'S' => NumberFormat::FORMAT_NUMBER, // Subtasks Total
            'T' => NumberFormat::FORMAT_NUMBER, // Subtasks Completed
        ];
    }

    public function title(): string
    {
        return 'Per Project Report';
    }
}
