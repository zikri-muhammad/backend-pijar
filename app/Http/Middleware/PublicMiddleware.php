<?php

namespace App\Http\Middleware;

use App\Models\Client;
use Closure;

class PublicMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $requestIp = $request->ip();
        $apiToken  = ! empty($request->header()) ? $request->header()['x-pijar-api-key'][0] : null; 

        // Pre-Middleware Action
        if ($apiToken !== env('PIJAR_SECRET_KEY')) 
            return set_error_response("Not authenticated - ".$requestIp, "HTTP_UNAUTHORIZED");
            

        // Check if there is domain set.
        if ( ! empty($request->all()['domain'])) {
            $domain      = $request->all()['domain'];
            $checkDomain = Client::whereDomain($domain)->first();
            
            if ( ! $checkDomain OR ! $domain) 
                return set_error_response("Domain is not valid ", "HTTP_UNAUTHORIZED");
            
            config(['client_id' => $checkDomain->id]);
        }
        
        $response = $next($request);

        return $response;
    }
}
