<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Content extends Model
{
    protected $fillable = [
        'title', 'content', 'note', 'cahnnel_id', 'status'
    ];

    public function server()
    {
        return $this->hasOne(Server::class, 'id', 'server_id');
    }
}