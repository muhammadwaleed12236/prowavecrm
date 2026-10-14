<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Builder;

class Lead extends Model
{
    use HasFactory;

    protected $fillable = [
        'assigned_to',
        'created_by',
        'client_name',
        'company_name',
        'email',
        'phone',
        'lead_type',
        'country',
        'service_needed',
        'source',
        'status',
        'budget',
        'currency',
        'notes',
        'last_contacted_at',
    ];

    protected function casts(): array
    {
        return [
            'budget' => 'decimal:2',
            'last_contacted_at' => 'datetime',
        ];
    }

    /**
     * Scope query to only include leads visible to the user.
     * Super Admin sees all; Sales Agents only see their assigned leads.
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->canViewAllLeads()) {
            return $query;
        }

        return $query->where('assigned_to', $user->id);
    }

    public function assignedAgent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function callLogs(): HasMany
    {
        return $this->hasMany(CallLog::class)->latest();
    }

    public function meetings(): HasMany
    {
        return $this->hasMany(Meeting::class)->orderBy('scheduled_at', 'asc');
    }

    public function deal(): HasOne
    {
        return $this->hasOne(Deal::class);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'new' => 'bg-blue-500/10 text-blue-400 border border-blue-500/20',
            'contacted' => 'bg-sky-500/10 text-sky-400 border border-sky-500/20',
            'interested' => 'bg-indigo-500/10 text-indigo-400 border border-indigo-500/20',
            'callback' => 'bg-amber-500/10 text-amber-400 border border-amber-500/20',
            'meeting_scheduled' => 'bg-purple-500/10 text-purple-400 border border-purple-500/20',
            'proposal_sent' => 'bg-cyan-500/10 text-cyan-400 border border-cyan-500/20',
            'deal_won' => 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20',
            'lost' => 'bg-rose-500/10 text-rose-400 border border-rose-500/20',
            'not_interested' => 'bg-slate-500/10 text-slate-400 border border-slate-500/20',
            default => 'bg-gray-500/10 text-gray-400 border border-gray-500/20',
        };
    }
}
