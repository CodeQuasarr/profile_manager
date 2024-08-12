<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ReformatQueryParameters
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        //TODO: Reformat query parameters : a revoire
//        $query = $request->query();
//        $formattedQuery = [];
//        $filters = collect();
//        foreach ($query as $key => $value) {
//            if ($key === 'filter') {
//                $filters->put($key, $value);
//            }
//            if ($key === 'page') {
//                $formattedQuery['page'] = $value;
//            }
//        }
//        $formattedQuery['filter'] = $filters->toArray();
//
//        $request->query->replace($formattedQuery);
        return $next($request);

    }
}
