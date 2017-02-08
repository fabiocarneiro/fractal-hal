<?php

declare(strict_types = 1);

namespace League\Fractal\Hal\Test\Serializer;

use League\Fractal\Hal\Serializer\HalSerializer;
use League\Fractal\Hal\Test\Stub\Customer;
use League\Fractal\Hal\Test\Stub\Order;
use League\Fractal\Hal\Test\Stub\Transformer\HalOrderTransformer;
use League\Fractal\Manager;
use League\Fractal\Pagination\PaginatorInterface;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Scope;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;
use Zend\Diactoros\Uri;

final class HalSerializerTest extends TestCase
{
    public function testSerializesItemResourceWithoutRelationships()
    {
        $manager = new Manager();

        $uri = new Uri('/order');

        $manager->setSerializer(new HalSerializer($uri));

        $order = new Order(1, 30.00, 'USD', 'shipped', new Customer(1, 'John Doe'));

        $resource = new Item($order, new HalOrderTransformer(), 'order');

        $scope = new Scope($manager, $resource);

        $expected = [
            'total' => 30.00,
            'currency' => 'USD',
            'status' => 'shipped',
            '_links' => [
                'self' => [
                    'href' => '/order/1',
                ],
            ],
        ];

        $this->assertSame($expected, $scope->toArray());
        $expectedJson = '{"total":30,"currency":"USD","status":"shipped","_links":{"self":{"href":"\/order\/1"}}}';
        $this->assertSame($expectedJson, $scope->toJson());
    }

    public function testSerializesItemResourceWithRelationships()
    {
        $manager = new Manager();

        $manager->parseIncludes(['customer']);

        $uri = new Uri('/order');

        $manager->setSerializer(new HalSerializer($uri));

        $order = new Order(1, 30.00, 'USD', 'shipped', new Customer(1, 'John Doe'));

        $resource = new Item($order, new HalOrderTransformer(), 'order');

        $scope = new Scope($manager, $resource);

        $expected = [
            'total' => 30.00,
            'currency' => 'USD',
            'status' => 'shipped',
            '_embedded' => [
                'customer' => [
                    'name' => 'John Doe',
                    '_links' => [
                        'self' => [
                            'href' => '/customer/1',
                        ],
                    ],
                ],
            ],
            '_links' => [
                'self' => [
                    'href' => '/order/1',
                ],
            ],
        ];

        $this->assertSame($expected, $scope->toArray());
        $expectedJson = '{"total":30,"currency":"USD","status":"shipped","_embedded":{"customer":{"name":"John Doe","_links":{"self":{"href":"\/customer\/1"}}}},"_links":{"self":{"href":"\/order\/1"}}}';
        $this->assertSame($expectedJson, $scope->toJson());
    }

