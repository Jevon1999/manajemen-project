<?php

namespace App\Exports;

use App\Models\Card;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class TasksExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle, ShouldAutoSize
{
    protected $projectId;
    protected $status;

    public function __construct($projectId = null, $status = null)
    {
        $this->projectId = $projectId;
        $this->status = $status;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
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

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'ID',
            'Project',
            'Task Title',
            'Description',
            'Status',
            'Priority',
            'Assigned To',
            'Assignee Email',
            'Deadline',
            'Completed At',
            'Is Overdue?',
            'Created At',
        ];
    }

    /**
     * @param mixed $task
     */
    public function map($task): array
    {
        $isOverdue = $task->deadline 
            && now() > $task->deadline 
            && $task->status !== 'done';

        return [
            $task->card_id,
            $task->project ? $task->project->project_name : 'N/A',
            $task->title,
            strip_tags($task->description ?? '-'),
            strtoupper($task->status),
            strtoupper($task->priority ?? 'MEDIUM'),
            $task->assignedUser ? $task->assignedUser->full_name : 'Unassigned',
            $task->assignedUser ? $task->assignedUser->email : '-',
            $task->deadline ? $task->deadline->format('Y-m-d') : '-',
            $task->completed_at ? $task->completed_at->format('Y-m-d H:i:s') : '-',
            $isOverdue ? 'YES' : 'NO',
            $task->created_at->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * @param Worksheet $sheet
     */
    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true, 
                    'size' => 12,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '10B981']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function columnWidths(): array
    {
        return [
            'A' => 8,   // ID
            'B' => 28,  // Project
            'C' => 32,  // Task Title
            'D' => 40,  // Description
            'E' => 12,  // Status
            'F' => 12,  // Priority
            'G' => 22,  // Assigned To
            'H' => 28,  // Assignee Email
            'I' => 14,  // Deadline
            'J' => 20,  // Completed At
            'K' => 12,  // Is Overdue
            'L' => 20,  // Created At
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Tasks Report';
    }
}
