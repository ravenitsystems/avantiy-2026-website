<?php

namespace App\Services;

use App\Duda\Partner;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Stripe\Customer;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;

class CurrentUser
{
    private static $is_loaded = false;
    private static $is_logged_in = false;

    private static ?int $user_id = null;
    private static string $admin_code = '';
    private static string $pricing_group = '';

    private static ?string $duda_username = '';
    private static ?string $stripe_username = '';

    private static string $account_type = '';

    private static ?string $billed_until = '';
    private static ?int $amount_charged = 0;
    private static ?string $payment_term = '';

    private static string $first_name = '';
    private static string $last_name = '';
    private static string $email = '';









    public static function reload(): void
    {
        // Reset the session
        self::$is_logged_in = false;
        self::$user_id = null;
        self::$admin_code = '';
        self::$pricing_group = '';
        self::$duda_username = '';
        self::$stripe_username = '';
        self::$account_type = '';
        self::$billed_until = '';
        self::$amount_charged = 0;
        self::$payment_term = '';
        self::$first_name = '';
        self::$last_name = '';
        self::$email = '';

        // Get the user id from the session
        $user_id = intval(session()->get('user_id') ?? 0);

        // If the user id is zero exit
        if ($user_id == 0) {
            return;
        }

        // Attempt to load the user from the database (exclude deleted users)
        $userQuery = DB::table('user')->where('id', $user_id);
        if (Schema::hasColumn('user', 'deleted')) {
            $userQuery->where('deleted', false);
        }
        $user_search = $userQuery->first();
        if ($user_search === null) {
            session()->set('user_id', 0);
            return;
        }

        // If we got here then a valid user is logged in, and we want to record their data
        self::$is_logged_in = true;
        self::$user_id = $user_search->id;
        self::$admin_code = $user_search->admin_code;
        self::$pricing_group = $user_search->pricing_group;
        self::$duda_username = $user_search->duda_username;
        self::$stripe_username = $user_search->stripe_username;
        self::$account_type = $user_search->account_type;
        self::$billed_until = $user_search->billed_until;
        self::$amount_charged = $user_search->amount_charged;
        self::$payment_term = $user_search->payment_term;
        self::$first_name = $user_search->first_name;
        self::$last_name = $user_search->last_name;
        self::$email = $user_search->email;

    }

    public static function isLoggedIn(): bool
    {
        if (self::$is_loaded === false) {
            self::reload();
        }
        return self::$is_logged_in;
    }

    public static function getUserId(): ?int
    {
        if (self::$is_loaded === false) {
            self::reload();
        }
        return self::$user_id;
    }

    public static function getAdminCode(): ?string
    {
        if (self::$is_loaded === false) {
            self::reload();
        }
        return self::$admin_code;
    }

    public static function getPricingGroup(): ?string
    {
        if (self::$is_loaded === false) {
            self::reload();
        }
        return self::$pricing_group;
    }

    public static function getDudaUsername(): ?string
    {
        if (self::$is_loaded === false) {
            self::reload();
        }
        if (self::$is_logged_in === false) {
            return null;
        }
        if (self::$duda_username === null) {
            $duda = new Partner();
            $duda_username = $duda->createAccount(self::$email, self::$first_name, self::$last_name);
            DB::table('user')->where('id', self::$user_id)->update(['duda_username' => $duda_username]);
        }
        self::reload();
        return self::$duda_username;
    }

    public static function getStripeUsername(): ?string
    {
        if (self::$is_loaded === false) {
            self::reload();
        }
        if (self::$is_logged_in === false) {
            return null;
        }
        if (self::$stripe_username === null) {
            $stripe_username = Customer::create([
                'email' => CurrentUser::getEmail(),
                'name' => trim(CurrentUser::getFirstName() . ' ' . CurrentUser::getLastName()),
            ]);
            DB::table('user')->where('id', self::$user_id)->update(['stripe_username' => $stripe_username]);
        }
        self::reload();
        return self::$stripe_username;
    }

    public static function getAccountType(): ?string
    {
        if (self::$is_loaded === false) {
            self::reload();
        }
        return self::$account_type;
    }

    public static function getBilledUntil(): ?string
    {
        if (self::$is_loaded === false) {
            self::reload();
        }
        return self::$billed_until;
    }

    public static function getAmountCharged(): ?int
    {
        if (self::$is_loaded === false) {
            self::reload();
        }
        return self::$amount_charged;
    }

    public static function getPaymentTerm(): ?string
    {
        if (self::$is_loaded === false) {
            self::reload();
        }
        return self::$payment_term;
    }

    public static function getFirstName(): ?string
    {
        if (self::$is_loaded === false) {
            self::reload();
        }
        return self::$first_name;
    }

    public static function getLastName(): ?string
    {
        if (self::$is_loaded === false) {
            self::reload();
        }
        return self::$last_name;
    }

    public static function getEmail(): ?string
    {
        if (self::$is_loaded === false) {
            self::reload();
        }
        return self::$email;
    }


}
