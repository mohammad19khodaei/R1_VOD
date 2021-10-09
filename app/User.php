<?php

namespace App;

use App\Events\UserUpdated;
use App\RealWorld\Favorite\HasFavorite;
use App\RealWorld\Follow\Followable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Tymon\JWTAuth\Facades\JWTAuth;

class User extends Authenticatable implements JWTSubject
{
    use Followable, HasFavorite;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username', 'email', 'password', 'bio', 'image', 'balance', 'disabled_at', 'is_admin'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $dates = [
        'disabled_at'
    ];

    /**
     * The event map for the model.
     *
     * @var array
     */
    protected $dispatchesEvents = [
        'updated' => UserUpdated::class,
    ];

    /**
     * Set the password using bcrypt hash.
     *
     * @param $value
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = bcrypt($value);
    }

    /**
     * Generate a JWT token for the user.
     *
     * @return string
     */
    public function getTokenAttribute()
    {
        return JWTAuth::fromUser($this);
    }

    /**
     * Get all the articles by the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function articles(): HasMany
    {
        return $this->hasMany(Article::class)->latest();
    }

    /**
     * Get all the transactions of the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class)->latest();
    }

    /**
     * Get all the comments by the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class)->latest();
    }

    /**
     * Get all the factors of the following users.
     *
     * @return HasManyThrough
     */
    public function factors(): HasManyThrough
    {
        return $this->hasManyThrough(Factor::class, Transaction::class);
    }

    /**
     * Get all the email histories of the following users.
     *
     * @return HasMany
     */
    public function emailHistories(): HasMany
    {
        return $this->hasMany(EmailHistory::class);
    }

    /**
     * Get all the articles of the following users.
     *
     * @return Builder
     */
    public function feed(): Builder
    {
        $followingIds = $this->following()->pluck('id')->toArray();

        return Article::loadRelations()->whereIn('user_id', $followingIds);
    }

    /**
     * Get the key name for route model binding.
     *
     * @return string
     */
    public function getRouteKeyName(): string
    {
        return 'username';
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims(): array
    {
        return [];
    }

    public function isNotifiedBefore(): bool
    {
        return $this->emailHistories()->where('in_progress', 1)->exists();
    }

    public function isDisabled(): bool
    {
        return !is_null($this->disabled_at);
    }

    /**
     * return true if the user disabled at more than 24 hour ago
     *
     * @return bool
     */
    public function mustRemove(): bool
    {
        return !is_null($this->disabled_at) && $this->disabled_at->addDay()->isPast();
    }

    public function isAdmin(): bool
    {
        return $this->is_admin;
    }
}
