<?php

namespace App\Http\Controllers\Api\Session;

use App\Http\Controllers\ApiBase;
use App\Services\CurrentUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentMethod;
use Stripe\Stripe;

class Paymentmethoddelete extends ApiBase
{
    public function handle(Request $request): array
    {
        if (! CurrentUser::isLoggedIn()) {
            $this->setFail();
            $this->setResponseCode(401);
            return ['message' => 'You must be signed in to remove a payment method.'];
        }
        $userId = (int) $request->session()->get('user_id');

        $id1 = $request->route('id1');
        $id = $id1 !== null ? (int) $id1 : 0;
        if ($id <= 0) {
            $this->setFail();
            $this->setResponseCode(400);
            return ['message' => 'Invalid payment method.'];
        }

        $row = DB::table('user_payment_method')
            ->where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$row) {
            $this->setFail();
            $this->setResponseCode(404);
            return ['message' => 'Payment method not found.'];
        }

        $secret = config('services.stripe.secret');
        if (!empty($secret)) {
            Stripe::setApiKey($secret);
            try {
                $pm = PaymentMethod::retrieve($row->stripe_payment_method_id);
                $pm->detach();
            } catch (ApiErrorException $e) {
                // Continue to delete from our table even if Stripe detach fails (e.g. already detached)
            }
        }

        DB::table('user_payment_method')->where('id', $id)->where('user_id', $userId)->delete();

        return ['message' => 'Payment method removed.'];
    }
}
