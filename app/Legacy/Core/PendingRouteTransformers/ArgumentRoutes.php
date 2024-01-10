<?php

namespace App\Legacy\Core\PendingRouteTransformers;

use ReflectionMethod;
use Illuminate\Support\Collection;
use Illuminate\Routing\Controller as BaseController;
use OpenDesa\RouteDiscovery\PendingRoutes\PendingRoute;
use OpenDesa\RouteDiscovery\PendingRoutes\PendingRouteAction;
use OpenDesa\RouteDiscovery\PendingRouteTransformers\PendingRouteTransformer;

class ArgumentRoutes implements PendingRouteTransformer
{
    /**
     * {@inheritdoc}
     */
    public function transform(Collection $pendingRoutes): Collection
    {
        return $pendingRoutes->each(function (PendingRoute $pendingRoute) {
            $pendingRoute->actions->each(function (PendingRouteAction $action) use ($pendingRoute) {
                if ($method = $pendingRoute->class->getMethod($action->method->name)) {
                    foreach ($method->getParameters() as $params) {
                        $action->uri = $params->isOptional()
                            ? "{$action->uri}/{{$params->getName()}?}"
                            : "{$action->uri}/{{$params->getName()}}";
                    }
                }
            });
        });
    }
}