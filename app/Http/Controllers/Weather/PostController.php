<?php

namespace App\Http\Controllers\Weather;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Http\Requests\Weather\SearchRequest;
use App\Models\Weather;


class PostController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(SearchRequest $request)
    {
        $areaNumber = $request->areaNumber();
        $date = date("Y-m-d");
        $exists = Weather::where('areaNumber', $areaNumber)
                    ->where('date', $date)
                    ->exists();
        
        if($exists) {
            $weather = Weather::where('areaNumber', $areaNumber)
                ->where('date', $date)
                ->first();
            $forecast = json_encode([[
                    "telop" => $weather->telop,
                    "date" => "{$weather->date}(データベースから取得)",
            ]]);
        } else {
            $response = Http::get("https://weather.tsukumijima.net/api/forecast/city/{$areaNumber}");
            $forecast = json_decode($response,true)['forecasts'];
            $weather = new Weather;
            $weather->areaNumber = $areaNumber;
            $weather->date = date("Y-m-d");
            $weather->telop = $forecast[0]['telop'];
            $weather->save();
        }
        
        
        return response($forecast, 200);
    }
}
