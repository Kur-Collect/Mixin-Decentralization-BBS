<?php

namespace App\Http\Middleware;

use App\Exceptions\InsufficientBalanceException;
use Closure;

class ResourceCheck
{
    /**
     * @param         $request
     * @param Closure $next
     *
     * @return mixed
     * @throws InsufficientBalanceException
     */
    public function handle($request, Closure $next)
    {
        $res = fetchMixinSDk()->wallet()->readAsset(config('data.assetId.NXC'));
        if ($res['balance']<100) {
            throw new InsufficientBalanceException();
        }

        return $next($request);
    }
}
