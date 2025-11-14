# Subtask System Documentation

## Overview
The subtask system has been fully implemented to allow users to create todo-list items within their assigned tasks. This system uses a task-based architecture (not card-based) with priority levels, completion tracking, and full CRUD capabilities.

## Database Schema

### Table: `subtasks`
| Column | Type | Description |
|--------|------|-------------|
| `subtask_id` | bigint (PK) | Primary key |
| `task_id` | bigint (FK) | References tasks table (cascade delete) |
| `title` | varchar(255) | Subtask title (required) |
| `description` | text | Optional detailed description |
| `priority` | enum | low, medium, high (default: medium) |
| `is_completed` | boolean | Completion status (default: false) |
| `created_by` | bigint (FK) | References users table |
| `completed_at` | timestamp | When subtask was marked complete |
| `created_at` | timestamp | Creation timestamp |
| `updated_at` | timestamp | Last update timestamp |

### Indexes
- `task_id` (foreign key index)
- `created_by` (foreign key index)
- `is_completed` (query optimization)

## Model: `App\Models\Subtask`

### Relations
```php
$subtask->task;      // belongsTo Task
$subtask->creator;   // belongsTo User
$task->subtasks;     // hasMany Subtask
```

### Constants
```php
Subtask::PRIORITY_LOW     // 'low'
Subtask::PRIORITY_MEDIUM  // 'medium'
Subtask::PRIORITY_HIGH    // 'high'
```

### Methods
```php
$subtask->markAsCompleted();   // Sets is_completed=true, completed_at=now()
$subtask->markAsIncomplete();  // Sets is_completed=false, completed_at=null
```

### Scopes
```php
Subtask::completed();           // Get completed subtasks
Subtask::incomplete();          // Get incomplete subtasks
Subtask::byPriority('high');   // Filter by priority (low/medium/high)
```

## Controller: `App\Http\Controllers\SubtaskController`

### Authorization
All methods verify that the authenticated user is assigned to the task before allowing operations.

### Methods

#### 1. Store New Subtask
**Endpoint:** `POST /tasks/{task}/subtasks`

**Request:**
```json
{
    "title": "Setup development environment",
    "description": "Install dependencies and configure local env",
    "priority": "high"
}
```

**Validation:**
- `title`: required, string, max 255 characters
- `description`: optional, string
- `priority`: required, enum (low, medium, high)

**Response:**
```json
{
    "success": true,
    "message": "Subtask berhasil ditambahkan.",
    "subtask": {
        "subtask_id": 1,
        "task_id": 5,
        "title": "Setup development environment",
        "description": "Install dependencies and configure local env",
        "priority": "high",
        "is_completed": false,
        "created_by": 12,
        "completed_at": null,
        "creator": {
            "user_id": 12,
            "full_name": "John Doe"
        }
    }
}
```

#### 2. Update Subtask
**Endpoint:** `PUT /tasks/{task}/subtasks/{subtask}`

**Request:**
```json
{
    "title": "Updated title",
    "description": "Updated description",
    "priority": "medium"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Subtask berhasil diupdate.",
    "subtask": {...}
}
```

#### 3. Toggle Completion
**Endpoint:** `POST /tasks/{task}/subtasks/{subtask}/toggle`

**Response:**
```json
{
    "success": true,
    "message": "Subtask ditandai sebagai selesai!",
    "subtask": {
        "subtask_id": 1,
        "is_completed": true,
        "completed_at": "2025-11-09 17:35:42"
    },
    "statistics": {
        "total": 5,
        "completed": 3,
        "progress": 60
    }
}
```

#### 4. Delete Subtask
**Endpoint:** `DELETE /tasks/{task}/subtasks/{subtask}`

**Response:**
```json
{
    "success": true,
    "message": "Subtask berhasil dihapus."
}
```

## Routes

All routes are defined in `routes/web.php`:

```php
Route::prefix('tasks/{task}/subtasks')->name('tasks.subtasks.')->group(function () {
    Route::post('/', [SubtaskController::class, 'store'])->name('store');
    Route::put('/{subtask}', [SubtaskController::class, 'update'])->name('update');
    Route::post('/{subtask}/toggle', [SubtaskController::class, 'toggleComplete'])->name('toggle');
    Route::delete('/{subtask}', [SubtaskController::class, 'destroy'])->name('destroy');
});
```

## Usage Examples

### Creating Subtasks in Blade View
```html
<form action="{{ route('tasks.subtasks.store', $task) }}" method="POST" id="subtaskForm">
    @csrf
    <input type="text" name="title" placeholder="Subtask title" required>
    <textarea name="description" placeholder="Description (optional)"></textarea>
    <select name="priority">
        <option value="low">🟢 Low</option>
        <option value="medium" selected>🟡 Medium</option>
        <option value="high">🔴 High</option>
    </select>
    <button type="submit">Add Subtask</button>
</form>
```

