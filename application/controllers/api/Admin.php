<?php
/**
 * Admin class.
 * 
 * @extends REST_Controller
 */
require APPPATH . '/libraries/REST_Controller.php';
use Restserver\Libraries\REST_Controller;
class Admin extends REST_Controller
{
	/**
	 * CONSTRUCTOR | LOAD MODEL
	 *
	 * @return Response
	 */
	public function __construct()
	{
		parent::__construct();
		$this->load->library('Authorization_Token');
		$this->load->model('API_model');
		$this->load->model('user_model');
		$this->perPage = 10;
		$this->now = date("Y-m-d H:i:s");
		//
		$this->load->library('appcommon_lib');
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
	/**
	 * SHOW | GET method.
	 *
	 * @return Response
	 */
	//
	public function login_post()
	{
		// set variables from the form
		$requestData = json_decode(file_get_contents('php://input'), true);
		$username = $requestData["username"];
		$password = $requestData["password"];
		if ($this->user_model->resolve_user_login($username, $password)) {
			$user_id = $this->user_model->get_user_id_from_username($username);
			$user = $this->user_model->get_user($user_id);
			$roleId = $user->role_id;
			$roleTypeId = $roleId;
			switch ($roleId) {
				case 1:
					$roleTypeName = "admin";
					break;
				case 2:
					$roleTypeName = "user";
					break;
				default:
					$roleTypeName = "user";
			}
			$firstName = 'User';
			$lastName = 'User';
			$outData = array(
				'userId' => (int) $user->id,
				'username' => (string) $user->username,
				'firstName' => (string) $firstName,
				'lastName' => (string) $lastName,
				'roleId' => (int) $roleId,
				'roleTypeId' => (int) $roleTypeId,
				'roleTypeName' => (string) $roleTypeName
			);
			// user login ok
			$token_data['uid'] = (int) $user_id;
			$token_data['roleId'] = (int) $roleId;
			$token_data['roleTypeId'] = (int) $roleTypeId;
			$token_data['username'] = (string) $user->username;
			$tokenData = $this->authorization_token->generateToken($token_data);
			//
			$final = array();
			$final['access_token'] = $tokenData;
			$final['status'] = true;
			$final['statusCode'] = 200;
			$final['message'] = 'Login success!';
			$final['note'] = 'You are now logged in.';
			$final['data'] = $outData;
			$this->response($final, REST_Controller::HTTP_OK);
		} else {
			// login failed
			$final = array();
			$final['status'] = false;
			$final['statusCode'] = 500;
			$final['message'] = 'Wrong username or password.';
			$final['data'] = null;
			$this->response($final, REST_Controller::HTTP_OK);
		}
	}
	//
		private function hash_password($password)
	{
		return password_hash($password, PASSWORD_BCRYPT);
	}
		//
	public function signup_post()
	{
		$requestData = json_decode(file_get_contents('php://input'), true);
		if ($requestData) {
			$insertArr = [];
			if (isset($requestData['email'])) {
				$insertArr['username'] = $requestData['email'];
			}
			if (isset($requestData['password'])) {
				$insertArr['password'] = $this->hash_password($requestData['password']);
			}
			if (isset($requestData['name'])) {
				$insertArr['name'] = $requestData['name'];
			}
			if (isset($requestData['mobile'])) {
				$insertArr['mobile'] = $requestData['mobile'];
			}
			if (isset($requestData['email'])) {
				$insertArr['email'] = $requestData['email'];
			}
			if (isset($requestData['roleId'])) {
				$insertArr['role_id'] = $requestData['roleId'];
			}

// 			if (isset($requestData['districtId'])) {
// 				$insertArr['district_id'] = $requestData['districtId'];
// 			}
// 			$insertArr['role_id'] = '3';

			// ------- Main Logic part -------
			$runQuery = $this->API_model->appInsert("users", $insertArr);
			if ($runQuery) {
				$final = array();
				$final['status'] = true;
				$final['statusCode'] = 200;
				$final['message'] = 'Data Inserted successfully';
				$final['data'] = $insertArr;
			} else {
				$final = array();
				$final['status'] = true;
				$final['statusCode'] = 500;
				$final['message'] = 'Data Insert failed!';
				$final['data'] = null;
			}
			$this->response($final, REST_Controller::HTTP_OK);
			// ------------- End -------------
			// }
		}
	}
	//
		public function getProfile_get()
	{
		$headers = $this->input->request_headers(); 
		try {
			if (isset($headers['Authorization-Token'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization-Token']);
			if ($decodedToken['status'])
			{
			$sessionData = $decodedToken["data"];
			$createdBy = $sessionData->uid;
			$con = array();
			$outData = array();
			$listData = $this->API_model->getRows("users", $con);
			if ($listData) {
				foreach ($listData as $row) {
					$listData1 = (array) $row;
					$outData = array("id" => $listData1['id'], "name" => $listData1['name'], "mobile" => $listData1['mobile'], "email" => $listData1['email']);
				}
			}
			$final = array();
			$final['status'] = true;
			$final['statusCode'] = 200;
			$final['message'] = 'success!';
			$final['data'] = $outData;
			$this->response($final, REST_Controller::HTTP_OK);
			// ------------- End -------------
			} 
			else {
				$this->response($decodedToken);
			}
			} else {
				$this->response(['Authentication failed'], REST_Controller::HTTP_OK);
			}
		} catch (Exception $e) {
			$this->set_error($e->getMessage());
			$this->response($e->getMessage(), REST_Controller::HTTP_OK);
			//return false;
		}
	}
	//
	public function addDistrict_post()
	{
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization-Token'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization-Token']);
			if ($decodedToken['status']) {
				$requestData = json_decode(file_get_contents('php://input'), true);
				if ($requestData) {
					foreach ($requestData as $key => $value) {
						$insertArr[$key] = $value;
					}
					// ------- Main Logic part -------
					$runQuery = $this->API_model->appInsert("district", $insertArr);
					if ($runQuery) {
						$final = array();
						$final['status'] = true;
						$final['statusCode'] = 200;
						$final['message'] = 'Data Inserted successfully';
						$final['data'] = $insertArr;
					} else {
						$final = array();
						$final['status'] = true;
						$final['statusCode'] = 500;
						$final['message'] = 'Data Insert failed!';
						$final['data'] = null;
					}
					$this->response($final, REST_Controller::HTTP_OK);
					// ------------- End -------------
				}
			} else {
				$this->response($decodedToken);
			}
		} else {
			$this->response(['Authentication failed'], REST_Controller::HTTP_OK);
		}
	}
	//
	public function addOfficerType_post()
	{
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization-Token'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization-Token']);
			if ($decodedToken['status']) {
				$requestData = json_decode(file_get_contents('php://input'), true);
				if ($requestData) {
					foreach ($requestData as $key => $value) {
						$insertArr[$key] = $value;
					}
					// ------- Main Logic part -------
					$runQuery = $this->API_model->appInsert("tbl_officer_type", $insertArr);
					if ($runQuery) {
						$final = array();
						$final['status'] = true;
						$final['statusCode'] = 200;
						$final['message'] = 'Data Inserted successfully';
						$final['data'] = $insertArr;
					} else {
						$final = array();
						$final['status'] = true;
						$final['statusCode'] = 500;
						$final['message'] = 'Data Insert failed!';
						$final['data'] = null;
					}
					$this->response($final, REST_Controller::HTTP_OK);
					// ------------- End -------------
				}
			} else {
				$this->response($decodedToken);
			}
		} else {
			$this->response(['Authentication failed'], REST_Controller::HTTP_OK);
		}
	}
	//
	public function addHospital_post()
	{
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization-Token'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization-Token']);
			if ($decodedToken['status']) {
				$requestData = json_decode(file_get_contents('php://input'), true);
				if ($requestData) {
					//foreach ($requestData as $key => $value) {
					//	$insertArr[$key] = $value;
					//}
						if (isset($requestData['district_id'])) { $insertArr['district_id'] = $requestData['district_id'];}
						if (isset($requestData['name'])) { $insertArr['name'] = $requestData['name'];}
						if (isset($requestData['address'])) {$insertArr['address'] = $requestData['address'];}
						if (isset($requestData['contact'])) {$insertArr['contact'] = json_encode($requestData['contact']);}
						if (isset($requestData['email'])) {$insertArr['email'] = $requestData['email'];}
					// ------- Main Logic part -------
					$runQuery = $this->API_model->appInsert("tbl_hopital_dtl", $insertArr);
					if ($runQuery) {
						$final = array();
						$final['status'] = true;
						$final['statusCode'] = 200;
						$final['message'] = 'Data Inserted successfully';
						$final['data'] = $insertArr;
					} else {
						$final = array();
						$final['status'] = true;
						$final['statusCode'] = 500;
						$final['message'] = 'Data Insert failed!';
						$final['data'] = null;
					}
					$this->response($final, REST_Controller::HTTP_OK);
					// ------------- End -------------
				}
			} else {
				$this->response($decodedToken);
			}
		} else {
			$this->response(['Authentication failed'], REST_Controller::HTTP_OK);
		}
	}
	//--//--//
	public function addOldAgeType_post()
	{
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization-Token'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization-Token']);
			if ($decodedToken['status']) {
				$requestData = json_decode(file_get_contents('php://input'), true);
				if ($requestData) {
					foreach ($requestData as $key => $value) {
						$insertArr[$key] = $value;
					}
					// ------- Main Logic part -------
					$runQuery = $this->API_model->appInsert("tbl_oah_type", $insertArr);
					if ($runQuery) {
						$final = array();
						$final['status'] = true;
						$final['statusCode'] = 200;
						$final['message'] = 'Data Inserted successfully';
						$final['data'] = $insertArr;
					} else {
						$final = array();
						$final['status'] = true;
						$final['statusCode'] = 500;
						$final['message'] = 'Data Insert failed!';
						$final['data'] = null;
					}
					$this->response($final, REST_Controller::HTTP_OK);
					// ------------- End -------------
				}
			} else {
				$this->response($decodedToken);
			}
		} else {
			$this->response(['Authentication failed'], REST_Controller::HTTP_OK);
		}
	}
	//
	public function addOfficer_post()
	{
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization-Token'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization-Token']);
			if ($decodedToken['status']) {
				$requestData = json_decode(file_get_contents('php://input'), true);
				if ($requestData) {
				// 	foreach ($requestData as $key => $value) {
				
						if (isset($requestData['district_id'])) { $insertArr['district_id'] = $requestData['district_id'];}
						if (isset($requestData['name'])) { $insertArr['name'] = $requestData['name'];}
						if (isset($requestData['address'])) {$insertArr['address'] = $requestData['address'];}
						if (isset($requestData['contact'])) {$insertArr['contact'] = json_encode($requestData['contact']);}
						if (isset($requestData['email'])) {$insertArr['email'] = $requestData['email'];}
						if (isset($requestData['fax'])) {$insertArr['fax'] = $requestData['fax'];}
						if (isset($requestData['officer_id'])) {$insertArr['officer_id'] = $requestData['officer_id'];}
				// 	}
			$OfficerId = $requestData['officer_id'];
// 			if ($requestData['officer_id']) {
// 				switch ($requestData['officer_id']) {
// 					case '1':
// 						$table = 'tbl_rdo';
// 						break;
// 					case '2':
// 						$table = 'tbl_dswo';
// 						break;
// 					case '3':
// 						$table = 'tbl_collectors';
// 						break;
// 				}
// 				}
					// ------- Main Logic part -------
					$runQuery = $this->API_model->appInsert("tbl_officer_dtl", $insertArr);
					if ($runQuery) {
						$final = array();
						$final['status'] = true;
						$final['statusCode'] = 200;
						$final['message'] = 'Data Inserted successfully';
						$final['data'] = $insertArr;
					} else {
						$final = array();
						$final['status'] = true;
						$final['statusCode'] = 500;
						$final['message'] = 'Data Insert failed!';
						$final['data'] = null;
					}
					$this->response($final, REST_Controller::HTTP_OK);
					// ------------- End -------------
				}
			} else {
				$this->response($decodedToken);
			}
		} else {
			$this->response(['Authentication failed'], REST_Controller::HTTP_OK);
		}
	}
	//
// 	public function addCollectors_post()
// 	{
// 		$headers = $this->input->request_headers();
// 		if (isset($headers['Authorization-Token'])) {
// 			$decodedToken = $this->authorization_token->validateToken($headers['Authorization-Token']);
// 			if ($decodedToken['status']) {
// 				$requestData = json_decode(file_get_contents('php://input'), true);
// 				if ($requestData) {
// 					$insertArr['name'] = $requestData['name'];
// 						$insertArr['address'] = $requestData['address'];
// 						$insertArr['contact'] = json_encode($requestData['contact']);
// 						$insertArr['email'] = $requestData['email'];
// 						$insertArr['fax'] = $requestData['fax'];
// 						$insertArr['officer_id'] = $requestData['officer_id'];
// 					// ------- Main Logic part -------
// 					$runQuery = $this->API_model->appInsert("tbl_collectors", $insertArr);
// 					if ($runQuery) {
// 						$final = array();
// 						$final['status'] = true;
// 						$final['statusCode'] = 200;
// 						$final['message'] = 'Data Inserted successfully';
// 						$final['data'] = $insertArr;
// 					} else {
// 						$final = array();
// 						$final['status'] = true;
// 						$final['statusCode'] = 500;
// 						$final['message'] = 'Data Insert failed!';
// 						$final['data'] = null;
// 					}
// 					$this->response($final, REST_Controller::HTTP_OK);
// 					// ------------- End -------------
// 				}
// 			} else {
// 				$this->response($decodedToken);
// 			}
// 		} else {
// 			$this->response(['Authentication failed'], REST_Controller::HTTP_OK);
// 		}
// 	}
	//
