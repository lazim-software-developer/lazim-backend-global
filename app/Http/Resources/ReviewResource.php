<?php

namespace App\Http\Resources;

use App\Enums\ReviewType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'       => $this->id,
            'oa_id'    => $this->oa_id,
            'flat_id'  => $this->flat_id,
            'type'     => $this->type->label(), 
            'comment'  => $this->comment,
            'feedback' => $this->feedback, 
        ];
    }
}
