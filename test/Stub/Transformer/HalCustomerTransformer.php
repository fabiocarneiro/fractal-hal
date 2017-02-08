<?php

declare(strict_types = 1);

namespace League\Fractal\Hal\Test\Stub\Transformer;

use League\Fractal\Hal\Test\Stub\Customer;
use League\Fractal\TransformerAbstract;

final class HalCustomerTransformer extends TransformerAbstract
{
    public function transform(Customer $customer): array
    {
        return [
            'id' => $customer->getId(),
            'name' => $customer->getName(),
        ];
    }
}
