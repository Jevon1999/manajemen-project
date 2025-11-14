<div id="create-project-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-40 backdrop-blur-sm">
    <div class="ph-card ph-card-elevated w-full h-full sm:h-auto sm:max-w-2xl sm:rounded-lg mx-0 sm:mx-4 overflow-auto sm:overflow-hidden">
        <div class="ph-card-header">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold" style="color: var(--ph-gray-900)">Create New Project</h3>
                <button type="button" onclick="closeCreateProjectModal()" class="ph-btn-icon" style="background-color: var(--ph-gray-100); color: var(--ph-gray-600);">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
        <form method="POST" action="{{ route('projects.store') }}" class="ph-card-body space-y-6" novalidate>
            @csrf
            
            <div>
                <label for="project_name" class="block text-sm font-medium mb-2" style="color: var(--ph-gray-700)">Project Name</label>
                <input id="project_name" name="project_name" required class="ph-input" placeholder="Enter project name" />
            </div>

            <div>
                <label for="description" class="block text-sm font-medium mb-2" style="color: var(--ph-gray-700)">Description</label>
                <textarea id="description" name="description" rows="3" class="ph-input" placeholder="Describe your project..."></textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="start_date" class="block text-sm font-medium mb-2" style="color: var(--ph-gray-700)">Start Date</label>
                    <input id="start_date" name="start_date" type="date" class="ph-input" />
                </div>
                <div>
                    <label for="end_date" class="block text-sm font-medium mb-2" style="color: var(--ph-gray-700)">End Date</label>
                    <input id="end_date" name="end_date" type="date" class="ph-input" />
                </div>
            </div>

            <!-- Status Toggle Section -->
            <div class="text-center">
                <label class="block text-sm font-medium mb-4" style="color: var(--ph-gray-700)">Project Status</label>
                
                <div class="flex flex-wrap items-center justify-center gap-2 p-2 rounded-xl w-full" style="background-color: var(--ph-gray-100);">
                    <input type="hidden" id="status" name="status" value="planning">
                    
                    <button type="button" class="status-toggle active" data-status="planning">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        Planning
                    </button>
                    
                    <button type="button" class="status-toggle" data-status="active">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        Active
                    </button>
                    
                    <button type="button" class="status-toggle" data-status="completed">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Completed
                    </button>
                    
                    <button type="button" class="status-toggle" data-status="on-hold">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        On Hold
                    </button>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row justify-center items-stretch sm:items-center sm:space-x-4 space-y-3 sm:space-y-0 pt-4 w-full">
                <button type="button" onclick="closeCreateProjectModal()" class="ph-btn ph-btn-secondary px-6 w-full sm:w-auto">
                    Cancel
                </button>
                <button type="submit" class="ph-btn ph-btn-primary px-6 w-full sm:w-auto">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Create Project
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    .status-toggle {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: var(--ph-space-2) var(--ph-space-4);
        margin: 0 2px;
        border-radius: var(--ph-radius-lg);
        font-size: var(--ph-text-sm);
        font-weight: 500;
        border: none;
        cursor: pointer;
        transition: all 0.18s ease-in-out;
        position: relative;
        overflow: hidden;
        background-color: transparent;
        color: var(--ph-gray-600);
        min-width: 84px;
    }

    .status-toggle:hover {
        background-color: var(--ph-gray-200);
        transform: translateY(-1px);
    }

    .status-toggle.active {
        background-color: var(--ph-primary-500);
        color: white;
        box-shadow: var(--ph-shadow-md);
        transform: translateY(-2px);
    }

    .status-toggle.active[data-status="planning"] {
        background: linear-gradient(135deg, var(--ph-warning), var(--ph-warning-dark));
    }

    .status-toggle.active[data-status="active"] {
        background: linear-gradient(135deg, var(--ph-success), var(--ph-success-dark));
    }

    .status-toggle.active[data-status="completed"] {
        background: linear-gradient(135deg, var(--ph-info), var(--ph-info-dark));
    }

    .status-toggle.active[data-status="on-hold"] {
        background: linear-gradient(135deg, var(--ph-error), var(--ph-error-dark));
    }

    .status-toggle::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.18), transparent);
        transition: left 0.45s ease-in-out;
    }

    .status-toggle:hover::before {
        left: 100%;
    }

    /* Modal enhancements */
    #create-project-modal {
        animation: fadeIn 0.25s ease-out;
    }

    #create-project-modal .ph-card {
        animation: slideIn 0.28s ease-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @keyframes slideIn {
        from { 
            opacity: 0; 
            transform: translateY(-30px) scale(0.98); 
        }
        to { 
            opacity: 1; 
            transform: translateY(0) scale(1); 
        }
    }

    /* Form enhancements */
    .ph-input:focus {
        transform: translateY(-1px);
        box-shadow: 0 6px 20px rgba(59, 130, 246, 0.12);
    }

    /* Mobile-specific adjustments */
    @media (max-width: 640px) {
        /* make modal card cover full viewport on small devices for better usability */
        #create-project-modal .ph-card {
            border-radius: 0;
            height: 100%;
            max-height: 100%;
        }

        /* compact status toggles on small screens */
        .status-toggle {
            min-width: 72px;
            padding: var(--ph-space-2) var(--ph-space-3);
            font-size: 0.8125rem; /* ~13px */
        }

        /* ensure form scrolls inside the modal and buttons are comfortable */
        #create-project-modal form {
            padding-bottom: var(--ph-space-6);
        }
    }
