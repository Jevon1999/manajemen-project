<?php

namespace App\Http\Controllers;

use App\Models\Card;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CardController extends Controller
{
    /**
     * Get detailed information about a specific task/card
     */
    public function getTaskDetails($id): JsonResponse
    {
        try {
            $card = Card::with([
                'board.project',
                'assignments.user',
                'subtasks',
                'comments.user'
            ])->findOrFail($id);

            $taskDetails = [
                'id' => $card->card_id,
                'title' => $card->title,
                'description' => $card->description,
                'status' => $card->status,
                'priority' => $card->priority,
                'due_date' => $card->due_date,
                'created_at' => $card->created_at,
                'updated_at' => $card->updated_at,
                'project_name' => $card->board->project->project_name ?? null,
                'board_name' => $card->board->board_name ?? null,
                'assignees' => $card->assignments->map(function ($assignment) {
                    return [
                        'id' => $assignment->user->user_id,
                        'full_name' => $assignment->user->full_name,
                        'email' => $assignment->user->email
                    ];
                }),
                'subtasks' => $card->subtasks->map(function ($subtask) {
                    return [
                        'id' => $subtask->subtask_id,
                        'title' => $subtask->title,
                        'completed' => $subtask->completed,
                        'created_at' => $subtask->created_at
                    ];
                }),
                'comments' => $card->comments->map(function ($comment) {
                    return [
                        'id' => $comment->comment_id,
                        'content' => $comment->content,
                        'user_name' => $comment->user->full_name,
                        'created_at' => $comment->created_at
                    ];
                })->sortByDesc('created_at')->take(5)->values()
            ];

            return response()->json($taskDetails);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Task not found or could not be loaded',
                'message' => $e->getMessage()
            ], 404);
        }
    }
}
