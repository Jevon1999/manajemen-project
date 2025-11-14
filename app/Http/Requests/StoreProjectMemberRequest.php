<?php

namespace App\Http\Requests;

use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProjectMemberRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Hanya leader dari project ini yang bisa menambahkan member
        $projectId = $this->route('project');
        $project = Project::find($projectId);
        
        if (!$project) {
            return false;
        }
        
        // Cek apakah user adalah leader dari project ini
        return $project->leader_id === auth()->id();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => [
                'required',
                'exists:users,user_id',
                function ($attribute, $value, $fail) {
                    $user = User::find($value);
                    
                    // Validasi 1: User harus aktif
                    if ($user && $user->status !== 'active') {
                        $fail('User tidak aktif dan tidak dapat ditambahkan ke project.');
                    }
                    
                    // Validasi 2: User harus memiliki role 'user' (bukan admin atau leader)
                    if ($user && $user->role !== 'user') {
                        $fail('Hanya user dengan role User yang dapat ditambahkan sebagai anggota project.');
                    }
                    
                    // Validasi 3: User belum menjadi member
                    $projectId = $this->route('project');
                    $existingMember = ProjectMember::where('project_id', $projectId)
                        ->where('user_id', $value)
                        ->exists();
                        
                    if ($existingMember) {
                        $fail('User sudah menjadi anggota project ini.');
                    }
                },
            ],
            'role' => [
                'required',
                Rule::in(['developer', 'designer']),
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'user_id.required' => 'Silakan pilih user yang akan ditambahkan.',
            'user_id.exists' => 'User yang dipilih tidak ditemukan.',
            'role.required' => 'Silakan pilih role untuk anggota.',
            'role.in' => 'Role harus Developer atau Designer.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'user_id' => 'user',
            'role' => 'role',
        ];
    }
}
