<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\SalesRole;
use App\Enums\UserType;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, LogsActivity, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'type',
        'brand_id',
        'profile_image',
        'status',
        'google_id',
        'avatar',
        'sales_role',
        'team_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @var list<string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'type' => UserType::class,
            'sales_role' => SalesRole::class,
        ];
    }

    // -- Relations --

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }

    public function adminProfile(): HasOne
    {
        return $this->hasOne(AdminProfile::class);
    }

    public function customerProfile(): HasOne
    {
        return $this->hasOne(CustomerProfile::class);
    }

    public function brand(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function activityLogs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function lead(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Lead::class, 'converted_to_customer_id');
    }

    // -- Helpers --

    public function isSuperAdmin(): bool
    {
        return $this->type === UserType::SUPER_ADMIN;
    }

    public function isAdmin(): bool
    {
        return $this->type === UserType::ADMIN;
    }

    public function isCustomer(): bool
    {
        return $this->type === UserType::CUSTOMER;
    }

    public function isSalesAdmin(): bool
    {
        return $this->isAdmin() && $this->sales_role === SalesRole::ADMIN;
    }

    public function isSalesManager(): bool
    {
        return $this->isAdmin() && $this->sales_role === SalesRole::MANAGER;
    }

    public function isSalesRep(): bool
    {
        return $this->isAdmin() && $this->sales_role === SalesRole::REP;
    }

    public function hasRole(string $roleName): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }
        if (! $this->isAdmin()) {
            return false;
        }

        return $this->roles->contains('name', $roleName);
    }

    public function hasPermission(string $permissionName): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }
        if (! $this->isAdmin()) {
            return false;
        }

        // Direct query to check permission
        return \Illuminate\Support\Facades\DB::table('user_roles')
            ->join('role_permissions', 'user_roles.role_id', '=', 'role_permissions.role_id')
            ->join('permissions', 'role_permissions.permission_id', '=', 'permissions.id')
            ->where('user_roles.user_id', $this->id)
            ->where('permissions.name', $permissionName)
            ->exists();
    }

    public function isOAuthUser(): bool
    {
        return ! empty($this->google_id);
    }

    public function getProfilePhotoUrlAttribute(): string
    {
        $profileImage = $this->profile_image;

        if (is_string($profileImage) && trim($profileImage) !== '') {
            if (Str::startsWith($profileImage, ['http://', 'https://', '//', 'data:'])) {
                return $profileImage;
            }

            $normalizedPath = ltrim($profileImage, '/');
            if (Str::startsWith($normalizedPath, 'storage/')) {
                $normalizedPath = substr($normalizedPath, 8);
            }

            if (Storage::disk('public')->exists($normalizedPath)) {
                return '/storage/'.$normalizedPath;
            }
        }

        if (is_string($this->avatar) && trim($this->avatar) !== '') {
            return $this->avatar;
        }

        return 'https://ui-avatars.com/api/?name='.urlencode($this->name ?? 'User').'&background=random';
    }
}
