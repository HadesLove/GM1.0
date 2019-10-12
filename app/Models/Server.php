<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Server extends Model
{
    protected $fillable = [
        'server_name', 'logo', 'type', 'channel_id', 'beginTime', 'endTime', 'note', 'status'
    ];
}