<?php

declare(strict_types=1);

/**
 * Youth Registration Routes (v1 API)
 * 
 * Public endpoints for youth registration (no auth required)
 */

use App\Controllers\YouthRecordController;
use Slim\App;

return function (App $app): void {
    $controller = $app->getContainer()->get(YouthRecordController::class);

    // Public youth routes
    $app->group('/v1/youth', function ($group) use ($controller) {
        // POST /v1/youth/register - Public registration
        $group->post('/register', [$controller, 'register']);
        
    }); // No Auth Middleware attached
};
