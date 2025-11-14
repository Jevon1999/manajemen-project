<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CardComment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'card_comments';
    protected $primaryKey = 'comment_id';

    protected $fillable = [
        'card_id',
        'user_id',
        'comment',
        'is_progress_update',
        'progress_percentage',
        'comment_type',
        'parent_id',
    ];

    protected $casts = [
        'is_progress_update' => 'boolean',
        'progress_percentage' => 'integer',
    ];

    /**
     * Get the card that owns the comment.
     */
    public function card()
    {
        return $this->belongsTo(Card::class, 'card_id', 'card_id');
    }

    /**
     * Get the user that created the comment.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Get the parent comment (for threaded comments).
     */
    public function parent()
    {
        return $this->belongsTo(CardComment::class, 'parent_id', 'comment_id');
    }

    /**
     * Get the replies to this comment.
     */
    public function replies()
    {
        return $this->hasMany(CardComment::class, 'parent_id', 'comment_id');
    }
}
