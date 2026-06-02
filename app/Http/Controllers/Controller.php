<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

/**
 * Base controller for the application.
 *
 * Laravel 11+ no longer ships authorization helpers in the controller class
 * by default; the {@see AuthorizesRequests} trait is re-introduced here so
 * subclasses may call `$this->authorize(...)` against registered policies.
 */
abstract class Controller
{
    use AuthorizesRequests;
}
