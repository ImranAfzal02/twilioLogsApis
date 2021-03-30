<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class SMSLog extends Model {
    protected $table = 'sms_logs';

    protected $fillable = [
        'id',
        'sid',
        'status',
        'from',
        'to',
        'body',
        'direction',
        'dateSent',
        'created_at',
    ];
}
