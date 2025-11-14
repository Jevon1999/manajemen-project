<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class CardAttachment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'card_attachments';
    protected $primaryKey = 'attachment_id';

    protected $fillable = [
        'card_id',
        'uploaded_by',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
        'original_name',
        'attachment_type',
        'description',
        'version',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'version' => 'integer',
    ];

    /**
     * Get the card that owns the attachment.
     */
    public function card()
    {
        return $this->belongsTo(Card::class, 'card_id', 'card_id');
    }

    /**
     * Get the user that uploaded the attachment.
     */
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by', 'user_id');
    }

    /**
     * Get the file URL.
     */
    public function getFileUrlAttribute()
    {
        return Storage::url($this->file_path);
    }

    /**
     * Get human readable file size.
     */
    public function getFileSizeHumanAttribute()
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
