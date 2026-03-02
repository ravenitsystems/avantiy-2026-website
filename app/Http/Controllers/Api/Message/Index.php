<?php

namespace App\Http\Controllers\Api\Message;

use App\Http\Controllers\ApiBase;
use App\Services\CurrentUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Index extends ApiBase
{
    public function handle(Request $request): array
    {
        if (! CurrentUser::isLoggedIn()) {
            $this->setFail();
            $this->setResponseCode(401);
            return ['message' => 'You must be signed in to view messages.'];
        }
        $userId = (int) $request->session()->get('user_id');

        $rows = DB::table('message')
            ->where('user_id', $userId)
            ->orderByDesc('sent_at')
            ->get(['id', 'subject', 'body', 'sent_at', 'read']);

        $messages = $rows->map(fn ($r) => [
            'id' => (int) $r->id,
            'subject' => $r->subject ?? '',
            'body' => $r->body,
            'sent_at' => $r->sent_at ? (string) $r->sent_at : null,
            'read' => (bool) $r->read,
        ])->values()->all();

        return ['messages' => $messages];
    }
}
