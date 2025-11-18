<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class FullReportExport implements WithMultipleSheets
{
    protected $projectId;

    public function __construct($projectId = null)
    {
        $this->projectId = $projectId;
    }

    public function sheets(): array
    {
        $sheets = [];

        // Projects sheet
        $sheets[] = new ComprehensiveProjectReport();

        // Tasks sheet
        $sheets[] = new TaskDetailsReport($this->projectId);

        // Users sheet (if no specific project)
        if (!$this->projectId) {
            $sheets[] = new UsersExport();
        }

        return $sheets;
    }
}
