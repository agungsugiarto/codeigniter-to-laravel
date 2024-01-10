<?php

return [
    /*
     * Routes will be registered for all controllers found in
     * these directories.
     */
    'discover_controllers_in_directory' => [
        app_path('Legacy/Controllers'),
    ],

    /*
     * Routes will be registered for all views found in these directories.
     * The key of an item will be used as the prefix of the uri.
     */
    'discover_views_in_directory' => [
        // 'docs' => resource_path('views/docs'),
    ],

    /*
     * After having discovered all controllers, these classes will manipulate the routes
     * before registering them to Laravel.
     *
     * In most cases, you shouldn't change these.
     */
    'pending_route_transformers' => [
        ...OpenDesa\RouteDiscovery\Config::defaultRouteTransformers(),
        \App\Legacy\Core\PendingRouteTransformers\RejectDefaultCIControllerMethodRoutes::class,
        \App\Legacy\Core\PendingRouteTransformers\RejectUriContainDotRoutes::class,
        \App\Legacy\Core\PendingRouteTransformers\DefaultControllerMiddleware::class,
        \App\Legacy\Core\PendingRouteTransformers\DefaultUriController::class,
        \App\Legacy\Core\PendingRouteTransformers\ArgumentRoutes::class,
    ],
];
