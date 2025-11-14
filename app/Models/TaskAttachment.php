<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class TaskAttachment extends Model
{
    use HasFactory;

    protected $table = 'task_attachments';
    protected $primaryKey = 'attachment_id';

    protected $fillable = [
        'card_id',
        'user_id',
        'filename',
        'original_filename',
        'file_path',
        'file_type',
        'mime_type',
        'file_size',
        'description'
    ];

    protected $casts = [
        'file_size' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Relationships
    public function card()
    {
        return $this->belongsTo(Card::class, 'card_id', 'card_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    // Accessors
    public function getFileSizeFormattedAttribute()
    {
        $bytes = $this->file_size;
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' bytes';
    }

    public function getFileUrlAttribute()
    {
        return Storage::url($this->file_path);
    }

    // Helper methods
    public function isImage()
    {
        return in_array($this->file_type, ['image', 'jpg', 'jpeg', 'png', 'gif', 'svg', 'webp']);
    }
}
