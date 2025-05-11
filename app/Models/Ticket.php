<?php

namespace App\Models;

use App\Traits\HasComments;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;
    use HasComments;

    protected $guarded = ['id'];

    /**
     * Получить пользователя, который создал тикет.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Получить прикреплённые файлы (фото) для тикета.
     */
    public function attachments()
    {
        return $this->hasMany(TicketAttachment::class);
    }

    /**
     * Обновить статус тикета.
     *
     * @param  string  $status
     * @return void
     */
    public function updateStatus(string $status)
    {
        $this->update(['status' => $status]);
    }

    /**
     * Получить тикет в зависимости от приоритета.
     */
    public function scopePriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

//    /**
//     * Получить тикет по категории.
//     */
//    public function scopeCategory($query, $category)
//    {
//        return $query->where('category', $category);
//    }

    /**
     * Получить тикет в зависимости от статуса.
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }
}
