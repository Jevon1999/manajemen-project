<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\RespondsWithJson;
use App\Http\Resources\CardResource;
use App\Http\Resources\CommentResource;
use App\Http\Resources\SubtaskResource;
use App\Http\Resources\TimeLogResource;
use App\Models\Board;
use App\Models\Card;
use App\Models\Comment;
use App\Models\Project;
use App\Models\Subtask;
use App\Models\TimeLog;
use Illuminate\Http\Request;

class CardApiController extends Controller
{
    use RespondsWithJson;
    public function listByProject(Request $request, $projectId)
    {
        $boardIds = Board::where('project_id', $projectId)->pluck('board_id');
        $cards = Card::whereIn('board_id', $boardIds)
            ->with(['creator'])
            ->withCount('comments')
            ->latest('created_at')
            ->paginate($request->integer('per_page', 10));
        return $this->successCollection(CardResource::collection($cards), 'OK');
    }

    public function store(Request $request, $projectId)
    {
        $request->validate([
            'board_id' => ['required','exists:boards,board_id'],
            'card_title' => ['required','string','max:150'],
            'description' => ['nullable','string'],
            'priority' => ['nullable','in:low,medium,high,critical'],
            'status' => ['nullable','string','max:50'],
            'due_date' => ['nullable','date'],
        ]);
        // Ensure board belongs to project
        abort_unless(Board::where('board_id', $request->board_id)->where('project_id', $projectId)->exists(), 422, 'Board not in project');
        $card = Card::create(array_merge($request->only(['board_id','card_title','description','priority','status','due_date']), [
            'created_by' => $request->user()->user_id,
        ]));
        return $this->successResource(new CardResource($card->load('creator')), 'Card created', 201);
    }

    public function show($cardId)
    {
        $card = Card::with(['creator','subtasks','comments.user'])->withCount('comments')->findOrFail($cardId);
        return $this->successResource(new CardResource($card), 'OK');
    }

    public function update(Request $request, $cardId)
    {
        $card = Card::findOrFail($cardId);
        $card->update($request->validate([
            'card_title' => ['sometimes','string','max:150'],
            'description' => ['nullable','string'],
            'priority' => ['nullable','in:low,medium,high,critical'],
            'status' => ['nullable','string','max:50'],
            'due_date' => ['nullable','date'],
        ]));
        return $this->successResource(new CardResource($card->refresh()->load('creator')), 'Card updated');
    }

    public function destroy($cardId)
    {
        Card::where('card_id', $cardId)->delete();
        return $this->success(null, 'Card deleted');
    }

    // Subtasks
    public function listSubtasks($cardId)
    {
        $subs = Subtask::where('card_id', $cardId)->orderBy('position')->paginate(100);
        return $this->successCollection(SubtaskResource::collection($subs), 'OK');
    }

    public function addSubtask(Request $request, $cardId)
    {
        $data = $request->validate([
            'subtaks_title' => ['required','string','max:150'],
            'description' => ['nullable','string'],
            'status' => ['nullable','string','max:50'],
            'position' => ['nullable','integer'],
        ]);
        $data['card_id'] = $cardId;
        $sub = Subtask::create($data);
        return $this->successResource(new SubtaskResource($sub), 'Subtask created', 201);
    }

    public function updateSubtask(Request $request, $subtaskId)
    {
        $sub = Subtask::findOrFail($subtaskId);
        $sub->update($request->only(['subtaks_title','description','status','position']));
        return $this->successResource(new SubtaskResource($sub), 'Subtask updated');
    }

    public function deleteSubtask($subtaskId)
    {
        Subtask::where('subtask_id', $subtaskId)->delete();
        return $this->success(null, 'Subtask deleted');
    }

    // Comments
    public function listComments($cardId)
    {
        $comments = Comment::where('card_id', $cardId)->with('user')->latest('created_at')->paginate(50);
        return $this->successCollection(CommentResource::collection($comments), 'OK');
    }

    public function addComment(Request $request, $cardId)
    {
        $data = $request->validate([
            'comment_text' => ['required','string'],
            'comment_type' => ['nullable','string','max:50'],
        ]);
        $data['card_id'] = $cardId;
        $data['user_id'] = $request->user()->user_id;
        $comment = Comment::create($data);
        return $this->successResource(new CommentResource($comment->load('user')), 'Comment created', 201);
    }

    public function deleteComment($commentId)
    {
        Comment::where('comment_id', $commentId)->delete();
        return $this->success(null, 'Comment deleted');
    }

    // Timelogs
    public function listTimeLogs($cardId)
    {
        $logs = TimeLog::where('card_id', $cardId)->with('user')->latest('start_time')->paginate(50);
        return $this->successCollection(TimeLogResource::collection($logs), 'OK');
    }

    public function addTimeLog(Request $request, $cardId)
    {
        $data = $request->validate([
            'subtask_id' => ['nullable','exists:subtasks,subtask_id'],
            'start_time' => ['required','date'],
            'end_time' => ['required','date','after_or_equal:start_time'],
            'duration_minutes' => ['nullable','integer'],
            'description' => ['nullable','string'],
        ]);
        $data['card_id'] = $cardId;
        $data['user_id'] = $request->user()->user_id;
        if (empty($data['duration_minutes'])) {
            $data['duration_minutes'] = (int) floor((strtotime($data['end_time']) - strtotime($data['start_time'])) / 60);
        }
        $log = TimeLog::create($data);
        return $this->successResource(new TimeLogResource($log->load('user')), 'Time log created', 201);
    }

    public function updateTimeLog(Request $request, $logId)
    {
        $log = TimeLog::findOrFail($logId);
        $log->update($request->only(['start_time','end_time','duration_minutes','description']));
        return $this->successResource(new TimeLogResource($log->refresh()->load('user')), 'Time log updated');
    }

    public function deleteTimeLog($logId)
    {
        TimeLog::where('log_id', $logId)->delete();
        return $this->success(null, 'Time log deleted');
    }
}
