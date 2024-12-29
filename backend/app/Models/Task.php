<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'description', 'due_date', 'priority', 'completed', 'completed_at', 'archived', 'archived_at'
    ];

    protected $casts = [
        'completed' => 'boolean',
        'archived' => 'boolean',
        'completed_at' => 'datetime',
        'archived_at' => 'datetime',
    ];

    public function tags()
    {
        return $this->belongsToMany(Tag::class)->withTimestamps();
    }

    public function attachments()
    {
        return $this->hasMany(Attachment::class);
    }
}
