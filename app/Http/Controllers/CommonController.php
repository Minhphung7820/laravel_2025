<?php

namespace App\Http\Controllers;

use App\Models\District;
use App\Models\Province;
use App\Models\Ward;
use Illuminate\Http\Request;

class CommonController extends Controller
{
    public function getProvinces(Request $request)
    {
        return $this->responseSuccess(Province::select(['code', 'full_name'])->get());
    }

    public function getDistricts(Request $request)
    {
        return $this->responseSuccess(
            District::where('province_code', $request['province_code'])
                ->select(['code', 'full_name'])
                ->get()
        );
    }

    public function getWards(Request $request)
    {
        return $this->responseSuccess(
            Ward::where('district_code', $request['district_code'])
                ->select(['code', 'full_name'])
                ->get()
        );
    }
}
