<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    use ApiResponses;
    
    public function include(string $relationship): bool
    {
        $params = request()->get('include');
        if(!$params){
            return false;
        }

        $includedValues = explode(',',strtolower($params));
        return in_array(strtolower($relationship), $includedValues);
    }
} 