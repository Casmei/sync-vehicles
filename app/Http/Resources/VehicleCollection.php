<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;

class VehicleCollection extends ResourceCollection
{
    public $collects = VehicleResource::class;

    public function toArray($request): array
    {
        $p = $this->resource;

        $links = [];
        $links[] = [
            'url' => $p->onFirstPage() ? null : $p->previousPageUrl(),
            'label' => '&laquo; Previous',
            'page' => $p->onFirstPage() ? null : $p->currentPage() - 1,
            'active' => false,
        ];

        foreach ($p->getUrlRange(1, $p->lastPage()) as $page => $url) {
            $links[] = [
                'url' => $url,
                'label' => (string) $page,
                'page' => $page,
                'active' => $page === $p->currentPage(),
            ];
        }

        $links[] = [
            'url' => $p->hasMorePages() ? $p->nextPageUrl() : null,
            'label' => 'Next &raquo;',
            'page' => $p->hasMorePages() ? $p->currentPage() + 1 : null,
            'active' => false,
        ];

        return [
            'current_page' => $p->currentPage(),
            'data' => VehicleResource::collection($this->collection),
            'first_page_url' => $p->url(1),
            'from' => $p->firstItem(),
            'last_page' => $p->lastPage(),
            'last_page_url' => $p->url($p->lastPage()),
            'links' => $links,
            'next_page_url' => $p->nextPageUrl(),
            'path' => $p->path(),
            'per_page' => $p->perPage(),
            'prev_page_url' => $p->previousPageUrl(),
            'to' => $p->lastItem(),
            'total' => $p->total(),
        ];
    }
}