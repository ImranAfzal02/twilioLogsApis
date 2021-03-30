<?php


namespace App\Console\Commands;


use App\Models\CallLog;
use App\Models\SMSLog;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client;

class SaveTwilioLogs extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:save-twilio-logs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Save daily logs to database';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        $sid = env("TWILIO_ACCOUNT_SID");
        $token = env("TWILIO_AUTH_TOKEN");
        $twilio = new Client($sid, $token);

        $calls = $twilio->calls->read([
            'startTimeBefore' => new \DateTime(Carbon::now()->format('Y-m-d'). 'T00:00:01'),
            'startTimeAfter' => new \DateTime(Carbon::now()->subDay()->format('Y-m-d') .'T00:00:00'),
        ]);
        if (count($calls) > 0) {
            foreach ($calls as $call) {
                CallLog::create([
                    'sid' => $call->sid,
                    'direction' => $call->direction,
                    'start_time' => \Illuminate\Support\Carbon::parse($call->startTime)->format('Y-m-d H:i:s'),
                    'end_time' => Carbon::parse($call->endTime)->format('Y-m-d H:i:s'),
                    'from' => $call->from,
                    'from_formatted' => $call->fromFormatted,
                    'to' => $call->to,
                    'to_formatted' => $call->toFormatted,
                    'status' => $call->status
                ]);
            }
        }

        $messages = $twilio->messages->read([
            'dateSentBefore' => new \DateTime(Carbon::now()->format('Y-m-d')),
            'dateSentAfter' => new \DateTime(Carbon::now()->subDay()->format('Y-m-d')),
        ]);
        if (count($messages) > 0) {
            foreach ($messages as $message) {

                SMSLog::create([
                    'sid' => $message->sid,
                    'status' => $message->status,
                    'from' => $message->from,
                    'to' => $message->to,
                    'body' => $message->body,
                    'direction' => $message->direction,
                    'dateSent' => \Illuminate\Support\Carbon::parse($message->dateSent)->format('Y-m-d H:i:s')
                ]);
            }
        }
    }
}
