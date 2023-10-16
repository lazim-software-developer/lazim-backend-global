<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CustomResponseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */

    public static $wrap = null;

    public function toArray($request)
    {
        return [
            'title' => $this['title'] ?? 'Response',
            'message' => $this['message'],
            'errorCode' => $this['errorCode'] ?? null,
            'status' => $this['status'] ?? 'error'
        ];
    }
}
