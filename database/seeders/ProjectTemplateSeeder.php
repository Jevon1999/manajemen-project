<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ProjectTemplate;

class ProjectTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default project templates
        ProjectTemplate::createDefaultTemplates();
        
        // Add additional specialized templates
        $additionalTemplates = [
            [
                'name' => 'E-commerce Website',
                'description' => 'Complete e-commerce solution with payment integration',
                'category' => 'web_development',
                'default_boards' => ['Requirements', 'Design', 'Development', 'Testing', 'Deployment', 'Launch'],
                'estimated_duration_days' => 180,
                'required_roles' => ['project_manager', 'developer', 'designer', 'tester'],
                'template_data' => [
                    'project_phases' => [
                        'Requirements & Analysis',
                        'UI/UX Design',
                        'Frontend Development',
                        'Backend Development',
                        'Payment Integration',
                        'Testing & QA',
                        'Deployment & Launch'
                    ],
                    'default_tasks' => [
                        'Market research',
                        'User journey mapping',
                        'Wireframe creation',
                        'Database design',
                        'Product catalog setup',
                        'Shopping cart implementation',
                        'Payment gateway integration',
                        'Security implementation',
                        'Performance optimization',
                        'User acceptance testing',
                        'Go-live deployment'
                    ],
                    'completion_criteria' => [
                        'All user stories completed',
                        'Payment processing tested',
                        'Security audit passed',
                        'Performance benchmarks met',
                        'SEO optimization complete'
                    ]
                ],
                'is_active' => true
            ],
            [
                'name' => 'Data Analytics Dashboard',
                'description' => 'Business intelligence dashboard with data visualization',
                'category' => 'data_analysis',
                'default_boards' => ['Data Collection', 'Analysis', 'Visualization', 'Testing', 'Deployment'],
                'estimated_duration_days' => 75,
                'required_roles' => ['project_manager', 'analyst', 'developer'],
                'template_data' => [
                    'project_phases' => [
                        'Data Source Identification',
                        'Data Collection & ETL',
                        'Data Analysis',
                        'Dashboard Development',
                        'Testing & Validation',
                        'Deployment'
                    ],
                    'default_tasks' => [
                        'Define KPIs and metrics',
                        'Data source mapping',
                        'ETL pipeline setup',
                        'Statistical analysis',
                        'Dashboard wireframes',
                        'Interactive visualizations',
                        'Data validation',
                        'Performance testing',
                        'User training'
                    ],
                    'completion_criteria' => [
                        'All data sources integrated',
                        'Visualizations accurate',
                        'Performance acceptable',
                        'User training completed'
                    ]
                ],
                'is_active' => true
            ],
            [
                'name' => 'Brand Identity Design',
                'description' => 'Complete brand identity and visual design project',
                'category' => 'design',
                'default_boards' => ['Research', 'Concept', 'Design', 'Refinement', 'Delivery'],
                'estimated_duration_days' => 45,
                'required_roles' => ['project_manager', 'designer'],
                'template_data' => [
                    'project_phases' => [
                        'Brand Research',
                        'Concept Development',
                        'Logo Design',
                        'Brand Guidelines',
                        'Asset Creation',
                        'Final Delivery'
                    ],
                    'default_tasks' => [
                        'Market analysis',
                        'Competitor research',
                        'Brand positioning',
                        'Logo concepts',
                        'Color palette',
                        'Typography selection',
                        'Brand guidelines document',
                        'Business card design',
                        'Letterhead design',
                        'Asset delivery'
                    ],
                    'completion_criteria' => [
                        'Client approval received',
                        'All deliverables completed',
                        'Brand guidelines finalized',
                        'Assets in required formats'
                    ]
                ],
                'is_active' => true
            ],
            [
                'name' => 'Software Testing Project',
                'description' => 'Comprehensive software quality assurance and testing',
                'category' => 'other',
                'default_boards' => ['Planning', 'Test Design', 'Execution', 'Bug Tracking', 'Reporting'],
                'estimated_duration_days' => 60,
                'required_roles' => ['project_manager', 'tester'],
                'template_data' => [
                    'project_phases' => [
                        'Test Planning',
                        'Test Case Design',
                        'Test Environment Setup',
                        'Test Execution',
                        'Defect Management',
                        'Test Reporting'
                    ],
                    'default_tasks' => [
                        'Test strategy document',
                        'Test plan creation',
                        'Test case writing',
                        'Environment setup',
                        'Smoke testing',
                        'Functional testing',
                        'Performance testing',
                        'Security testing',
                        'Bug reporting',
                        'Test closure'
                    ],
                    'completion_criteria' => [
                        'All test cases executed',
                        'Critical bugs fixed',
                        'Test coverage achieved',
                        'Final test report delivered'
                    ]
                ],
                'is_active' => true
            ],
            [
                'name' => 'Content Marketing Campaign',
                'description' => 'Multi-channel content marketing and social media campaign',
                'category' => 'marketing',
                'default_boards' => ['Strategy', 'Content Creation', 'Publishing', 'Promotion', 'Analysis'],
                'estimated_duration_days' => 90,
                'required_roles' => ['project_manager', 'marketing_specialist', 'content_writer'],
                'template_data' => [
                    'project_phases' => [
                        'Content Strategy',
                        'Content Calendar',
                        'Content Creation',
                        'Multi-channel Publishing',
                        'Community Management',
                        'Performance Analysis'
                    ],
                    'default_tasks' => [
                        'Audience research',
                        'Content audit',
                        'Editorial calendar',
                        'Blog posts creation',
                        'Social media content',
                        'Video content',
                        'Email campaigns',
                        'Influencer outreach',
                        'Engagement tracking',
                        'ROI analysis'
                    ],
                    'completion_criteria' => [
                        'Content calendar completed',
                        'Engagement targets met',
                        'Lead generation goals achieved',
                        'Campaign ROI positive'
                    ]
                ],
                'is_active' => true
            ],
            [
                'name' => 'API Development',
                'description' => 'RESTful API development with documentation',
                'category' => 'web_development',
                'default_boards' => ['Planning', 'Design', 'Development', 'Testing', 'Documentation', 'Deployment'],
                'estimated_duration_days' => 80,
                'required_roles' => ['project_manager', 'developer'],
                'template_data' => [
                    'project_phases' => [
                        'Requirements Analysis',
                        'API Design',
                        'Database Schema',
                        'Endpoint Development',
                        'Authentication & Security',
                        'Testing & Documentation',
                        'Deployment'
                    ],
                    'default_tasks' => [
                        'API specification',
                        'Database design',
                        'Authentication setup',
                        'CRUD endpoints',
                        'Data validation',
                        'Error handling',
                        'Rate limiting',
                        'Unit testing',
                        'Integration testing',
                        'API documentation',
                        'Deployment configuration'
                    ],
                    'completion_criteria' => [
                        'All endpoints functional',
                        'Security measures implemented',
                        'Documentation complete',
                        'Performance benchmarks met'
                    ]
                ],
                'is_active' => true
            ]
        ];

        foreach ($additionalTemplates as $template) {
            ProjectTemplate::firstOrCreate(
                ['name' => $template['name']],
                $template
            );
        }

        // Update usage counts for realistic data
        $templates = ProjectTemplate::all();
        foreach ($templates as $template) {
            $template->usage_count = rand(0, 25);
            $template->save();
        }
    }
}