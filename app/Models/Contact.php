<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Database\Factories\ContactFactory;

class Contact extends Model
{
    /** @use HasFactory<ContactFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'job_id',
        'user_id',
        'first_name',
        'last_name',
        'position',
        'city',
        'email',
        'phone',
        'description',
        'linkedin_url',
        'facebook_url',
        'whatsapp_url',
    ];

    /**
     * @return BelongsTo<Job, $this>
     */
    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
