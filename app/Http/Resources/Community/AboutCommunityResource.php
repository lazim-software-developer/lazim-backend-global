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
        $services = $this->services->where('type','inhouse')->unique();
        $facilitiesData = $facilities->map(function ($facility) {
            return [
                'id' => $facility->id,
                'name' => $facility->name,
                'icon' => $facility->icon,
            ];
        });
        
        $servicesData = $services->map(function ($service) {
            return [
                'id' => $service->id,
                'name' => $service->name,
                'icon' => $service->icon,
            ];
        });
        
        return [
            "image" => env('AWS_URL').'/'.'dev/1702022013.pdf',
            "about" => 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Officiis sint asperiores sapiente voluptatum, atque delectus facere repellendus dolor esse, temporibus nesciunt iure ratione consequuntur, fugit numquam recusandae id nulla rerum iusto. Unde delectus quos fuga ad officiis fugit facilis nam quas recusandae temporibus perferendis tempora id quibusdam illo, a aperiam praesentium totam obcaecati molestias similique exercitationem. Veritatis aliquid ipsam similique doloribus alias, saepe reiciendis quaerat expedita, modi perspiciatis, perferendis accusantium nostrum esse laborum eaque soluta quidem rerum autem magnam odit eos sint incidunt ducimus. Molestiae maxime hic tempora voluptas accusamus sed! Unde corrupti facere dolorem suscipit harum dolore, nemo illum.',
            "facilities" => $facilitiesData,
            "services" => $servicesData,
        ];
    }
}
