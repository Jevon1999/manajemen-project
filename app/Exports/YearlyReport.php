<?php

namespace App\Exports;

use App\Models\Project;
use App\Models\Card;
use App\Models\User;
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

class YearlyReport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    ShouldAutoSize,
    WithTitle,
    WithColumnFormatting
{
    protected $year;

    public function __construct($year = null)
    {
        $this->year = $year ?? now()->year;
    }

    public function collection()
    {
        $startDate = Carbon::createFromDate($this->year, 1, 1)->startOfYear();
        $endDate = Carbon::createFromDate($this->year, 12, 31)->endOfYear();

        return Project::with(['leader', 'cards', 'members'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Project ID',
            'Project Name',
            'Category',
            'Leader',
            'Leader Email',
            'Status',
            'Priority',
            'Created Date',
            'Start Date',
            'Deadline',
            'Completed At',
            'Duration (Days)',
            'Total Team Members',
            'Total Tasks',
            'Completed Tasks',
            'In Progress Tasks',
            'Todo Tasks',
            'Progress Percentage',
            'On Time Status',
            'Budget (IDR)',
            'Quarter',
        ];
    }

    public function map($project): array
    {
        $createdAt = Carbon::parse($project->created_at);
        $completedAt = $project->completed_at ? Carbon::parse($project->completed_at) : null;
        $deadline = $project->deadline ? Carbon::parse($project->deadline) : null;
        
        // Calculate duration
        $duration = 0;
        if ($project->created_at && $completedAt) {
            $duration = $createdAt->diffInDays($completedAt);
        } elseif ($project->created_at) {
            $duration = $createdAt->diffInDays(now());
        }

        // Task statistics
        $totalTasks = $project->cards->count();
        $completedTasks = $project->cards->where('status', 'completed')->count();
        $inProgressTasks = $project->cards->where('status', 'in_progress')->count();
        $todoTasks = $project->cards->where('status', 'todo')->count();

        // Progress percentage
        $progress = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 1) : 0;

        // On time status
        $onTimeStatus = 'ON TIME';
        if ($deadline) {
            if ($completedAt && $completedAt->greaterThan($deadline)) {
                $onTimeStatus = 'LATE';
            } elseif (!$completedAt && now()->greaterThan($deadline) && $project->status !== 'completed') {
                $onTimeStatus = 'OVERDUE';
            }
        }

        // Quarter calculation
        $quarter = 'Q' . $createdAt->quarter;

        return [
            $project->id,
            $project->name,
            $project->category ?? 'General',
            $project->leader ? $project->leader->name : 'N/A',
            $project->leader ? $project->leader->email : 'N/A',
            strtoupper($project->status),
            strtoupper($project->priority ?? 'MEDIUM'),
            $createdAt->format('Y-m-d'),
            $project->start_date ?? 'N/A',
            $deadline ? $deadline->format('Y-m-d') : 'N/A',
            $completedAt ? $completedAt->format('Y-m-d') : 'Ongoing',
            $duration,
            $project->members->count(),
            $totalTasks,
            $completedTasks,
            $inProgressTasks,
            $todoTasks,
            $progress . '%',
            $onTimeStatus,
            $project->budget ? number_format($project->budget, 0, ',', '.') : 'N/A',
            $quarter,
        ];
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
                    'startColor' => ['rgb' => '8B5CF6'], // Purple color
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
            'H' => NumberFormat::FORMAT_DATE_YYYYMMDD2, // Created Date
            'I' => NumberFormat::FORMAT_DATE_YYYYMMDD2, // Start Date
            'J' => NumberFormat::FORMAT_DATE_YYYYMMDD2, // Deadline
            'K' => NumberFormat::FORMAT_DATE_YYYYMMDD2, // Completed At
            'L' => NumberFormat::FORMAT_NUMBER, // Duration
            'M' => NumberFormat::FORMAT_NUMBER, // Team Members
            'N' => NumberFormat::FORMAT_NUMBER, // Total Tasks
            'O' => NumberFormat::FORMAT_NUMBER, // Completed
            'P' => NumberFormat::FORMAT_NUMBER, // In Progress
            'Q' => NumberFormat::FORMAT_NUMBER, // Todo
        ];
    }

    public function title(): string
    {
        return 'Yearly Report ' . $this->year;
    }
}
