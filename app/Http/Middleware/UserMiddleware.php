<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use Illuminate\Http\Request;
use validator;
use App\Helpers\GeneralHelper;
use App\Helpers\ApiResponse;

class UserMiddleware
{
     public $apiResponse;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(ApiResponse $apiResponse)
    {
            $this->apiResponse = $apiResponse;

    }
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $rules = [
            'ApiToken'=>'required',
            'ApiKey' => 'required',

        ];

        $validation=Validator::make($request->all(),$rules);

        if($validation->fails()){
            return $this->apiResponse->setError($validation->errors()->first())->send();
        }
        $api_key = env('APP_KEY');
        if ($api_key != $request->ApiKey) {
            return $this->apiResponse->setError("Unauthorized (invalid apiKey)!")->send();
        }
        $user=User::where('ApiToken',$request->ApiToken)->first();


        if(empty($user)){
            return $this->apiResponse->setError('UnAutharized (invalid ApiToken)')->send();
        }
         GeneralHelper::SetCurrentUser($request->ApiToken);
         return $next($request);
    }
}
