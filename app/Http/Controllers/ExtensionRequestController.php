<?php

namespace App\Http\Controllers;

use App\Models\ExtensionRequest;
use App\Models\Card;
use App\Models\Task;
use App\Helpers\NotificationHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExtensionRequestController extends Controller
{
    /**
     * Display pending extension requests for leaders
     * 
     * @param Request $request HTTP request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        /** @var Request $request */
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Only leaders can see extension requests
        if (!$user->isLeader() && !$user->isAdmin()) {
            abort(403, 'Unauthorized');
        }
        
        // Get projects where user is leader
        $projectIds = $user->ledProjects()->pluck('project_id');
        
        // Get pending requests for both cards and tasks in those projects
        $requests = ExtensionRequest::with(['card.board.project', 'task.project', 'requester'])
            ->where('status', 'pending')
            ->where(function($query) use ($projectIds) {
                // Cards: via board.project
                $query->whereHas('card.board.project', function($q) use ($projectIds) {
                    $q->whereIn('project_id', $projectIds);
                })
                // Tasks: direct project relation
                ->orWhereHas('task.project', function($q) use ($projectIds) {
                    $q->whereIn('project_id', $projectIds);
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return view('extension-requests.index', compact('requests'));
    }
    
    /**
     * Request extension for overdue task
     * 
     * @param Request $request HTTP request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        /** @var Request $request */
        
        $validated = $request->validate([
            'card_id' => 'nullable|exists:cards,card_id',
            'task_id' => 'nullable|exists:tasks,task_id',
            'reason' => 'required|string|min:10|max:500',
            'requested_deadline' => 'required|date|after:today',
        ]);
        
        // Must have either card_id or task_id
        if (empty($validated['card_id']) && empty($validated['task_id'])) {
            return response()->json([
                'success' => false,
                'message' => 'Either card_id or task_id is required'
            ], 400);
        }
        
        try {
            // Determine entity type and get entity
            if (!empty($validated['task_id'])) {
                $entity = Task::findOrFail($validated['task_id']);
                $entityType = 'task';
                $entityId = $entity->task_id;
                $assignedTo = $entity->assigned_to;
                $deadline = $entity->deadline;
                
                // Get project for notification
                $project = $entity->project;
            } else {
                $entity = Card::findOrFail($validated['card_id']);
                $entityType = 'card';
                $entityId = $entity->card_id;
                
                // Get assignment from assignments relation
                $assignment = $entity->assignments()->where('user_id', Auth::id())->first();
                if (!$assignment) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You are not assigned to this task'
                    ], 403);
                }
                $assignedTo = Auth::id();
                $deadline = $entity->due_date;
                
                // Get project for notification
                $project = $entity->board->project;
            }
            
            // Check if user is assigned to this task
            if ($entityType === 'task' && $assignedTo !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not assigned to this task'
                ], 403);
            }
            
            // Check if task is overdue
            if (!$entity->isOverdue()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Task is not overdue'
                ], 400);
            }
            
            // Check if already has pending request
            if ($entity->hasPendingExtensionRequest()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Extension request already pending'
                ], 400);
            }
            
            DB::beginTransaction();
            
            // Create extension request
            $extensionRequest = ExtensionRequest::create([
                'entity_type' => $entityType,
                'card_id' => $entityType === 'card' ? $entityId : null,
                'task_id' => $entityType === 'task' ? $entityId : null,
                'requested_by' => Auth::id(),
                'reason' => $validated['reason'],
                'old_deadline' => $deadline,
                'requested_deadline' => $validated['requested_deadline'],
                'status' => 'pending',
            ]);
            
            // Block the entity
            $entity->block('Pending deadline extension approval');
            
            // Notify project leader
            if ($project && $project->leader) {
                NotificationHelper::extensionRequested(
                    $extensionRequest,
                    $project->leader->user_id,
                    Auth::id()
                );
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Extension request submitted successfully',
                'request' => $extensionRequest
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Extension Request Store Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit extension request: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Approve extension request
     * 
     * @param Request $request HTTP request
     * @param int $id Extension request ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function approve(Request $request, $id)
    {
        /** @var Request $request */
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        try {
            $extensionRequest = ExtensionRequest::with(['card.board.project', 'task.project'])->findOrFail($id);
            
            // Get project and entity based on type
            if ($extensionRequest->entity_type === 'task') {
                $entity = $extensionRequest->task;
                if (!$entity) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Task not found'
                    ], 404);
                }
                $project = $entity->project;
                $deadlineField = 'deadline';
            } else {
                $entity = $extensionRequest->card;
                if (!$entity) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Card not found'
                    ], 404);
                }
                $project = $entity->board->project;
                $deadlineField = 'due_date';
            }
            
            // Check if user is project leader
            if ($project->leader_id !== $user->user_id && !$user->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }
            
            // Check if already reviewed
            if ($extensionRequest->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Request already reviewed'
                ], 400);
            }
            
            DB::beginTransaction();
            
            Log::info("Approving extension request {$id}", [
                'entity_type' => $extensionRequest->entity_type,
                'entity_id' => $extensionRequest->entity_type === 'task' ? $extensionRequest->task_id : $extensionRequest->card_id,
                'old_status' => $entity->status,
                'is_blocked' => $entity->is_blocked,
                'old_deadline' => $extensionRequest->old_deadline,
                'new_deadline' => $extensionRequest->requested_deadline,
            ]);
            
            // Approve request
            $extensionRequest->update([
                'status' => 'approved',
                'reviewed_by' => $user->user_id,
                'reviewed_at' => now(),
            ]);
            
            // Update entity deadline
            $entity->update([
                $deadlineField => $extensionRequest->requested_deadline,
            ]);
            
            // Unblock the entity FIRST (before status change)
            $entity->unblock();
            
            // Refresh entity to get updated state
            $entity->refresh();
            
            Log::info("Entity unblocked", [
                'entity_type' => $extensionRequest->entity_type,
                'is_blocked_after' => $entity->is_blocked,
                'current_status' => $entity->status,
            ]);
            
            // Change status from overdue back to in_progress
            // Support both 'overdue' status and tasks that were already 'in_progress' but blocked
            $oldStatus = $entity->status;
            
            if ($extensionRequest->entity_type === 'task') {
                // Always set to in_progress after extension approval (unless it's already done/review)
                if (in_array($entity->status, ['overdue', 'todo'])) {
                    // Status needs to change
                    DB::table('tasks')
                        ->where('task_id', $entity->task_id)
                        ->update([
                            'status' => 'in_progress',
                            'updated_at' => now(),
                        ]);
                    
                    $entity->refresh();
                    
                    Log::info("Task status updated to in_progress", [
                        'task_id' => $entity->task_id,
                        'old_status' => $oldStatus,
                        'new_status' => $entity->status,
                        'method' => 'direct_db_update',
                    ]);
                } elseif ($entity->status === 'in_progress') {
                    // Already in_progress, just log it
                    Log::info("Task already in_progress, keeping status", [
                        'task_id' => $entity->task_id,
                        'status' => $entity->status,
                    ]);
                } else {
                    // Status is review/done, don't change
                    Log::info("Task status is {$entity->status}, not changing", [
                        'task_id' => $entity->task_id,
                        'status' => $entity->status,
                    ]);
                }
            } elseif ($extensionRequest->entity_type === 'card') {
                // Always set to in_progress after extension approval
                if (in_array($entity->status, ['overdue', 'todo'])) {
                    DB::table('cards')
                        ->where('card_id', $entity->card_id)
                        ->update([
                            'status' => 'in_progress',
                            'updated_at' => now(),
                        ]);
                    
                    $entity->refresh();
                }
            }
            
            // Final verification - refresh to get latest state
            $entity->refresh();
            
            Log::info("Extension approved successfully", [
                'entity_type' => $extensionRequest->entity_type,
                'old_status' => $oldStatus,
                'new_status' => $entity->status,
                'is_blocked' => $entity->is_blocked,
                'block_reason' => $entity->block_reason,
                'deadline' => $entity->{$deadlineField},
            ]);
            
            // Notify developer
            NotificationHelper::extensionApproved(
                $extensionRequest,
                $extensionRequest->requested_by,
                $user->user_id
            );
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Extension request approved successfully. Task is now unblocked and ready to work.',
                'request' => $extensionRequest->fresh(),
                'task' => $extensionRequest->entity_type === 'task' ? [
                    'task_id' => $entity->task_id,
                    'status' => $entity->status,
                    'is_blocked' => $entity->is_blocked,
                    'deadline' => $entity->deadline,
                ] : null,
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error approving extension request: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve extension request: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Reject extension request
     * 
     * @param Request $request HTTP request
     * @param int $id Extension request ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function reject(Request $request, $id)
    {
        /** @var Request $request */
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        $validated = $request->validate([
            'reason' => 'required|string|min:10|max:500',
        ]);
        
        try {
            $extensionRequest = ExtensionRequest::with(['card.board.project', 'task.project'])->findOrFail($id);
            
            // Get project based on entity type
            if ($extensionRequest->entity_type === 'task') {
                $project = $extensionRequest->task->project;
            } else {
                $project = $extensionRequest->card->board->project;
            }
            
            // Check if user is project leader
            if ($project->leader_id !== $user->user_id && !$user->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }
            
            // Check if already reviewed
            if ($extensionRequest->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Request already reviewed'
                ], 400);
            }
            
            DB::beginTransaction();
            
            // Reject request
            $extensionRequest->update([
                'status' => 'rejected',
                'reviewed_by' => $user->user_id,
                'reviewed_at' => now(),
                'rejection_reason' => $validated['reason'],
            ]);
            
            // Entity remains blocked
            
            // Notify developer
            NotificationHelper::extensionRejected(
                $extensionRequest,
                $extensionRequest->requested_by,
                $user->user_id
            );
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Extension request rejected',
                'request' => $extensionRequest->fresh()
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error rejecting extension request: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject extension request: ' . $e->getMessage()
            ], 500);
        }
    }
}

