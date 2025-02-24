<?php

namespace App\Core\Traits;

use Illuminate\Http\Resources\Json\JsonResource;

trait PaginatedResponseTrait
{
    /**
     * Map response data to pagination and items structure.
     *
     * @param  array  $responseData
     * @return array
     */
    public function mapPaginatedResponse(array $responseData)
    {


        // Check if the items are empty, and return an empty result if true
        if (empty($responseData['data']['items'])) {
            return $this->getEmptyPaginatedResponse();
        }

        $pagination = [
            'current_page' => $responseData['data']['current_page'],
            'per_page' => $responseData['data']['per_page'],
            'total' => $responseData['data']['total'],
            'total_pages' => $responseData['data']['total_pages'],
            'next_page_url' => $responseData['data']['next_page_url'],
            'prev_page_url' => $responseData['data']['prev_page_url'],
        ];

        $items = collect($responseData['data']['items'])->map(function ($item) {
            return $item; // Automatically includes all fields dynamically without needing to specify
        });

        return [
            'pagination' => $pagination,
            'items' => $items,
        ];
    }

    public function getEmptyPaginatedResponse()
    {
        return [
            'pagination' => [
                'current_page' => 1,
                'per_page' => 0,
                'total' => 0,
                'total_pages' => 1,
                'next_page_url' => null,
                'prev_page_url' => null,
            ],
            'items' => [],
        ];
    }
}
