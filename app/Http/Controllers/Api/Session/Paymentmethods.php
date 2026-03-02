<?php

namespace App\Http\Controllers\Api\Session;

use App\Http\Controllers\ApiBase;
use App\Services\CurrentUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Paymentmethods extends ApiBase
{
    public function handle(Request $request): array
    {
        if (! CurrentUser::isLoggedIn()) {
            $this->setFail();
            $this->setResponseCode(401);
            return ['message' => 'You must be signed in to view payment methods.'];
        }
        $userId = (int) $request->session()->get('user_id');

        $rows = DB::table('user_payment_method')
            ->where('user_id', $userId)
            ->orderBy('display_order')
            ->orderBy('id')
            ->get(['id', 'stripe_payment_method_id', 'name', 'description', 'last_four', 'brand', 'display_order']);

        $payment_methods = $rows->map(fn ($r) => [
            'id' => (int) $r->id,
            'stripe_payment_method_id' => $r->stripe_payment_method_id,
            'name' => $r->name ?? '',
            'description' => $r->description,
            'last_four' => $r->last_four,
            'brand' => $r->brand,
            'display_order' => (int) $r->display_order,
        ])->values()->all();

        return ['payment_methods' => $payment_methods];
    }
}
