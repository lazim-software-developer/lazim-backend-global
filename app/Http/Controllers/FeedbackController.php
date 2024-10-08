<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    public function index(Request $request){
        $buildingName = $request->input('buildingName');
        return view('feedback.index', compact('buildingName'));
    }

    public function submitFeedback(Request $request)
    {
        $emoji = $request->input('emoji');
        $buildingName =  $request->input('buildingName');

        // Redirect based on the emoji selected
        if ($emoji === 'happy') {
            return redirect('https://search.google.com/local/writereview?placeid=ChIJ5fyZn9tlXz4RVlp__oiNaCY');
        } elseif ($emoji === 'neutral') {
            if($buildingName == "Suntech Tower"){
                return redirect('https://forms.gle/tG8ptEMggznXzw2K8');
            } elseif($buildingName == "Sunshine Residence"){
                return redirect('https://forms.gle/JU3WDAv4ZDE2Cwuu7');
            } elseif($buildingName == "Sunbeam Homes"){
                return redirect('https://forms.gle/t8BDBSsdMuMK1TPJ6');
            } elseif($buildingName == "Apex Atrium Building"){
                return redirect('https://forms.gle/98JERNVwCXSwJN817');
            } elseif($buildingName == "Spanish Twin Villa"){
                return redirect('https://forms.gle/4sJ6cjeGicbi9gAb9');
            } elseif($buildingName == "Empire Heights"){
                return redirect('https://forms.gle/uKQN4PG9rnhvRM3p9');
            } elseif($buildingName == "Suncity Homes"){
                return redirect('https://forms.gle/VR6u5Y3S8uiGDxyD8');
            } elseif($buildingName == "Continental Tower"){
                return redirect('https://forms.gle/rRe2UndLCEzc25Ly5');
            } elseif($buildingName == "Samana Greens"){
                return redirect('https://forms.gle/oMmH9aoYTeWsjrTT7');
            } elseif($buildingName == "2020 Marquis"){
                return redirect('https://forms.gle/KrcGa2NDPAmJpzp59');
            } elseif($buildingName == "East40"){
                return redirect('https://forms.gle/dvoSaLaAERwkrUek8');
            } elseif($buildingName == "Royal Residence"){
                return redirect('https://forms.gle/ZB8baB24XEjD6GHMA');
            }

        } elseif($emoji === 'sad') {
            if($buildingName == "Suntech Tower"){
                return redirect('https://forms.gle/j2LYLvwy7U73MHLm9');
            } elseif($buildingName == "Sunshine Residence"){
                return redirect('https://forms.gle/NhqZAhXaHJis4wby7');
            } elseif($buildingName == "Sunbeam Homes"){
                return redirect('https://forms.gle/BV2MJQmQzueuy64J9');
            } elseif($buildingName == "Apex Atrium Building"){
                return redirect('https://forms.gle/4tuJ3Ls5K6L3uGY37');
            } elseif($buildingName == "Spanish Twin Villa"){
                return redirect('https://forms.gle/bnk8wZisHygLfKUz9');
            } elseif($buildingName == "Empire Heights"){
                return redirect('https://forms.gle/5zvmGijtwAPeNryr8');
            } elseif($buildingName == "Suncity Homes"){
                return redirect('https://forms.gle/vUrBGpU8LVHBXYXW6');
            } elseif($buildingName == "Continental Tower"){
                return redirect('https://forms.gle/soehBg7PBRgPtFN3A');
            } elseif($buildingName == "Samana Greens"){
                return redirect('https://forms.gle/2Qcd2HV4AqbNrW6R7');
            } elseif($buildingName == "2020 Marquis"){
                return redirect('https://forms.gle/ZRjiuV2FNhimUqPW8');
            } elseif($buildingName == "East40"){
                return redirect('https://forms.gle/CtPYCkuEyH6Yb2U88');
            } elseif($buildingName == "Royal Residence"){
                return redirect('https://forms.gle/g7LWjtioF4gFUJjq8');
            }
        }


        return redirect()->back();
    }
}
