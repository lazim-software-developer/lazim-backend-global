<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Http\Resources\Community\AboutCommunityResource;
use App\Http\Resources\OfferPromotionsResource;
use App\Models\Building\Building;
use App\Models\OfferPromotion;

use function PHPUnit\Framework\isEmpty;

class CommunityController extends Controller
{
    public function about(Building $building)
    {
        return new AboutCommunityResource($building);
    }

    public function rules(Building $building)
    {
        return [
            "rules" => $building->ruleregulations->value('rule_regulation'),
        ];
    }

    public function emergencyHotline(Building $building)
    {
        return isEmpty($building->emergencyNumbers) ? 'No Emergency numbers yet! create in admin panel.' : $building->emergencyNumbers;
    }

    public function offerPromotions(Building $building)
    {
        $activeOfferPromotion = OfferPromotion::where('building_id', $building->id)->whereDate('end_date', '>=', now())->get();
        return isEmpty($activeOfferPromotion) ? 'No Active Offers or Promotions! create in admin panel.' : OfferPromotionsResource::collection($activeOfferPromotion);
    }
}
