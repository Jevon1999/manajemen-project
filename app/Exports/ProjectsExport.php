<?php

namespace App\Exports;

use App\Models\Project;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProjectsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle
{
    protected $status;

    public function __construct($status = null)
    {
        $this->status = $status;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $query = Project::with(['leader', 'members']);

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
            'Project ID',
            'Project Name',
            'Description',
            'Status',
            'Priority',
            'Category',
            'Leader',
            'Team Members',
            'Deadline',
            'Completed At',
            'Is Overdue',
            'Delay Days',
            'Budget',
            'Created At',
        ];
    }

    /**
     * @param mixed $project
     */
    public function map($project): array
    {
        return [
            $project->project_id,
            $project->project_name,
            $project->description ?? '-',
            ucfirst($project->status),
            ucfirst($project->priority ?? 'medium'),
            $project->category ?? '-',
            $project->leader ? $project->leader->full_name : 'No Leader',
            $project->members->count() . ' members',
            $project->deadline ? $project->deadline->format('d M Y') : '-',
            $project->completed_at ? $project->completed_at->format('d M Y H:i') : '-',
            $project->is_overdue ? 'Yes' : 'No',
            $project->delay_days ?? 0,
            $project->budget ? 'Rp ' . number_format($project->budget, 0, ',', '.') : '-',
            $project->created_at->format('d M Y H:i'),
        ];
    }

    /**
     * @param Worksheet $sheet
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style for header row
            1 => [
                'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4F46E5']
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
            'A' => 12,  // Project ID
            'B' => 30,  // Project Name
            'C' => 40,  // Description
            'D' => 12,  // Status
            'E' => 12,  // Priority
            'F' => 15,  // Category
            'G' => 20,  // Leader
            'H' => 15,  // Team Members
            'I' => 15,  // Deadline
            'J' => 18,  // Completed At
            'K' => 12,  // Is Overdue
            'L' => 12,  // Delay Days
            'M' => 18,  // Budget
            'N' => 18,  // Created At
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return $this->status ? ucfirst($this->status) . ' Projects' : 'All Projects';
    }
}
