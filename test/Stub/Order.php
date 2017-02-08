<?php

declare(strict_types = 1);

namespace League\Fractal\Hal\Test\Stub;

final class Order
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var float
     */
    private $total;

    /**
     * @var string
     */
    private $currency;

    /**
     * @var string
     */
    private $status;

    /**
     * @var Customer
     */
    private $customer;

    public function __construct(int $id, float $total, string $currency, string $status, Customer $customer)
    {
        $this->id = $id;
        $this->total = $total;
        $this->currency = $currency;
        $this->status = $status;
        $this->customer = $customer;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTotal(): float
    {
        return $this->total;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getCustomer(): Customer
    {
        return $this->customer;
    }
}