// 	public function addDswo_post()
// 	{
// 		$headers = $this->input->request_headers();
// 		if (isset($headers['Authorization-Token'])) {
// 			$decodedToken = $this->authorization_token->validateToken($headers['Authorization-Token']);
// 			if ($decodedToken['status']) {
// 				$requestData = json_decode(file_get_contents('php://input'), true);
// 				if ($requestData) {
// 					$insertArr['name'] = $requestData['name'];
// 						$insertArr['address'] = $requestData['address'];
// 						$insertArr['contact'] = json_encode($requestData['contact']);
// 						$insertArr['email'] = $requestData['email'];
// 						$insertArr['fax'] = $requestData['fax'];
// 						$insertArr['officer_id'] = $requestData['officer_id'];
// 					// ------- Main Logic part -------
// 					$runQuery = $this->API_model->appInsert("tbl_dswo", $insertArr);
// 					if ($runQuery) {
// 						$final = array();
// 						$final['status'] = true;
// 						$final['statusCode'] = 200;
// 						$final['message'] = 'Data Inserted successfully';
// 						$final['data'] = $insertArr;
// 					} else {
// 						$final = array();
// 						$final['status'] = true;
// 						$final['statusCode'] = 500;
// 						$final['message'] = 'Data Insert failed!';
// 						$final['data'] = null;
// 					}
// 					$this->response($final, REST_Controller::HTTP_OK);
// 					// ------------- End -------------
// 				}
// 			} else {
// 				$this->response($decodedToken);
// 			}
// 		} else {
// 			$this->response(['Authentication failed'], REST_Controller::HTTP_OK);
// 		}
// 	}
	//
	public function addOldAge_post()
	{
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization-Token'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization-Token']);
			if ($decodedToken['status']) {
				$requestData = json_decode(file_get_contents('php://input'), true);
				if ($requestData) {
				// 	foreach ($requestData as $key => $value) {
				// 		$insertArr[$key] = $value;
				// 	}
					    if (isset($requestData['oah_type'])) {$insertArr['oah_type'] = $requestData['oah_type'];}
						if (isset($requestData['district_id'])) {$insertArr['district_id'] = $requestData['district_id'];}
						if (isset($requestData['name'])) {$insertArr['name'] = $requestData['name'];}
						if (isset($requestData['address'])) {$insertArr['address'] = $requestData['address'];}
						if (isset($requestData['contact'])) {$insertArr['contact'] = json_encode($requestData['contact']);}
						if (isset($requestData['email'])) {$insertArr['email'] = $requestData['email'];}
						if (isset($requestData['fax'])) {$insertArr['fax'] = $requestData['fax'];}
					// ------- Main Logic part -------
					$runQuery = $this->API_model->appInsert("tbl_oah_dtl", $insertArr);
					if ($runQuery) {
						$final = array();
						$final['status'] = true;
						$final['statusCode'] = 200;
						$final['message'] = 'Data Inserted successfully';
						$final['data'] = $insertArr;
					} else {
						$final = array();
						$final['status'] = true;
						$final['statusCode'] = 500;
						$final['message'] = 'Data Insert failed!';
						$final['data'] = null;
					}
					$this->response($final, REST_Controller::HTTP_OK);
					// ------------- End -------------
				}
			} else {
				$this->response($decodedToken);
			}
		} else {
			$this->response(['Authentication failed'], REST_Controller::HTTP_OK);
		}
	}
	//
	//
	public function getDistrict_get()
	{
		$headers = $this->input->request_headers();
		try {
			if (isset($headers['Authorization-Token'])) {
				$decodedToken = $this->authorization_token->validateToken($headers['Authorization-Token']);
				if ($decodedToken['status']) {
					$sessionData = $decodedToken["data"];
					$createdBy = $sessionData->uid;
					$con = array();
					$con['conditions'] = array(
						'is_active' => 1
					);
					$outData = array();
					$data = array();
					$listData1 = $this->API_model->getRows("district", $con);
					if ($listData1) {
						foreach ($listData1 as $listData) {
							$listData = (array) $listData;
							$listData = array("id" => $listData['id'], "name" => $listData['name']);
							$outData[] = $listData;
						}
					}
					$colData = array();
					$colData[] = array("field" => "id", "header" => "id");
					$colData[] = array("field" => "name", "header" => "Name");
					$data['cols'] = $colData;
					$data['values'] = $outData;
					$final = array();
					$final['status'] = true;
					$final['statusCode'] = 200;
					$final['message'] = 'success!';
					$final['data'] = $data;
					$this->response($final, REST_Controller::HTTP_OK);
					// ------------- End -------------
				} else {
					$this->response($decodedToken);
				}
			} else {
				$this->response(['Authentication failed'], REST_Controller::HTTP_OK);
			}
		} catch (Exception $e) {
			$this->set_error($e->getMessage());
			$this->response($e->getMessage(), REST_Controller::HTTP_OK);
			//return false;
		}
	}
	//
	//
	public function getHospitalList_get()
	{
		$headers = $this->input->request_headers();
		try {
			if (isset($headers['Authorization-Token'])) {
				$decodedToken = $this->authorization_token->validateToken($headers['Authorization-Token']);
				if ($decodedToken['status']) {
					$sessionData = $decodedToken["data"];
					$createdBy = $sessionData->uid;
					$con = array();
					$con['conditions'] = array(
						'is_active' => 1
					);
					$outData = array();
					$data = array();
					$listData1 = $this->API_model->getRows("tbl_hopital_dtl", $con);
					if ($listData1) {
						foreach ($listData1 as $listData) {
							$listData = (array) $listData;
							$district_name = (array) ($this->user_model->get_district_name($listData['district_id']));
						$rr = json_decode($listData['contact'], TRUE);
				$arr='';
				if($rr){
				foreach($rr as $key => $value ){
				    	$arr .= $value['contact'].',';
				}
				}
							$listData = array("id" => $listData['id'], "name" => $listData['name'], "district_id" => $district_name['name'], "contact" => substr($arr, 0, -1), "email" => $listData['email'], "address" => $listData['address']);
							$outData[] = $listData;
						}
					}
					$colData = array();
					$colData[] = array("field" => "id", "header" => "id");
					$colData[] = array("field" => "name", "header" => "Name");
					$colData[] = array("field" => "district_id", "header" => "District");
					$colData[] = array("field" => "contact", "header" => "Contact");
					$colData[] = array("field" => "email", "header" => "Email");
					$colData[] = array("field" => "address", "header" => "Address");
					$data['cols'] = $colData;
					$data['values'] = $outData;
					$final = array();
					$final['status'] = true;
					$final['statusCode'] = 200;
					$final['message'] = 'success!';
					$final['data'] = $data;
					$this->response($final, REST_Controller::HTTP_OK);
					// ------------- End -------------
				} else {
					$this->response($decodedToken);
				}
			} else {
				$this->response(['Authentication failed'], REST_Controller::HTTP_OK);
			}
		} catch (Exception $e) {
			$this->set_error($e->getMessage());
			$this->response($e->getMessage(), REST_Controller::HTTP_OK);
			//return false;
		}
	}
	//
	//
	public function getRoleType_get()
	{
// 		$headers = $this->input->request_headers();
		try {
// 			if (isset($headers['Authorization-Token'])) {
				// $decodedToken = $this->authorization_token->validateToken($headers['Authorization-Token']);
				// if ($decodedToken['status']) {
				// 	$sessionData = $decodedToken["data"];
				// 	$createdBy = $sessionData->uid;
					$con = array();
					$con['conditions'] = array(
						'is_active' => 1
					);
					$outData = array();
					$data = array();
					$listData1 = $this->API_model->getRows("role", $con);
					if ($listData1) {
						foreach ($listData1 as $listData) {
							$listData = (array) $listData;
							$listData = array("id" => $listData['id'], "name" => $listData['name']);
							$outData[] = $listData;
						}
					}

					$final = array();
					$final['status'] = true;
					$final['statusCode'] = 200;
					$final['message'] = 'success!';
					$final['data'] = $outData;
					$this->response($final, REST_Controller::HTTP_OK);
					// ------------- End -------------
				// } else {
				// 	$this->response($decodedToken);
				// }
// 			} else {
// 				$this->response(['Authentication failed'], REST_Controller::HTTP_OK);
// 			}
		} catch (Exception $e) {
			$this->set_error($e->getMessage());
			$this->response($e->getMessage(), REST_Controller::HTTP_OK);
			//return false;
		}
	}
	//
	//
	public function getOldAgeType_get()
	{
		$headers = $this->input->request_headers();
		try {
			if (isset($headers['Authorization-Token'])) {
				$decodedToken = $this->authorization_token->validateToken($headers['Authorization-Token']);
				if ($decodedToken['status']) {
					$sessionData = $decodedToken["data"];
					$createdBy = $sessionData->uid;
					$con = array();
					$con['conditions'] = array(
						'is_active' => 1
					);
					$outData = array();
					$data = array();
					$listData1 = $this->API_model->getRows("tbl_oah_type", $con);
					if ($listData1) {
						foreach ($listData1 as $listData) {
							$listData = (array) $listData;
							$listData = array("id" => $listData['id'], "name" => $listData['name']);
							$outData[] = $listData;
						}
					}
					//
					$colData = array();
					$colData[] = array("field" => "id", "header" => "id");
					$colData[] = array("field" => "name", "header" => "Name");
					$data['cols'] = $colData;
					$data['values'] = $outData;
					$final = array();
					$final['status'] = true;
					$final['statusCode'] = 200;
					$final['message'] = 'success!';
					$final['data'] = $data;
					$this->response($final, REST_Controller::HTTP_OK);
					// ------------- End -------------
				} else {
					$this->response($decodedToken);
				}
			} else {
				$this->response(['Authentication failed'], REST_Controller::HTTP_OK);
			}
		} catch (Exception $e) {
			$this->set_error($e->getMessage());
			$this->response($e->getMessage(), REST_Controller::HTTP_OK);
			//return false;
		}
	}
	//
	public function getOfficerList_get()
	{
		$headers = $this->input->request_headers();
		try {
			if (isset($headers['Authorization-Token'])) {
				$decodedToken = $this->authorization_token->validateToken($headers['Authorization-Token']);
				if ($decodedToken['status']) {
					$sessionData = $decodedToken["data"];
					$createdBy = $sessionData->uid;
			if (isset($requestData['OfficerId'])) {
			    $OfficerId = $requestData['OfficerId'];
			}else{
			    $OfficerId = 1;
			}
					$con = array();
					$con['conditions'] = array(
						'is_active' => 1,
						'officer_id' => $OfficerId
					);

					$outData = array();
					$data = array();
					$listData1 = $this->API_model->getRows("tbl_officer_dtl", $con);
					if ($listData1) {
						foreach ($listData1 as $listData) {
							$listData = (array) $listData;
							$district_name = (array) ($this->user_model->get_district_name($listData['district_id']));
				$rr = json_decode($listData['contact'], TRUE);
				$arr='';
				if($rr){
				foreach($rr as $key => $value ){
				    	$arr .= $value['contact'].',';
				}
				}

							$listData = array("id" => $listData['id'], "district_id" => $district_name['name'], "name" => $listData['name'], "contact" => substr($arr, 0, -1)
, "fax" => $listData['fax'], "email" => $listData['email'], "address" => $listData['address']);
							$outData[] = $listData;
						}
					}
					
					$colData = array();
					$colData[] = array("field" => "id", "header" => "id");
					$colData[] = array("field" => "district_id", "header" => "District");
					$colData[] = array("field" => "name", "header" => "Name");
					$colData[] = array("field" => "contact", "header" => "Contact");
					$colData[] = array("field" => "fax", "header" => "Fax");
					$colData[] = array("field" => "email", "header" => "Email");
					$data['cols'] = $colData;
					$data['values'] = $outData;
					$final = array();
					$final['status'] = true;
					$final['statusCode'] = 200;
					$final['message'] = 'success!';
					$final['data'] = $data;
					$this->response($final, REST_Controller::HTTP_OK);
					// ------------- End -------------
				} else {
					$this->response($decodedToken);
				}
			} else {
				$this->response(['Authentication failed'], REST_Controller::HTTP_OK);
			}
		} catch (Exception $e) {
			$this->set_error($e->getMessage());
			$this->response($e->getMessage(), REST_Controller::HTTP_OK);
			//return false;
		}
	}
	//
	//
	public function getOldAgeList_get()
	{
		$headers = $this->input->request_headers();
		try {
			if (isset($headers['Authorization-Token'])) {
				$decodedToken = $this->authorization_token->validateToken($headers['Authorization-Token']);
				if ($decodedToken['status']) {
					$sessionData = $decodedToken["data"];
				// 	$createdBy = $sessionData->uid;
					$con = array();
					$con['conditions'] = array(
						'is_active' => 1
					);
					$outData = array();
					$data = array();
					$listData1 = $this->API_model->getRows("tbl_oah_dtl", $con);
					if ($listData1) {
						foreach ($listData1 as $listData) {
							$listData = (array) $listData;
							$district_name = (array) ($this->user_model->get_district_name($listData['district_id']));
							$rr = json_decode($listData['contact'], TRUE);
// print_r($rr);
				$arr='';
				if(is_array($rr) || is_object($rr)){
				foreach($rr as $key => $value ){
				    	$arr .= $value['contact'].',';
				}
				}
							
							$listData = array("id" => $listData['id'], "oah_type" => $listData['oah_type'], "district_id" => $district_name['name'], "name" => $listData['name'], "contact" => substr($arr, 0, -1), "email" => $listData['email'], "address" => $listData['address']);
							$outData[] = $listData;
						}
					}
					$colData = array();
					$colData[] = array("field" => "id", "header" => "id");
					$colData[] = array("field" => "district_id", "header" => "District");
					$colData[] = array("field" => "name", "header" => "Name");
					$colData[] = array("field" => "contact", "header" => "Contact");
					$colData[] = array("field" => "fax", "header" => "Fax");
					$colData[] = array("field" => "email", "header" => "Email");
					$data['cols'] = $colData;
					$data['values'] = $outData;
					$final = array();
					$final['status'] = true;
					$final['statusCode'] = 200;
					$final['message'] = 'success!';
					$final['data'] = $data;
					$this->response($final, REST_Controller::HTTP_OK);
					// ------------- End -------------
				} else {
					$this->response($decodedToken);
				}
			} else {
				$this->response(['Authentication failed'], REST_Controller::HTTP_OK);
			}
		} catch (Exception $e) {
			$this->set_error($e->getMessage());
			$this->response($e->getMessage(), REST_Controller::HTTP_OK);
			//return false;
		}
	}
	////
	public function districtUpdate_post()
	{
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization-Token'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization-Token']);
			if ($decodedToken['status']) {
				$requestData = json_decode(file_get_contents('php://input'), true);
				if ($requestData) {
					$districtId = $requestData["districtId"];
					$updateArr = [];
					$skipInput = array("districtId");
					foreach ($requestData as $key => $value) {
						if (!(in_array($key, $skipInput))) {
							$updateArr[$key] = $value;
						}
					}
					// ------- Main Logic part -------
					$con = array("id" => $districtId);
					$runQuery = $this->API_model->appUpdate("district", $updateArr, $con);
					if ($runQuery) {
						$final = array();
						$final['status'] = true;
						$final['statusCode'] = 200;
						$final['message'] = 'Data updated successfully';
						$final['data'] = $updateArr;
					} else {
						$final = array();
						$final['status'] = true;
						$final['statusCode'] = 500;
						$final['message'] = 'Data update failed!';
						$final['data'] = null;
					}
					$this->response($final, REST_Controller::HTTP_OK);
					// ------------- End -------------
				}
			} else {
				$this->response($decodedToken);
			}
		} else {
			$this->response(['Authentication failed'], REST_Controller::HTTP_OK);
		}
	}
	//
	public function districtEdit_post()
	{
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization-Token'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization-Token']);
			if ($decodedToken['status']) {
				$requestData = json_decode(file_get_contents('php://input'), true);
				if ($requestData) {
					$districtId = $requestData["districtId"];
					$con = array();
					$con['conditions'] = array(
						'id' => $districtId
					);
					$outData = array();
					$listData = $this->API_model->getRows("district", $con);
				$outData = array();
				//
				if ($listData) {
					foreach ($listData as $row) {
						$listData1 = (array) $row;
						$outData = array( "id" => $listData1['id'], "name" => $listData1['name']);
					}
				}
					// ------- Main Logic part -------
						$final = array();
						$final['status'] = true;
						$final['statusCode'] = 200;
			        	$final['message'] = 'success!';
						$final['data'] = $outData;
				
					$this->response($final, REST_Controller::HTTP_OK);
					// ------------- End -------------
				}
			} else {
				$this->response($decodedToken);
			}
		} else {
			$this->response(['Authentication failed'], REST_Controller::HTTP_OK);
		}
	}
	//
	public function hospitalUpdate_post()
	{
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization-Token'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization-Token']);
			if ($decodedToken['status']) {
				$requestData = json_decode(file_get_contents('php://input'), true);
				if ($requestData) {
					$hospitalId = $requestData["hospitalId"];
					$updateArr = [];
				// 	$skipInput = array("hospitalId");
				// 	foreach ($requestData as $key => $value) {
				// 		if (!(in_array($key, $skipInput))) {
				// 			$updateArr[$key] = $value;
				// 		}
				// 	}
						if (isset($requestData['district_id'])) { $updateArr['district_id'] = $requestData['district_id'];}
						if (isset($requestData['name'])) { $updateArr['name'] = $requestData['name'];}
						if (isset($requestData['address'])) {$updateArr['address'] = $requestData['address'];}
						if (isset($requestData['contact'])) {$updateArr['contact'] = json_encode($requestData['contact']);}
						if (isset($requestData['email'])) {$updateArr['email'] = $requestData['email'];}
					// ------- Main Logic part -------
					$con = array("id" => $hospitalId);
					$runQuery = $this->API_model->appUpdate("tbl_hopital_dtl", $updateArr, $con);
					if ($runQuery) {
						$final = array();
						$final['status'] = true;
						$final['statusCode'] = 200;
						$final['message'] = 'Data updated successfully';
						$final['data'] = $updateArr;
					} else {
						$final = array();
						$final['status'] = true;
						$final['statusCode'] = 500;
						$final['message'] = 'Data update failed!';
						$final['data'] = null;
					}
					$this->response($final, REST_Controller::HTTP_OK);
					// ------------- End -------------
				}
			} else {
				$this->response($decodedToken);
			}
		} else {
			$this->response(['Authentication failed'], REST_Controller::HTTP_OK);
		}
	}

		//
	public function hospitalEdit_post()
	{
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization-Token'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization-Token']);
			if ($decodedToken['status']) {
				$requestData = json_decode(file_get_contents('php://input'), true);
				if ($requestData) {
					$hospitalId = $requestData["hospitalId"];
					$con = array();
					$con['conditions'] = array(
						'id' => $hospitalId
					);
					$outData = array();
					$listData = $this->API_model->getRows("tbl_hopital_dtl", $con);
				$outData = array();
				//
				if ($listData) {
					foreach ($listData as $row) {
						$listData1 = (array) $row;
						$contact = json_decode($listData1['contact'], TRUE);
						$outData = array( "id" => $listData1['id'],"district_id" => $listData1['district_id'], "name" => $listData1['name'], "contact" => $contact, "email" => $listData1['email'], "address" => $listData1['address']);
					}
				}
					// ------- Main Logic part -------
						$final = array();
						$final['status'] = true;
						$final['statusCode'] = 200;
			        	$final['message'] = 'success!';
						$final['data'] = $outData;
				
					$this->response($final, REST_Controller::HTTP_OK);
					// ------------- End -------------
				}
			} else {
				$this->response($decodedToken);
			}
		} else {
			$this->response(['Authentication failed'], REST_Controller::HTTP_OK);
		}
	}
	//
	public function officerTypeUpdate_post()
	{
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization-Token'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization-Token']);
			if ($decodedToken['status']) {
				$requestData = json_decode(file_get_contents('php://input'), true);
				if ($requestData) {
					$officerTypeId = $requestData["officerTypeId"];
					$updateArr = [];
					$skipInput = array("officerTypeId");
					foreach ($requestData as $key => $value) {
						if (!(in_array($key, $skipInput))) {
							$updateArr[$key] = $value;
						}
					}
					// ------- Main Logic part -------
					$con = array("id" => $officerTypeId);
					$runQuery = $this->API_model->appUpdate("tbl_officer_type", $updateArr, $con);
					if ($runQuery) {
						$final = array();
						$final['status'] = true;
						$final['statusCode'] = 200;
						$final['message'] = 'Data updated successfully';
						$final['data'] = $updateArr;
					} else {
						$final = array();
						$final['status'] = true;
						$final['statusCode'] = 500;
						$final['message'] = 'Data update failed!';
						$final['data'] = null;
					}
					$this->response($final, REST_Controller::HTTP_OK);
					// ------------- End -------------
				}
			} else {
				$this->response($decodedToken);
			}
		} else {
			$this->response(['Authentication failed'], REST_Controller::HTTP_OK);
		}
	}
	//
		public function officerTypeEdit_post()
	{
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization-Token'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization-Token']);
			if ($decodedToken['status']) {
				$requestData = json_decode(file_get_contents('php://input'), true);
				if ($requestData) {
					$officerTypeId = $requestData["officerTypeId"];
					$con = array();
					$con['conditions'] = array(
						'id' => $officerTypeId
					);
					$outData = array();
					$listData = $this->API_model->getRows("tbl_officer_type", $con);
				$outData = array();
				//
				if ($listData) {
					foreach ($listData as $row) {
						$listData1 = (array) $row;
						$outData = array( "id" => $listData1['id'],"name" => $listData1['name']);
					}
				}
					// ------- Main Logic part -------
						$final = array();
						$final['status'] = true;
						$final['statusCode'] = 200;
			        	$final['message'] = 'success!';
						$final['data'] = $outData;
				
					$this->response($final, REST_Controller::HTTP_OK);
					// ------------- End -------------
				}
			} else {
				$this->response($decodedToken);
			}
		} else {
			$this->response(['Authentication failed'], REST_Controller::HTTP_OK);
		}
	}
	//
	public function oahTypeUpdate_post()
	{
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization-Token'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization-Token']);
			if ($decodedToken['status']) {
				$requestData = json_decode(file_get_contents('php://input'), true);
				if ($requestData) {
					$oahTypeId = $requestData["oahTypeId"];
					$updateArr = [];
					$skipInput = array("oahTypeId");
					foreach ($requestData as $key => $value) {
						if (!(in_array($key, $skipInput))) {
							$updateArr[$key] = $value;
						}
					}
					// ------- Main Logic part -------
					$con = array("id" => $oahTypeId);
					$runQuery = $this->API_model->appUpdate("tbl_oah_type", $updateArr, $con);
					if ($runQuery) {
						$final = array();
						$final['status'] = true;
						$final['statusCode'] = 200;
						$final['message'] = 'Data updated successfully';
						$final['data'] = $updateArr;
					} else {
						$final = array();
						$final['status'] = true;
						$final['statusCode'] = 500;
						$final['message'] = 'Data update failed!';
						$final['data'] = null;
					}
					$this->response($final, REST_Controller::HTTP_OK);
					// ------------- End -------------
				}
			} else {
				$this->response($decodedToken);
			}
		} else {
			$this->response(['Authentication failed'], REST_Controller::HTTP_OK);
		}
	}
	//
			public function oahTypeEdit_post()
	{
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization-Token'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization-Token']);
			if ($decodedToken['status']) {
				$requestData = json_decode(file_get_contents('php://input'), true);
				if ($requestData) {
					$oahTypeId = $requestData["oahTypeId"];
					$con = array();
					$con['conditions'] = array(
						'id' => $oahTypeId
					);
					$outData = array();
					$listData = $this->API_model->getRows("tbl_oah_type", $con);
				$outData = array();
				//
				if ($listData) {
					foreach ($listData as $row) {
						$listData1 = (array) $row;
						$outData = array( "id" => $listData1['id'],"name" => $listData1['name']);
					}
				}
					// ------- Main Logic part -------
						$final = array();
						$final['status'] = true;
						$final['statusCode'] = 200;
			        	$final['message'] = 'success!';
						$final['data'] = $outData;
				
					$this->response($final, REST_Controller::HTTP_OK);
					// ------------- End -------------
				}
			} else {
				$this->response($decodedToken);
			}
		} else {
			$this->response(['Authentication failed'], REST_Controller::HTTP_OK);
		}
	}
	//
	public function officerUpdate_post()
	{
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization-Token'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization-Token']);
			if ($decodedToken['status']) {
				$requestData = json_decode(file_get_contents('php://input'), true);
				if ($requestData) {
					$officerId = $requestData["officerId"];
					$updateArr = [];
				// 	$skipInput = array("officerId");
				// 	foreach ($requestData as $key => $value) {
				// 		if (!(in_array($key, $skipInput))) {
				// 			$updateArr[$key] = $value;
				// 		}
				// 	}
						if (isset($requestData['officer_id'])) { $updateArr['officer_id'] = $requestData['officer_id'];}
						if (isset($requestData['district_id'])) { $updateArr['district_id'] = $requestData['district_id'];}
						if (isset($requestData['name'])) { $updateArr['name'] = $requestData['name'];}
						if (isset($requestData['address'])) {$updateArr['address'] = $requestData['address'];}
						if (isset($requestData['contact'])) {$updateArr['contact'] = json_encode($requestData['contact']);}
						if (isset($requestData['email'])) {$updateArr['email'] = $requestData['email'];}
					// ------- Main Logic part -------
					$con = array("id" => $officerId);
					$runQuery = $this->API_model->appUpdate("tbl_officer_dtl", $updateArr, $con);
					if ($runQuery) {
						$final = array();
						$final['status'] = true;
						$final['statusCode'] = 200;
						$final['message'] = 'Data updated successfully';
						$final['data'] = $updateArr;
					} else {
						$final = array();
						$final['status'] = true;
						$final['statusCode'] = 500;
						$final['message'] = 'Data update failed!';
						$final['data'] = null;
					}
					$this->response($final, REST_Controller::HTTP_OK);
					// ------------- End -------------
				}
			} else {
				$this->response($decodedToken);
			}
		} else {
			$this->response(['Authentication failed'], REST_Controller::HTTP_OK);
		}
	}
	//
