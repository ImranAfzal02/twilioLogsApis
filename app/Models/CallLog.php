<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class CallLog extends Model {
    protected $table = 'call_logs';

    protected $fillable = [
        'id',
        'sid',
        'direction',
        'start_time',
        'end_time',
        'from',
        'from_formatted',
        'to',
        'to_formatted',
        'status',
        'start_time',
        'end_time',
        'created_at',
    ];
}
