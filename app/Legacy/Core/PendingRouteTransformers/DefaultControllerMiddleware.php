<?php

namespace App\Legacy\Core\PendingRouteTransformers;

use Illuminate\Support\Collection;
use OpenDesa\RouteDiscovery\PendingRoutes\PendingRoute;
use OpenDesa\RouteDiscovery\PendingRoutes\PendingRouteAction;
use OpenDesa\RouteDiscovery\PendingRouteTransformers\PendingRouteTransformer;

class DefaultControllerMiddleware implements PendingRouteTransformer
{
    /**
     * {@inheritdoc}
     */
    public function transform(Collection $pendingRoutes): Collection
    {
        return $pendingRoutes->each(function (PendingRoute $pendingRoute) {
            $pendingRoute->actions->each(function (PendingRouteAction $action) {
                $action->middleware = [
                    \App\Http\Middleware\EncryptCookies::class,
                    \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
                    \Illuminate\Session\Middleware\StartSession::class,
                    \Illuminate\View\Middleware\ShareErrorsFromSession::class,
                    \Illuminate\Routing\Middleware\SubstituteBindings::class,
                ];
            });
        });
    }
}