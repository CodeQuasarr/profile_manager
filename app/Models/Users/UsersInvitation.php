<?php

namespace App\Models\Users;

use App\Models\Traits\GlobalTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class UsersInvitation extends Model
{
    use HasFactory, SoftDeletes, GlobalTrait;

    protected $fillable = [
        'coach_id',
        'email',
        'token'
    ];

    public function coach(): BelongsTo
    {
        return $this->belongsTo(User::class, 'coach_id');
    }
}