### Displaying Subtasks
```html
<div class="subtask-list">
    @foreach($task->subtasks()->orderBy('priority', 'desc')->get() as $subtask)
        <div class="subtask-item {{ $subtask->is_completed ? 'completed' : '' }}">
            <input type="checkbox" 
                   data-subtask-id="{{ $subtask->subtask_id }}"
                   {{ $subtask->is_completed ? 'checked' : '' }}
                   onchange="toggleSubtask(this)">
            
            <span class="priority-badge priority-{{ $subtask->priority }}">
                {{ strtoupper($subtask->priority) }}
            </span>
            
            <span class="subtask-title">{{ $subtask->title }}</span>
            
            <button onclick="editSubtask({{ $subtask->subtask_id }})">Edit</button>
            <button onclick="deleteSubtask({{ $subtask->subtask_id }})">Delete</button>
        </div>
    @endforeach
</div>

<!-- Progress Bar -->
<div class="progress">
    @php
        $total = $task->subtasks()->count();
        $completed = $task->subtasks()->completed()->count();
        $progress = $total > 0 ? round(($completed / $total) * 100) : 0;
    @endphp
    <div class="progress-bar" style="width: {{ $progress }}%">
        {{ $progress }}%
    </div>
</div>
```

### AJAX Examples
```javascript
// Toggle completion
function toggleSubtask(checkbox) {
    const subtaskId = checkbox.dataset.subtaskId;
    const taskId = {{ $task->task_id }};
    
    fetch(`/tasks/${taskId}/subtasks/${subtaskId}/toggle`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update UI with new statistics
            document.querySelector('.progress-bar').style.width = data.statistics.progress + '%';
            document.querySelector('.progress-text').textContent = 
                `${data.statistics.completed} / ${data.statistics.total}`;
        }
    });
}

// Delete subtask
function deleteSubtask(subtaskId) {
    if (!confirm('Apakah Anda yakin ingin menghapus subtask ini?')) return;
    
    const taskId = {{ $task->task_id }};
    
    fetch(`/tasks/${taskId}/subtasks/${subtaskId}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload(); // Reload to update list
        }
    });
}
```

## Migration Files

1. **2025_09_08_043257_create_subtasks_table.php** - Original (card-based, now obsolete)
2. **2025_11_09_172626_update_subtasks_table_for_tasks.php** - Migration that updates table structure to task-based system

## Testing

Use the provided test script to verify functionality:

```bash
php test_subtask_system.php
```

This script tests:
- ✅ Subtask creation with all priority levels
- ✅ Model relations (task, creator)
- ✅ Completion methods (markAsCompleted, markAsIncomplete)
- ✅ Query scopes (completed, incomplete, byPriority)
- ✅ Progress calculation
- ✅ Update operations
- ✅ Deletion

## Security & Authorization

- **User Authorization:** Users can only create/edit/delete subtasks on tasks assigned to them
- **Leader Access:** Project leaders can view all subtasks but cannot modify them (unless they're also assigned to the task)
- **Cascade Deletion:** When a task is deleted, all its subtasks are automatically deleted
- **Foreign Key Constraints:** Ensures data integrity with proper relationships

## Next Steps

To complete the subtask feature, you need to:

1. **Update Task Detail View** (`resources/views/tasks/show.blade.php`)
   - Add subtask creation form
   - Display subtask list with checkboxes
   - Show progress bar
   - Add inline editing capability

2. **Add JavaScript** for AJAX operations
   - Toggle completion without page reload
   - Inline editing
   - Real-time progress updates

3. **Add CSS Styles** for subtask UI
   - Priority badges (🔴 High, 🟡 Medium, 🟢 Low)
   - Completed state styling (strikethrough, greyed out)
   - Progress bar design
   - Responsive mobile layout

4. **Add Notifications** (optional)
   - Notify when all subtasks completed
   - Remind about pending high-priority subtasks

## Benefits

✅ **Better Task Breakdown:** Users can break large tasks into manageable pieces  
✅ **Progress Tracking:** Visual progress bar shows completion percentage  
✅ **Priority Management:** High/medium/low priorities help focus on important items  
✅ **User Autonomy:** Users control their own todo list within assigned tasks  
✅ **Leader Visibility:** Leaders can see team progress at subtask level  
✅ **Clean Architecture:** Proper relations, validation, and authorization

---

**Status:** ✅ Backend Complete | ⏳ Frontend Pending  
**Last Updated:** 2025-11-09  
**Migration Status:** Applied successfully  
**Test Status:** Ready for testing (requires tasks in database)
