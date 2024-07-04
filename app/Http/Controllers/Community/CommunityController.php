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
        if($building->emergencyNumbers->isEmpty()){
            return response()->json(['message' => 'No Emergency numbers currently available. Please try again later.'], 404);
        }
        return $building->emergencyNumbers;
    }
 
    public function offerPromotions(Building $building)
    {
        $activeOfferPromotion = OfferPromotion::where('building_id', $building->id)->whereDate('end_date', '>=', now())->get();
        if($activeOfferPromotion->isEmpty()){
            return response()->json(['message' => 'No active offer promotions currently available. Please try again later.'], 404);
        }
        return  OfferPromotionsResource::collection($activeOfferPromotion);
    }
}
