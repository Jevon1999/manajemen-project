<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class CreateProjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->role === 'admin';
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'project_name' => [
                'required',
                'string',
                'min:3',
                'max:255',
                'regex:/^[a-zA-Z0-9\s\-_\.]+$/', // Allow alphanumeric, spaces, hyphens, underscores, dots
                'unique:projects,project_name'
            ],
            'description' => [
                'nullable',
                'string',
                'max:2000'
            ],
            'start_date' => [
                'nullable',
                'date',
                'after_or_equal:today'
            ],
            'end_date' => [
                'nullable',
                'date',
                'after:start_date'
            ],
            'status' => [
                'nullable',
                'in:planning,active,completed,on_hold,cancelled'
            ],
            'priority' => [
                'nullable',
                'in:low,medium,high'
            ],
            'category' => [
                'nullable',
                'string',
                'max:50'
            ],
            'budget' => [
                'nullable',
                'numeric',
                'min:0',
                'max:999999999.99'
            ],
            'leader_id' => [
                'nullable',
                'exists:users,user_id',
                function ($attribute, $value, $fail) {
                    if ($value) {
                        $user = \App\Models\User::find($value);
                        if (!$user || $user->role !== 'leader' || $user->status !== 'active') {
                            $fail('The selected team leader must be an active leader.');
                        }
                    }
                },
            ],
            'template_id' => [
                'nullable',
                'exists:project_templates,id'
            ],
            'documents.*' => [
                'nullable',
                'file',
                'max:10240', // 10MB
                'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif,zip,rar'
            ],
            'initial_boards' => [
                'nullable',
                'array',
                'max:10'
            ],
            'initial_boards.*' => [
                'string',
                'max:100'
            ],
            'notifications_enabled' => [
                'boolean'
            ],
            'public_visibility' => [
                'boolean'
            ],
            'allow_member_invite' => [
                'boolean'
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'project_name.required' => 'Project name is required.',
            'project_name.min' => 'Project name must be at least 3 characters.',
            'project_name.max' => 'Project name cannot exceed 255 characters.',
            'project_name.regex' => 'Project name contains invalid characters.',
            'project_name.unique' => 'A project with this name already exists.',
            
            'start_date.required' => 'Start date is required.',
            'start_date.after_or_equal' => 'Start date cannot be in the past.',
            
            'end_date.after' => 'End date must be after start date.',
            
            'status.required' => 'Project status is required.',
            'status.in' => 'Invalid project status selected.',
            
            'category.required' => 'Project category is required.',
            'category.in' => 'Invalid project category selected.',
            
            'budget.numeric' => 'Budget must be a valid number.',
            'budget.min' => 'Budget cannot be negative.',
            'budget.max' => 'Budget amount is too large.',
            
            'leader_id.exists' => 'Selected team leader does not exist.',
            
            'template_id.exists' => 'Selected template does not exist.',
            
            'documents.*.file' => 'Each document must be a valid file.',
            'documents.*.max' => 'Document size cannot exceed 10MB.',
            'documents.*.mimes' => 'Document must be a supported file type (PDF, DOC, XLS, PPT, images, ZIP).',
            
            'initial_boards.array' => 'Initial boards must be an array.',
            'initial_boards.max' => 'Cannot create more than 10 initial boards.',
            'initial_boards.*.max' => 'Board name cannot exceed 100 characters.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'project_name' => 'project name',
            'start_date' => 'start date',
            'end_date' => 'end date',
            'leader_id' => 'team leader',
            'template_id' => 'project template',
            'initial_boards' => 'initial boards',
            'documents.*' => 'document',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'project_name' => trim($this->project_name),
            'description' => $this->description ? trim($this->description) : null,
            'budget' => $this->budget ? (float) $this->budget : null,
            'notifications_enabled' => $this->boolean('notifications_enabled', true),
            'public_visibility' => $this->boolean('public_visibility', false),
            'allow_member_invite' => $this->boolean('allow_member_invite', true),
        ]);
    }

    /**
     * Handle a passed validation attempt.
     */
    protected function passedValidation(): void
    {
        // Additional validation logic after basic rules pass
        $this->validateProjectDateRange();
        $this->validateLeaderAvailability();
    }

    /**
     * Validate project date range makes business sense
     */
    private function validateProjectDateRange(): void
    {
        if ($this->start_date && $this->end_date) {
            $startDate = \Carbon\Carbon::parse($this->start_date);
            $endDate = \Carbon\Carbon::parse($this->end_date);
            
            // Project duration validation
            $maxDuration = 365 * 2; // 2 years max
            if ($startDate->diffInDays($endDate) > $maxDuration) {
                $this->validator->errors()->add('end_date', 'Project duration cannot exceed 2 years.');
            }
        }
    }

    /**
     * Validate leader availability (not overloaded with projects)
     */
    private function validateLeaderAvailability(): void
    {
        if ($this->leader_id) {
            $activeProjectsCount = \App\Models\ProjectMember::where('user_id', $this->leader_id)
                ->where('role', 'project_manager')
                ->whereHas('project', function($query) {
                    $query->whereIn('status', ['planning', 'active']);
                })
                ->count();

            if ($activeProjectsCount >= 3) { // Max 3 active projects per leader
                $this->validator->errors()->add('leader_id', 'Selected team leader is managing maximum number of projects (3). Please choose another leader.');
            }
        }
    }
}