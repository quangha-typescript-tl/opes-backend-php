<?php

namespace App\Http\Controllers;
use App\HashTag;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Request;

class HashTagController extends Controller
{
    public function getListHashTag()
    {
        $hashTag = Request::input('hashTag') ? Request::input('hashTag') : '';
        $result = HashTag::getListHashTag($hashTag);

        if ($result['hashTag']) {
            $status = config('constants.http_status.HTTP_GET_SUCCESS');
            return response()->json($result, $status);
        } else {
            $status = config('constants.http_status.HTTP_INTERNAL_SERVER_ERROR');
            $message = trans('get hashTag fail');
            return response()->json($message, $status);
        }
    }
}
