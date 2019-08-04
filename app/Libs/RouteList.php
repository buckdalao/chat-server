<?php

namespace App\Libs;


use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route as Routes;

class RouteList
{
    protected $sort;
    protected $reverse;

    /**
     * @param $str
     * @return $this
     */
    public function setReverse($str)
    {
        $this->reverse = $str;
        return $this;
    }

    /**
     * @param $str
     * @return $this
     */
    public function setSort($str)
    {
        $this->sort = $str;
        return $this;
    }

    /**
     * Compile the routes into a displayable format.
     *
     * @return array
     */
    public function getRoutes()
    {
        $haveRoute = [];
        $routes = collect(Routes::getRoutes())->map(function ($route) use (&$haveRoute) {
            $haveRoute[] = $route->getName();
            return $this->getRouteInformation($route);
        })->all();

        collect(app('Dingo\Api\Routing\Router')->getRoutes())->each(function ($collection) use (&$routes, &$haveRoute) {
            collect($collection)->each(function ($route) use (&$routes, &$haveRoute) {
                if (!in_array($route->getName(), $haveRoute)) {
                    $api = $this->getRouteInformation($route);
                    $api['versions'] = implode(', ', $route->versions());
                    array_push($routes, $api);
                    $haveRoute[] = $route->getName();
                }
            });
        });

        if ($sort = $this->sort) {
            $routes = $this->sortRoutes($sort, $routes);
        }

        if ($this->reverse) {
            $routes = array_reverse($routes);
        }

        return array_filter($routes);
    }

    /**
     * Get the route information for a given route.
     *
     * @param  \Illuminate\Routing\Route  $route
     * @return array
     */
    protected function getRouteInformation(Route $route)
    {
        return [
            'host'   => $route->domain(),
            'method' => implode('|', $route->methods()),
            'uri'    => $route->uri(),
            'name'   => $route->getName(),
            'action' => ltrim($route->getActionName(), '\\'),
            'middleware' => $this->getMiddleware($route),
            'versions' => method_exists($route, 'versions') ? implode(', ', $route->versions()) : ''
        ];
    }

    /**
     * Sort the routes by a given element.
     *
     * @param  string  $sort
     * @param  array  $routes
     * @return array
     */
    protected function sortRoutes($sort, $routes)
    {
        return Arr::sort($routes, function ($route) use ($sort) {
            return $route[$sort];
        });
    }

    /**
     * Get before filters.
     *
     * @param  \Illuminate\Routing\Route  $route
     * @return string
     */
    protected function getMiddleware($route)
    {
        return collect($route->gatherMiddleware())->map(function ($middleware) {
            return $middleware instanceof Closure ? 'Closure' : $middleware;
        })->implode(',');
    }
}