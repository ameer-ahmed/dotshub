<?php

namespace App\Http\Requests;

use App\Enums\Platform;
use Illuminate\Foundation\Http\FormRequest;

abstract class PlatformRequest extends FormRequest
{
    abstract public static function platform(): Platform;
}
