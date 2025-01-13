<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Http\Resources\Community\AboutCommunityResource;
use App\Http\Resources\OfferPromotionsResource;
use App\Models\Building\Building;
use App\Models\OfferPromotion;

use function PHPUnit\Framework\isEmpty;
use Illuminate\Http\Request;

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

    public function emergencyHotline(Building $building,Request $request)
    {
        $emergencyNumbers = $building->emergencyNumbers()->paginate($request->paginate ?? 10);

        if($emergencyNumbers->isEmpty()){
            return response()->json(['message' => 'No Emergency numbers currently available. Please try again later.'], 404);
        }
        return $emergencyNumbers;
    }

    public function offerPromotions(Building $building)
    {
        $activeOfferPromotion = OfferPromotion::where('building_id', $building->id)->whereDate('end_date', '>=', now())->where('active',true)->get();
        if($activeOfferPromotion->isEmpty()){
            return response()->json(['message' => 'No active offer promotions currently available. Please try again later.'], 404);
        }
        return  OfferPromotionsResource::collection($activeOfferPromotion);
    }
}
