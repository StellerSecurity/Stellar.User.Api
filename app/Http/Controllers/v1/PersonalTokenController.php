<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\PersonalAccessToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PersonalTokenController extends Controller
{

    /**
     * @param String $token
     * @return JsonResponse
     */
    public function find(String $token): JsonResponse
    {

        $personalToken = PersonalAccessToken::findToken($token);

        if($personalToken === null) {
            return response()->json(['response_code' => 400]);
        }

        return response()->json($personalToken);

    }

}