public function officerEdit_post()
	{
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization-Token'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization-Token']);
			if ($decodedToken['status']) {
				$requestData = json_decode(file_get_contents('php://input'), true);
				if ($requestData) {
					$officerId = $requestData["officerId"];
					$con = array();
					$con['conditions'] = array(
						'id' => $officerId
					);
					$outData = array();
					$listData = $this->API_model->getRows("tbl_officer_dtl", $con);
				$outData = array();
				//
				if ($listData) {
					foreach ($listData as $row) {
						$listData1 = (array) $row;
					    $contact = json_decode($listData1['contact'], TRUE);
						$outData = array( "id" => $listData1['id'],"district_id" => $listData1['district_id'],"officer_id" => $listData1['officer_id'],"name" => $listData1['name'],"contact" => $contact,"email" => $listData1['email'],"address" => $listData1['address']);
					}
				}
					// ------- Main Logic part -------
						$final = array();
						$final['status'] = true;
						$final['statusCode'] = 200;
			        	$final['message'] = 'success!';
						$final['data'] = $outData;
				
					$this->response($final, REST_Controller::HTTP_OK);
					// ------------- End -------------
				}
			} else {
				$this->response($decodedToken);
			}
		} else {
			$this->response(['Authentication failed'], REST_Controller::HTTP_OK);
		}
	}
	//
		//
	public function collectorUpdate_post()
	{
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization-Token'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization-Token']);
			if ($decodedToken['status']) {
				$requestData = json_decode(file_get_contents('php://input'), true);
				if ($requestData) {
					$officerId = $requestData["officerId"];
					$updateArr = [];
					$skipInput = array("officerId");
					foreach ($requestData as $key => $value) {
						if (!(in_array($key, $skipInput))) {
							$updateArr[$key] = $value;
						}
					}
					// ------- Main Logic part -------
					$con = array("id" => $officerId);
					$runQuery = $this->API_model->appUpdate("tbl_collectors", $updateArr, $con);
					if ($runQuery) {
						$final = array();
						$final['status'] = true;
						$final['statusCode'] = 200;
						$final['message'] = 'Data updated successfully';
						$final['data'] = $updateArr;
					} else {
						$final = array();
						$final['status'] = true;
						$final['statusCode'] = 500;
						$final['message'] = 'Data update failed!';
						$final['data'] = null;
					}
					$this->response($final, REST_Controller::HTTP_OK);
					// ------------- End -------------
				}
			} else {
				$this->response($decodedToken);
			}
		} else {
			$this->response(['Authentication failed'], REST_Controller::HTTP_OK);
		}
	}
	//
