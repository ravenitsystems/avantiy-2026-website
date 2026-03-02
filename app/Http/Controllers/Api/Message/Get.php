<?php

namespace App\Http\Controllers\Api\Message;

use App\Http\Controllers\ApiBase;
use App\Services\CurrentUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Get extends ApiBase
{
    public function handle(Request $request): array
    {
        if (! CurrentUser::isLoggedIn()) {
            $this->setFail();
            $this->setResponseCode(401);
            return ['message' => 'You must be signed in to view messages.'];
        }
        $userId = (int) $request->session()->get('user_id');

        $id1 = $request->route('id1');
        $id = $id1 !== null ? (int) $id1 : 0;
        if ($id <= 0) {
            $this->setFail();
            $this->setResponseCode(400);
            return ['message' => 'Invalid message.'];
        }

        $row = DB::table('message')
            ->where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$row) {
            $this->setFail();
            $this->setResponseCode(404);
            return ['message' => 'Message not found.'];
        }

        DB::table('message')
            ->where('id', $id)
            ->where('user_id', $userId)
            ->update(['read' => true, 'updated_at' => now()]);

        return [
            'id' => (int) $row->id,
            'subject' => $row->subject ?? '',
            'body' => $row->body,
            'sent_at' => $row->sent_at ? (string) $row->sent_at : null,
            'read' => true,
        ];
    }
}
