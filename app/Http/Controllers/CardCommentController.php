<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\CardComment;
use App\Models\ProjectMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Helpers\NotificationHelper;

class CardCommentController extends Controller
{
    /**
     * Get all comments for a card
     */
    public function index($cardId)
    {
        try {
            $card = Card::with('board.project')->findOrFail($cardId);
            
            // Check if user has access to this card
            if (!$this->canAccessCard($card)) {
                return response()->json(['error' => 'Akses ditolak'], 403);
            }
            
            $comments = CardComment::where('card_id', $cardId)
                ->with('user:user_id,full_name,email')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($comment) {
                    return [
                        'comment_id' => $comment->comment_id,
                        'comment' => $comment->comment,
                        'user' => [
                            'user_id' => $comment->user->user_id,
                            'name' => $comment->user->full_name,
                            'email' => $comment->user->email,
                            'initials' => $this->getInitials($comment->user->full_name),
                        ],
                        'created_at' => $comment->created_at->format('d M Y H:i'),
                        'created_at_human' => $comment->created_at->diffForHumans(),
                        'is_owner' => $comment->user_id === Auth::id(),
                    ];
                });
            
            return response()->json([
                'success' => true,
                'comments' => $comments,
                'total' => $comments->count(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching card comments', [
                'card_id' => $cardId,
                'error' => $e->getMessage()
            ]);
            
            return response()->json(['error' => 'Gagal mengambil komentar'], 500);
        }
    }
    
    /**
     * Store a new comment
     */
    public function store(Request $request, $cardId)
    {
        try {
            $card = Card::with(['board.project', 'assignments'])->findOrFail($cardId);
            
            // Check if user has access to this card
            if (!$this->canAccessCard($card)) {
                return response()->json(['error' => 'Akses ditolak'], 403);
            }
            
            // Validate request
            $validated = $request->validate([
                'comment' => 'required|string|max:5000',
            ]);
            
            // Create comment
            $comment = CardComment::create([
                'card_id' => $cardId,
                'user_id' => Auth::id(),
                'comment' => $validated['comment'],
                'comment_type' => 'general',
            ]);
            
            // Load user relationship
            $comment->load('user:user_id,full_name,email');
            
            Log::info('Card comment created', [
                'comment_id' => $comment->comment_id,
                'card_id' => $cardId,
                'user_id' => Auth::id()
            ]);
            
            // Notify all assigned users and project leader
            $project = $card->board->project;
            $notifyUsers = collect();
            
            // Add assigned users
            foreach ($card->assignments as $assignment) {
                if ($assignment->user_id !== Auth::id()) {
                    $notifyUsers->push($assignment->user_id);
                }
            }
            
            // Add project leader
            if ($project->leader_id && $project->leader_id !== Auth::id()) {
                $notifyUsers->push($project->leader_id);
            }
            
            // Send notifications
            foreach ($notifyUsers->unique() as $userId) {
                NotificationHelper::newCommentOnTask($card, $comment, $userId);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Komentar berhasil ditambahkan',
                'comment' => [
                    'comment_id' => $comment->comment_id,
                    'comment' => $comment->comment,
                    'user' => [
                        'user_id' => $comment->user->user_id,
                        'name' => $comment->user->full_name,
                        'email' => $comment->user->email,
                        'initials' => $this->getInitials($comment->user->full_name),
                    ],
                    'created_at' => $comment->created_at->format('d M Y H:i'),
                    'created_at_human' => $comment->created_at->diffForHumans(),
                    'is_owner' => true,
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Validasi gagal',
                'messages' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error creating card comment', [
                'card_id' => $cardId,
                'error' => $e->getMessage()
            ]);
            
            return response()->json(['error' => 'Gagal menambahkan komentar'], 500);
        }
    }
    
    /**
     * Delete a comment
     */
    public function destroy($cardId, $commentId)
    {
        try {
            $card = Card::findOrFail($cardId);
            
            // Check if user has access to this card
            if (!$this->canAccessCard($card)) {
                return response()->json(['error' => 'Akses ditolak'], 403);
            }
            
            $comment = CardComment::where('comment_id', $commentId)
                ->where('card_id', $cardId)
                ->firstOrFail();
            
            // Only owner or admin can delete
            if ($comment->user_id !== Auth::id() && Auth::user()->role !== 'admin') {
                return response()->json(['error' => 'Anda tidak berhak menghapus komentar ini'], 403);
            }
            
            $comment->delete();
            
            Log::info('Card comment deleted', [
                'comment_id' => $commentId,
                'card_id' => $cardId,
                'deleted_by' => Auth::id()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Komentar berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting card comment', [
                'comment_id' => $commentId,
                'card_id' => $cardId,
                'error' => $e->getMessage()
            ]);
            
            return response()->json(['error' => 'Gagal menghapus komentar'], 500);
        }
    }
    
    /**
     * Check if user can access this card
     */
    private function canAccessCard($card)
    {
        $user = Auth::user();
        
        // Admin can access all
        if ($user->role === 'admin') {
            return true;
        }
        
        $project = $card->board->project;
        
        // Check if user is project member
        $isMember = ProjectMember::where('project_id', $project->project_id)
            ->where('user_id', $user->user_id)
            ->exists();
        
        return $isMember;
    }
    
    /**
     * Get user initials
     */
    private function getInitials($name)
    {
        $words = explode(' ', $name);
        $initials = '';
        foreach (array_slice($words, 0, 2) as $word) {
            $initials .= strtoupper(substr($word, 0, 1));
        }
        return $initials ?: strtoupper(substr($name, 0, 2));
    }
}
