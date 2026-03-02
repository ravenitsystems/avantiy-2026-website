<?php

namespace App\Http\Controllers\Api\Session;

use App\Http\Controllers\ApiBase;
use App\Services\ActivityLog;
use App\Services\CurrentUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentMethod;
use Stripe\Stripe;

class Paymentmethodadd extends ApiBase
{
    public function handle(Request $request): array
    {
        if (! CurrentUser::isLoggedIn()) {
            $this->setFail();
            $this->setResponseCode(401);
            return ['message' => 'You must be signed in to add a payment method.'];
        }

        $paymentMethodId = trim((string) $request->input('payment_method_id', ''));
        $name = trim((string) $request->input('name', ''));
        if (strlen($name) > 64) {
            $name = substr($name, 0, 64);
        }
        if (empty($paymentMethodId)) {
            $this->setFail();
            $this->setResponseCode(400);
            return ['message' => 'Payment method ID is required.'];
        }

        $secret = config('services.stripe.secret');
        if (empty($secret)) {
            $this->setFail();
            $this->setResponseCode(500);
            return ['message' => 'Payment configuration is not available.'];
        }

        Stripe::setApiKey($secret);

        $stripeUsername = CurrentUser::getStripeUsername();
        if (empty($stripeUsername)) {
            $this->setFail();
            $this->setResponseCode(400);
            return ['message' => 'Please try adding the card again from the start.'];
        }

        $userId = (int) $request->session()->get('user_id');
        $existing = DB::table('user_payment_method')
            ->where('user_id', $userId)
            ->where('stripe_payment_method_id', $paymentMethodId)
            ->exists();
        if ($existing) {
            $this->setFail();
            $this->setResponseCode(409);
            return ['message' => 'This card is already saved.'];
        }

        try {
            $pm = PaymentMethod::retrieve($paymentMethodId);
        } catch (ApiErrorException $e) {
            $this->setFail();
            $this->setResponseCode(400);
            return ['message' => 'Invalid payment method. Please try again.'];
        }

        if (($pm->customer ?? null) !== $stripeUsername) {
            try {
                $pm->attach(['customer' => $stripeUsername]);
            } catch (ApiErrorException $e) {
                $this->setFail();
                $this->setResponseCode(400);
                return ['message' => 'Could not attach card to your account. Please try again.'];
            }
        }

        $card = $pm->card ?? null;
        if (!$card) {
            $this->setFail();
            $this->setResponseCode(400);
            return ['message' => 'Only card payment methods are supported.'];
        }

        $brand = $card->brand ?? 'card';
        $lastFour = $card->last4 ?? '****';
        $description = ucfirst($brand) . ' ending ' . $lastFour;

        $maxOrder = (int) DB::table('user_payment_method')->where('user_id', $userId)->max('display_order');
        $displayOrder = $maxOrder + 1;

        $id = DB::table('user_payment_method')->insertGetId([
            'user_id' => $userId,
            'stripe_payment_method_id' => $paymentMethodId,
            'name' => $name === '' ? null : $name,
            'description' => $description,
            'last_four' => $lastFour,
            'brand' => $brand,
            'display_order' => $displayOrder,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        ActivityLog::log('payment_method.added', ['payment_method_id' => $id, 'last_four' => $lastFour, 'brand' => $brand]);

        return [
            'message' => 'Card added.',
            'payment_method' => [
                'id' => $id,
                'stripe_payment_method_id' => $paymentMethodId,
                'name' => $name,
                'description' => $description,
                'last_four' => $lastFour,
                'brand' => $brand,
                'display_order' => $displayOrder,
            ],
        ];
    }
}
