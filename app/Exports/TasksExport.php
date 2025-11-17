<?php

namespace App\Exports;

use App\Models\Card;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TasksExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle
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
            'Task ID',
            'Project',
            'Task Title',
            'Description',
            'Status',
            'Priority',
            'Assigned To',
            'Deadline',
            'Completed At',
            'Is Overdue',
            'Created At',
        ];
    }

    /**
     * @param mixed $task
     */
    public function map($task): array
    {
        return [
            $task->card_id,
            $task->project ? $task->project->project_name : '-',
            $task->title,
            $task->description ?? '-',
            ucfirst($task->status),
            ucfirst($task->priority ?? 'medium'),
            $task->assignedUser ? $task->assignedUser->full_name : 'Unassigned',
            $task->deadline ? $task->deadline->format('d M Y') : '-',
            $task->completed_at ? $task->completed_at->format('d M Y H:i') : '-',
            ($task->deadline && now() > $task->deadline && $task->status !== 'done') ? 'Yes' : 'No',
            $task->created_at->format('d M Y H:i'),
        ];
    }

    /**
     * @param Worksheet $sheet
     */
    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '10B981']
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
            'A' => 10,  // Task ID
            'B' => 25,  // Project
            'C' => 30,  // Task Title
            'D' => 35,  // Description
            'E' => 12,  // Status
            'F' => 12,  // Priority
            'G' => 20,  // Assigned To
            'H' => 15,  // Deadline
            'I' => 18,  // Completed At
            'J' => 12,  // Is Overdue
            'K' => 18,  // Created At
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
