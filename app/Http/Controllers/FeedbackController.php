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
        } elseif ($emoji === 'neutral' || $emoji === 'sad') {
            if($buildingName == "Suntech Tower"){
                return redirect('https://forms.gle/LcazWpSizJnCYJ2q9');
            }
        }

        return redirect()->back();
    }
}