// public function collectorEdit_post()
// 	{
// 		$headers = $this->input->request_headers();
// 		if (isset($headers['Authorization-Token'])) {
// 			$decodedToken = $this->authorization_token->validateToken($headers['Authorization-Token']);
// 			if ($decodedToken['status']) {
// 				$requestData = json_decode(file_get_contents('php://input'), true);
// 				if ($requestData) {
// 					$officerId = $requestData["officerId"];
// 					$con = array();
// 					$con['conditions'] = array(
// 						'id' => $officerId
// 					);
// 					$outData = array();
// 					$listData = $this->API_model->getRows("tbl_collectors", $con);
// 				$outData = array();
// 				//
// 				if ($listData) {
// 					foreach ($listData as $row) {
// 						$listData1 = (array) $row;
// 						$outData = array( "id" => $listData1['id'],"district_id" => $listData1['district_id'],"name" => $listData1['name'],"contact" => $listData1['contact'],"email" => $listData1['email'],"address" => $listData1['address']);
// 					}
// 				}
// 					// ------- Main Logic part -------
// 						$final = array();
// 						$final['status'] = true;
// 						$final['statusCode'] = 200;
// 			        	$final['message'] = 'success!';
// 						$final['data'] = $outData;
				
// 					$this->response($final, REST_Controller::HTTP_OK);
// 					// ------------- End -------------
// 				}
// 			} else {
// 				$this->response($decodedToken);
// 			}
// 		} else {
// 			$this->response(['Authentication failed'], REST_Controller::HTTP_OK);
// 		}
// 	}
	//
	
// 	public function dswoUpdate_post()
// 	{
// 		$headers = $this->input->request_headers();
// 		if (isset($headers['Authorization-Token'])) {
// 			$decodedToken = $this->authorization_token->validateToken($headers['Authorization-Token']);
// 			if ($decodedToken['status']) {
// 				$requestData = json_decode(file_get_contents('php://input'), true);
// 				if ($requestData) {
// 					$officerId = $requestData["officerId"];
// 					$updateArr = [];
// 					$skipInput = array("officerId");
// 					foreach ($requestData as $key => $value) {
// 						if (!(in_array($key, $skipInput))) {
// 							$updateArr[$key] = $value;
// 						}
// 					}
// 					// ------- Main Logic part -------
// 					$con = array("id" => $officerId);
// 					$runQuery = $this->API_model->appUpdate("tbl_dswo", $updateArr, $con);
// 					if ($runQuery) {
// 						$final = array();
// 						$final['status'] = true;
// 						$final['statusCode'] = 200;
// 						$final['message'] = 'Data updated successfully';
// 						$final['data'] = $updateArr;
// 					} else {
// 						$final = array();
// 						$final['status'] = true;
// 						$final['statusCode'] = 500;
// 						$final['message'] = 'Data update failed!';
// 						$final['data'] = null;
// 					}
// 					$this->response($final, REST_Controller::HTTP_OK);
// 					// ------------- End -------------
// 				}
// 			} else {
// 				$this->response($decodedToken);
// 			}
// 		} else {
// 			$this->response(['Authentication failed'], REST_Controller::HTTP_OK);
// 		}
// 	}
	//
// public function dswoEdit_post()
// 	{
// 		$headers = $this->input->request_headers();
// 		if (isset($headers['Authorization-Token'])) {
// 			$decodedToken = $this->authorization_token->validateToken($headers['Authorization-Token']);
// 			if ($decodedToken['status']) {
// 				$requestData = json_decode(file_get_contents('php://input'), true);
// 				if ($requestData) {
// 					$officerId = $requestData["officerId"];
// 					$con = array();
// 					$con['conditions'] = array(
// 						'id' => $officerId
// 					);
// 					$outData = array();
// 					$listData = $this->API_model->getRows("tbl_dswo", $con);
// 				$outData = array();
// 				//
// 				if ($listData) {
// 					foreach ($listData as $row) {
// 						$listData1 = (array) $row;
// 						$outData = array( "id" => $listData1['id'],"district_id" => $listData1['district_id'],"name" => $listData1['name'],"contact" => $listData1['contact'],"email" => $listData1['email'],"address" => $listData1['address']);
// 					}
// 				}
// 					// ------- Main Logic part -------
// 						$final = array();
// 						$final['status'] = true;
// 						$final['statusCode'] = 200;
// 			        	$final['message'] = 'success!';
// 						$final['data'] = $outData;
				
// 					$this->response($final, REST_Controller::HTTP_OK);
// 					// ------------- End -------------
// 				}
// 			} else {
// 				$this->response($decodedToken);
// 			}
// 		} else {
// 			$this->response(['Authentication failed'], REST_Controller::HTTP_OK);
// 		}
// 	}
	//
	public function oahUpdate_post()
	{
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization-Token'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization-Token']);
			if ($decodedToken['status']) {
				$requestData = json_decode(file_get_contents('php://input'), true);
				if ($requestData) {
					$oahId = $requestData["oahId"];
					$updateArr = [];
						if (isset($requestData['oah_type'])) { $updateArr['oah_type'] = $requestData['oah_type'];}
						if (isset($requestData['district_id'])) { $updateArr['district_id'] = $requestData['district_id'];}
						if (isset($requestData['name'])) { $updateArr['name'] = $requestData['name'];}
						if (isset($requestData['address'])) {$updateArr['address'] = $requestData['address'];}
						if (isset($requestData['contact'])) {$updateArr['contact'] = json_encode($requestData['contact']);}
						if (isset($requestData['email'])) {$updateArr['email'] = $requestData['email'];}
					// ------- Main Logic part -------
					$con = array("id" => $oahId);
					$runQuery = $this->API_model->appUpdate("tbl_oah_dtl", $updateArr, $con);
					if ($runQuery) {
						$final = array();
						$final['status'] = true;
						$final['statusCode'] = 200;
						$final['message'] = 'Data updated successfully';
						$final['data'] = $updateArr;
					} else {
						$final = array();
						$final['status'] = true;
						$final['statusCode'] = 500;
						$final['message'] = 'Data update failed!';
						$final['data'] = null;
					}
					$this->response($final, REST_Controller::HTTP_OK);
					// ------------- End -------------
				}
			} else {
				$this->response($decodedToken);
			}
		} else {
			$this->response(['Authentication failed'], REST_Controller::HTTP_OK);
		}
	}
	//
				public function oahEdit_post()
	{
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization-Token'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization-Token']);
			if ($decodedToken['status']) {
				$requestData = json_decode(file_get_contents('php://input'), true);
				if ($requestData) {
					$oahId = $requestData["oahId"];
					$con = array();
					$con['conditions'] = array(
						'id' => $oahId
					);
					$outData = array();
					$listData = $this->API_model->getRows("tbl_oah_dtl", $con);
				$outData = array();
				//
				if ($listData) {
					foreach ($listData as $row) {
						$listData1 = (array) $row;
						$contact = json_decode($listData1['contact'], TRUE);
						$outData = array( "id" => $listData1['id'],"oah_type" => $listData1['oah_type'],"district_id" => $listData1['district_id'],"name" => $listData1['name'],"contact" => $contact,"email" => $listData1['email'],"address" => $listData1['address']);
					}
				}
					// ------- Main Logic part -------
						$final = array();
						$final['status'] = true;
						$final['statusCode'] = 200;
			        	$final['message'] = 'success!';
						$final['data'] = $outData;
				
					$this->response($final, REST_Controller::HTTP_OK);
					// ------------- End -------------
				}
			} else {
				$this->response($decodedToken);
			}
		} else {
			$this->response(['Authentication failed'], REST_Controller::HTTP_OK);
		}
	}
	public function getDistrictList_get()
	{
		$headers = $this->input->request_headers();
		try {
			if (isset($headers['Authorization-Token'])) {
				$decodedToken = $this->authorization_token->validateToken($headers['Authorization-Token']);
				if ($decodedToken['status']) {
					$sessionData = $decodedToken["data"];
					$createdBy = $sessionData->uid;
					$con = array();
					$con['conditions'] = array(
						'is_active' => 1
					);
					$outData = array();
					$listData = $this->API_model->getRows("district", $con);
					if ($listData) {
						foreach ($listData as $row) {
							$listData1 = (array) $row;
							$outData[] = array("id" => $listData1['id'], "name" => $listData1['name']);
						}
					}
					$final = array();
					$final['status'] = true;
					$final['statusCode'] = 200;
					$final['message'] = 'success!';
					$final['data'] = $outData;
					$this->response($final, REST_Controller::HTTP_OK);
					// ------------- End -------------
				} else {
					$this->response($decodedToken);
				}
			} else {
				$this->response(['Authentication failed'], REST_Controller::HTTP_OK);
			}
		} catch (Exception $e) {
			$this->set_error($e->getMessage());
			$this->response($e->getMessage(), REST_Controller::HTTP_OK);
			//return false;
		}
	}
	//
	public function getofficerTypeList_get()
	{
		$headers = $this->input->request_headers();
		try {
			if (isset($headers['Authorization-Token'])) {
				$decodedToken = $this->authorization_token->validateToken($headers['Authorization-Token']);
				if ($decodedToken['status']) {
					$sessionData = $decodedToken["data"];
					$createdBy = $sessionData->uid;
					$con = array();				
					$con['conditions'] = array(
						'is_active' => 1
					);
					$outData = array();
					$listData = $this->API_model->getRows("tbl_officer_type", $con);
					if ($listData) {
						foreach ($listData as $row) {
							$listData1 = (array) $row;
							$outData[] = array("id" => $listData1['id'], "name" => $listData1['name']);
						}
					}
					$final = array();
					$final['status'] = true;
					$final['statusCode'] = 200;
					$final['message'] = 'success!';
					$final['data'] = $outData;
					$this->response($final, REST_Controller::HTTP_OK);
					// ------------- End -------------
				} else {
					$this->response($decodedToken);
				}
			} else {
				$this->response(['Authentication failed'], REST_Controller::HTTP_OK);
			}
		} catch (Exception $e) {
			$this->set_error($e->getMessage());
			$this->response($e->getMessage(), REST_Controller::HTTP_OK);
			//return false;
		}
	}
		//
	public function getoahTypeList_get()
	{
		$headers = $this->input->request_headers();
		try {
			if (isset($headers['Authorization-Token'])) {
				$decodedToken = $this->authorization_token->validateToken($headers['Authorization-Token']);
				if ($decodedToken['status']) {
					$sessionData = $decodedToken["data"];
					$createdBy = $sessionData->uid;
					$con = array();
					$outData = array();
					$listData = $this->API_model->getRows("tbl_oah_type", $con);
					if ($listData) {
						foreach ($listData as $row) {
							$listData1 = (array) $row;
							$outData[] = array("id" => $listData1['id'], "name" => $listData1['name']);
						}
					}
					$final = array();
					$final['status'] = true;
					$final['statusCode'] = 200;
					$final['message'] = 'success!';
					$final['data'] = $outData;
					$this->response($final, REST_Controller::HTTP_OK);
					// ------------- End -------------
				} else {
					$this->response($decodedToken);
				}
			} else {
				$this->response(['Authentication failed'], REST_Controller::HTTP_OK);
			}
		} catch (Exception $e) {
			$this->set_error($e->getMessage());
			$this->response($e->getMessage(), REST_Controller::HTTP_OK);
			//return false;
		}
	}
