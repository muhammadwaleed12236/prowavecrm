<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'role_id',
        'phone',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function roleModel(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin' || ($this->roleModel && $this->roleModel->slug === 'super_admin');
    }

    public function hasRole(string|array $roles): bool
    {
        $roleList = is_array($roles) ? $roles : [$roles];

        if (in_array($this->role, $roleList)) {
            return true;
        }

        if ($this->roleModel && in_array($this->roleModel->slug, $roleList)) {
            return true;
        }

        return false;
    }

    public function hasPermission(string $permissionSlug): bool
    {
        // Super admin has all permissions unconditionally
        if ($this->isSuperAdmin()) {
            return true;
        }

        if (!$this->relationLoaded('roleModel')) {
            $this->load('roleModel.permissions');
        }

        return $this->roleModel ? $this->roleModel->hasPermission($permissionSlug) : false;
    }

    public function canViewAllLeads(): bool
    {
        return $this->hasPermission('leads.view_all');
    }

    public function canAssignLeads(): bool
    {
        return $this->hasPermission('leads.assign');
    }

    public function canManageTeam(): bool
    {
        return $this->hasPermission('team.manage');
    }

    public function canManageRoles(): bool
    {
        return $this->hasPermission('roles.manage');
    }

    public function isSalesAgent(): bool
    {
        return $this->role === 'sales_agent' || ($this->roleModel && $this->roleModel->slug === 'sales_agent');
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class, 'assigned_to');
    }

    public function createdLeads(): HasMany
    {
        return $this->hasMany(Lead::class, 'created_by');
    }

    public function callLogs(): HasMany
    {
        return $this->hasMany(CallLog::class);
    }

    public function meetings(): HasMany
    {
        return $this->hasMany(Meeting::class);
    }

    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class);
    }

    public function dailyReports(): HasMany
    {
        return $this->hasMany(DailyReport::class);
    }
}
