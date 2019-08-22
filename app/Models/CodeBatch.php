<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CodeBatch extends Model
{
    protected $fillable = [
        'batch_name', 'batch_detail', 'code_box_id', 'code_prefix',
        'code_length', 'platform', 'channel_id', 'use_count',
        'account_id', 'start_time', 'end_time'
    ];

    public function account()
    {
        return $this->hasOne(Account::class, 'id', 'account_id');
    }
}