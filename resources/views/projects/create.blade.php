@extends('layout.app')

@section('title', 'Create New Project')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" rel="stylesheet">
<style>
    .template-card {
        transition: all 0.3s ease;
        cursor: pointer;
    }
    .template-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    }
    .template-card.selected {
        border-color: #3b82f6;
        background-color: #eff6ff;
    }
    .step-indicator {
        display: flex;
        justify-content: center;
        margin-bottom: 2rem;
    }
    .step {
        display: flex;
        align-items: center;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        margin: 0 0.5rem;
        background: #f3f4f6;
        color: #6b7280;
        font-size: 0.875rem;
    }
    .step.active {
        background: #3b82f6;
        color: white;
    }
    .step.completed {
        background: #10b981;
        color: white;
    }
    .form-section {
        display: none;
    }
    .form-section.active {
        display: block;
    }
    .file-drop-zone {
        border: 2px dashed #d1d5db;
        border-radius: 8px;
        padding: 2rem;
        text-align: center;
        transition: all 0.3s ease;
    }
    .file-drop-zone:hover {
        border-color: #3b82f6;
        background-color: #f8fafc;
    }
    .file-drop-zone.dragover {
        border-color: #3b82f6;
        background-color: #eff6ff;
    }
    .board-input {
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        padding: 0.75rem;
        margin-bottom: 0.5rem;
    }
    .remove-board {
        color: #ef4444;
        cursor: pointer;
        padding: 0.25rem;
    }
    .remove-board:hover {
        color: #dc2626;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">Create New Project</h1>
                    <p class="text-muted">Set up a new project with customizable templates and configurations</p>
                </div>
                <a href="{{ route('projects.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Projects
                </a>
            </div>

            <!-- Step Indicator -->
            <div class="step-indicator">
                <div class="step active" data-step="1">
                    <i class="fas fa-clipboard-list me-2"></i>
                    Basic Info
                </div>
                <div class="step" data-step="2">
                    <i class="fas fa-users me-2"></i>
                    Team & Settings
                </div>
                <div class="step" data-step="3">
                    <i class="fas fa-cog me-2"></i>
                    Configuration
                </div>
                <div class="step" data-step="4">
                    <i class="fas fa-check-circle me-2"></i>
                    Review
                </div>
            </div>

            <!-- Main Form -->
            <form id="projectForm" method="POST" action="{{ route('projects.store') }}" enctype="multipart/form-data">
                @csrf

                <!-- Step 1: Basic Information -->
                <div class="form-section active" data-section="1">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h4 class="card-title mb-4">
                                <i class="fas fa-info-circle text-primary me-2"></i>
                                Project Basic Information
                            </h4>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="project_name" class="form-label">Project Name *</label>
                                        <input type="text" 
                                               class="form-control @error('project_name') is-invalid @enderror" 
                                               id="project_name" 
                                               name="project_name" 
                                               value="{{ old('project_name') }}" 
                                               required>
                                        @error('project_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">Choose a unique and descriptive project name</div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="category" class="form-label">Project Category *</label>
                                        <select class="form-select @error('category') is-invalid @enderror" 
                                                id="category" 
                                                name="category" 
                                                required>
                                            <option value="">Select Category</option>
                                            @foreach(\App\Models\ProjectTemplate::getCategories() as $value => $label)
                                                <option value="{{ $value }}" {{ old('category') == $value ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('category')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                          id="description" 
                                          name="description" 
                                          rows="4" 
                                          placeholder="Describe the project goals, scope, and requirements...">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="start_date" class="form-label">Start Date *</label>
                                        <input type="date" 
                                               class="form-control @error('start_date') is-invalid @enderror" 
                                               id="start_date" 
                                               name="start_date" 
                                               value="{{ old('start_date', date('Y-m-d')) }}" 
                                               required>
                                        @error('start_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="end_date" class="form-label">End Date</label>
                                        <input type="date" 
                                               class="form-control @error('end_date') is-invalid @enderror" 
                                               id="end_date" 
                                               name="end_date" 
                                               value="{{ old('end_date') }}">
                                        @error('end_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="priority" class="form-label">Priority</label>
                                        <select class="form-select @error('priority') is-invalid @enderror" 
                                                id="priority" 
                                                name="priority">
                                            @foreach(\App\Models\Project::getPriorityOptions() as $value => $label)
                                                <option value="{{ $value }}" {{ old('priority', 'medium') == $value ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('priority')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Project Templates -->
                            <div class="mb-4">
                                <label class="form-label">Choose Project Template (Optional)</label>
                                <div class="row" id="templateContainer">
                                    <div class="col-md-4">
                                        <div class="template-card card h-100 border" data-template-id="">
                                            <div class="card-body text-center">
                                                <i class="fas fa-plus-circle fa-2x text-muted mb-2"></i>
                                                <h6>Blank Project</h6>
                                                <p class="text-muted small">Start from scratch</p>
                                            </div>
                                        </div>
                                    </div>
                                    @foreach($templates as $template)
                                        <div class="col-md-4 mb-3">
                                            <div class="template-card card h-100 border" 
                                                 data-template-id="{{ $template->id }}"
                                                 data-boards="{{ json_encode($template->default_boards) }}"
                                                 data-duration="{{ $template->estimated_duration_days }}">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                                        <h6 class="card-title mb-0">{{ $template->name }}</h6>
                                                        <span class="badge bg-primary">{{ ucfirst(str_replace('_', ' ', $template->category)) }}</span>
                                                    </div>
                                                    <p class="card-text text-muted small">{{ $template->description }}</p>
                                                    <div class="mt-auto">
                                                        <small class="text-muted">
                                                            <i class="fas fa-clock me-1"></i>~{{ $template->estimated_duration_days }} days
                                                        </small>
                                                        @if($template->usage_count > 0)
                                                            <br><small class="text-success">
                                                                <i class="fas fa-check me-1"></i>Used {{ $template->usage_count }} times
                                                            </small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <input type="hidden" name="template_id" id="selectedTemplate">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Team & Settings -->
                <div class="form-section" data-section="2">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h4 class="card-title mb-4">
                                <i class="fas fa-users text-primary me-2"></i>
                                Team & Project Settings
                            </h4>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="leader_id" class="form-label">Project Leader</label>
                                        <select class="form-select @error('leader_id') is-invalid @enderror" 
                                                id="leader_id" 
                                                name="leader_id">
                                            <option value="">Select Project Leader</option>
                                            @foreach($leaders as $leader)
                                                <option value="{{ $leader->user_id }}" {{ old('leader_id') == $leader->user_id ? 'selected' : '' }}>
                                                    {{ $leader->name }} ({{ $leader->email }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('leader_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="budget" class="form-label">Project Budget</label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" 
                                                   class="form-control @error('budget') is-invalid @enderror" 
                                                   id="budget" 
                                                   name="budget" 
                                                   value="{{ old('budget') }}" 
                                                   step="0.01" 
                                                   min="0"
                                                   placeholder="0.00">
                                        </div>
                                        @error('budget')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Project Permissions -->
                            <div class="row">
                                <div class="col-md-12">
                                    <label class="form-label">Project Permissions</label>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="notifications_enabled" name="notifications_enabled" value="1" {{ old('notifications_enabled', true) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="notifications_enabled">
                                                    Enable Notifications
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="public_visibility" name="public_visibility" value="1" {{ old('public_visibility') ? 'checked' : '' }}>
                                                <label class="form-check-label" for="public_visibility">
                                                    Public Visibility
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="allow_member_invite" name="allow_member_invite" value="1" {{ old('allow_member_invite', true) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="allow_member_invite">
                                                    Allow Member Invitations
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Configuration -->
                <div class="form-section" data-section="3">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h4 class="card-title mb-4">
                                <i class="fas fa-cog text-primary me-2"></i>
                                Project Configuration
                            </h4>

                            <!-- Initial Boards -->
                            <div class="mb-4">
                                <label class="form-label">Initial Project Boards</label>
                                <div id="boardContainer">
                                    <div class="board-item d-flex align-items-center mb-2">
                                        <input type="text" class="form-control board-input" name="initial_boards[]" value="To Do" placeholder="Board name">
                                        <button type="button" class="btn btn-sm remove-board ms-2" style="display:none;">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                                <button type="button" id="addBoard" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-plus me-1"></i>Add Board
                                </button>
                            </div>

                            <!-- Document Upload -->
                            <div class="mb-4">
                                <label class="form-label">Project Documents (Optional)</label>
                                <div class="file-drop-zone" id="fileDropZone">
                                    <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                                    <p class="mb-2">Drop files here or click to browse</p>
                                    <p class="text-muted small">Supported: PDF, DOC, XLS, PPT, Images, ZIP (Max: 10MB each)</p>
                                    <input type="file" id="documents" name="documents[]" multiple class="d-none" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.gif,.zip,.rar">
                                </div>
                                <div id="fileList" class="mt-3"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 4: Review -->
                <div class="form-section" data-section="4">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h4 class="card-title mb-4">
                                <i class="fas fa-check-circle text-primary me-2"></i>
                                Review & Create Project
                            </h4>

                            <div id="reviewContent">
                                <!-- Review content will be populated by JavaScript -->
                            </div>

                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="confirmCreate" required>
                                <label class="form-check-label" for="confirmCreate">
                                    I confirm that all information is correct and I want to create this project
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Navigation Buttons -->
                <div class="card shadow-sm mt-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <button type="button" id="prevBtn" class="btn btn-outline-secondary" style="display:none;">
                                <i class="fas fa-arrow-left me-2"></i>Previous
                            </button>
                            <div class="ms-auto">
                                <button type="button" id="saveAsDraft" class="btn btn-outline-info me-2">
                                    <i class="fas fa-save me-2"></i>Save as Draft
                                </button>
                                <button type="button" id="nextBtn" class="btn btn-primary">
                                    Next<i class="fas fa-arrow-right ms-2"></i>
                                </button>
                                <button type="submit" id="createBtn" class="btn btn-success" style="display:none;">
                                    <i class="fas fa-plus-circle me-2"></i>Create Project
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentStep = 1;
    const totalSteps = 4;
    
    // Initialize Choices.js for select elements
    const leaderSelect = new Choices('#leader_id', {
        searchEnabled: true,
        placeholder: true,
        placeholderValue: 'Select Project Leader'
    });

    // Template selection
    document.querySelectorAll('.template-card').forEach(card => {
        card.addEventListener('click', function() {
            // Remove previous selection
            document.querySelectorAll('.template-card').forEach(c => c.classList.remove('selected'));
            
            // Add selection to current card
            this.classList.add('selected');
            
            // Update hidden input
            const templateId = this.dataset.templateId;
            document.getElementById('selectedTemplate').value = templateId;
            
            // Update boards if template has default boards
            if (templateId && this.dataset.boards) {
                const boards = JSON.parse(this.dataset.boards);
                updateBoardsFromTemplate(boards);
            } else {
                // Reset to default boards
                updateBoardsFromTemplate(['To Do', 'In Progress', 'Done']);
            }
        });
    });

    // Board management
    document.getElementById('addBoard').addEventListener('click', function() {
        const container = document.getElementById('boardContainer');
        const boardItem = document.createElement('div');
        boardItem.className = 'board-item d-flex align-items-center mb-2';
        boardItem.innerHTML = `
            <input type="text" class="form-control board-input" name="initial_boards[]" placeholder="Board name">
            <button type="button" class="btn btn-sm remove-board ms-2">
                <i class="fas fa-times"></i>
            </button>
        `;
        container.appendChild(boardItem);
        
        // Add remove functionality
        boardItem.querySelector('.remove-board').addEventListener('click', function() {
            boardItem.remove();
            updateRemoveButtons();
        });
        
        updateRemoveButtons();
    });

    // File upload handling
    const fileDropZone = document.getElementById('fileDropZone');
    const fileInput = document.getElementById('documents');
    const fileList = document.getElementById('fileList');

    fileDropZone.addEventListener('click', () => fileInput.click());

    fileDropZone.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.classList.add('dragover');
    });

    fileDropZone.addEventListener('dragleave', function(e) {
        e.preventDefault();
        this.classList.remove('dragover');
    });

    fileDropZone.addEventListener('drop', function(e) {
        e.preventDefault();
        this.classList.remove('dragover');
        const files = e.dataTransfer.files;
        fileInput.files = files;
        displayFiles(files);
    });

    fileInput.addEventListener('change', function() {
        displayFiles(this.files);
    });

    // Step navigation
    document.getElementById('nextBtn').addEventListener('click', function() {
        if (validateCurrentStep()) {
            if (currentStep < totalSteps) {
                nextStep();
            }
        }
    });

    document.getElementById('prevBtn').addEventListener('click', function() {
        if (currentStep > 1) {
            prevStep();
        }
    });

    // Save as draft
    document.getElementById('saveAsDraft').addEventListener('click', function() {
        // Add draft status and submit
        const form = document.getElementById('projectForm');
        const draftInput = document.createElement('input');
        draftInput.type = 'hidden';
        draftInput.name = 'save_as_draft';
        draftInput.value = '1';
        form.appendChild(draftInput);
        form.submit();
    });

    function updateBoardsFromTemplate(boards) {
        const container = document.getElementById('boardContainer');
        container.innerHTML = '';
        
        boards.forEach((board, index) => {
            const boardItem = document.createElement('div');
            boardItem.className = 'board-item d-flex align-items-center mb-2';
            boardItem.innerHTML = `
                <input type="text" class="form-control board-input" name="initial_boards[]" value="${board}" placeholder="Board name">
                <button type="button" class="btn btn-sm remove-board ms-2" ${index === 0 ? 'style="display:none;"' : ''}>
                    <i class="fas fa-times"></i>
                </button>
            `;
            container.appendChild(boardItem);
            
            // Add remove functionality
            if (index > 0) {
                boardItem.querySelector('.remove-board').addEventListener('click', function() {
                    boardItem.remove();
                    updateRemoveButtons();
                });
            }
        });
        
        updateRemoveButtons();
    }

    function updateRemoveButtons() {
        const boardItems = document.querySelectorAll('.board-item');
        boardItems.forEach((item, index) => {
            const removeBtn = item.querySelector('.remove-board');
            if (removeBtn) {
                removeBtn.style.display = boardItems.length > 1 && index > 0 ? 'block' : 'none';
            }
        });
    }

    function displayFiles(files) {
        fileList.innerHTML = '';
        Array.from(files).forEach(file => {
            const fileItem = document.createElement('div');
            fileItem.className = 'alert alert-info d-flex justify-content-between align-items-center';
            fileItem.innerHTML = `
                <div>
                    <i class="fas fa-file me-2"></i>
                    <span>${file.name}</span>
                    <small class="text-muted ms-2">(${formatFileSize(file.size)})</small>
                </div>
                <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>
            `;
            fileList.appendChild(fileItem);
        });
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    function nextStep() {
        document.querySelector(`[data-section="${currentStep}"]`).classList.remove('active');
        document.querySelector(`[data-step="${currentStep}"]`).classList.add('completed');
        document.querySelector(`[data-step="${currentStep}"]`).classList.remove('active');
        
        currentStep++;
        
        document.querySelector(`[data-section="${currentStep}"]`).classList.add('active');
        document.querySelector(`[data-step="${currentStep}"]`).classList.add('active');
        
        updateButtons();
        
        if (currentStep === 4) {
            updateReview();
        }
    }

    function prevStep() {
        document.querySelector(`[data-section="${currentStep}"]`).classList.remove('active');
        document.querySelector(`[data-step="${currentStep}"]`).classList.remove('active');
        
        currentStep--;
        
        document.querySelector(`[data-section="${currentStep}"]`).classList.add('active');
        document.querySelector(`[data-step="${currentStep}"]`).classList.add('active');
        document.querySelector(`[data-step="${currentStep}"]`).classList.remove('completed');
        
        updateButtons();
    }

    function updateButtons() {
        document.getElementById('prevBtn').style.display = currentStep > 1 ? 'block' : 'none';
        document.getElementById('nextBtn').style.display = currentStep < totalSteps ? 'block' : 'none';
        document.getElementById('createBtn').style.display = currentStep === totalSteps ? 'block' : 'none';
    }

    function validateCurrentStep() {
        const currentSection = document.querySelector(`[data-section="${currentStep}"]`);
        const requiredFields = currentSection.querySelectorAll('[required]');
        
        let isValid = true;
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.classList.add('is-invalid');
                isValid = false;
            } else {
                field.classList.remove('is-invalid');
            }
        });
        
        return isValid;
    }

    function updateReview() {
        const formData = new FormData(document.getElementById('projectForm'));
        const reviewContent = document.getElementById('reviewContent');
        
        const selectedTemplate = document.querySelector('.template-card.selected');
        const templateName = selectedTemplate ? 
            (selectedTemplate.dataset.templateId ? selectedTemplate.querySelector('h6').textContent : 'Blank Project') : 
            'None';
            
        const leader = document.querySelector('#leader_id option:checked');
        const leaderName = leader && leader.value ? leader.textContent : 'Not assigned';
        
        const boards = Array.from(document.querySelectorAll('[name="initial_boards[]"]'))
            .map(input => input.value)
            .filter(value => value.trim());
            
        reviewContent.innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <h6>Basic Information</h6>
                    <dl class="row">
                        <dt class="col-sm-4">Project Name:</dt>
                        <dd class="col-sm-8">${formData.get('project_name') || 'Not specified'}</dd>
                        
                        <dt class="col-sm-4">Category:</dt>
                        <dd class="col-sm-8">${document.querySelector('#category option:checked')?.textContent || 'Not specified'}</dd>
                        
                        <dt class="col-sm-4">Priority:</dt>
                        <dd class="col-sm-8">${document.querySelector('#priority option:checked')?.textContent || 'Not specified'}</dd>
                        
                        <dt class="col-sm-4">Template:</dt>
                        <dd class="col-sm-8">${templateName}</dd>
                    </dl>
                </div>
                
                <div class="col-md-6">
                    <h6>Settings</h6>
                    <dl class="row">
                        <dt class="col-sm-4">Project Leader:</dt>
                        <dd class="col-sm-8">${leaderName}</dd>
                        
                        <dt class="col-sm-4">Budget:</dt>
                        <dd class="col-sm-8">${formData.get('budget') ? '$' + formData.get('budget') : 'Not specified'}</dd>
                        
                        <dt class="col-sm-4">Start Date:</dt>
                        <dd class="col-sm-8">${formData.get('start_date') || 'Not specified'}</dd>
                        
                        <dt class="col-sm-4">End Date:</dt>
                        <dd class="col-sm-8">${formData.get('end_date') || 'Not specified'}</dd>
                    </dl>
                </div>
            </div>
            
            <hr>
            
            <h6>Initial Boards</h6>
            <p>${boards.length > 0 ? boards.join(', ') : 'Default boards will be created'}</p>
            
            ${formData.get('description') ? `
            <h6>Description</h6>
            <p>${formData.get('description')}</p>
            ` : ''}
        `;
    }
});
</script>
@endpush