</style>

<script>
    function openCreateProjectModal() {
        const modal = document.getElementById('create-project-modal');
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            // Focus first input
            setTimeout(() => {
                const firstInput = modal.querySelector('#project_name');
                if (firstInput) firstInput.focus();
            }, 100);
        }
    }

    function closeCreateProjectModal() {
        const modal = document.getElementById('create-project-modal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            // Reset form
            const form = modal.querySelector('form');
            if (form) form.reset();
            // Reset status toggle
            const toggles = modal.querySelectorAll('.status-toggle');
            toggles.forEach(toggle => toggle.classList.remove('active'));
            const defaultToggle = modal.querySelector('[data-status="planning"]');
            if (defaultToggle) defaultToggle.classList.add('active');
            document.getElementById('status').value = 'planning';
        }
    }

    // Status toggle functionality
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('create-project-modal');
        if (modal) {
            const statusToggles = modal.querySelectorAll('.status-toggle');
            const statusInput = modal.querySelector('#status');

            statusToggles.forEach(toggle => {
                toggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Remove active class from all toggles
                    statusToggles.forEach(t => t.classList.remove('active'));
                    
                    // Add active class to clicked toggle
                    this.classList.add('active');
                    
                    // Update hidden input value
                    const status = this.getAttribute('data-status');
                    statusInput.value = status;
                    
                    // Add a subtle pulse effect
                    this.style.transform = 'scale(0.95)';
                    setTimeout(() => {
                        this.style.transform = '';
                    }, 100);
                });
            });
        }
    });

    // Close modal on ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeCreateProjectModal();
    });

    // Close modal on backdrop click
    document.addEventListener('click', function(e) {
        const modal = document.getElementById('create-project-modal');
        if (e.target === modal) {
            closeCreateProjectModal();
        }
    });

    // Form validation enhancements
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('#create-project-modal form');
        if (form) {
            form.addEventListener('submit', function(e) {
                const projectName = document.getElementById('project_name');
                if (!projectName.value.trim()) {
                    e.preventDefault();
                    projectName.focus();
                    projectName.style.borderColor = 'var(--ph-error)';
                    
                    // Show error message briefly
                    const errorMsg = document.createElement('div');
                    errorMsg.textContent = 'Project name is required';
                    errorMsg.style.color = 'var(--ph-error)';
                    errorMsg.style.fontSize = 'var(--ph-text-xs)';
                    errorMsg.style.marginTop = 'var(--ph-space-1)';
                    
                    const existingError = projectName.parentNode.querySelector('.error-message');
                    if (existingError) existingError.remove();
                    
                    errorMsg.classList.add('error-message');
                    projectName.parentNode.appendChild(errorMsg);
                    
                    setTimeout(() => {
                        if (errorMsg.parentNode) errorMsg.remove();
                        projectName.style.borderColor = '';
                    }, 3000);
                }
            });
        }
    });
</script>
