<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Deal extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'user_id',
        'client_name',
        'project_title',
        'project_scope',
        'deal_amount',
        'currency',
        'closed_date',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'deal_amount' => 'decimal:2',
            'closed_date' => 'date',
        ];
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->canViewAllLeads()) {
            return $query;
        }

        return $query->where('user_id', $user->id);
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
