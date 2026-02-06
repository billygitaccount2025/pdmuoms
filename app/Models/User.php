<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Str;
use App\Notifications\VerifyEmailNotification;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tbusers';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'idno';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'fname',
        'lname',
        'agency',
        'position',
        'region',
        'province',
        'office',
        'emailaddress',
        'mobileno',
        'username',
        'password',
        'role',
        'status',
        'access',
        'verification_token',
        'email_verified_at',
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
     * Get the name of the unique identifier for the user.
     *
     * @return string
     */
    public function getAuthIdentifierName()
    {
        return $this->getKeyName();  // Return primary key (idno)
    }

    /**
     * Get the unique identifier for the user.
     *
     * @return mixed
     */
    public function getAuthIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->password;
    }

    /**
     * Get the column name for the "remember me" token.
     *
     * @return string
     */
    public function getRememberTokenName()
    {
        return 'remember_token';
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'email_verified_at' => 'datetime',
        ];
    }

    /**
     * Get the email address that should be used for verification.
     *
     * @return string
     */
    public function getEmailForVerification()
    {
        return $this->emailaddress;
    }

    /**
     * Determine if the user has verified their email address.
     *
     * @return bool
     */
    public function hasVerifiedEmail()
    {
        return !is_null($this->email_verified_at);
    }

    /**
     * Mark the given user's email as verified.
     *
     * @return bool
     */
    public function markEmailAsVerified()
    {
        return $this->forceFill([
            'email_verified_at' => $this->freshTimestamp(),
        ])->save();
    }

    /**
     * Generate a verification token and save it.
     *
     * @return string
     */
    public function generateVerificationToken()
    {
        $token = Str::random(64);
        $this->verification_token = $token;
        $this->save();
        return $token;
    }

    /**
     * Verify the user's email using the token.
     *
     * @param string $token
     * @return bool
     */
    public function verifyEmailWithToken($token)
    {
        if ($this->verification_token === $token && $this->status === 'inactive') {
            $this->email_verified_at = now();
            $this->status = 'active';
            $this->verification_token = null; // Clear the token after verification
            return $this->save();
        }
        return false;
    }

    /**
     * Send the email verification notification.
     *
     * @return void
     */
    public function sendEmailVerificationNotification()
    {
        try {
            // Generate token if not exists
            if (!$this->verification_token) {
                $this->generateVerificationToken();
            }
            
            // Try sending via Mailable first
            \Illuminate\Support\Facades\Mail::send(new \App\Mail\VerifyEmailMailable($this));
            
            // Log successful send
            \Illuminate\Support\Facades\Log::info('Verification email sent', [
                'user_id' => $this->id,
                'email' => $this->emailaddress,
                'timestamp' => now()
            ]);
        } catch (\Exception $e) {
            // Log the error but don't throw - allow registration to complete
            \Illuminate\Support\Facades\Log::error('Failed to send verification email', [
                'user_id' => $this->id,
                'email' => $this->emailaddress,
                'error' => $e->getMessage(),
                'driver' => config('mail.default')
            ]);
            
            // Still send the notification to ensure it's queued if needed
            $this->notify(new VerifyEmailNotification);
        }
    }

    /**
     * Get the email address to be used for password reset.
     *
     * @return string
     */
    public function getEmailForPasswordReset()
    {
        return $this->emailaddress;
    }
    /**
     * Find the user by username for authentication
     *
     * @param string $username
     * @return mixed
     */
    public static function findByUsername($username)
    {
        return static::where('username', $username)->first();
    }}
