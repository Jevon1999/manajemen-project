<?php

namespace App\Exports;

use App\Models\WorkSession;
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
use Carbon\Carbon;

class MonthlyReport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    ShouldAutoSize,
    WithTitle,
    WithColumnFormatting
{
    protected $month;
    protected $year;

    public function __construct($month = null, $year = null)
    {
        $this->month = $month ?? now()->month;
        $this->year = $year ?? now()->year;
    }

    public function collection()
    {
        $startDate = Carbon::createFromDate($this->year, $this->month, 1)->startOfMonth();
        $endDate = Carbon::createFromDate($this->year, $this->month, 1)->endOfMonth();

        return WorkSession::with(['user', 'card.project'])
            ->whereBetween('start_time', [$startDate, $endDate])
            ->orderBy('start_time', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Session ID',
            'Date',
            'User Name',
            'User Email',
            'Role',
            'Project Name',
            'Task Title',
            'Task Status',
            'Start Time',
            'Stop Time',
            'Duration (Hours)',
            'Productive Hours',
            'Break Time (Hours)',
            'Session Status',
            'Completion Rate',
            'Notes',
        ];
    }

    public function map($session): array
    {
        $startTime = $session->start_time ? Carbon::parse($session->start_time) : null;
        $stopTime = $session->stop_time ? Carbon::parse($session->stop_time) : null;
        
        // Calculate duration in hours
        $durationHours = 0;
        if ($startTime && $stopTime) {
            $durationHours = round($stopTime->diffInMinutes($startTime) / 60, 2);
        }

        // Calculate productive hours (assuming 80% productive)
        $productiveHours = $durationHours > 0 ? round($durationHours * 0.8, 2) : 0;
        $breakTime = $durationHours > 0 ? round($durationHours * 0.2, 2) : 0;

        // Completion rate
        $completionRate = $session->card && $session->card->status === 'completed' ? '100%' : 'In Progress';

        return [
            $session->id,
            $startTime ? $startTime->format('Y-m-d') : 'N/A',
            $session->user ? $session->user->name : 'N/A',
            $session->user ? $session->user->email : 'N/A',
            $session->user ? strtoupper($session->user->role) : 'N/A',
            $session->card && $session->card->project ? $session->card->project->name : 'N/A',
            $session->card ? $this->cleanText($session->card->title) : 'N/A',
            $session->card ? strtoupper($session->card->status) : 'N/A',
            $startTime ? $startTime->format('Y-m-d H:i:s') : 'N/A',
            $stopTime ? $stopTime->format('Y-m-d H:i:s') : 'Ongoing',
            $durationHours > 0 ? $durationHours : 0,
            $productiveHours,
            $breakTime,
            $stopTime ? 'COMPLETED' : 'ACTIVE',
            $completionRate,
            $session->notes ?? 'N/A',
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
                    'startColor' => ['rgb' => 'F59E0B'], // Amber color
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
            'B' => NumberFormat::FORMAT_DATE_YYYYMMDD2, // Date
            'I' => NumberFormat::FORMAT_DATE_DATETIME, // Start Time
            'J' => NumberFormat::FORMAT_DATE_DATETIME, // Stop Time
            'K' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // Duration
            'L' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // Productive Hours
            'M' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // Break Time
        ];
    }

    public function title(): string
    {
        return 'Monthly Report ' . Carbon::createFromDate($this->year, $this->month, 1)->format('M Y');
    }
}
