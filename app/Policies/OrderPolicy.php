<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Order;
use Illuminate\Auth\Access\Response;

class OrderPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Order $order): bool|Response
    {
        if ($user->is_admin) {
            return true;
        }

        if ($order->user()->is($user)) {
            return true;
        }

        return Response::denyAsNotFound();
    }

    /**
     * Determine whether the user can create models.
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
     */
    public function create(User $user): bool
    {
        return true;
    }
}
