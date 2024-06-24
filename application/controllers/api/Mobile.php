<?php
/**
 * Admin class.
 * 
 * @extends REST_Controller
 */
require APPPATH . '/libraries/REST_Controller.php';
use Restserver\Libraries\REST_Controller;
     
class Mobile extends REST_Controller {
    
	  /**
     * CONSTRUCTOR | LOAD MODEL
     *
     * @return Response
    */
    public function __construct() {
       parent::__construct();
       $this->load->library('Authorization_Token');	
       $this->load->model('API_model');
	   $this->perPage = 10;
	   $this->now = date("Y-m-d H:i:s");
	   //
	   $this->load->library('app_lib'); 
	   header('Access-Control-Allow-Origin: *');
	   header("Access-Control-Allow-Methods: HEAD, GET, POST, PUT, PATCH, DELETE, OPTIONS");
	   header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method,Access-Control-Request-Headers, Authorization, Authorization-Token");
	   header('Content-Type: application/json');
	   $method = $_SERVER['REQUEST_METHOD'];
	   if ($method == "OPTIONS") {
		   header('Access-Control-Allow-Origin: *');
		   header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method,Access-Control-Request-Headers, Authorization, Authorization-Token");
		   header("HTTP/1.1 200 OK");
		   die();
	   }
    }
    //
	public function getBeatEdit_post()
    {
        $headers = $this->input->request_headers(); 
		if (isset($headers['Authorization-Token'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization-Token']);
            if ($decodedToken['status'])
            {
				$requestData = json_decode(file_get_contents('php://input'), true);
				if($requestData){
					$beatId = $requestData["beatId"];
					// ------- Main Logic part -------
					$outData = $this->app_lib->getBeatMobileEdit($beatId);
					$final = array();
					$final['statusCode'] = 200;
					$final['status'] = true;
					$final['message'] = 'success!';
					$final['data'] = $outData;
					$this->response($final, REST_Controller::HTTP_OK);
				}
            }
            else {
                $this->response($decodedToken);
            }
		}
		else {
			$this->response(['Authentication failed'], REST_Controller::HTTP_OK);
		}
    }
	//
	public function getSpecialDutyList_post()
	{
		$decodedToken = $this->authorization_token->validateToken();
		if ($decodedToken['status'])
		{
			try {
				$sessionData = $decodedToken["data"];
				$createdBy = $sessionData->uid;
				$loginUserId = $sessionData->uid;
				$loginPoliceStationId = $sessionData->policeStationId;
				//
				$requestData = json_decode(file_get_contents('php://input'), true);
				$searchType = "all";
				if(isset($requestData["searchType"])){
					$searchType = $requestData["searchType"];
				}else{
					$requestData["searchType"] = $searchType;
				}
				$requestData["loginUserId"] = $loginUserId;
				$requestData["loginPoliceStationId"] = $loginPoliceStationId;
				$outData = $this->app_lib->getSpecialDutyListNew($requestData);
				$final = array();
				$final['statusCode'] = "200";
				$final['status'] = true;
				$final['message'] = 'Special Duty List';
				$final['data'] = $outData;
				$this->response($final, REST_Controller::HTTP_OK);
				
			}catch (Exception $e) {
				$this->set_error($e->getMessage());
				$this->response(["statusCode"=>500,"message"=>$e->getMessage()], REST_Controller::HTTP_OK);
				//return false;
			}		
		}
		else
		{
			$this->response($decodedToken, REST_Controller::HTTP_OK);
			//$this->response($decodedToken);
		}
  	}
}