    public function testSerializesCollectionResourceWithoutRelationships()
    {
        $manager = new Manager();

        $uri = new Uri('/orders');

        $manager->setSerializer(new HalSerializer($uri));

        $orders = [
            new Order(1, 30.00, 'USD', 'shipped', new Customer(1, 'John doe')),
            new Order(2, 20.00, 'USD', 'processing', new Customer(2, 'Jane Roe')),
        ];

        $resource = new Collection($orders, new HalOrderTransformer(), 'orders');

        $scope = new Scope($manager, $resource);

        $expected = [
            '_links' => [
                'self' => [
                    'href' => (string) $uri,
                ],
            ],
            '_embedded' => [
                'orders' => [
                    [
                        'total' => 30.00,
                        'currency' => 'USD',
                        'status' => 'shipped',
                        '_links' => [
                            'self' => [
                                'href' => '/orders/1',
                            ],
                        ],
                    ],
                    [
                        'total' => 20.00,
                        'currency' => 'USD',
                        'status' => 'processing',
                        '_links' => [
                            'self' => [
                                'href' => '/orders/2',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->assertSame($expected, $scope->toArray());
        $expectedJson = '{"_links":{"self":{"href":"\/orders"}},"_embedded":{"orders":[{"total":30,"currency":"USD","status":"shipped","_links":{"self":{"href":"\/orders\/1"}}},{"total":20,"currency":"USD","status":"processing","_links":{"self":{"href":"\/orders\/2"}}}]}}';
        $this->assertSame($expectedJson, $scope->toJson());
    }

    public function testSerializesCollectionResourceWithRelationships()
    {
        $manager = new Manager();

        $manager->parseIncludes(['customer']);

        $uri = new Uri('/orders');

        $manager->setSerializer(new HalSerializer($uri));

        $orders = [
            new Order(1, 30.00, 'USD', 'shipped', new Customer(1, 'John Doe')),
            new Order(2, 20.00, 'USD', 'processing', new Customer(2, 'Jane Roe')),
        ];

        $resource = new Collection($orders, new HalOrderTransformer(), 'orders');

        $scope = new Scope($manager, $resource);

        $expected = [
            '_links' => [
                'self' => [
                    'href' => (string) $uri,
                ],
            ],
            '_embedded' => [
                'orders' => [
                    [
                        'total' => 30.00,
                        'currency' => 'USD',
                        'status' => 'shipped',
                        '_embedded' => [
                            'customer' => [
                                'name' => 'John Doe',
                                '_links' => [
                                    'self' => [
                                        'href' => '/customer/1',
                                    ],
                                ],
                            ],
                        ],
                        '_links' => [
                            'self' => [
                                'href' => '/orders/1',
                            ],
                        ],
                    ],
                    [
                        'total' => 20.00,
                        'currency' => 'USD',
                        'status' => 'processing',
                        '_embedded' => [
                            'customer' => [
                                'name' => 'Jane Roe',
                                '_links' => [
                                    'self' => [
                                        'href' => '/customer/2',
                                    ],
                                ],
                            ],
                        ],
                        '_links' => [
                            'self' => [
                                'href' => '/orders/2',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->assertSame($expected, $scope->toArray());
        $expectedJson = '{"_links":{"self":{"href":"\/orders"}},"_embedded":{"orders":[{"total":30,"currency":"USD","status":"shipped","_embedded":{"customer":{"name":"John Doe","_links":{"self":{"href":"\/customer\/1"}}}},"_links":{"self":{"href":"\/orders\/1"}}},{"total":20,"currency":"USD","status":"processing","_embedded":{"customer":{"name":"Jane Roe","_links":{"self":{"href":"\/customer\/2"}}}},"_links":{"self":{"href":"\/orders\/2"}}}]}}';
        $this->assertSame($expectedJson, $scope->toJson());
    }

    public function testSerializesPaginatedCollectionResource()
    {
        $manager = new Manager();

        $uri = new Uri('/orders');

        $manager->setSerializer(new HalSerializer($uri));

        $orders = [
            new Order(1, 30.00, 'USD', 'shipped', new Customer(1, 'John Doe')),
            new Order(2, 20.00, 'USD', 'processing', new Customer(2, 'Jane Roe')),
            new Order(3, 15.00, 'USD', 'processing', new Customer(3, 'John Smith')),
            new Order(4, 35.00, 'USD', 'shipped', new Customer(2, 'Jane Roe')),
            new Order(5, 5.00, 'USD', 'processing', new Customer(1, 'John Doe')),
        ];

        $resource = new Collection($orders, new HalOrderTransformer(), 'orders');

        /** @var PaginatorInterface|PHPUnit_Framework_MockObject_MockObject $paginator */
        $paginator = $this->createMock(PaginatorInterface::class);

        $paginator->expects($this->any())->method('getTotal')->willReturn('10');
        $paginator->expects($this->any())->method('getCurrentPage')->willReturn('1');
        $paginator->expects($this->any())->method('getCount')->willReturn('5');
        $paginator->expects($this->any())->method('getLastPage')->willReturn('2');
        $paginator->expects($this->any())->method('getPerPage')->willReturn('5');
        $paginator->expects($this->any())->method('getUrl')->with(2)->willReturn('/orders?page=2');

        $resource->setPaginator($paginator);

        $scope = new Scope($manager, $resource);

        $expected = [
            '_links' => [
                'self' => [
                    'href' => '/orders',
                ],
            ],
            '_embedded' => [
                'orders' => [
                    [
                        'total' => 30.0,
                        'currency' => 'USD',
                        'status' => 'shipped',
                        '_links' => [
                            'self' => [
                                'href' => '/orders/1',
                            ],
                        ],
                    ],
                    [
                        'total' => 20.0,
                        'currency' => 'USD',
                        'status' => 'processing',
                        '_links' => [
                            'self' => [
                                'href' => '/orders/2',
                            ],
                        ],
                    ],
                    [
                        'total' => 15.0,
                        'currency' => 'USD',
                        'status' => 'processing',
                        '_links' => [
                            'self' => [
                                'href' => '/orders/3',
                            ],
                        ],
                    ],
                    [
                        'total' => 35.0,
                        'currency' => 'USD',
                        'status' => 'shipped',
                        '_links' => [
                            'self' => [
                                'href' => '/orders/4',
                            ],
                        ],
                    ],
                    [
                        'total' => 5.0,
                        'currency' => 'USD',
                        'status' => 'processing',
                        '_links' => [
                            'self' => [
                                'href' => '/orders/5',
                            ],
                        ],
                    ],
                ],
            ],
            'pagination' => [
                'total' => 10,
                'count' => 5,
                'per_page' => 5,
                'current_page' => 1,
                'total_pages' => 2,
                '_links' => [
                    'next' => '/orders?page=2',
                ],
            ],
        ];

        $this->assertSame($expected, $scope->toArray());
        $expectedJson = '{"_links":{"self":{"href":"\/orders"}},"_embedded":{"orders":[{"total":30,"currency":"USD","status":"shipped","_links":{"self":{"href":"\/orders\/1"}}},{"total":20,"currency":"USD","status":"processing","_links":{"self":{"href":"\/orders\/2"}}},{"total":15,"currency":"USD","status":"processing","_links":{"self":{"href":"\/orders\/3"}}},{"total":35,"currency":"USD","status":"shipped","_links":{"self":{"href":"\/orders\/4"}}},{"total":5,"currency":"USD","status":"processing","_links":{"self":{"href":"\/orders\/5"}}}]},"pagination":{"total":10,"count":5,"per_page":5,"current_page":1,"total_pages":2,"_links":{"next":"\/orders?page=2"}}}';
        $this->assertSame($expectedJson, $scope->toJson());
    }
}
