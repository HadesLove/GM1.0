<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Broadcast extends Model
{
    protected $fillable = [
        'interval', 'times', 'content'
    ];

    public function account()
    {
        return $this->hasOne(Account::class, 'id', 'account_id');
    }

}