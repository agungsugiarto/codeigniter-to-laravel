<?php

namespace App\Legacy\Core\PendingRouteTransformers;

use App\Legacy\Core\CI_Controller;
use App\Legacy\Core\MY_Controller;
use OpenDesa\RouteDiscovery\PendingRouteTransformers\RejectDefaultControllerMethodRoutes;

class RejectDefaultCIControllerMethodRoutes extends RejectDefaultControllerMethodRoutes
{
    /**
     * @var array<int, string>
     */
    public array $rejectMethodsInClasses = [
        CI_Controller::class,
        MY_Controller::class,
    ];
}
