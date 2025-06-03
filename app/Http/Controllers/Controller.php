<?php

namespace App\Http\Controllers;

use App\Traits\UploadFileTrait;
use App\Traits\ApiResponseTrait;

abstract class Controller
{
    use UploadFileTrait, ApiResponseTrait;
}
