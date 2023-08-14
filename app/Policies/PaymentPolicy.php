<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Payment;
use Illuminate\Auth\Access\Response;

class PaymentPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool|Response
    {
        if ($user->is_admin) {
            return true;
        }

        return Response::denyAsNotFound();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Payment $payment): bool|Response
    {
        if ($user->is_admin) {
            return true;
        }

        if ($payment->user()->is($user)) {
            return true;
        }

        return Response::denyAsNotFound();
    }

    /**
     * Determine whether the user can create models.
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
     */
    public function create(User $user): bool|Response
    {
        return true;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Payment $payment): bool|Response
    {
        if ($payment->user()->is($user)) {
            return true;
        }

        return Response::denyAsNotFound();
    }
}
