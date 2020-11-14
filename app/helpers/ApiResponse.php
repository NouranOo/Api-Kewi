<?php
/**
* hossam yahia
* how to use create folder helper in same plcae where is http folder exist
* but it inside 
* how to call it in controller
*1)use App\Helpers\ApiResponse; 
* public $apiResponse; inside controller first line after 
*add this constracutor after public $apiResponse
*   public function __construct( ApiResponse $apiResponse)
 *   {
  *       $this->apiResponse = $apiResponse;
 *   }
 * return responce like that      return $this->apiResponse->setSuccess(" Successfuly")->setData($product)->send(); ,or with error
 *      return $this->apiResponse->setError("  Error message")->setData()->send(); // set data empty beacuse we will send it null 

 */

namespace App\Helpers;

use Illuminate\Http\JsonResponse;
use Laravel\Lumen\Http\ResponseFactory;

class ApiResponse
{

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var ResponseFactory
     */
    protected $response;

    /**
     * @var array
     */
    protected $body;


    public function __construct(ResponseFactory $response)
    {
        $this->response = $response;
        //$this->initialize();
    }



    /**
     * Set response data.
     *
     * @param $data
     * @return $this
     */
    public function setData($data=null)
    {
        $this->body['data'] = $data;
        return $this;
    }


    public function setError($error)
    {
        $this->body['status'] = false;
        $this->body['message'] = $error;
        return $this;
    }
    public function setSuccess($message)
    {
        $this->body['status'] = true;
        $this->body['message'] = $message;
        return $this;
    }
    public function setVerify($status=null)
    {
         $this->body['verify'] = $status;
        return $this;
    }
    /** optional we don't need to use it  in beauty in  */
    public function setCode($code)
    {
        $this->body['code'] = $code;
        return $this;
    }
  

    public function send()
    {
        return $this->response->json($this->body,200,[],JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);
    }

    private  function initialize()
    {
        $body = [
            'data' => [],
        ];

        $this->body = $body;
    }





}