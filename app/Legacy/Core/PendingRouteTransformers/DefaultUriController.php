<?php

namespace App\Legacy\Core\PendingRouteTransformers;

use ReflectionMethod;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use OpenDesa\RouteDiscovery\PendingRoutes\PendingRoute;
use OpenDesa\RouteDiscovery\PendingRoutes\PendingRouteAction;
use OpenDesa\RouteDiscovery\PendingRouteTransformers\PendingRouteTransformer;

class DefaultUriController implements PendingRouteTransformer
{
    /** @var array<string, string> */
    protected array $wheres = [];

    protected $uri;

    /**
     * {@inheritdoc}
     */
    public function transform(Collection $pendingRoutes): Collection
    {
        return $pendingRoutes->each(function (PendingRoute $pendingRoute) {
            $pendingRoute->actions->each(function (PendingRouteAction $action) use ($pendingRoute) {
                if (Str::contains($action->uri, 'index') && $method = $pendingRoute->class->getMethod($action->method->name)) {
                    $this->uri = Str::remove(['/index', '.php'], $action->uri);
                    foreach ($method->getParameters() as $params) {
                        $this->wheres[$params->getName()] = sprintf("^(?!%s).*$", $this->ignoreMethod($pendingRoute));

                        $this->uri = $params->isOptional()
                            ? "{$this->uri}/{{$params->getName()}?}"
                            : "{$this->uri}/{{$params->getName()}}";
                    }
                    Route::match($action->methods, $this->uri, $action->action())->middleware($action->middleware)->where($this->wheres);

                    // reset after match
                    $this->wheres = [];
                    $this->uri;
                }
            });
        });
    }

    protected function ignoreMethod(PendingRoute $pendingRoute)
    {
        return collect($pendingRoute->class->getMethods())
            ->filter(function (ReflectionMethod $method) {
                return $method->isPublic() && ! $method->isConstructor();
            })
            ->pluck('name')
            ->join('|');
    }
}