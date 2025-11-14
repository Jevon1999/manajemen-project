<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectTemplate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'category',
        'template_data',
        'default_boards',
        'estimated_duration_days',
        'required_roles',
        'is_active',
        'created_by',
        'usage_count'
    ];

    protected $casts = [
        'template_data' => 'array',
        'default_boards' => 'array',
        'required_roles' => 'array',
        'is_active' => 'boolean',
        'estimated_duration_days' => 'integer',
        'usage_count' => 'integer'
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    /**
     * Get the user who created this template
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get projects that used this template
     */
    public function projects()
    {
        return $this->hasMany(Project::class, 'template_id');
    }

    /**
     * Scope for active templates
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for templates by category
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Get popular templates (most used)
     */
    public function scopePopular($query, $limit = 5)
    {
        return $query->where('is_active', true)
                    ->orderBy('usage_count', 'desc')
                    ->limit($limit);
    }

    /**
     * Get default project structure from template
     */
    public function getDefaultProjectStructure()
    {
        return [
            'boards' => $this->default_boards ?? ['To Do', 'In Progress', 'Review', 'Done'],
            'estimated_duration' => $this->estimated_duration_days,
            'required_roles' => $this->required_roles ?? ['project_manager'],
            'template_settings' => $this->template_data ?? []
        ];
    }

    /**
     * Create project from this template
     */
    public function createProjectFromTemplate($projectData)
    {
        $templateStructure = $this->getDefaultProjectStructure();
        
        // Merge template data with project data
        $mergedData = array_merge($templateStructure['template_settings'], $projectData);
        
        // Increment usage count
        $this->increment('usage_count');
        
        return $mergedData;
    }

    /**
     * Get template categories
     */
    public static function getCategories()
    {
        return [
            'web_development' => 'Web Development',
            'mobile_app' => 'Mobile Application',
            'desktop_software' => 'Desktop Software',
            'data_analysis' => 'Data Analysis',
            'marketing' => 'Marketing Campaign',
            'design' => 'Design Project',
            'research' => 'Research Project',
            'other' => 'Other'
        ];
    }

    /**
     * Get available roles
     */
    public static function getAvailableRoles()
    {
        return [
            'project_manager' => 'Project Manager',
            'developer' => 'Developer',
            'designer' => 'Designer',
            'analyst' => 'Data Analyst',
            'tester' => 'Quality Tester',
            'content_writer' => 'Content Writer',
            'marketing_specialist' => 'Marketing Specialist'
        ];
    }

    /**
     * Validate template data structure
     */
    public function validateTemplateData()
    {
        $templateData = $this->template_data ?? [];
        
        $requiredKeys = ['project_phases', 'default_tasks', 'completion_criteria'];
        
        foreach ($requiredKeys as $key) {
            if (!isset($templateData[$key])) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Get template statistics
     */
    public function getTemplateStats()
    {
        $projectsCreated = $this->projects()->count();
        $averageCompletionRate = $this->projects()
            ->where('status', 'completed')
            ->count();
        
        $completionPercentage = $projectsCreated > 0 
            ? round(($averageCompletionRate / $projectsCreated) * 100, 2)
            : 0;
            
        return [
            'total_projects' => $projectsCreated,
            'completed_projects' => $averageCompletionRate,
            'completion_rate' => $completionPercentage,
            'usage_count' => $this->usage_count,
            'last_used' => $this->projects()->latest()->first()?->created_at
        ];
    }

    /**
     * Create default system templates
     */
    public static function createDefaultTemplates()
    {
        $defaultTemplates = [
            [
                'name' => 'Basic Web Development',
                'description' => 'Standard web development project with common phases',
                'category' => 'web_development',
                'default_boards' => ['Backlog', 'To Do', 'In Progress', 'Testing', 'Done'],
                'estimated_duration_days' => 90,
                'required_roles' => ['project_manager', 'developer', 'designer'],
                'template_data' => [
                    'project_phases' => [
                        'Planning & Analysis',
                        'Design & Wireframing', 
                        'Development',
                        'Testing & QA',
                        'Deployment & Launch'
                    ],
                    'default_tasks' => [
                        'Requirements gathering',
                        'UI/UX Design',
                        'Database design',
                        'Frontend development',
                        'Backend development',
                        'Testing',
                        'Deployment'
                    ],
                    'completion_criteria' => [
                        'All requirements met',
                        'Testing completed',
                        'Performance optimized',
                        'Documentation complete'
                    ]
                ],
                'is_active' => true
            ],
            [
                'name' => 'Mobile App Development',
                'description' => 'Mobile application project template',
                'category' => 'mobile_app',
                'default_boards' => ['Backlog', 'Design', 'Development', 'Testing', 'App Store'],
                'estimated_duration_days' => 120,
                'required_roles' => ['project_manager', 'developer', 'designer'],
                'template_data' => [
                    'project_phases' => [
                        'Market Research',
                        'UI/UX Design',
                        'Development',
                        'Testing',
                        'App Store Submission'
                    ],
                    'default_tasks' => [
                        'Market analysis',
                        'Wireframe creation',
                        'App development',
                        'Beta testing',
                        'Store optimization'
                    ],
                    'completion_criteria' => [
                        'App store approval',
                        'User acceptance testing passed',
                        'Performance benchmarks met'
                    ]
                ],
                'is_active' => true
            ],
            [
                'name' => 'Marketing Campaign',
                'description' => 'Digital marketing campaign project',
                'category' => 'marketing',
                'default_boards' => ['Ideas', 'Planning', 'Creation', 'Review', 'Published'],
                'estimated_duration_days' => 60,
                'required_roles' => ['project_manager', 'marketing_specialist', 'content_writer'],
                'template_data' => [
                    'project_phases' => [
                        'Strategy Development',
                        'Content Creation',
                        'Campaign Launch',
                        'Performance Monitoring'
                    ],
                    'default_tasks' => [
                        'Target audience analysis',
                        'Content strategy',
                        'Creative development',
                        'Campaign execution',
                        'Analytics tracking'
                    ],
                    'completion_criteria' => [
                        'KPI targets achieved',
                        'Campaign metrics positive',
                        'ROI objectives met'
                    ]
                ],
                'is_active' => true
            ]
        ];

        foreach ($defaultTemplates as $template) {
            self::firstOrCreate(
                ['name' => $template['name']],
                $template
            );
        }
    }
}