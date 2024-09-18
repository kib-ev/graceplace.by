<?php

namespace App\Traits;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasComments
{
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'model_id', 'id')
            ->where('model_class', $this->getMorphClass())
            ->orderBy('created_at');
    }

    public function addComment(User $user, string $text, string $type = null)
    {
        $comment = Comment::make([
            'user_id' => $user->id,
            'text' => $text,
            'model_class' => $this->getMorphClass(),
            'model_id' => $this->id,
            'type' => $type
        ]);

        $this->comments()->save($comment);
    }
}
