<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Board extends Model
{
    use HasFactory;
    
    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'board_id';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'project_id',
        'board_name',
        'description',
        'position',
    ];
    
    /**
     * Get the project that owns the board.
     */
    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id', 'project_id');
    }
    
    /**
     * Get the cards in the board.
     */
    public function cards()
    {
        return $this->hasMany(Card::class, 'board_id', 'board_id');
    }
}
