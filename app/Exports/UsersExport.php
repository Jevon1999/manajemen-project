<?php

namespace App\Exports;

use App\Models\User;
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

class UsersExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle, ShouldAutoSize
{
    protected $role;

    public function __construct($role = null)
    {
        $this->role = $role;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $query = User::query();

        if ($this->role) {
            $query->where('role', $this->role);
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
            'Full Name',
            'Username',
            'Email',
            'Role',
            'Phone',
            'Status',
            'Registered At',
            'Last Login',
        ];
    }

    /**
     * @param mixed $user
     */
    public function map($user): array
    {
        return [
            $user->user_id,
            $user->full_name,
            $user->username,
            $user->email,
            strtoupper($user->role),
            $user->phone ?? '-',
            $user->is_active ? 'ACTIVE' : 'INACTIVE',
            $user->created_at->format('Y-m-d H:i:s'),
            $user->last_login_at ? $user->last_login_at->format('Y-m-d H:i:s') : 'Never',
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
                    'startColor' => ['rgb' => '3B82F6']
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
            'B' => 25,  // Full Name
            'C' => 18,  // Username
            'D' => 32,  // Email
            'E' => 12,  // Role
            'F' => 18,  // Phone
            'G' => 12,  // Status
            'H' => 20,  // Registered At
            'I' => 20,  // Last Login
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return $this->role ? ucfirst($this->role) . ' Users' : 'All Users';
    }
}
