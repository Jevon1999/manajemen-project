<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class GeneralReport implements WithMultipleSheets
{
    protected $month;
    protected $year;

    public function __construct($month = null, $year = null)
    {
        $this->month = $month;
        $this->year = $year;
    }

    public function sheets(): array
    {
        $sheets = [];

        // Sheet 1: Project Summary
        $sheets[] = new ComprehensiveProjectReport();

        // Sheet 2: Task Details
        $sheets[] = new TaskDetailsReport();

        // Sheet 3: Users Overview
        $sheets[] = new UsersExport();

        // Sheet 4: Monthly Time Tracking (if month/year provided)
        if ($this->month && $this->year) {
            $sheets[] = new MonthlyReport($this->month, $this->year);
        }

        return $sheets;
    }
}
