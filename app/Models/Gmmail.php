<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gmmail extends Model
{
    protected $fillable = [
        'role_list', 'server_id', 'reason', 'title', 'content', 'attach_s', 'account_id'
    ];
}