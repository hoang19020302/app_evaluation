<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailOpen extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'status',
        'action',
        'sent_at',
        'opened_at',
    ];
}
