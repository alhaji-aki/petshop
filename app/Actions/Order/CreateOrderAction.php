<?php

namespace App\Actions\Order;

use App\Models\User;
use App\Models\Order;
use RuntimeException;
use App\Models\Payment;
use App\Models\Product;
use App\Models\OrderStatus;
use App\Models\OrderProduct;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CreateOrderAction
{
    /**
     * @param array{
     *   order_status_uuid: string,
     *   payment_uuid: string,
     *   products: array<int, array{uuid:string, quantity:int}>,
     *   address: array<string, string>
     * } $data
     */
    public function execute(User $user, array $data): Order
    {
        $orderStatusId = $this->getOrderStatusId($data['order_status_uuid']);

        $paymentId = $this->getPaymentId($user, $data['payment_uuid']);

        $orderProducts = $this->convertOrderProductsForInsertion($data['products']);

        return DB::transaction(function () use ($user, $orderStatusId, $paymentId, $data, $orderProducts) {
            /** @var float $amount */
            $amount = $orderProducts->sum('amount');

            /** @var \App\Models\Order $order */
            $order = $this->createOrder(
                $user->id,
                $orderStatusId,
                $paymentId,
                $data['address'],
                $amount
            );

            $orderProducts->each(fn ($item) => OrderProduct::query()->create([...$item, 'order_id' => $order->id]));

            return $order;
        });
    }

    private function getOrderStatusId(string $uuid): int
    {
        $orderStatus = OrderStatus::query()->where('uuid', $uuid)->first();

        if (!$orderStatus) {
            throw new RuntimeException('Failed to create order. Invalid order status submitted');
        }

        return $orderStatus->id;
    }

    private function getPaymentId(User $user, string $uuid): int
    {
        $payment = Payment::query()->whereBelongsTo($user)->where('uuid', $uuid)->first();

        if (!$payment) {
            throw new RuntimeException('Failed to create order. Invalid payment submitted');
        }

        return $payment->id;
    }

    /**
     * @param array<int, array{uuid: string, quantity: int}> $items
     * @return Collection<int, array{product_id: int, quantity: int, unit_price: float, amount: float}>
     */
    private function convertOrderProductsForInsertion(array $items): Collection
    {
        $products = Product::query()->whereIn('uuid', data_get($items, '*.uuid'))->get();

        return collect($items)->map(function (array $item) use ($products) {
            /** @var \App\Models\Product $product */
            $product = $products->where('uuid', $item['uuid'])->first();

            /** @var int $quantity */
            $quantity = $item['quantity'];

            return [
                'product_id' => $product->id,
                'quantity' => $quantity,
                'unit_price' => (float) $product->price,
                'amount' => (float) $product->price * $quantity,
            ];
        });
    }

    /**
     * @param array<string, string> $address
     */
    private function createOrder(int $userId, int $orderStatusId, int $paymentId, array $address, float $amount): Order
    {
        return Order::query()->create([
            'user_id' => $userId,
            'order_status_id' => $orderStatusId,
            'payment_id' => $paymentId,
            'address' => $address,
            'amount' => $amount,
            'delivery_fee' => $amount > 500 ? null : 15,
            'shipped_at' => null,
        ]);
    }
}
