<?php

declare(strict_types = 1);

namespace League\Fractal\Hal\Test\Stub\Transformer;

use League\Fractal\Hal\Test\Stub\Order;
use League\Fractal\Resource\ResourceInterface;
use League\Fractal\TransformerAbstract;

final class HalOrderTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'customer',
    ];

    public function transform(Order $order)
    {
        return [
            'id' => $order->getId(),
            'total' => $order->getTotal(),
            'currency' => $order->getCurrency(),
            'status' => $order->getStatus(),
        ];
    }

    public function includeCustomer(Order $order): ResourceInterface
    {
        return $this->item($order->getCustomer(), new HalCustomerTransformer(), 'customer');
    }
}
