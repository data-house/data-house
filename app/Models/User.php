<?php

namespace App\Models;

use App\Data\NotificationSettingsData;
use App\HasPreferences;
use App\HasRole;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Jetstream\HasTeams;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\Traits\CausesActivity;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use HasTeams;
    use Notifiable;
    use TwoFactorAuthenticatable;
    use HasRole;
    use HasPreferences;
    use CausesActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name', 'email', 'password', 'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    protected $with = [
        'userPreferences',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::creating(function (User $user) {
            if(blank($user->password_updated_at)){
                $user->password_updated_at = now();
            }
        });
    }


    public function imports()
    {
        return $this->hasMany(Import::class, 'created_by');
    }
    
    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    /**
     * The last used passwords of the user.
     * 
     * Use to provide the password compliance check that the user is not reusing
     * older passwords, up to the amount defined in auth.password_validation.historical_password_amount
     */
    public function passwords(): HasMany
    {
        return $this->hasMany(Password::class);
    }

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password_updated_at' => 'datetime',
            'role' => Role::class,
            'notification_settings' => NotificationSettingsData::class . ':default',
        ];
    }

    protected function defaultProfilePhotoUrl()
    {
        $name = str($this->name)->trim()->transliterate()->substr(0, 1)->toString();

        return route('avatar', ['avatar' => urlencode($name)]);
    }
}
