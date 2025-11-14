<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\RespondsWithJson;
use App\Models\CardComment;
use App\Models\Card;
use App\Http\Resources\CardCommentResource;
use Illuminate\Http\Request;

class CardCommentController extends Controller
{
    use RespondsWithJson;

    /**
     * Get comments for a card
     */
    public function index(Request $request, $cardId)
    {
        $card = Card::findOrFail($cardId);
        $this->authorizeCardAccess($request->user(), $card);

        $comments = CardComment::where('card_id', $cardId)
            ->with(['user', 'replies.user'])
            ->whereNull('parent_id')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return $this->successCollection(
            CardCommentResource::collection($comments), 
            'Comments retrieved'
        );
    }

    /**
     * Store a new comment
     */
    public function store(Request $request, $cardId)
    {
        $card = Card::findOrFail($cardId);
        $this->authorizeCardAccess($request->user(), $card);

        $data = $request->validate([
            'comment' => ['required', 'string'],
            'is_progress_update' => ['nullable', 'boolean'],
            'progress_percentage' => ['nullable', 'integer', 'min:0', 'max:100'],
            'comment_type' => ['nullable', 'in:general,progress,blocker,question,feedback'],
            'parent_id' => ['nullable', 'exists:card_comments,comment_id'],
        ]);

        $data['card_id'] = $cardId;
        $data['user_id'] = $request->user()->user_id;
        $data['comment_type'] = $data['comment_type'] ?? 'general';

        $comment = CardComment::create($data);

        // Update card if progress update
        if ($data['is_progress_update'] ?? false) {
            $card->update([
                'last_progress_update' => now(),
                'completion_percentage' => $data['progress_percentage'] ?? $card->completion_percentage,
                'has_progress_comment_today' => true,
            ]);
        }

        \App\Models\ActivityLog::logActivity(
            'added_comment',
            'card_comment',
            $comment->comment_id,
            "Commented on task '{$card->title}'"
        );

        return $this->successResource(new CardCommentResource($comment->load('user')), 'Comment added', 201);
    }

    /**
     * Update a comment
     */
    public function update(Request $request, $cardId, $commentId)
    {
        $comment = CardComment::where('comment_id', $commentId)
            ->where('card_id', $cardId)
            ->firstOrFail();

        if ($comment->user_id !== $request->user()->user_id) {
            return $this->error('Unauthorized', 403);
        }

        $data = $request->validate([
            'comment' => ['sometimes', 'string'],
            'progress_percentage' => ['nullable', 'integer', 'min:0', 'max:100'],
        ]);

        $comment->update($data);
        return $this->successResource(new CardCommentResource($comment->load('user')), 'Comment updated');
    }

    /**
     * Delete a comment
     */
    public function destroy(Request $request, $cardId, $commentId)
    {
        $comment = CardComment::where('comment_id', $commentId)
            ->where('card_id', $cardId)
            ->firstOrFail();

        if ($comment->user_id !== $request->user()->user_id && !$request->user()->isAdmin()) {
            return $this->error('Unauthorized', 403);
        }

        $comment->delete();
        return $this->success(null, 'Comment deleted');
    }

    private function authorizeCardAccess($user, $card)
    {
        if ($user->isAdmin()) return;
        
        $isAssigned = $card->assignments()->where('user_id', $user->user_id)->exists();
        $isMember = $card->board->project->members()->where('user_id', $user->user_id)->exists();

        abort_unless($isAssigned || $isMember, 403, 'Unauthorized');
    }
}
