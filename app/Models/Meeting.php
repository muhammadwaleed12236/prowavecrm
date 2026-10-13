<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class Meeting extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'user_id',
        'title',
        'scheduled_at',
        'meeting_link',
        'status',
        'agenda',
        'outcome_notes',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
        ];
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->canViewAllLeads()) {
            return $query;
        }

        return $query->where('user_id', $user->id);
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('scheduled_at', '>=', now())
                     ->where('status', 'scheduled')
                     ->orderBy('scheduled_at', 'asc');
    }

    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('scheduled_at', Carbon::today())
                     ->where('status', 'scheduled')
                     ->orderBy('scheduled_at', 'asc');
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
