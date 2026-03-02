<?php

namespace App\Http\Controllers\Api\Session;

use App\Http\Controllers\ApiBase;
use App\Services\CurrentUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Paymentmethodupdate extends ApiBase
{
    public function handle(Request $request): array
    {
        if (! CurrentUser::isLoggedIn()) {
            $this->setFail();
            $this->setResponseCode(401);
            return ['message' => 'You must be signed in to update a payment method.'];
        }
        $userId = (int) $request->session()->get('user_id');

        $id1 = $request->route('id1');
        $id = $id1 !== null ? (int) $id1 : 0;
        if ($id <= 0) {
            $this->setFail();
            $this->setResponseCode(400);
            return ['message' => 'Invalid payment method.'];
        }

        $name = trim((string) $request->input('name', ''));
        if (strlen($name) > 64) {
            $name = substr($name, 0, 64);
        }

        $updated = DB::table('user_payment_method')
            ->where('id', $id)
            ->where('user_id', $userId)
            ->update([
                'name' => $name === '' ? null : $name,
                'updated_at' => now(),
            ]);

        if (!$updated) {
            $this->setFail();
            $this->setResponseCode(404);
            return ['message' => 'Payment method not found.'];
        }

        return [
            'message' => 'Card name updated.',
            'name' => $name,
        ];
    }
}
