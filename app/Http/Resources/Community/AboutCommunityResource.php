<?php

namespace App\Http\Resources\Community;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AboutCommunityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $facilities = $this->facilities->unique();
        $services = $this->services->where('type', 'inhouse')->unique();

        $facilitiesData = $facilities->map(function ($facility) {
            return [
                'id' => $facility->id,
                'name' => $facility->name,
                'icon' => env('AWS_URL') . '/' . $facility->icon,
            ];
        });

        $servicesData = $services->map(function ($service) {
            return [
                'id' => $service->id,
                'name' => $service->name,
                'icon' => env('AWS_URL') . '/' . $service->icon,
            ];
        });

        return [
            "image" => $this->cover_photo ?  env('AWS_URL') . '/' . $this->cover_photo : env('AWS_URL') . '/' . 'dev/images/amenity.jpg',
            "about" => $this->description,
            "facilities" => $facilitiesData,
            "services" => $servicesData,
            "slug" => $this->slug,
        ];
    }
}