//
// public function addFeedback_post()
// {
//     $headers = $this->input->request_headers();
//     if (isset($headers['Authorization-Token'])) {
//         $decodedToken = $this->authorization_token->validateToken($headers['Authorization-Token']);
//         if ($decodedToken['status']) {
//             $requestData = json_decode(file_get_contents('php://input'), true);
//             if ($requestData) {
//                 foreach ($requestData as $key => $value) {
//                     $insertArr[$key] = $value;
//                 }
//                 // ------- Main Logic part -------
//                 $runQuery = $this->API_model->appInsert("tbl_helpline", $insertArr);
//                 if ($runQuery) {
//                     $final = array();
//                     $final['status'] = true;
//                     $final['statusCode'] = 200;
//                     $final['message'] = 'Data Inserted successfully';
//                     $final['data'] = $insertArr;
//                 } else {
//                     $final = array();
//                     $final['status'] = true;
//                     $final['statusCode'] = 500;
//                     $final['message'] = 'Data Insert failed!';
//                     $final['data'] = null;
//                 }
//                 $this->response($final, REST_Controller::HTTP_OK);
//                 // ------------- End -------------
//             }
//         } else {
//             $this->response($decodedToken);
//         }
//     } else {
//         $this->response(['Authentication failed'], REST_Controller::HTTP_OK);
//     }
// }
//
	public function getfeedbackList_get()
	{
		$headers = $this->input->request_headers();
		try {
			if (isset($headers['Authorization-Token'])) {
				$decodedToken = $this->authorization_token->validateToken($headers['Authorization-Token']);
				if ($decodedToken['status']) {
					$sessionData = $decodedToken["data"];
					$createdBy = $sessionData->uid;
					$con = array();
					$con['conditions'] = array(
						'is_active' => 1
					);
					$outData = array();
					$data = array();
					$listData1 = $this->API_model->getRows("tbl_helpline", $con);
					if ($listData1) {
						foreach ($listData1 as $listData) {
							$listData = (array) $listData;
							$listData = array("name" => $listData['name'], "age" => $listData['age'], "email" => $listData['email'], "gender" => $listData['gender'], "comment" => $listData['comment']);
							$outData[] = $listData;
						}
					}
					$colData = array();
					$colData[] = array("field" => "name", "header" => "Name");
					$colData[] = array("field" => "age", "header" => "Age");
					$colData[] = array("field" => "email", "header" => "Email");
				    $colData[] = array("field" => "gender", "header" => "Gender");
				    $colData[] = array("field" => "comment", "header" => "Comment");
					$data['cols'] = $colData;
					$data['values'] = $outData;
					$final = array();
					$final['status'] = true;
					$final['statusCode'] = 200;
					$final['message'] = 'success!';
					$final['data'] = $data;
					$this->response($final, REST_Controller::HTTP_OK);
					// ------------- End -------------
				} else {
					$this->response($decodedToken);
				}
			} else {
				$this->response(['Authentication failed'], REST_Controller::HTTP_OK);
			}
		} catch (Exception $e) {
			$this->set_error($e->getMessage());
			$this->response($e->getMessage(), REST_Controller::HTTP_OK);
			//return false;
		}
	}
	//
				// 		$where = array("id"=>$taskId);
				// 	$updateData = array("taskStatusId"=>2,"updatedAt"=>$this->now,"updatedBy"=>$updatedBy);
				// 	$runQuery = $this->API_model->appUpdate("tasks", $updateData, $where);
				
		//
	public function districtDelete_post()
	{
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization-Token'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization-Token']);
			if ($decodedToken['status']) {
				$requestData = json_decode(file_get_contents('php://input'), true);
				if ($requestData) {
					$districtId = $requestData["districtId"];
					$where = array("id"=>$districtId);
					$updateData = array("updated_at"=>$this->now,"is_active"=>0,"is_deleted"=>1);
					$runQuery = $this->API_model->appUpdate("district", $updateData, $where);
					if ($runQuery) {
						$final = array();
						$final['status'] = true;
						$final['statusCode'] = 200;
						$final['message'] = 'Data updated successfully';
						$final['data'] = $updateData;
					} else {
						$final = array();
						$final['status'] = true;
						$final['statusCode'] = 500;
						$final['message'] = 'Data update failed!';
						$final['data'] = null;
					}
					$this->response($final, REST_Controller::HTTP_OK);
					// ------------- End -------------
				}
			} else {
				$this->response($decodedToken);
			}
		} else {
			$this->response(['Authentication failed'], REST_Controller::HTTP_OK);
		}
	}
	//
		public function deleteHospital_post()
	{
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization-Token'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization-Token']);
			if ($decodedToken['status']) {
				$requestData = json_decode(file_get_contents('php://input'), true);
				if ($requestData) {
					$hospitalId = $requestData["hospitalId"];
					$where = array("id"=>$hospitalId);
					$updateData = array("updated_at"=>$this->now,"is_active"=>0,"is_deleted"=>1);
					$runQuery = $this->API_model->appUpdate("tbl_hopital_dtl", $updateData, $where);
					if ($runQuery) {
						$final = array();
						$final['status'] = true;
						$final['statusCode'] = 200;
						$final['message'] = 'Data updated successfully';
						$final['data'] = $updateData;
					} else {
						$final = array();
						$final['status'] = true;
						$final['statusCode'] = 500;
						$final['message'] = 'Data update failed!';
						$final['data'] = null;
					}
					$this->response($final, REST_Controller::HTTP_OK);
					// ------------- End -------------
				}
			} else {
				$this->response($decodedToken);
			}
		} else {
			$this->response(['Authentication failed'], REST_Controller::HTTP_OK);
		}
	}
	//
			public function deleteofficerType_post()
	{
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization-Token'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization-Token']);
			if ($decodedToken['status']) {
				$requestData = json_decode(file_get_contents('php://input'), true);
				if ($requestData) {
					$officerTypeId = $requestData["officerTypeId"];
					$where = array("id"=>$officerTypeId);
					$updateData = array("updated_at"=>$this->now,"is_active"=>0,"is_deleted"=>1);
					$runQuery = $this->API_model->appUpdate("tbl_officer_type", $updateData, $where);
					if ($runQuery) {
						$final = array();
						$final['status'] = true;
						$final['statusCode'] = 200;
						$final['message'] = 'Data updated successfully';
						$final['data'] = $updateData;
					} else {
						$final = array();
						$final['status'] = true;
						$final['statusCode'] = 500;
						$final['message'] = 'Data update failed!';
						$final['data'] = null;
					}
					$this->response($final, REST_Controller::HTTP_OK);
					// ------------- End -------------
				}
			} else {
				$this->response($decodedToken);
			}
		} else {
			$this->response(['Authentication failed'], REST_Controller::HTTP_OK);
		}
	}
	//
		//
			public function deleteoahType_post()
	{
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization-Token'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization-Token']);
			if ($decodedToken['status']) {
				$requestData = json_decode(file_get_contents('php://input'), true);
				if ($requestData) {
					$oahTypeId = $requestData["oahTypeId"];
					$where = array("id"=>$oahTypeId);
					$updateData = array("updated_at"=>$this->now,"is_active"=>0,"is_deleted"=>1);
					$runQuery = $this->API_model->appUpdate("tbl_oah_type", $updateData, $where);
					if ($runQuery) {
						$final = array();
						$final['status'] = true;
						$final['statusCode'] = 200;
						$final['message'] = 'Data updated successfully';
						$final['data'] = $updateData;
					} else {
						$final = array();
						$final['status'] = true;
						$final['statusCode'] = 500;
						$final['message'] = 'Data update failed!';
						$final['data'] = null;
					}
					$this->response($final, REST_Controller::HTTP_OK);
					// ------------- End -------------
				}
			} else {
				$this->response($decodedToken);
			}
		} else {
			$this->response(['Authentication failed'], REST_Controller::HTTP_OK);
		}
	}
		//
			public function deleteofficer_post()
	{
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization-Token'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization-Token']);
			if ($decodedToken['status']) {
				$requestData = json_decode(file_get_contents('php://input'), true);
				if ($requestData) {
					$officerId = $requestData["officerId"];
					$where = array("id"=>$officerId);
					$updateData = array("updated_at"=>$this->now,"is_active"=>0,"is_deleted"=>1);
					$runQuery = $this->API_model->appUpdate("tbl_officer_dtl", $updateData, $where);
					if ($runQuery) {
						$final = array();
						$final['status'] = true;
						$final['statusCode'] = 200;
						$final['message'] = 'Data updated successfully';
						$final['data'] = $updateData;
					} else {
						$final = array();
						$final['status'] = true;
						$final['statusCode'] = 500;
						$final['message'] = 'Data update failed!';
						$final['data'] = null;
					}
					$this->response($final, REST_Controller::HTTP_OK);
					// ------------- End -------------
				}
			} else {
				$this->response($decodedToken);
			}
		} else {
			$this->response(['Authentication failed'], REST_Controller::HTTP_OK);
		}
	}
	//
				public function deleteoah_post()
	{
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization-Token'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization-Token']);
			if ($decodedToken['status']) {
				$requestData = json_decode(file_get_contents('php://input'), true);
				if ($requestData) {
					$oahId = $requestData["oahId"];
					$where = array("id"=>$oahId);
					$updateData = array("updated_at"=>$this->now,"is_active"=>0,"is_deleted"=>1);
					$runQuery = $this->API_model->appUpdate("tbl_oah_dtl", $updateData, $where);
					if ($runQuery) {
						$final = array();
						$final['status'] = true;
						$final['statusCode'] = 200;
						$final['message'] = 'Data updated successfully';
						$final['data'] = $updateData;
					} else {
						$final = array();
						$final['status'] = true;
						$final['statusCode'] = 500;
						$final['message'] = 'Data update failed!';
						$final['data'] = null;
					}
					$this->response($final, REST_Controller::HTTP_OK);
					// ------------- End -------------
				}
			} else {
				$this->response($decodedToken);
			}
		} else {
			$this->response(['Authentication failed'], REST_Controller::HTTP_OK);
		}
	}
	//
	public function getSchemeList_get()
	{
		$headers = $this->input->request_headers();
		try {
			if (isset($headers['Authorization-Token'])) {
				$decodedToken = $this->authorization_token->validateToken($headers['Authorization-Token']);
				if ($decodedToken['status']) {
					$sessionData = $decodedToken["data"];
					$createdBy = $sessionData->uid;
					$con = array();
					$con['conditions'] = array(
						'is_active' => 1
					);
					$outData = array();
					$data = array();
					$listData1 = $this->API_model->getRows("tbl_scheme_dtl", $con);
			    	$base_dir = __DIR__;
			    	$fileUrl = base_url();
					if ($listData1) {
						foreach ($listData1 as $listData) {
							$listData = (array) $listData;
							if(isset($listData['document'])){
							 $fileUrl = base_url()."public/upload-files/scheme/".$listData['document'];
							}else{
							 $fileUrl = '';
							}
							$listData = array("id" => $listData['id'],"scheme_name" => $listData['name'], "scheme_objective" => $listData['objective_scheme'], "scheme_eligibility" => $listData['eligible_persons'], "document" => $fileUrl);
							$outData[] = $listData;
						}
					}
					$colData = array();
					$colData[] = array("field" => "id", "header" => "Id");
					$colData[] = array("field" => "scheme_name", "header" => "Name of the Schemes");
					$colData[] = array("field" => "scheme_objective", "header" => "Objective Schemes");
					$colData[] = array("field" => "scheme_eligibility", "header" => "Eligibility");
				    $colData[] = array("field" => "document", "header" => "Document","type" =>"file");
					$data['cols'] = $colData;
					$data['values'] = $outData;
					$final = array();
					$final['status'] = true;
					$final['statusCode'] = 200;
					$final['message'] = 'success!';
					$final['data'] = $data;
					$this->response($final, REST_Controller::HTTP_OK);
					// ------------- End -------------
				} else {
					$this->response($decodedToken);
				}
			} else {
				$this->response(['Authentication failed'], REST_Controller::HTTP_OK);
			}
		} catch (Exception $e) {
			$this->set_error($e->getMessage());
			$this->response($e->getMessage(), REST_Controller::HTTP_OK);
			//return false;
		}
	}
	//
	public function getChartData1_get()
	{
		$headers = $this->input->request_headers();
		try {
			if (isset($headers['Authorization-Token'])) {
				$decodedToken = $this->authorization_token->validateToken($headers['Authorization-Token']);
				if ($decodedToken['status']) {
					$sessionData = $decodedToken["data"];
					$createdBy = $sessionData->uid;
					$con = array();
					$con['conditions'] = array(
						'is_active' => 0
					);
					$outData = array();
				// 	$data = array();
				
					$data = array (
    "labels"  => array('December', 'January', 'Feburary', 'March', 'April', 'May'),
    "userMonthwise" => array(28, 48, 40, 19, 86, 27, 90),
    "helplineMonthwise"   => array(28, 48, 40, 19, 86, 27, 90));
    //  $data = json_encode($fruits);

					$listData1 = $this->API_model->getRows("tbl_scheme_dtl", $con);
			    	// $base_dir = __DIR__;
			    	// $fileUrl = base_url();
				// 	if ($listData1) {
				// 		foreach ($listData1 as $listData) {
				// 			$listData = (array) $listData;
				//             $fileUrl .= "public/upload-files/scheme/".$listData['document'];
				// 			$listData = array("scheme_name" => $listData['name'], "scheme_objective" => $listData['objective_scheme'], "scheme_eligibility" => $listData['eligible_persons'], "document" => $fileUrl);
				// 			$outData[] = $listData;
				// 		}
				// 	}
				// 	$colData = array();
				// 	$colData[] = array("field" => "scheme_name", "header" => "Name of the Schemes");
				// 	$colData[] = array("field" => "scheme_objective", "header" => "Objective Schemes");
				// 	$colData[] = array("field" => "scheme_eligibility", "header" => "Eligibility");
				//     $colData[] = array("field" => "document", "header" => "Document");
				// 	$data['cols'] = $colData;
				// 	$data['values'] = $outData;
					$final = array();
					$final['status'] = true;
					$final['statusCode'] = 200;
					$final['message'] = 'success!';
					$final['data'] = $data;
					$this->response($final, REST_Controller::HTTP_OK);
					// ------------- End -------------
				} else {
					$this->response($decodedToken);
				}
			} else {
				$this->response(['Authentication failed'], REST_Controller::HTTP_OK);
			}
		} catch (Exception $e) {
			$this->set_error($e->getMessage());
			$this->response($e->getMessage(), REST_Controller::HTTP_OK);
			//return false;
		}
	}
	//
		//
	public function getChartData_get()
	{
		$headers = $this->input->request_headers();
		try {
			if (isset($headers['Authorization-Token'])) {
				$decodedToken = $this->authorization_token->validateToken($headers['Authorization-Token']);
				if ($decodedToken['status']) {
					$sessionData = $decodedToken["data"];
					$createdBy = $sessionData->uid;
					$con = array();
					$con['conditions'] = array(
						'is_active' => 0
					);
					$outData = array();
				// 	$data = array();
				$monthCnt = 6;
                $labels = [];
                $yearArray = $monthArray = [];
                $labels[] = date('M, Y');
                $yearArray[] = date("Y");
                $monthArray[] = date("m");
                for ($i = 1; $i < $monthCnt; $i++) {
                    $yearArray[] = date('Y', strtotime("-$i month"));
                    $monthArray[] = date('m', strtotime("-$i month"));
                      $labels[] = date('M, Y', strtotime("-$i month"));
                }
                //
// 	  $data = array (
//     "labels"  => $labels,
//     "userMonthwise" => array(28, 48, 40, 19, 86, 27, 90),
//     "helplineMonthwise"   => array(28, 48, 40, 19, 86, 27, 90));
     //$data = json_encode($fruits);
                $helplineChart = $userChat = array(); 
                $datasets = [];
                foreach($labels as $key => $date){
                    $year = $yearArray[$key];
                    $month = $monthArray[$key];
                    //
                    $sqlAccessCondi= " AND `is_active`=1";
                    
                    $sql = "SELECT COUNT(`id`) as `total` FROM `tbl_users` WHERE YEAR(`created_at`) = '".$year."' AND MONTH(`created_at`) = '".$month."'";
                    $sql.=$sqlAccessCondi;
                    $chartData = $this->API_model->getCustomRows($sql, "single");
                    $userChat[] = $chartData->total;
                    //
                    $sql = "SELECT COUNT(`id`) as `total` FROM `tbl_helpline` WHERE YEAR(`created_at`) = '".$year."' AND MONTH(`created_at`) = '".$month."'";
                    $sql.=$sqlAccessCondi;
                    $chartData = $this->API_model->getCustomRows($sql, "single");
                    $helplineChart[] = $chartData->total;

                    
                    //
                }
                    $datasets[] = array(
                    "labels" => $labels,
                    "userMonthwise" => $userChat,
                    "helplineMonthwise" => $helplineChart
                );
					//$listData1 = $this->API_model->getRows("tbl_scheme_dtl", $con);
			    	// $base_dir = __DIR__;
			    	// $fileUrl = base_url();
				// 	if ($listData1) {
				// 		foreach ($listData1 as $listData) {
				// 			$listData = (array) $listData;
				//             $fileUrl .= "public/upload-files/scheme/".$listData['document'];
				// 			$listData = array("scheme_name" => $listData['name'], "scheme_objective" => $listData['objective_scheme'], "scheme_eligibility" => $listData['eligible_persons'], "document" => $fileUrl);
				// 			$outData[] = $listData;
				// 		}
				// 	}
				// 	$colData = array();
				// 	$colData[] = array("field" => "scheme_name", "header" => "Name of the Schemes");
				// 	$colData[] = array("field" => "scheme_objective", "header" => "Objective Schemes");
				// 	$colData[] = array("field" => "scheme_eligibility", "header" => "Eligibility");
				//     $colData[] = array("field" => "document", "header" => "Document");
				// 	$data['cols'] = $colData;
				// 	$data['values'] = $outData;
					$final = array();
					$final['status'] = true;
					$final['statusCode'] = 200;
					$final['message'] = 'success!';
					$final['data'] = $datasets;
					$this->response($final, REST_Controller::HTTP_OK);
					// ------------- End -------------
				} else {
					$this->response($decodedToken);
				}
			} else {
				$this->response(['Authentication failed'], REST_Controller::HTTP_OK);
			}
		} catch (Exception $e) {
			$this->set_error($e->getMessage());
			$this->response($e->getMessage(), REST_Controller::HTTP_OK);
			//return false;
		}
	}
	//
	//
