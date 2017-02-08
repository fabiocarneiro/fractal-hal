<?php

declare(strict_types = 1);

namespace League\Fractal\Hal\Serializer;

use League\Fractal\Pagination\CursorInterface;
use League\Fractal\Pagination\PaginatorInterface;
use League\Fractal\Resource\ResourceInterface;
use League\Fractal\Serializer\SerializerAbstract;
use Psr\Http\Message\UriInterface;

final class HalSerializer extends SerializerAbstract
{
    /**
     * @var UriInterface|null
     */
    private $baseUrl;

    public function __construct(UriInterface $baseUrl = null)
    {
        $this->baseUrl = $baseUrl;
    }

    /**
     * {@inheritdoc}
     */
    public function collection($resourceKey, array $data): array
    {
        $resources = [];

        foreach ($data as $resource) {
            $resources[] = $this->item($resourceKey, $resource);
        }

        if (!$this->shouldIncludeLinks()) {
            return ['_embedded' => [$resourceKey => $resources]];
        }

        return [
            '_links' => [
                'self' => [
                    'href' => (string) $this->baseUrl,
                ],
            ],
            '_embedded' => [
                $resourceKey => $resources,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function item($resourceKey, array $data): array
    {
        if (!$this->shouldIncludeLinks()) {
            return $data;
        }

        if (array_key_exists('id', $data)) {
            $resourceKey = $resourceKey . '/' . $data['id'];
            unset($data['id']);
        }

        $data['_links'] = [
            'self' => [
                'href' => (string) $this->baseUrl->withPath('/' . $resourceKey),
            ],
        ];

        return $data;
    }

    protected function shouldIncludeLinks(): bool
    {
        return $this->baseUrl !== null;
    }

    /**
     * {@inheritdoc}
     */
    public function null(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function includedData(ResourceInterface $resource, array $data): array
    {
        $serializedData = [];

        foreach ($data as $value) {
            foreach ($value as $includeKey => $includeObject) {
                if (empty($includeObject)) {
                    continue;
                }

                $serializedData[$includeKey] = $includeObject;
            }
        }

        if (empty($serializedData)) {
            return [];
        }

        return ['_embedded' => $serializedData];
    }

    public function mergeIncludes($transformedData, $includedData): array
    {
        if (empty($includedData)) {
            return $transformedData;
        }

        return array_merge($transformedData, ['_embedded' => $includedData]);
    }

    /**
     * {@inheritdoc}
     */
    public function sideloadIncludes(): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function meta(array $meta): array
    {
        if (empty($meta)) {
            return [];
        }

        return $meta;
    }

    /**
     * {@inheritdoc}
     */
    public function paginator(PaginatorInterface $paginator): array
    {
        $currentPage = (int) $paginator->getCurrentPage();
        $lastPage = (int) $paginator->getLastPage();

        $pagination = [
            'total' => (int) $paginator->getTotal(),
            'count' => (int) $paginator->getCount(),
            'per_page' => (int) $paginator->getPerPage(),
            'current_page' => $currentPage,
            'total_pages' => $lastPage,
        ];

        $pagination['_links'] = [];

        if ($currentPage > 1) {
            $pagination['_links']['previous'] = $paginator->getUrl($currentPage - 1);
        }

        if ($currentPage < $lastPage) {
            $pagination['_links']['next'] = $paginator->getUrl($currentPage + 1);
        }

        return ['pagination' => $pagination];
    }

    /**
     * {@inheritdoc}
     */
    public function cursor(CursorInterface $cursor): array
    {
        $cursor = [
            'current' => $cursor->getCurrent(),
            'prev' => $cursor->getPrev(),
            'next' => $cursor->getNext(),
            'count' => (int) $cursor->getCount(),
        ];

        return ['cursor' => $cursor];
    }
}
