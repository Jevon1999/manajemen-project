<?php

namespace App\Exports;

use App\Models\Project;
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

class ComprehensiveProjectReport implements 
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    ShouldAutoSize,
    WithTitle,
    WithColumnFormatting
{
    protected $status;

    public function __construct($status = null)
    {
        $this->status = $status;
    }

    public function collection()
    {
        $query = Project::with(['leader', 'cards', 'members']);

        if ($this->status) {
            $query->where('status', $this->status);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'Project ID',
            'Project Name',
            'Description',
            'Leader',
            'Leader Email',
            'Status',
            'Priority',
            'Category',
            'Team Size',
            'Total Tasks',
            'Completed Tasks',
            'Progress (%)',
            'Start Date',
            'Deadline',
            'Completed At',
            'Duration (Days)',
            'Is Overdue',
            'Delay Days',
            'Budget (IDR)',
            'Notes',
        ];
    }

    public function map($project): array
    {
        // Calculate task metrics
        $totalTasks = $project->cards->count();
        $completedTasks = $project->cards->where('status', 'done')->count();
        $progress = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;

        // Calculate duration
        $duration = 0;
        if ($project->start_date && $project->deadline) {
            $duration = $project->start_date->diffInDays($project->deadline);
        }

        // Calculate overdue status
        $isOverdue = 'NO';
        $delayDays = 0;
        
        if ($project->deadline) {
            if ($project->completed_at && $project->completed_at > $project->deadline) {
                $isOverdue = 'YES';
                $delayDays = $project->completed_at->diffInDays($project->deadline);
            } elseif (!$project->completed_at && now() > $project->deadline) {
                $isOverdue = 'ONGOING LATE';
                $delayDays = now()->diffInDays($project->deadline);
            }
        }

        // Team size
        $teamSize = $project->members->count() + 1; // +1 for leader

        return [
            $project->project_id,
            $project->project_name,
            $this->cleanText($project->description),
            $project->leader ? $project->leader->full_name : 'N/A',
            $project->leader ? $project->leader->email : 'N/A',
            strtoupper($project->status),
            strtoupper($project->priority ?? 'MEDIUM'),
            $this->cleanText($project->category ?? 'General'),
            $teamSize,
            $totalTasks,
            $completedTasks,
            $progress,
            $project->start_date ? $project->start_date->format('Y-m-d') : 'N/A',
            $project->deadline ? $project->deadline->format('Y-m-d') : 'N/A',
            $project->completed_at ? $project->completed_at->format('Y-m-d H:i:s') : 'N/A',
            $duration,
            $isOverdue,
            $delayDays,
            $project->budget ?? 0,
            $this->cleanText($project->notes ?? ''),
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
                    'startColor' => ['rgb' => '4F46E5'],
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
            'M' => NumberFormat::FORMAT_DATE_YYYYMMDD,
            'N' => NumberFormat::FORMAT_DATE_YYYYMMDD,
            'O' => NumberFormat::FORMAT_DATE_DATETIME,
            'S' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
        ];
    }

    public function title(): string
    {
        return 'Project Summary';
    }
}