public function addScheme_post()
    {
        $headers = $this->input->request_headers(); 
		if (isset($headers['Authorization-Token'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization-Token']);
            if ($decodedToken['status'])
            {
				$requestData = $this->input->post();
				if($requestData){
					$base_dir = __DIR__;
					$fileInputData = array();
					if(isset($_FILES['document'])){
					$fileInputData = $_FILES['document'];
					// foreach($fileInputCheck as $fKey => $_FILES['name']){
						//
						$fileFolder = 'scheme';
						$filePath = $base_dir."/../../../public/upload-files/".$fileFolder."/";
						if (!file_exists($filePath) && !is_dir($filePath)) {
							mkdir($filePath, 0777, true);
						}
						$fileNameRequest = $_FILES['document'];
						if(isset($fileNameRequest['name'])){
							$fileName=$fileNameRequest['name'];
							// $fileSize=$fileInputData[$fileNameRequest]['size'];
							$fileNameCmps = explode(".", $fileNameRequest['name']);
							$fileExtension = strtolower(end($fileNameCmps));
							$fileNameGen = strtotime($this->now)."".rand(1111,9999).".".$fileExtension;
							$inputFileArray = array();
							$inputFileArray["fileName"] = $fileName;
							// $inputFileArray["fileExt"] = $fileExtension;
							// $inputFileArray["filePathName"] = $fileNameGen;
							// $inputFileArray["fileSize"] = $fileSize;
							if(move_uploaded_file($fileNameRequest["tmp_name"],$filePath.$fileNameGen)){
								$insertData = array(
									"document" => $fileNameGen
								);

								//
							}
						}
					}
						if (isset($requestData['name'])) { $insertData['name'] = $requestData['name'];}
						if (isset($requestData['objectiveScheme'])) { $insertData['objective_scheme'] = $requestData['objectiveScheme'];}
						if (isset($requestData['eligiblePersons'])) { $insertData['eligible_persons'] = $requestData['eligiblePersons'];}
						 $runQuery = $this->API_model->appInsert("tbl_scheme_dtl", $insertData);
					// }
					if($runQuery){
						$final = array();
						$final['status'] = true;
						$final['statusCode'] = 200;
						$final['message'] = 'Data updated successfully';
						$final['data'] = $insertData;
					}else{
						$final = array();
						$final['status'] = true;
						$final['statusCode'] = 500;
						$final['message'] = 'Data update failed!';
						$final['data'] = null;
					}
					$this->response($final, REST_Controller::HTTP_OK);

					// ------------- End -------------
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
    	//public function getusersReport_get()
    	public function postSeniorCitizenReport_post()
	{
		$config = array('orientation' => 'L', 'size' => 'A4');
		$this->load->library('mypdf', $config);
		$where ='';

        if (isset($requestData['startDate']) && isset($requestData['endDate'])) {
        $start_date =  date('Y-m-d 00:00:00', strtotime($requestData['startDate']));
        $end_date   =  date('Y-m-d 23:59:00', strtotime($requestData['endDate']));
        $where .= "AND `created_at` BETWEEN '".$start_date."' AND '".$end_date."'";
        }
		//For Beat
		$widthTotal = 280;
		//
		$masterType = array();
		// $sql = "SELECT `id`, `name` FROM `tbl_users` WHERE `is_active`=1 AND `is_deleted`=0";
		// $typeData = $this->API_model->getCustomRows($sql);
		$data = array();

		$sql = "SELECT  `id`, `name`, `age`, `gender`, `email`, `comment` FROM `tbl_users` WHERE `is_active`=1 AND `is_deleted`=0 ".$where;
		$typeData = $this->API_model->getCustomRows($sql);
		if ($typeData) {
			foreach ($typeData as $dData) {
				$data[] = array($dData->name,$dData->age,$dData->gender,$dData->email,$dData->comment);
			}
		}
		//
		if ($typeData) {
			foreach ($typeData as $dData) {
				$masterType[$dData->id] = $dData->name;
			}
		}
		//
		// $cnt = count($masterType);
		// $cnt++;
		// $width = $widthTotal / $cnt;
		// $w = array();
		// for ($i = 0; $i < $cnt; $i++) {
		// 	$w[] = $width;
		// }
	    $w = array(35, 40, 45, 45, 45);
		// print_r($w);
		$header = array("Name","Age","Gender","Email","comment");
		// foreach ($masterType as $key => $typeName) {
		// 	$header[] = $typeName;
		// }
		// print_r($header);

		// $data = array();

// print_r($masterType);


		// for ($i = 0; $i <= 6; $i++) {
			// $array = array("111");

			// foreach ($masterType as $key => $typeName) {
			// 	$array[] = $typeName;
			// }

			//

			//
			// $this->mypdf->SetFont('Arial', 'B', 13);
			//
			// $this->mypdf->Cell(0, 10, 'Beat Summary', 0, 1, 'C');


			// $data[] = $array;
			// $data[] = $array;
			// $data[] = $array;

		// }

		$this->mypdf->SetFont('Arial', 'B', 13);
		$this->mypdf->AddPage("L");
		$this->mypdf->Cell(0, 10, 'User Report', 0, 1, 'C');



		
		$this->mypdf->SetFont('Arial', '', 10);
		$totalUser = $inactive7days = $notloggedIn = $userActive = 0;
		$this->mypdf->Cell(70, 5, 'Total No. of User', 0, 0);
		$this->mypdf->Cell(54, 5, ' : ' . $totalUser, 0, 0);
		//$this->mypdf->Cell(130 ,5,'Total No. of User',0,0);
		$this->mypdf->Cell(55, 5, 'Date Range', 0, 0);
		$this->mypdf->Cell(34, 5, ' : (01-05-2023 to 04-05-2023)', 0, 1);
		//
		$this->mypdf->Cell(70, 5, 'Male:', 0, 0);
		$this->mypdf->Cell(54, 5, ' : ' . $notloggedIn, 0, 0);
		$this->mypdf->Cell(55, 5, 'Female', 0, 0);
		$this->mypdf->Cell(34, 5, ' : ' .$notloggedIn . '', 0, 1);
		// $this->mypdf->Cell(70, 5, 'User inactive for more than 7 days', 0, 0);
		// $this->mypdf->Cell(54, 5, ' : ' . $inactive7days, 0, 0);
		// $this->mypdf->Cell(55, 5, 'Zone Name', 0, 0);
		// $this->mypdf->Cell(34, 5, ' : ORD001', 0, 1);
		// $this->mypdf->Cell(70, 5, 'Active users:', 0, 0);
		// $this->mypdf->Cell(54, 5, ' : ' . $userActive, 0, 0);
		// $this->mypdf->Cell(55, 5, 'Range Name:', 0, 0);
		// $this->mypdf->Cell(34, 5, ' : ORD001', 0, 1);
		// $this->mypdf->Cell(124, 5, ' ', 0, 0);
		// $this->mypdf->Cell(55, 5, 'District Name', 0, 0);
		// $this->mypdf->Cell(34, 5, ' : ORD001', 0, 1);
		//
		// $this->mypdf->Cell(124, 5, ' ', 0, 0);
		// $this->mypdf->Cell(55, 5, 'Subdivision Name', 0, 0);
		// $this->mypdf->Cell(34, 5, ' : ORD001', 0, 1);
		// //
		// $this->mypdf->Cell(124, 5, ' ', 0, 0);
		// $this->mypdf->Cell(75, 5, 'Police Station Name', 0, 0);
		// $this->mypdf->Cell(34, 5, ' : ORD001', 0, 1);








		
		$this->mypdf->SetFont('Arial', 'B', 13);
		$this->mypdf->FancyTable2($header, $data ,$w);

		$base_dir = __DIR__;
		$filename = 'report.pdf';
		$filePath = $base_dir . "/../../../public/upload-files/export/";
		if (!file_exists($filePath) && !is_dir($filePath)) {
			mkdir($filePath, 0777, true);
		}

		$f = $filePath . $filename;
		$fileUrl = base_url();
		$fileUrl .= "public/upload-files/export/" . $filename;
		$this->mypdf->Output("F", $f);

		// $outData["fileUrl"] = $f;
		// $outData["request"] = $requestData;
		//
		$final = array();
		$final['status'] = true;
		$final['statusCode'] = 200;
		$final['message'] = 'success!';
		// $final['data'] = $outData;
		$final['fileUrl'] = $fileUrl;
		$this->response($final, REST_Controller::HTTP_OK);

	}
	//
	    	public function getSchemeReport_get()
	{
		$config = array('orientation' => 'L', 'size' => 'A4');
		$this->load->library('mypdf', $config);
		$where ='';

        if (isset($requestData['startDate']) && isset($requestData['endDate'])) {
        $start_date =  date('Y-m-d 00:00:00', strtotime($requestData['startDate']));
        $end_date   =  date('Y-m-d 23:59:00', strtotime($requestData['endDate']));
        $where .= "AND `created_at` BETWEEN '".$start_date."' AND '".$end_date."'";
        }
		//For Beat
		$widthTotal = 280;
		//
		$masterType = array();
		// $sql = "SELECT `id`, `name` FROM `tbl_users` WHERE `is_active`=1 AND `is_deleted`=0";
		// $typeData = $this->API_model->getCustomRows($sql);
		$data = array();

		$sql = "SELECT  `id`, `name`, `document`, `objective_scheme`, `assistance_provided`, `eligible_persons`, `doc_submitted`, `other_benefits`, `whom_application_sent`, `sanctioning_authority`, `authority_services`, `is_active`, `is_deleted`, `created_at`, `updated_at` FROM `tbl_scheme_dtl` WHERE `is_active`=0 AND `is_deleted`=0 ".$where;
		
		$typeData = $this->API_model->getCustomRows($sql);
		if ($typeData) {
			foreach ($typeData as $dData) {
		$data[] = array($dData->name,$dData->objective_scheme,$dData->eligible_persons);
			}
		}
		//
		if ($typeData) {
			foreach ($typeData as $dData) {
				$masterType[$dData->id] = $dData->name;
			}
		}
		//
		// $cnt = count($masterType);
		// $cnt++;
		// $width = $widthTotal / $cnt;
		// $w = array();
		// for ($i = 0; $i < $cnt; $i++) {
		// 	$w[] = $width;
		// }
	    $w = array(80, 80, 80);
		// print_r($w);
		$header = array("Scheme Name","Objective Scheme","Eligible Persons");
		// foreach ($masterType as $key => $typeName) {
		// 	$header[] = $typeName;
		// }
		// print_r($header);

		// $data = array();

// print_r($masterType);


		// for ($i = 0; $i <= 6; $i++) {
			// $array = array("111");

			// foreach ($masterType as $key => $typeName) {
			// 	$array[] = $typeName;
			// }

			//

			//
			// $this->mypdf->SetFont('Arial', 'B', 13);
			//
			// $this->mypdf->Cell(0, 10, 'Beat Summary', 0, 1, 'C');


			// $data[] = $array;
			// $data[] = $array;
			// $data[] = $array;

		// }

		$this->mypdf->SetFont('Arial', 'B', 13);
		$this->mypdf->AddPage("L");
		$this->mypdf->Cell(0, 10, 'User Report', 0, 1, 'C');



		
		$this->mypdf->SetFont('Arial', '', 10);
		$totalUser = $inactive7days = $notloggedIn = $userActive = 0;
		$this->mypdf->Cell(70, 5, 'Total No. of User', 0, 0);
		$this->mypdf->Cell(54, 5, ' : ' . $totalUser, 0, 0);
		//$this->mypdf->Cell(130 ,5,'Total No. of User',0,0);
		$this->mypdf->Cell(55, 5, 'Date Range', 0, 0);
		$this->mypdf->Cell(34, 5, ' : (01-05-2023 to 04-05-2023)', 0, 1);
		//
		$this->mypdf->Cell(70, 5, 'Male:', 0, 0);
		$this->mypdf->Cell(54, 5, ' : ' . $notloggedIn, 0, 0);
		$this->mypdf->Cell(55, 5, 'Female', 0, 0);
		$this->mypdf->Cell(34, 5, ' : ' .$notloggedIn . '', 0, 1);
		// $this->mypdf->Cell(70, 5, 'User inactive for more than 7 days', 0, 0);
		// $this->mypdf->Cell(54, 5, ' : ' . $inactive7days, 0, 0);
		// $this->mypdf->Cell(55, 5, 'Zone Name', 0, 0);
		// $this->mypdf->Cell(34, 5, ' : ORD001', 0, 1);
		// $this->mypdf->Cell(70, 5, 'Active users:', 0, 0);
		// $this->mypdf->Cell(54, 5, ' : ' . $userActive, 0, 0);
		// $this->mypdf->Cell(55, 5, 'Range Name:', 0, 0);
		// $this->mypdf->Cell(34, 5, ' : ORD001', 0, 1);
		// $this->mypdf->Cell(124, 5, ' ', 0, 0);
		// $this->mypdf->Cell(55, 5, 'District Name', 0, 0);
		// $this->mypdf->Cell(34, 5, ' : ORD001', 0, 1);
		//
		// $this->mypdf->Cell(124, 5, ' ', 0, 0);
		// $this->mypdf->Cell(55, 5, 'Subdivision Name', 0, 0);
		// $this->mypdf->Cell(34, 5, ' : ORD001', 0, 1);
		// //
		// $this->mypdf->Cell(124, 5, ' ', 0, 0);
		// $this->mypdf->Cell(75, 5, 'Police Station Name', 0, 0);
		// $this->mypdf->Cell(34, 5, ' : ORD001', 0, 1);
		$this->mypdf->SetFont('Arial', 'B', 13);
		$this->mypdf->FancyTable2($header, $data ,$w);

		$base_dir = __DIR__;
		$filename = 'report.pdf';
		$filePath = $base_dir . "/../../../public/upload-files/export/";
		if (!file_exists($filePath) && !is_dir($filePath)) {
			mkdir($filePath, 0777, true);
		}

		$f = $filePath . $filename;
		$fileUrl = base_url();
		$fileUrl .= "public/upload-files/export/" . $filename;
		$this->mypdf->Output("F", $f);

		$file["fileUrl"] = $fileUrl;
		// $outData["request"] = $requestData;
		//
		$final = array();
		$final['status'] = true;
		$final['statusCode'] = 200;
		$final['message'] = 'success!';
		// $final['data'] = $outData;
		$final['data'] = $file;

// 		$final['fileUrl'] = $fileUrl;
		$this->response($final, REST_Controller::HTTP_OK);

	}
	
	    //
	public function getAdminDashboard_get()
	{
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization-Token'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization-Token']);
			if ($decodedToken['status']) {
				$sqlAccessCondi = " AND `is_active`=1";//total staff, total entry, today entry , user creation
				$sql = "SELECT COUNT(`id`) as `total` FROM `users` WHERE 1 ";
				$sql .= $sqlAccessCondi;
				$staffData = $this->API_model->getCustomRows($sql, "single");
				$staffCount = $staffData->total;
				//
				$sql = "SELECT COUNT(`id`) as `total` FROM `tbl_family_details` WHERE 1 ";
				$sql .= $sqlAccessCondi;
				$familyData = $this->API_model->getCustomRows($sql, "single");
				$familyCount = $familyData->total;
				//

				//
				$datasets = array(
					"staffCount" => $staffCount,
					"totalEntry" => $familyCount,
					"todayEntry" => $familyCount,
					"newUser" => $staffCount
				);
				// }
				$final = array();
				$final['status'] = true;
				$final['statusCode'] = 200;
				$final['message'] = 'Data success!';
				$final['data'] = $datasets;
				$this->response($final, REST_Controller::HTTP_OK);
				// ------------- End -------------
			} else {
				$this->response($decodedToken);
			}
		} else {
			$this->response(['Authentication failed'], REST_Controller::HTTP_OK);
		}
	}
	//
		public function getAdminChart_get()
	{
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization-Token'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization-Token']);
			if ($decodedToken['status']) {

$datasets = [
    "labels" => ['madurai', 'Trichy', 'chennai', 'Erode', 'Karur', 'Namakkal', 'Vellore'],
    "datasets" => [
        [
            "label" => 'Target',
            "backgroundColor" => '#4AB58E',
            "data" => [65, 59, 80, 81, 56, 76, 67]
        ],
        [
            "label" => 'Competition',
            "backgroundColor" => '#FFCF00',
            "data" => [28, 48, 40, 19, 86, 45, 67]
        ]
    ]
];




				// }
				$final = array();
				$final['status'] = true;
				$final['statusCode'] = 200;
				$final['message'] = 'Data success!';
				$final['data'] = $datasets;
				$this->response($final, REST_Controller::HTTP_OK);
				// ------------- End -------------
			} else {
				$this->response($decodedToken);
			}
		} else {
			$this->response(['Authentication failed'], REST_Controller::HTTP_OK);
		}
	}
	//
		public function getMobInstallList_get()
	{
		$headers = $this->input->request_headers();
		try {
			if (isset($headers['Authorization-Token'])) {
				$decodedToken = $this->authorization_token->validateToken($headers['Authorization-Token']);
				if ($decodedToken['status']) {
					$sessionData = $decodedToken["data"];
					$createdBy = $sessionData->uid;
					$con = array();
					$con['conditions'] = array(
						'is_active' => 1
					);
					$outData = array();
					$data = array();
					$listData1 = $this->API_model->getRows("tbl_users_log", $con);
			    	$base_dir = __DIR__;
			    	$fileUrl = base_url();
					if ($listData1) {
						foreach ($listData1 as $listData) {
							$listData = (array) $listData;
							if(isset($listData['document'])){
							 $fileUrl = base_url()."public/upload-files/scheme/".$listData['document'];
							}else{
							 $fileUrl = '';
							}
							$listData = array("device" => $listData['device'],"name" => $listData['device_agent'], "mobile" => $listData['mobile']);
							$outData[] = $listData;
						}
					}
					$colData = array();
					$colData[] = array("field" => "device", "header" => "Device Name");
					$colData[] = array("field" => "name", "header" => "Name");
					$colData[] = array("field" => "mobile", "header" => "Mobile");
					$data['cols'] = $colData;
					$data['values'] = $outData;
					$final = array();
					$final['status'] = true;
					$final['statusCode'] = 200;
					$final['message'] = 'success!';
					$final['data'] = $data;
					$this->response($final, REST_Controller::HTTP_OK);
					// ------------- End -------------
				} else {
					$this->response($decodedToken);
				}
			} else {
				$this->response(['Authentication failed'], REST_Controller::HTTP_OK);
			}
		} catch (Exception $e) {
			$this->set_error($e->getMessage());
			$this->response($e->getMessage(), REST_Controller::HTTP_OK);
			//return false;
		}
	}
	//
	public function schemeUpdate_post()
    {
        $headers = $this->input->request_headers(); 
		if (isset($headers['Authorization-Token'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization-Token']);
            if ($decodedToken['status'])
            {
				$requestData = $this->input->post();
				if($requestData){
				    $schemeId = $requestData["schemeId"];
					$base_dir = __DIR__;
					$fileInputData = array();
				    $updateArr = [];
					if(isset($_FILES['document'])){
					$fileInputData = $_FILES['document'];
					//
					//
					// foreach($fileInputCheck as $fKey => $_FILES['name']){
						//
						$fileFolder = 'scheme';
						$filePath = $base_dir."/../../../public/upload-files/".$fileFolder."/";
						if (!file_exists($filePath) && !is_dir($filePath)) {
							mkdir($filePath, 0777, true);
						}
						//
						$fileNameRequest = $_FILES['document'];
						if(isset($fileNameRequest['name'])){
							$fileName=$fileNameRequest['name'];
							// $fileSize=$fileInputData[$fileNameRequest]['size'];
							$fileNameCmps = explode(".", $fileNameRequest['name']);
							$fileExtension = strtolower(end($fileNameCmps));
							$fileNameGen = strtotime($this->now)."".rand(1111,9999).".".$fileExtension;
							$inputFileArray = array();
							$inputFileArray["fileName"] = $fileName;
							// $inputFileArray["fileExt"] = $fileExtension;
							// $inputFileArray["filePathName"] = $fileNameGen;
							// $inputFileArray["fileSize"] = $fileSize;
							if(move_uploaded_file($fileNameRequest["tmp_name"],$filePath.$fileNameGen)){
								$updateArr = array(
									"document" => $fileNameGen
								);

								//
							}
						}
					}
//
if(!empty($updateArr['document'])){
				$sql = "SELECT `document` FROM `tbl_scheme_dtl` WHERE `id`= '".$requestData['schemeId']."' ";
				$Data = $this->API_model->getCustomRows($sql, "single");
                $prevDocument = (array)$Data;
    // 			$fileFolder = 'scheme';
    			$filePath = $base_dir."/../../../public/upload-files/scheme/".$prevDocument['document']."";
    unlink($filePath);
}
						if (isset($requestData['name'])) { $updateArr['name'] = $requestData['name'];}
						if (isset($requestData['objective_scheme'])) { $updateArr['objective_scheme'] = $requestData['objective_scheme'];}
						if (isset($requestData['eligible_persons'])) { $updateArr['eligible_persons'] = $requestData['eligible_persons'];}
    					$con = array("id" => $schemeId);
    					$runQuery = $this->API_model->appUpdate("tbl_scheme_dtl", $updateArr, $con);
					// }
					if($runQuery){
						$final = array();
						$final['status'] = true;
						$final['statusCode'] = 200;
						$final['message'] = 'Data updated successfully';
						$final['data'] = $updateArr;
					}else{
						$final = array();
						$final['status'] = true;
						$final['statusCode'] = 500;
						$final['message'] = 'Data update failed!';
						$final['data'] = null;
					}
					$this->response($final, REST_Controller::HTTP_OK);

					// ------------- End -------------
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
    	public function schemeEdit_post()
	{
		$headers = $this->input->request_headers();
		try {
			if (isset($headers['Authorization-Token'])) {
				$decodedToken = $this->authorization_token->validateToken($headers['Authorization-Token']);
				if ($decodedToken['status']) {
				    $requestData = json_decode(file_get_contents('php://input'), true);
				    if ($requestData) {
				    $schemeId = $requestData["schemeId"];
					$sessionData = $decodedToken["data"];
					$createdBy = $sessionData->uid;
					$con = array();
					$con['conditions'] = array(
						'is_active' => 1,
						'id' => $schemeId
					);
					$outData = array();
					$data = array();
					$listData1 = $this->API_model->getRows("tbl_scheme_dtl", $con);
			    	$base_dir = __DIR__;
			    	$fileUrl = base_url();
			    	$listData ='';
					if ($listData1) {
						foreach ($listData1 as $listData) {
							$listData = (array) $listData;
							if(isset($listData['document'])){
							 $fileUrl = base_url()."public/upload-files/scheme/".$listData['document'];
							}else{
							 $fileUrl = '';
							}
							$listData = array("scheme_name" => $listData['name'], "scheme_objective" => $listData['objective_scheme'], "scheme_eligibility" => $listData['eligible_persons'], "document" => $fileUrl);
				// 			$outData[] = $listData;
						}
					}
				// 	$colData = array();
				// 	$colData[] = array("field" => "scheme_name", "header" => "Name of the Schemes");
				// 	$colData[] = array("field" => "scheme_objective", "header" => "Objective Schemes");
				// 	$colData[] = array("field" => "scheme_eligibility", "header" => "Eligibility");
				//     $colData[] = array("field" => "document", "header" => "Document","type" =>"file");
				// 	$data['cols'] = $colData;
				// 	$data['values'] = $outData;
					$final = array();
					$final['status'] = true;
					$final['statusCode'] = 200;
					$final['message'] = 'success!';
					$final['data'] = $listData;
					$this->response($final, REST_Controller::HTTP_OK);
					// ------------- End -------------
				} else {
					$this->response($decodedToken);
				}
				}
			} else {
				$this->response(['Authentication failed'], REST_Controller::HTTP_OK);
			}
		} catch (Exception $e) {
			$this->set_error($e->getMessage());
			$this->response($e->getMessage(), REST_Controller::HTTP_OK);
			//return false;
		}
	}
	//
		public function schemeDelete_post()
	{
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization-Token'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization-Token']);
			if ($decodedToken['status']) {
				$requestData = json_decode(file_get_contents('php://input'), true);
				if ($requestData) {
					$schemeId = $requestData["schemeId"];
					$where = array("id"=>$schemeId);
					$updateData = array("updated_at"=>$this->now,"is_active"=>0,"is_deleted"=>1);
					$runQuery = $this->API_model->appUpdate("tbl_scheme_dtl", $updateData, $where);
					if ($runQuery) {
						$final = array();
						$final['status'] = true;
						$final['statusCode'] = 200;
						$final['message'] = 'Data updated successfully';
						$final['data'] = $updateData;
					} else {
						$final = array();
						$final['status'] = true;
						$final['statusCode'] = 500;
						$final['message'] = 'Data update failed!';
						$final['data'] = null;
					}
					$this->response($final, REST_Controller::HTTP_OK);
					// ------------- End -------------
				}
			} else {
				$this->response($decodedToken);
			}
		} else {
			$this->response(['Authentication failed'], REST_Controller::HTTP_OK);
		}
	}
	//
		public function addAlrMedicalType_post()
	{
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization-Token'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization-Token']);
			if ($decodedToken['status']) {
				$requestData = json_decode(file_get_contents('php://input'), true);
				if ($requestData) {
					foreach ($requestData as $key => $value) {
						$insertArr[$key] = $value;
					}
					// ------- Main Logic part -------
					$runQuery = $this->API_model->appInsert("tbl_alr_medical_type", $insertArr);
					if ($runQuery) {
						$final = array();
						$final['status'] = true;
						$final['statusCode'] = 200;
						$final['message'] = 'Data Inserted successfully';
						$final['data'] = $insertArr;
					} else {
						$final = array();
						$final['status'] = true;
						$final['statusCode'] = 500;
						$final['message'] = 'Data Insert failed!';
						$final['data'] = null;
					}
					$this->response($final, REST_Controller::HTTP_OK);
					// ------------- End -------------
				}
			} else {
				$this->response($decodedToken);
			}
		} else {
			$this->response(['Authentication failed'], REST_Controller::HTTP_OK);
		}
	}
	//
		public function getAlrMedicalType_get()
	{
		$headers = $this->input->request_headers();
		try {
			if (isset($headers['Authorization-Token'])) {
				$decodedToken = $this->authorization_token->validateToken($headers['Authorization-Token']);
				if ($decodedToken['status']) {
					$sessionData = $decodedToken["data"];
					$createdBy = $sessionData->uid;
					$con = array();
					$con['conditions'] = array(
						'is_active' => 1
					);
					$outData = array();
					$data = array();
					$listData1 = $this->API_model->getRows("tbl_alr_medical_type", $con);
					if ($listData1) {
						foreach ($listData1 as $listData) {
							$listData = (array) $listData;
							$listData = array("id" => $listData['id'], "name" => $listData['name']);
							$outData[] = $listData;
						}
					}
					$colData = array();
					$colData[] = array("field" => "id", "header" => "id");
					$colData[] = array("field" => "name", "header" => "Name");
					$data['cols'] = $colData;
					$data['values'] = $outData;
					$final = array();
					$final['status'] = true;
					$final['statusCode'] = 200;
					$final['message'] = 'success!';
					$final['data'] = $data;
					$this->response($final, REST_Controller::HTTP_OK);
					// ------------- End -------------
				} else {
					$this->response($decodedToken);
				}
			} else {
				$this->response(['Authentication failed'], REST_Controller::HTTP_OK);
			}
		} catch (Exception $e) {
			$this->set_error($e->getMessage());
			$this->response($e->getMessage(), REST_Controller::HTTP_OK);
			//return false;
		}
	}
	//
		public function AlrMedicalTypeUpdate_post()
	{
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization-Token'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization-Token']);
			if ($decodedToken['status']) {
				$requestData = json_decode(file_get_contents('php://input'), true);
				if ($requestData) {
					$medicalTypeId = $requestData["medicalTypeId"];
					$updateArr = [];
					$skipInput = array("medicalTypeId");
					foreach ($requestData as $key => $value) {
						if (!(in_array($key, $skipInput))) {
							$updateArr[$key] = $value;
						}
					}
					// ------- Main Logic part -------
					$con = array("id" => $medicalTypeId);
					$runQuery = $this->API_model->appUpdate("tbl_alr_medical_type", $updateArr, $con);
					if ($runQuery) {
						$final = array();
						$final['status'] = true;
						$final['statusCode'] = 200;
						$final['message'] = 'Data updated successfully';
						$final['data'] = $updateArr;
					} else {
						$final = array();
						$final['status'] = true;
						$final['statusCode'] = 500;
						$final['message'] = 'Data update failed!';
						$final['data'] = null;
					}
					$this->response($final, REST_Controller::HTTP_OK);
					// ------------- End -------------
				}
			} else {
				$this->response($decodedToken);
			}
		} else {
			$this->response(['Authentication failed'], REST_Controller::HTTP_OK);
		}
	}
	//
			public function AlrMedicalTypeEdit_post()
	{
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization-Token'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization-Token']);
			if ($decodedToken['status']) {
				$requestData = json_decode(file_get_contents('php://input'), true);
				if ($requestData) {
					$medicalTypeId = $requestData["medicalTypeId"];
					$con = array();
					$con['conditions'] = array(
						'id' => $medicalTypeId
					);
					$outData = array();
					$listData = $this->API_model->getRows("tbl_alr_medical_type", $con);
				$outData = array();
				//
				if ($listData) {
					foreach ($listData as $row) {
						$listData1 = (array) $row;
						$outData = array( "id" => $listData1['id'],"name" => $listData1['name']);
					}
				}
					// ------- Main Logic part -------
						$final = array();
						$final['status'] = true;
						$final['statusCode'] = 200;
			        	$final['message'] = 'success!';
						$final['data'] = $outData;
				
					$this->response($final, REST_Controller::HTTP_OK);
					// ------------- End -------------
				}
			} else {
				$this->response($decodedToken);
			}
		} else {
			$this->response(['Authentication failed'], REST_Controller::HTTP_OK);
		}
	}
	//
		public function getAlrMedicalTypeList_get()
	{
		$headers = $this->input->request_headers();
		try {
			if (isset($headers['Authorization-Token'])) {
				$decodedToken = $this->authorization_token->validateToken($headers['Authorization-Token']);
				if ($decodedToken['status']) {
					$sessionData = $decodedToken["data"];
					$createdBy = $sessionData->uid;
					$con = array();				
					$con['conditions'] = array(
						'is_active' => 1
					);
					$outData = array();
					$listData = $this->API_model->getRows("tbl_alr_medical_type", $con);
					if ($listData) {
						foreach ($listData as $row) {
							$listData1 = (array) $row;
							$outData[] = array("id" => $listData1['id'], "name" => $listData1['name']);
						}
					}
					$final = array();
					$final['status'] = true;
					$final['statusCode'] = 200;
					$final['message'] = 'success!';
					$final['data'] = $outData;
					$this->response($final, REST_Controller::HTTP_OK);
					// ------------- End -------------
				} else {
					$this->response($decodedToken);
				}
			} else {
				$this->response(['Authentication failed'], REST_Controller::HTTP_OK);
			}
		} catch (Exception $e) {
			$this->set_error($e->getMessage());
			$this->response($e->getMessage(), REST_Controller::HTTP_OK);
			//return false;
		}
	}
		//
	}