<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Session;

class TokenAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $return_array = array();
        $return_array['success'] = '0';
        try {
            $token = $request->header('access-token');
            $imei_number = $request->header('imei-no');
            $data = JWTAuth::setToken($token)->getPayload();
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            // $return_array['success'] = '4';
            // $return_array['message'] = 'Token Expired';
            // return response()->json($return_array, 200);
            errorMessage(__('auth.token_expired'), $return_array);
            // echo json_encode($return_array);
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            // $return_array['message'] = 'Authentication Failed';
            errorMessage(__('auth.authentication_failed'), $return_array);
            return response()->json($return_array, 200);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            // $return_array['message'] = 'Authentication Failed';
            errorMessage(__('auth.authentication_failed'), $return_array);
            return response()->json($return_array, 200);
        }
        Session::flash('tokenData', $token);
        Session::flash('customerImeiNoData', $imei_number);
        return $next($request);
    }
}
