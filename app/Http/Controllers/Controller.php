<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Laravel\Lumen\Routing\Controller as BaseController;
use Twilio\Rest\Client;

class Controller extends BaseController {

    protected $sid, $token, $twilio;
    public function __construct() {
        $this->sid = env("TWILIO_ACCOUNT_SID");
        $this->token = env("TWILIO_AUTH_TOKEN");
        $this->twilio = new Client($this->sid, $this->token);
    }

    public function login(Request $request) {
        if ($request->email == env('AUTH_EMAIL') && $request->password == env('PASSWORD')) {
            return response()->json([
                'status' => env('STATUS_OK'),
                'message' => 'Logged-In successfully',
                'data' => [
                    'token' => hash('sha256', time())
                ]
            ], env('STATUS_OK'));
        } else {
            return response()->json([
                'status' => env('STATUS_NOT_FOUND'),
                'message' => 'Invalid email or password',
                'data' => []
            ], env('STATUS_OK'));
        }
    }

    public function getCallLogs() {
        try {

            $calls = $this->twilio->calls->read([], 150);

            $messages = $this->twilio->messages->read([], 150);

            $callLogs = $messageLogs = [];

            if (count($calls) > 0) {
                foreach ($calls as $call) {
                    $callLogs[] = [
                        'sid' => $call->sid,
                        'direction' => $call->direction,
                        'start_time' => Carbon::parse($call->startTime)->format('Y-m-d H:i:s'),
                        'end_time' => Carbon::parse($call->endTime)->format('Y-m-d H:i:s'),
                        'from' => $call->from,
                        'from_formatted' => $call->fromFormatted,
                        'to' => $call->to,
                        'to_formatted' => $call->toFormatted,
                        'status' => $call->status
                    ];
                }
            }

            if (count($messages) > 0) {
                foreach ($messages as $message) {

                    $messageLogs[] = ['sid' => $message->sid, 'status' => $message->status, 'from' => $message->from, 'to' => $message->to, 'body' => $message->body, 'direction' => $message->direction, 'dateSent' => Carbon::parse($message->dateSent)->format('Y-m-d H:i:s')];
                }
            }

            return response()->json([
                'status' => env('STATUS_OK'),
                'message' => 'Call and message logs',
                'data' => [
                    'call_logs' => $callLogs,
                    'message_logs' => $messageLogs
                ]
            ], env('STATUS_OK'));

        } catch (\Exception $e) {
            return response()->json([
                'status' => env('STATUS_NOT_MODIFIED'),
                'message' => $e->getMessage(),
                'data' => []
            ], env('STATUS_NOT_MODIFIED'));
        }
    }

    public function filterMessageLogs(Request $request) {
        try {

            $search = [];

            if (isset($request->toDate) && !empty($request->toDate)) {
                $endDate = Carbon::parse($request->toDate)->addDay()->format('Y-m-d');
                $search['dateSentBefore'] = new \DateTime($endDate);
            }

            if (isset($request->fromDate) && !empty($request->fromDate)) {
                $startDate = Carbon::parse($request->fromDate)->format('Y-m-d');
                $search['dateSentAfter'] = new \DateTime($startDate);
            }

            $messages = $this->twilio->messages->read($search);

            $messageLogs = [];

            if (count($messages) > 0) {
                foreach ($messages as $message) {

                    $messageLogs[] = [
                        'sid' => $message->sid,
                        'status' => $message->status,
                        'from' => $message->from,
                        'to' => $message->to,
                        'body' => $message->body,
                        'direction' => $message->direction,
                        'dateSent' => Carbon::parse($message->dateSent)->format('Y-m-d H:i:s')
                    ];
                }
            }

            return response()->json([
                'status' => env('STATUS_OK'),
                'message' => 'Filtered Message Logs',
                'data' => $messageLogs
            ], env('STATUS_OK'));

        } catch (\Exception $e) {
            return response()->json([
                'status' => env('STATUS_NOT_MODIFIED'),
                'message' => $e->getMessage(),
                'data' => []
            ], env('STATUS_NOT_MODIFIED'));
        }
    }

    public function filterCallLogs(Request $request) {
        try {

            $startDate = Carbon::parse($request->fromDate)->format('Y-m-d');

            $search['status'] = $request->status;

            if (isset($request->toDate) && !empty($request->toDate)) {
                $endDate = Carbon::parse($request->toDate)->addDay()->format('Y-m-d');
                $search['startTimeBefore'] = new \DateTime($endDate . 'T23:59:59Z');
            }

            if (isset($request->fromDate) && !empty($request->fromDate)) {
                $startDate = Carbon::parse($request->fromDate)->format('Y-m-d');
                $search['startTimeAfter'] = new \DateTime($startDate . 'T00:00:00Z');
            }

            $calls = $this->twilio->calls->read($search);

            $callLogs = [];

            if (count($calls) > 0) {
                foreach ($calls as $call) {
                    $callLogs[] = [
                        'sid' => $call->sid,
                        'direction' => $call->direction,
                        'start_time' => Carbon::parse($call->startTime)->format('Y-m-d H:i:s'),
                        'end_time' => Carbon::parse($call->endTime)->format('Y-m-d H:i:s'),
                        'from' => $call->from,
                        'from_formatted' => $call->fromFormatted,
                        'to' => $call->to,
                        'to_formatted' => $call->toFormatted,
                        'status' => $call->status
                    ];
                }
            }

            return response()->json([
                'status' => env('STATUS_OK'),
                'message' => 'Filtered Call Logs',
                'data' => $callLogs
            ], env('STATUS_OK'));

        } catch (\Exception $e) {
            return response()->json([
                'status' => env('STATUS_NOT_MODIFIED'),
                'message' => $e->getMessage(),
                'data' => []
            ], env('STATUS_NOT_MODIFIED'));
        }
    }
}
