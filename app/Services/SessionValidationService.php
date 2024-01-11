<?php
namespace App\Services;

use Illuminate\Support\Facades\Session;

class SessionValidationService
{
    public function validate(array $keys)
    {
        foreach ($keys as $key) {
            if (!Session::has($key)) {
                throw new \Exception("$key not found in session.");
            }
        }
    }
}
