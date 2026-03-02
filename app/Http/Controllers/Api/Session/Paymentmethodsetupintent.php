<?php

namespace App\Http\Controllers\Api\Session;

use App\Http\Controllers\ApiBase;
use App\Services\CurrentUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Stripe\Customer;
use Stripe\Exception\ApiErrorException;
use Stripe\SetupIntent;
use Stripe\Stripe;

class Paymentmethodsetupintent extends ApiBase
{
    public function handle(Request $request): array
    {
        if (! CurrentUser::isLoggedIn()) {
            $this->setFail();
            $this->setResponseCode(401);
            return ['message' => 'You must be signed in to add a payment method.'];
        }

        $secret = config('services.stripe.secret');
        if (empty($secret)) {
            $this->setFail();
            $this->setResponseCode(500);
            return ['message' => 'Payment configuration is not available.'];
        }

        Stripe::setApiKey($secret);

        $customerId = CurrentUser::getStripeUsername();
        try {
            $intent = SetupIntent::create([
                'customer' => $customerId,
                'usage' => 'off_session',
                'automatic_payment_methods' => ['enabled' => true],
            ]);
        } catch (ApiErrorException $e) {
            $this->setFail();
            $this->setResponseCode(502);
            return ['message' => 'Could not start card setup. Please try again.'];
        }

        return [
            'client_secret' => $intent->client_secret,
        ];
    }
}
