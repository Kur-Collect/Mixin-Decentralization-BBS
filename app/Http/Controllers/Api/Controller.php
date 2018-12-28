<?php

namespace App\Http\Controllers\Api\V1;

use Dingo\Api\Routing\Helpers;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use  Helpers;

    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @param int    $status
     * @param        $code
     * @param string $msg
     * @param array  $data
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function failed(int $status, $code = 0, string $msg = '', array $data = [])
    {
        return $this->response->array([
            'code'    => $code,
            'success' => false,
            'message' => $msg,
            'data'    => $data,
        ])->setStatusCode($status);
    }

    /**
     * @param array  $data
     * @param string $msg
     * @param string $code
     * @param int    $status
     *
     * @return mixed
     */
    public function success(array $data = [], $msg = '', $code = '0', int $status = 200)
    {
        return $this->response->array([
            'code'    => $code,
            'success' => true,
            'message' => $msg,
            'data'    => $data,
        ])->setStatusCode($status);
    }
}
