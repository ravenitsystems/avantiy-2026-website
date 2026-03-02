<?php

namespace App\Http\Controllers\Api\Session;

use App\Http\Controllers\ApiBase;
use App\Services\CurrentUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Paymentmethodreorder extends ApiBase
{
    public function handle(Request $request): array
    {
        if (! CurrentUser::isLoggedIn()) {
            $this->setFail();
            $this->setResponseCode(401);
            return ['message' => 'You must be signed in to reorder payment methods.'];
        }
        $userId = (int) $request->session()->get('user_id');

        $orderedIds = $request->input('ordered_ids');
        if (!is_array($orderedIds)) {
            $this->setFail();
            $this->setResponseCode(400);
            return ['message' => 'Ordered list of payment method IDs is required.'];
        }

        $orderedIds = array_map('intval', array_values($orderedIds));
        $orderedIds = array_filter($orderedIds, fn ($id) => $id > 0);

        $mine = DB::table('user_payment_method')
            ->where('user_id', $userId)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        foreach ($orderedIds as $index => $id) {
            if (in_array($id, $mine, true)) {
                DB::table('user_payment_method')
                    ->where('id', $id)
                    ->where('user_id', $userId)
                    ->update(['display_order' => $index, 'updated_at' => now()]);
            }
        }

        return ['message' => 'Order updated.'];
    }
}
