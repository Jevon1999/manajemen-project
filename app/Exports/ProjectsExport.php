<?php

namespace App\Exports;

use App\Models\Project;
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

class ProjectsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle, ShouldAutoSize
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
            'ID',
            'Project Name',
            'Description',
            'Status',
            'Priority',
            'Category',
            'Leader',
            'Leader Email',
            'Team Size',
            'Start Date',
            'Deadline',
            'Completed At',
            'On Time?',
            'Delay Days',
            'Budget (IDR)',
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
            strip_tags($project->description ?? '-'),
            strtoupper($project->status),
            strtoupper($project->priority ?? 'MEDIUM'),
            $project->category ?? '-',
            $project->leader ? $project->leader->full_name : 'No Leader',
            $project->leader ? $project->leader->email : '-',
            $project->members->count(),
            $project->created_at->format('Y-m-d'),
            $project->deadline ? $project->deadline->format('Y-m-d') : '-',
            $project->completed_at ? $project->completed_at->format('Y-m-d H:i:s') : '-',
            $project->is_overdue ? 'LATE' : ($project->completed_at ? 'ON TIME' : '-'),
            $project->delay_days ?? 0,
            $project->budget ? number_format($project->budget, 0, ',', '.') : '-',
            $project->created_at->format('Y-m-d H:i:s'),
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
                'font' => [
                    'bold' => true, 
                    'size' => 12,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4F46E5']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
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
            'B' => 30,  // Project Name
            'C' => 45,  // Description
            'D' => 12,  // Status
            'E' => 12,  // Priority
            'F' => 18,  // Category
            'G' => 22,  // Leader
            'H' => 28,  // Leader Email
            'I' => 12,  // Team Size
            'J' => 14,  // Start Date
            'K' => 14,  // Deadline
            'L' => 20,  // Completed At
            'M' => 12,  // On Time?
            'N' => 12,  // Delay Days
            'O' => 18,  // Budget
            'P' => 20,  // Created At
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
