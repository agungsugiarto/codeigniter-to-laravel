<?php

namespace App\Legacy\Core\PendingRouteTransformers;

use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use OpenDesa\RouteDiscovery\PendingRoutes\PendingRoute;
use OpenDesa\RouteDiscovery\PendingRoutes\PendingRouteAction;
use OpenDesa\RouteDiscovery\PendingRouteTransformers\PendingRouteTransformer;

class RejectUriContainDotRoutes implements PendingRouteTransformer
{
    /**
     * {@inheritdoc}
     */
    public function transform(Collection $pendingRoutes): Collection
    {
        return $pendingRoutes->each(function (PendingRoute $pendingRoute) {
            $pendingRoute->actions->each(function (PendingRouteAction $action) {
                $action->uri = Str::remove('.php', $action->uri);
                $action->name = Str::remove('.php', $action->name);
            });
        });
    }
}