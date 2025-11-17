<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UsersExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle
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
            'User ID',
            'Full Name',
            'Username',
            'Email',
            'Role',
            'Status',
            'Registered At',
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
            ucfirst($user->role),
            $user->is_active ? 'Active' : 'Inactive',
            $user->created_at->format('d M Y H:i'),
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
                    'startColor' => ['rgb' => '3B82F6']
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
            'A' => 10,  // User ID
            'B' => 25,  // Full Name
            'C' => 20,  // Username
            'D' => 30,  // Email
            'E' => 12,  // Role
            'F' => 12,  // Status
            'G' => 18,  // Registered At
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
