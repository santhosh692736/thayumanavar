<?php
defined('BASEPATH') or exit('No direct script access allowed');
/**
 * User class.
 * 
 * @extends REST_Controller
 */
require(APPPATH . '/libraries/REST_Controller.php');

use Restserver\Libraries\REST_Controller;

class User extends REST_Controller
{
	/**
	 * __construct function.
	 * 
	 * @access public
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
		$this->load->library('Authorization_Token');
		$this->load->model('user_model');
		$this->load->library('appcommon_lib');
		$this->load->model('API_model');
		$this->now = date("Y-m-d H:i:s");
		//
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
	 * login function.
	 * 
	 * @access public
	 * @return void
	 */
	public function login_post()
	{
		// set variables from the form
		$requestData = json_decode(file_get_contents('php://input'), true);
		$username = $requestData["userName"];
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
				'roleTypeName' => (string) $roleTypeName,
				'userRole' => (string) $user->user_role,
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
	public function logout_post()
	{
		$headers = $this->input->request_headers();
		try {
			if (isset($headers['Authorization-Token'])) {
				$decodedToken = $this->authorization_token->validateToken($headers['Authorization-Token']);
				if ($decodedToken['status']) {
					$sessionData = $decodedToken["data"];
					$createdBy = $sessionData->uid;
					$requestData = json_decode(file_get_contents('php://input'), true);
					$userId = $requestData["userId"];
					//
					$updateData = array("outDatetime" => date("Y-m-d H:i:s"));
					if ($this->API_model->appUpdate("login", $updateData, array("userId" => $userId))) {
						//
						$token = $headers['Authorization-Token'];
						$updateData = array("outDatetime" => date("Y-m-d H:i:s"));
						$this->API_model->appUpdate("login_log", $updateData, array("userId" => $userId, "token" => $token));
					}
					$final = array();
					$final['status'] = true;
					$final['statusCode'] = 200;
					$final['message'] = 'Logout successfully!';
					$final['data'] = null;
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
	public function pds_post()
	{
		$headers = $this->input->request_headers();
		try {
			if (isset($headers['Authorization-Token'])) {
				$decodedToken = $this->authorization_token->validateToken($headers['Authorization-Token']);
				if ($decodedToken['status']) {
					$sessionData = $decodedToken["data"];
					$createdBy = $sessionData->uid;
					$requestData = json_decode(file_get_contents('php://input'), true);
					//
					if (substr((string)$requestData['pdsNo'], 0, 3) === '333') {
						$pdsKey = 'rationNo';
					} else {
						$pdsKey = 'aadhaarNo';
					}
					if ($pdsKey) {
						$pdsNo = $requestData["pdsNo"];
						//
						$url = 'https://dev.vividtranstech.com/cmupt/api/v1/auth/login';
						// The data you want to send in the request
						$data = array(
							'username' => 'johndoe',
							'password' => '1234'
						);
						// Encode the data as JSON
						$json_data = json_encode($data);
						// Initialize cURL
						$ch = curl_init($url);
						// Set the necessary options for cURL
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
						curl_setopt($ch, CURLOPT_POST, true);
						curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
						curl_setopt($ch, CURLOPT_HTTPHEADER, array(
							'Content-Type: application/json',
							'Content-Length: ' . strlen($json_data)
						));
						// Execute the request and store the response
						$response = curl_exec($ch);
						// Close the cURL session
						curl_close($ch);
						// Decode the JSON response into a PHP array
						$response_data = json_decode($response, true);
						// Check if the 'data' and 'accessToken' keys exist and get the accessToken
						if (isset($response_data['data']) && isset($response_data['data']['accessToken'])) {
							$accessToken = $response_data['data']['accessToken'];
							// The URL you are sending the request to
							$url = 'https://dev.vividtranstech.com/cmupt/api/v1/pds';
							// The data you want to send in the request
							$data = array(
								$pdsKey => $pdsNo
							);
							// Encode the data as JSON
							$json_data = json_encode($data);
							// Your access token
							// Initialize cURL
							$ch = curl_init($url);
							// Set the necessary options for cURL
							curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
							curl_setopt($ch, CURLOPT_POST, true);
							curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
							curl_setopt($ch, CURLOPT_HTTPHEADER, array(
								'Content-Type: application/json',
								'Authorization: Bearer ' . $accessToken,
								'Content-Length: ' . strlen($json_data)
							));
							// Execute the request and store the response
							$response = curl_exec($ch);
							// Decode JSON string to PHP array
							$array = json_decode($response, true);
							//
							if ($array['statusCode'] == 501) {
								$array = json_decode($response, true);
								$this->response($array, REST_Controller::HTTP_OK);
								exit;
							}
							//
							curl_close($ch);
							// Print the resulting array
							$familyId = '';
							if (isset($array['data']) && isset($array['data']['familyDetails'])) {
								$familyDetails = $array['data']['familyDetails'];
								// Initialize variable to hold the aadhaarId
								$aadhaarId = null;
								// Iterate through family members to find the family head's aadhaarId
								foreach ($familyDetails['familyMembers'] as $member) {
									if ($member['taRelationToHead'] === 'Family Head') {
										$aadhaarId = $member['aadhaarId'];
										break;
									}
								}
								$con = array();
								$con['returnType'] = '';
								$con['conditions'] = array(
									'pds_number' => $familyDetails['rationCardNo']
								);
								$selectedData = $this->API_model->getRows('tbl_family_details', $con);
								if ($selectedData == '') {
									if (isset($familyDetails['rationCardNo'])) {
										$insertfamilyIdData['pds_number'] = $familyDetails['rationCardNo'];
									}
									if (isset($familyDetails['name'])) {
										$insertfamilyIdData['head_name'] = $familyDetails['name'];
									}
									if (isset($familyDetails['address'])) {
										$insertfamilyIdData['address'] = $familyDetails['address'];
									}
									if (isset($familyDetails['village'])) {
										$insertfamilyIdData['village'] = $familyDetails['village'];
									}
									if (isset($familyDetails['taluk'])) {
										$insertfamilyIdData['taluk'] = $familyDetails['taluk'];
									}
									if (isset($familyDetails['district'])) {
										$insertfamilyIdData['district_name'] = $familyDetails['district'];
									}
									if (isset($familyDetails['pincode'])) {
										$insertfamilyIdData['pin_code'] = $familyDetails['pincode'];
									}
									if (isset($aadhaarId)) {
										$insertfamilyIdData['aadhaar_number'] = $aadhaarId;
									}
									$familyId = $this->API_model->appInsert("tbl_family_details", $insertfamilyIdData);
									//
									$familyMembers = $array['data']['familyDetails']['familyMembers'];
									//
									if (isset($familyMembers)) {
										foreach ($familyMembers as $familyKey => $familyValue) {
											$insertData['family_id'] = $familyId;
											$insertData['member_name'] = $familyValue['name'];
											$insertData['member_relation'] = $familyValue['taRelationToHead'];
											$insertData['age'] = '';
											$insertData['dob'] = '';
											$insertData['gender'] = $familyValue['gender'];
											$familyData = $this->API_model->appInsert("tbl_family_member_details", $insertData);
										}
									}
									$con = array();
									$con['returnType'] = '';
									$con['conditions'] = array(
										'pds_number' => $familyDetails['rationCardNo']
									);
									//
									$selectedData = $this->API_model->getRows('tbl_family_details', $con);

									// $
								} else {
									$familyDetails = $array['data']['familyDetails'];
									$con = array();
									$con['returnType'] = '';
									$con['conditions'] = array(
										'pds_number' => $familyDetails['rationCardNo']
									);
									//
									$selectedData = $this->API_model->getRows('tbl_family_details', $con);
									$familyId = $selectedData[0]->id;
								}
							}
							//
							$memberlistData = array();
							$familyMembersResult = (array)$selectedData[0];
							$memberlistData['familyDetails'] = array(
								"familyId" => $familyMembersResult['id'],
								"headName" => $familyMembersResult['head_name'],
								"rationCardNo" => $familyMembersResult['pds_number'],
								"aadhaarId" => $familyMembersResult['aadhaar_number'],
								"address" => $familyMembersResult['address'],
								"districtName" => $familyMembersResult['district_name'],
								"taluk" => $familyMembersResult['taluk'],
								"pincode" => $familyMembersResult['pin_code'],
								"village" => $familyMembersResult['village']
							);
							//
							$con1 = array();
							$con1['returnType'] = '';
							$con1['conditions'] = array(
								'family_id' => $familyId
							);
							$isAlive = ["Alive", "Not Alive"];
							$familyMemberData = $this->API_model->getRows('tbl_family_member_details', $con1);
							if ($familyMemberData) {
								foreach ($familyMemberData as $listData) {
									$listData = (array) $listData;
									$status = 'pending';
									if (!empty($listData['education_details'])) {
										$status = 'completed';
									}
									$memberlistData['familyDetails']['familyMembers'][] = array(
										"memberId" => $listData['id'],
										"familyId" => $listData['family_id'],
										"name" => $listData['member_name'],
										"memberRelation" => $listData['member_relation'],
										"dob" => $listData['dob'],
										"gender" => $listData['gender'],
										"isAlive" => $isAlive[$listData['is_alive']],
										"status" => $status
									);
								}
							}
							// Close the cURL session
						}
					}
					//
					$final = array();
					$final['code'] = 'success';
					$final['message'] = 'success!';
					$final['statusCode'] = 200;
					$final['success'] = true;
					$final['data'] = $memberlistData;
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
	public function updateMemberAlive_post()
	{
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization-Token'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization-Token']);
			if ($decodedToken['status']) {
				$requestData = json_decode(file_get_contents('php://input'), true);
				if ($requestData) {
					$memberId = $requestData["memberId"];
					//
					$updateData['is_alive'] = $requestData['isAlive'];
					//

					if (isset($requestData['isAlive']) == 0) {
						$updateData['education_details'] = '-';
					}
					if (isset($requestData['oldHeadId']) && !empty($requestData['oldHeadId'])) {
						$con = array();
						$con['returnType'] = '';
						$con['conditions'] = array(
							'id' => $requestData['memberId']
						);
						//
						$selectedData = $this->API_model->getRows('tbl_family_member_details', $con);
						$memberData = $selectedData[0];
						$updateData1['head_name'] = $memberData->member_name;
						$con1 = array("id" => $requestData['oldHeadId']);
						$runQuery1 = $this->API_model->appUpdate("tbl_family_details", $updateData1, $con1);
						$updateData['member_relation'] = 18;
					}
					$con = array("id" => $memberId);
					$runQuery = $this->API_model->appUpdate("tbl_family_member_details", $updateData, $con);

					if ($runQuery) {
						$final = array();
						$final['code'] = 'success';
						$final['message'] = 'Data updated successfully';
						$final['statusCode'] = 200;
						$final['success'] = true;
						$final['data'] = $updateData;
					} else {
						$final = array();
						$final['code'] = 'success';
						$final['message'] = 'Data update failed!';
						$final['statusCode'] = 500;
						$final['success'] = false;
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
	public function getFamilyDetalis_get()
	{
		$headers = $this->input->request_headers();
		try {
			if (isset($headers['Authorization-Token'])) {
				$decodedToken = $this->authorization_token->validateToken($headers['Authorization-Token']);
				if ($decodedToken['status']) {
					$sessionData = $decodedToken["data"];
					$createdBy = $sessionData->uid;
					// $requestData = json_decode(file_get_contents('php://input'), true);
					$requestData = $this->input->get();
					//
					if ($requestData) {
						$con = array();
						$con['returnType'] = '';
						$con['conditions'] = array(
							'id' => $requestData['familyId']
						);
						//
						$selectedData = $this->API_model->getRows('tbl_family_details', $con);
						$familyId = $selectedData[0]->id;

						//
						$memberlistData = array();
						$familyMembersResult = (array)$selectedData[0];
						$memberlistData['familyDetails'] = array(
							"familyId" => $familyMembersResult['id'],
							"headName" => $familyMembersResult['head_name'],
							"rationCardNo" => $familyMembersResult['pds_number'],
							"aadhaarId" => $familyMembersResult['aadhaar_number'],
							"address" => $familyMembersResult['address'],
							"districtName" => $familyMembersResult['district_name'],
							"taluk" => $familyMembersResult['taluk'],
							"pincode" => $familyMembersResult['pin_code'],
							"village" => $familyMembersResult['village']
						);
						//
						$con1 = array();
						$con1['returnType'] = '';
						$con1['conditions'] = array(
							'family_id' => $familyId
						);
						$familyMemberData = $this->API_model->getRows('tbl_family_member_details', $con1);
						if ($familyMemberData) {
							foreach ($familyMemberData as $listData) {
								$listData = (array) $listData;
								$status = 'pending';
								if (!empty($listData['education_details'])) {
									$status = 'completed';
								}
								$memberlistData['familyDetails']['familyMembers'][] = array(
									"memberId" => $listData['id'],
									"familyId" => $listData['family_id'],
									"name" => $listData['member_name'],
									"memberRelation" => $listData['member_relation'],
									"dob" => $listData['dob'],
									"gender" => $listData['gender'],
									"isAlive" => $listData['is_alive'],
									"status" => $status
								);
							}
						}
					}
					//
					$final = array();
					$final['code'] = 'success';
					$final['message'] = 'success!';
					$final['statusCode'] = 200;
					$final['success'] = true;
					$final['data'] = $memberlistData;
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
	public function getFamilyMemberName_get()
	{
		$headers = $this->input->request_headers();
		try {
			if (isset($headers['Authorization-Token'])) {
				$decodedToken = $this->authorization_token->validateToken($headers['Authorization-Token']);
				if ($decodedToken['status']) {
					$sessionData = $decodedToken["data"];
					$createdBy = $sessionData->uid;
					// $requestData = json_decode(file_get_contents('php://input'), true);
					$requestData = $this->input->get();

					//
					if ($requestData) {

						//
						$familyId = $requestData['familyId'];

						//
						$con1 = array();
						$con1['returnType'] = '';
						$con1['conditions'] = array(
							'family_id' => $familyId
						);
						$memberlistData = array();
						$familyMemberData = $this->API_model->getRows('tbl_family_member_details', $con1);
						if ($familyMemberData) {
							foreach ($familyMemberData as $listData) {
								$listData = (array) $listData;
								$memberlistData[] = array(
									"memberId" => $listData['id'],
									"name" => $listData['member_name'],
								);
							}
						}
					}
					//
					$final = array();
					$final['code'] = 'success';
					$final['message'] = 'success!';
					$final['statusCode'] = 200;
					$final['success'] = true;
					$final['data'] = $memberlistData;
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
	public function getFamilyRelation_get()
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
					$listData = $this->API_model->getRows("tbl_family_relation", $con);
					if ($listData) {
						foreach ($listData as $row) {
							$listData1 = (array) $row;
							$outData[] = array("id" => $listData1['id'], "name" => $listData1['name']);
						}
					}
					$final = array();
					$final['code'] = 'success';
					$final['message'] = 'success!';
					$final['statusCode'] = 200;
					$final['success'] = true;
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
	public function addFamilyDetails_post()
	{
		$headers = $this->input->request_headers();
		try {
			if (isset($headers['Authorization-Token'])) {
				$decodedToken = $this->authorization_token->validateToken($headers['Authorization-Token']);
				if ($decodedToken['status']) {
					$sessionData = $decodedToken["data"];
					$createdBy = $sessionData->uid;
					// 	$requestData = json_decode(file_get_contents('php://input'), true);
					$requestData = $this->input->post();
					//

					// if (isset($familyDetails['rationCardNo'])) {
					// 	$insertfamilyIdData['pds_number'] = $familyDetails['rationCardNo'];
					// }
					// if (isset($familyDetails['name'])) {
					// 	$insertfamilyIdData['head_name'] = $familyDetails['name'];
					// }
					// if (isset($familyDetails['address'])) {
					// 	$insertfamilyIdData['address'] = $familyDetails['address'];
					// }
					// if (isset($familyDetails['village'])) {
					// 	$insertfamilyIdData['village'] = $familyDetails['village'];
					// }
					// if (isset($familyDetails['taluk'])) {
					// 	$insertfamilyIdData['taluk'] = $familyDetails['taluk'];
					// }
					// if (isset($familyDetails['district'])) {
					// 	$insertfamilyIdData['district_name'] = $familyDetails['district'];
					// }
					// if (isset($familyDetails['pincode'])) {
					// 	$insertfamilyIdData['pin_code'] = $familyDetails['pincode'];
					// }
					// if (isset($aadhaarId)) {
					// 	$insertfamilyIdData['aadhaar_number'] = $aadhaarId;
					// }
					if ($requestData) {
						//
						$insertData['head_name'] = $requestData['name'];
						// $insertData['address'] = $requestData['address'];
						// $insertData['village'] = $requestData['village'];
						// $insertData['taluk'] = $requestData['taluk'];
						// $insertData['district_name'] = $requestData['district'];
						// $insertData['pin_code'] = $requestData['pincode'];
						// $insertData['aadhaar_number'] = $requestData['aadhaarId'];
						//
						$familyId = $this->API_model->appInsert("tbl_family_details", $insertData);

						$insertData1['family_id'] = $familyId;
						$insertData1['member_name'] = $requestData['name'];
						$insertData1['member_relation'] = $requestData['taRelationToHead'];
						$insertData1['dob'] = $requestData['dob'];
						// $insertData['vulnerable'] = $requestData['isVulnerable'];
						$insertData1['age'] = $requestData['age'];
						$insertData1['gender'] = $requestData['gender'];
						$insertData1['education_details'] = $requestData['eduQualification'];
						$insertData1['school_type'] = $requestData['schoolType'];
						$insertData1['school_discontinued'] = $requestData['schoolDiscontinued'];
						$insertData1['school_drop_reason'] = $requestData['dropReason'];
						$insertData1['nalivutror'] = $requestData['malnutrition'];
						$insertData1['job_type'] = $requestData['jobType'];
						$insertData1['annual_income'] = $requestData['annualIncome'];
						$insertData1['incom_tax_pay'] = $requestData['incomTaxPay'];
						$insertData1['physically_challenged'] = $requestData['physicallyChallenged'];
						$insertData1['national_physically_challenged_no'] = $requestData['nationalChallengedId'];
						$insertData1['is_vulnerable'] = $requestData['isVulnerable'];
						$insertData1['vulnerable'] = $requestData['vulnerable'];
						$insertData1['shg_member'] = $requestData['shgMember'];
						$insertData1['is_shg'] = $requestData['shgActivity'];
						//	
						$base_dir = __DIR__;
						//
						if (isset($_FILES['image'])) {
							$filePath = $base_dir . "/../../../public/upload-files/familyDocuments/$familyId/";
							$uploadData = $this->uploadFiles($filePath, $_FILES['image']);
							$insertData1['image'] = $uploadData;
						}
						//

						$runQuery = $this->API_model->appInsert("tbl_family_member_details", $insertData1);

						//
						//
					}

					$requestData['familyId'] = $familyId;
					//
					if ($runQuery) {
						$final = array();
						$final['code'] = 'success';
						$final['message'] = 'Data insert successfully';
						$final['statusCode'] = 200;
						$final['success'] = true;
						$final['data'] = $requestData;
					} else {
						$final = array();
						$final['code'] = 'success';
						$final['message'] = 'Data insert failed!';
						$final['statusCode'] = 500;
						$final['success'] = false;
						$final['data'] = null;
					}
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
	public function updateFamilyMember_post()
	{
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization-Token'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization-Token']);
			if ($decodedToken['status']) {
				// $requestData = json_decode(file_get_contents('php://input'), true);
				$requestData = $this->input->post();
				if ($requestData) {
					$memberId = $requestData["memberId"];
					//
					$updateData['family_id'] = $requestData['familyId'];
					$updateData['member_name'] = $requestData['name'];
					$updateData['member_relation'] = $requestData['taRelationToHead'];
					$updateData['dob'] = $requestData['dob'];
					$updateData['age'] = $requestData['age'];
					$updateData['gender'] = $requestData['gender'];
					//
					$updateData['member_relation'] = $requestData['taRelationToHead'];
					$updateData['education_details'] = $requestData['eduQualification'];
					$updateData['school_type'] = $requestData['schoolType'];
					$updateData['school_discontinued'] = $requestData['schoolDiscontinued'];
					$updateData['school_drop_reason'] = $requestData['dropReason'];
					$updateData['nalivutror'] = $requestData['malnutrition'];
					$updateData['job_type'] = $requestData['jobType'];
					$updateData['annual_income'] = $requestData['annualIncome'];
					$updateData['incom_tax_pay'] = $requestData['incomTaxPay'];
					$updateData['physically_challenged'] = $requestData['physicallyChallenged'];
					$updateData['national_physically_challenged_no'] = $requestData['nationalChallengedId'];
					$updateData['is_vulnerable'] = $requestData['isVulnerable'];
					$updateData['vulnerable'] = $requestData['vulnerable'];
					$updateData['shg_member'] = $requestData['shgMember'];
					$base_dir = __DIR__;
					//
					if (isset($_FILES['image'])) {
						$filePath = $base_dir . "/../../../public/upload-files/familyDocuments/$familyId/";
						$uploadData = $this->uploadFiles($filePath, $_FILES['image']);
						$updateData['image'] = $uploadData;
					}

					//
					$con = array("id" => $memberId);
					$runQuery = $this->API_model->appUpdate("tbl_family_member_details", $updateData, $con);
					if ($runQuery) {
						$final = array();
						$final['code'] = 'success';
						$final['message'] = 'Data updated successfully';
						$final['statusCode'] = 200;
						$final['success'] = true;
						$final['data'] = $updateData;
					} else {
						$final = array();
						$final['code'] = 'success';
						$final['message'] = 'Data update failed!';
						$final['statusCode'] = 500;
						$final['success'] = false;
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
	public function addFamilyMember_post()
	{
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization-Token'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization-Token']);
			if ($decodedToken['status']) {
				// $requestData = json_decode(file_get_contents('php://input'), true);
				$requestData = $this->input->post();
				if ($requestData) {
					// $familyId = $requestData["familyId"];
					//
					$insertData['family_id'] = $requestData['familyId'];
					$insertData['member_name'] = $requestData['name'];
					$insertData['member_relation'] = $requestData['taRelationToHead'];
					$insertData['dob'] = $requestData['dob'];
					$insertData['age'] = $requestData['age'];
					$insertData['gender'] = $requestData['gender'];
					//
					$insertData['education_details'] = $requestData['eduQualification'];
					$insertData['school_type'] = $requestData['schoolType'];
					$insertData['school_discontinued'] = $requestData['schoolDiscontinued'];
					$insertData['school_drop_reason'] = $requestData['dropReason'];
					$insertData['nalivutror'] = $requestData['malnutrition'];
					$insertData['job_type'] = $requestData['jobType'];
					$insertData['annual_income'] = $requestData['annualIncome'];
					$insertData['incom_tax_pay'] = $requestData['incomTaxPay'];
					$insertData['physically_challenged'] = $requestData['physicallyChallenged'];
					$insertData['national_physically_challenged_no'] = $requestData['nationalChallengedId'];
					$insertData['is_vulnerable'] = $requestData['isVulnerable'];
					$insertData['vulnerable'] = $requestData['vulnerable'];
					$insertData['is_shg'] = $requestData['isShg'];
					$insertData['shg_member'] = $requestData['shgMember'];
					//
					$base_dir = __DIR__;
					//
					if (isset($_FILES['image'])) {
						$filePath = $base_dir . "/../../../public/upload-files/familyDocuments/$familyId/";
						$uploadData = $this->uploadFiles($filePath, $_FILES['image']);
						$insertData1['image'] = $uploadData;
					}
					$runQuery = $this->API_model->appInsert("tbl_family_member_details", $insertData);
					if ($runQuery) {
						$final = array();
						$final['code'] = 'success';
						$final['message'] = 'Data updated successfully';
						$final['statusCode'] = 200;
						$final['success'] = true;
						$final['data'] = $insertData;
					} else {
						$final = array();
						$final['code'] = 'success';
						$final['message'] = 'Data update failed!';
						$final['statusCode'] = 500;
						$final['success'] = false;
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
	public function addFamilyBasicDetails_post()
	{
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization-Token'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization-Token']);
			if ($decodedToken['status']) {
				$requestData = json_decode(file_get_contents('php://input'), true);
				if ($requestData) {
					// $familyId = $requestData["familyId"];
					//
					$insertData['family_id'] = $requestData['familyId'];
					$insertData['female_head'] = $requestData['femaleHead'];
					$insertData['toilet_available'] = $requestData['toiletAvailable'];
					$insertData['public_toilet'] = $requestData['publicToilet'];
					$insertData['water_facility'] = $requestData['waterFacility'];
					$insertData['public_water'] = $requestData['publicWater'];
					$insertData['gas_available'] = $requestData['gasAvailable'];
					$insertData['electricity_available'] = $requestData['electricityAvailable'];
					$insertData['eb_number'] = $requestData['ebNumber'];
					//
					$runQuery = $this->API_model->appInsert("tbl_family_basic_details", $insertData);
					if ($runQuery) {
						$final = array();
						$final['code'] = 'success';
						$final['message'] = 'Data Inserted successfully';
						$final['statusCode'] = 200;
						$final['success'] = true;
						$final['data'] = $insertData;
					} else {
						$final = array();
						$final['code'] = 'success';
						$final['message'] = 'Data update failed!';
						$final['statusCode'] = 500;
						$final['success'] = false;
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
	public function addPropertyDetails_post()
	{
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization-Token'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization-Token']);
			if ($decodedToken['status']) {
				$requestData = json_decode(file_get_contents('php://input'), true);
				if ($requestData) {
					// $familyId = $requestData["familyId"];
					//
					$insertData['family_id'] = $requestData['familyId'];
					$insertData['settlement_type'] = $requestData['settlementType'];
					$insertData['house_details'] = $requestData['houseDetails'];
					$insertData['house_type'] = $requestData['houseType'];
					$insertData['house_rent'] = $requestData['houseRent'];
					$insertData['house_sqft'] = $requestData['houseSqft'];
					$insertData['resident_type'] = $requestData['residentType'];
					$insertData['other_own_house'] = $requestData['otherOwnHouse'];
					$insertData['other_house_area'] = $requestData['otherHouseArea'];
					$insertData['other_house_plot'] = $requestData['otherHousePlot'];
					$insertData['annual_income'] = $requestData['annualIncome'];
					//
					$runQuery = $this->API_model->appInsert("tbl_property_details", $insertData);
					if ($runQuery) {
						$final = array();
						$final['code'] = 'success';
						$final['message'] = 'Data Inserted successfully';
						$final['statusCode'] = 200;
						$final['success'] = true;
						$final['data'] = $insertData;
					} else {
						$final = array();
						$final['code'] = 'success';
						$final['message'] = 'Data update failed!';
						$final['statusCode'] = 500;
						$final['success'] = false;
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
	public function addHouseholdThings_post()
	{
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization-Token'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization-Token']);
			if ($decodedToken['status']) {
				// $requestData = json_decode(file_get_contents('php://input'), true);
				$requestData = $this->input->post();
				if ($requestData) {
					$familyId = $requestData["familyId"];
					//
					$insertData['family_id'] = $requestData['familyId'];
					$insertData['own_vehicle'] = $requestData['ownVehicle'];
					$insertData['cycle'] = $requestData['cycle'];
					$insertData['two_wheeler'] = $requestData['twoWheeler'];
					$insertData['three_wheeler'] = $requestData['threeWheeler'];
					$insertData['four_wheeler'] = $requestData['fourWheeler'];
					$insertData['tractor'] = $requestData['tractor'];
					$insertData['fishery_boat'] = $requestData['fisheryBoat'];
					//
					$base_dir = __DIR__;
					//
					$updateData['comments_remarks'] = $requestData['commentRemarks'];
					if (isset($_FILES['voiceRec'])) {
						$filePath = $base_dir . "/../../../public/upload-files/familyDocuments/$familyId/audio/";
						$uploadData = $this->uploadFiles($filePath, $_FILES['voiceRec']);
						$updateData['voice_rec'] = $uploadData;
					}
					$con = array("id" => $familyId);
					$this->API_model->appUpdate("tbl_family_details", $updateData, $con);
					//
					$runQuery = $this->API_model->appInsert("tbl_household_things", $insertData);
					if ($runQuery) {
						$final = array();
						$final['code'] = 'success';
						$final['message'] = 'Data Inserted successfully';
						$final['statusCode'] = 200;
						$final['success'] = true;
						$final['data'] = $insertData;
					} else {
						$final = array();
						$final['code'] = 'success';
						$final['message'] = 'Data update failed!';
						$final['statusCode'] = 500;
						$final['success'] = false;
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
	public function addnotAvailablePerson_post()
	{
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization-Token'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization-Token']);
			if ($decodedToken['status']) {
				// $requestData = json_decode(file_get_contents('php://input'), true);
				$requestData = $this->input->post();
				if ($requestData) {
					//
					$insertData['ward_name'] = $requestData['wardName'];
					$insertData['address'] = $requestData['address'];
					$insertData['comments_remarks'] = $requestData['commentsRemarks'];
					$insertData['name'] = $requestData['name'];
					$insertData['mobile'] = $requestData['mobile']; //
					$base_dir = __DIR__;
					//
					if (isset($_FILES['image'])) {
						$filePath = $base_dir . "/../../../public/upload-files/notAvailable/1/image/";
						$uploadData = $this->uploadFiles($filePath, $_FILES['image']);
						$insertData['image'] = $uploadData;
					}
					if (isset($_FILES['voiceRec'])) {
						$filePath = $base_dir . "/../../../public/upload-files/notAvailable/1/audio/";
						$uploadData = $this->uploadFiles($filePath, $_FILES['voiceRec']);
						$insertData['voice_rec'] = $uploadData;
					}
					//
					$runQuery = $this->API_model->appInsert("tbl_person_not_available", $insertData);
					if ($runQuery) {
						$final = array();
						$final['code'] = 'success';
						$final['message'] = 'Data Inserted successfully';
						$final['statusCode'] = 200;
						$final['success'] = true;
						$final['data'] = $insertData;
					} else {
						$final = array();
						$final['code'] = 'success';
						$final['message'] = 'Data update failed!';
						$final['statusCode'] = 500;
						$final['success'] = false;
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
	public function updateFamilyDetails_post()
	{
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization-Token'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization-Token']);
			if ($decodedToken['status']) {
				$requestData = json_decode(file_get_contents('php://input'), true);
				if ($requestData) {
					$familyId = $requestData["familyId"];
					//
					// $updateData['family_id'] = $requestData['familyId'];
					$updateData['guardian_name'] = $requestData['guardianName'];
					$updateData['address'] = $requestData['address'];
					$updateData['district_name'] = $requestData['districtName'];
					$updateData['taluk'] = $requestData['taluk'];
					$updateData['pin_code'] = $requestData['pinCode'];
					$updateData['village'] = $requestData['village'];
					$updateData['is_same_address'] = $requestData['isSameAddress'];
					$updateData['current_address'] = $requestData['currentAddress'];
					$updateData['year_of_residence'] = $requestData['residenceYear'];
					$updateData['month_of_residence'] = $requestData['residenceMonth'];
					$updateData['aadhaar_is'] = $requestData['isAadhaar'];
					$updateData['aadhaar_number'] = $requestData['aadhaarNumber'];
					$updateData['aadhaar_regd_mobile'] = $requestData['aadhaarMobile'];
					$updateData['is_ration_card'] = $requestData['isRationCard'];
					$updateData['ration_card_no'] = $requestData['rationCardNo'];
					$updateData['card_type'] = $requestData['cardType'];
					$updateData['marrital_status'] = $requestData['marritalStatus'];
					$updateData['caste'] = $requestData['caste'];
					$updateData['religion'] = $requestData['religion'];
					$updateData['availed_ration'] = $requestData['availedRation'];
					$updateData['bread_winner'] = $requestData['breadWinner'];
					//
					$con = array("id" => $familyId);
					$runQuery = $this->API_model->appUpdate("tbl_family_details", $updateData, $con);
					//
					if ($runQuery) {
						$final = array();
						$final['code'] = 'success';
						$final['message'] = 'Data updated successfully';
						$final['statusCode'] = 200;
						$final['success'] = true;
						$final['data'] = $updateData;
					} else {
						$final = array();
						$final['code'] = 'success';
						$final['message'] = 'Data update failed!';
						$final['statusCode'] = 500;
						$final['success'] = false;
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
	// 	public function addAlrMedicalType_post()
	// {
	// 	$headers = $this->input->request_headers();
	// 	if (isset($headers['Authorization-Token'])) {
	// 		$decodedToken = $this->authorization_token->validateToken($headers['Authorization-Token']);
	// 		if ($decodedToken['status']) {
	// 			$requestData = json_decode(file_get_contents('php://input'), true);
	// 			if ($requestData) {
	// 				foreach ($requestData as $key => $value) {
	// 					$insertArr[$key] = $value;
	// 				}
	// 				// ------- Main Logic part -------
	// 				$runQuery = $this->API_model->appInsert("tbl_alr_medical_type", $insertArr);
	// 				if ($runQuery) {
	// 					$final = array();
	// 					$final['status'] = true;
	// 					$final['statusCode'] = 200;
	// 					$final['message'] = 'Data Inserted successfully';
	// 					$final['data'] = $insertArr;
	// 				} else {
	// 					$final = array();
	// 					$final['status'] = true;
	// 					$final['statusCode'] = 500;
	// 					$final['message'] = 'Data Insert failed!';
	// 					$final['data'] = null;
	// 				}
	// 				$this->response($final, REST_Controller::HTTP_OK);
	// 				// ------------- End -------------
	// 			}
	// 		} else {
	// 			$this->response($decodedToken);
	// 		}
	// 	} else {
	// 		$this->response(['Authentication failed'], REST_Controller::HTTP_OK);
	// 	}
	// }
	// public function addFamilyDetails_post()
	// {
	// 	$headers = $this->input->request_headers();
	// 	if (isset($headers['Authorization-Token'])) {
	// 		$decodedToken = $this->authorization_token->validateToken($headers['Authorization-Token']);
	// 		if ($decodedToken['status']) {
	// 			$requestData = json_decode(file_get_contents('php://input'), true);
	// 			if ($requestData) {
	// 				foreach ($requestData as $key => $value) {
	// 					$insertArr[$key] = $value;
	// 				}
	// 				// ------- Main Logic part -------
	// 				$runQuery = $this->API_model->appInsert("tbl_helpline", $insertArr);
	// 				if ($runQuery) {
	// 					$final = array();
	// 					$final['status'] = true;
	// 					$final['statusCode'] = 200;
	// 					$final['message'] = 'Data Inserted successfully';
	// 					$final['data'] = $insertArr;
	// 				} else {
	// 					$final = array();
	// 					$final['status'] = true;
	// 					$final['statusCode'] = 500;
	// 					$final['message'] = 'Data Insert failed!';
	// 					$final['data'] = null;
	// 				}
	// 				$this->response($final, REST_Controller::HTTP_OK);
	// 				// ------------- End -------------
	// 			}
	// 		} else {
	// 			$this->response($decodedToken);
	// 		}
	// 	} else {
	// 		$this->response(['Authentication failed'], REST_Controller::HTTP_OK);
	// 	}
	// }
	//
	public function getMemberDetails_get()
	{
		$headers = $this->input->request_headers();
		try {
			if (isset($headers['Authorization-Token'])) {
				$decodedToken = $this->authorization_token->validateToken($headers['Authorization-Token']);
				if ($decodedToken['status']) {
					$requestData = $this->input->get();
					$memberId = $requestData['memberId'];
					//
					$sessionData = $decodedToken["data"];
					$createdBy = $sessionData->uid;
					$con = array();
					$con['returnType'] = 'single';
					$con['conditions'] = array(
						'id' => $memberId
					);
					$outData = array();
					$familyMemberDetails = $this->API_model->getRows("tbl_family_member_details", $con);
					if ($familyMemberDetails) {
						// foreach ($listData as $row) {
						$familyMemberDetailsResult = (array) $familyMemberDetails;
						$outData['familyMemberDetails'] = array(
							"id" => $familyMemberDetailsResult['id'],
							"familyId" => $familyMemberDetailsResult['family_id'],
							"memberName" => $familyMemberDetailsResult['member_name'],
							"memberRelation" => $familyMemberDetailsResult['member_relation'],
							"dob" => $familyMemberDetailsResult['dob'],
							"age" => $familyMemberDetailsResult['age'],
							"gender" => $familyMemberDetailsResult['gender'],
							"educationDetails" => $familyMemberDetailsResult['education_details'],
							"vitaminDeficiency" => $familyMemberDetailsResult['vitamin_deficiency'],
							"schoolDiscontinued" => $familyMemberDetailsResult['school_discontinued'],
							"schoolDropReason" => $familyMemberDetailsResult['school_drop_reason'],
							"schoolType" => $familyMemberDetailsResult['school_type'],
							"annualIncome" => $familyMemberDetailsResult['annual_income'],
							"jobType" => $familyMemberDetailsResult['job_type'],
							"incomeTaxPay" => $familyMemberDetailsResult['incom_tax_pay'],
							"physicallyChallenged" => $familyMemberDetailsResult['physically_challenged'],
							"nationalPhysicallyChallengedNo" => $familyMemberDetailsResult['national_physically_challenged_no'],
							"nalivutror" => $familyMemberDetailsResult['nalivutror'],
							"vulnerable" => $familyMemberDetailsResult['vulnerable'],
							"isShg" => $familyMemberDetailsResult['is_shg'],
							"shgMember" => $familyMemberDetailsResult['shg_member'],
							"isAlive" => $familyMemberDetailsResult['is_alive'],
							"isActive" => $familyMemberDetailsResult['is_active'],
							"isDeleted" => $familyMemberDetailsResult['is_deleted'],
							"createdAt" => $familyMemberDetailsResult['created_at'],
							"updatedBy" => $familyMemberDetailsResult['updated_by']
						);
						$con1 = array();
						$con1['returnType'] = 'single';
						$con1['conditions'] = array(
							'id' => $familyMemberDetailsResult['family_id']
						);
						$familyDetails = $this->API_model->getRows("tbl_family_details", $con);
						if ($familyDetails) {
							// foreach ($listData as $row) {
							$familyDetailsResult = (array) $familyDetails;
							$outData['familyDetails'] = array(
								"id" => $familyDetailsResult['id'],
								"pdsNumber" => $familyDetailsResult['pds_number'],
								"headName" => $familyDetailsResult['head_name'],
								"guardianName" => $familyDetailsResult['guardian_name'],
								"address" => $familyDetailsResult['address'],
								"districtName" => $familyDetailsResult['district_name'],
								"taluk" => $familyDetailsResult['taluk'],
								"pinCode" => $familyDetailsResult['pin_code'],
								"village" => $familyDetailsResult['village'],
								"isSameAddress" => $familyDetailsResult['is_same_address'],
								"currentAddress" => $familyDetailsResult['current_address'],
								"yearOfResidence" => $familyDetailsResult['year_of_residence'],
								"monthOfResidence" => $familyDetailsResult['month_of_residence'],
								"aadhaarIs" => $familyDetailsResult['aadhaar_is'],
								"aadhaarNumber" => $familyDetailsResult['aadhaar_number'],
								"aadhaarRegdMobile" => $familyDetailsResult['aadhaar_regd_mobile'],
								"isRationCard" => $familyDetailsResult['is_ration_card'],
								"rationCardNo" => $familyDetailsResult['ration_card_no'],
								"cardType" => $familyDetailsResult['card_type'],
								"marritalStatus" => $familyDetailsResult['marrital_status'],
								"caste" => $familyDetailsResult['caste'],
								"religion" => $familyDetailsResult['religion'],
								"availedRation" => $familyDetailsResult['availed_ration'],
								"breadWinner" => $familyDetailsResult['bread_winner'],
								"commentsRemarks" => $familyDetailsResult['comments_remarks'],
								"voiceRec" => $familyDetailsResult['voice_rec'],
								"isActive" => $familyDetailsResult['is_active'],
								"isDeleted" => $familyDetailsResult['is_deleted'],
								"createdAt" => $familyDetailsResult['created_at'],
								"updatedBy" => $familyDetailsResult['updated_by']
							);
						}
						// }
					}
					$final = array();
					$final['code'] = 'success';
					$final['message'] = 'success!';
					$final['statusCode'] = 200;
					$final['success'] = true;
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
	public function getBoatType_get()
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
					$lang = $this->lang();
					//
					$listData = $this->API_model->getRows("tbl_boat_type", $con);
					if ($listData) {
						foreach ($listData as $row) {
							$listData1 = (array) $row;
							$outData[] = array("id" => $listData1['id'], "name" => $listData1[$lang.'name']);
						}
					}
					$final = array();
					$final['code'] = 'success';
					$final['message'] = 'success!';
					$final['statusCode'] = 200;
					$final['success'] = true;
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
	public function getGender_get()
	{
		$headers = $this->input->request_headers();
		try {
			if (isset($headers['Authorization-Token'])) {
				$decodedToken = $this->authorization_token->validateToken($headers['Authorization-Token']);
				if ($decodedToken['status']) {
					$sessionData = $decodedToken["data"];
					$createdBy = $sessionData->uid;
					$con = array();
					$outData = array(
						array("id" => '1', "name" => 'male'),
						array("id" => '2', "name" => 'female'),
						array("id" => '3', "name" => 'transgender'),
					);

					$final = array();
					$final['code'] = 'success';
					$final['message'] = 'success!';
					$final['statusCode'] = 200;
					$final['success'] = true;
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
	public function getCardType_get()
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
					$lang = $this->lang();
					$listData = $this->API_model->getRows("tbl_card_type", $con);
					if ($listData) {
						foreach ($listData as $row) {
							$listData1 = (array) $row;
							$outData[] = array("id" => $listData1['id'], "name" => $listData1[$lang.'name']);
						}
					}
					$final = array();
					$final['code'] = 'success';
					$final['message'] = 'success!';
					$final['statusCode'] = 200;
					$final['success'] = true;
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
	public function getHouseDetails_get()
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
					$lang = $this->lang();
					$listData = $this->API_model->getRows("tbl_house_details", $con);
					if ($listData) {
						foreach ($listData as $row) {
							$listData1 = (array) $row;
							$outData[] = array("id" => $listData1['id'], "name" => $listData1[$lang.'name']);
						}
					}
					$final = array();
					$final['code'] = 'success';
					$final['message'] = 'success!';
					$final['statusCode'] = 200;
					$final['success'] = true;
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
	public function getStayType_get()
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
					$lang = $this->lang();
					$listData = $this->API_model->getRows("tbl_stay_type", $con);
					if ($listData) {
						foreach ($listData as $row) {
							$listData1 = (array) $row;
							$outData[] = array("id" => $listData1['id'], "name" => $listData1[$lang.'name']);
						}
					}
					$final = array();
					$final['code'] = 'success';
					$final['message'] = 'success!';
					$final['statusCode'] = 200;
					$final['success'] = true;
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
	public function getHouseType_get()
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
					$lang = $this->lang();
					$listData = $this->API_model->getRows("tbl_house_type", $con);
					if ($listData) {
						foreach ($listData as $row) {
							$listData1 = (array) $row;
							$outData[] = array("id" => $listData1['id'], "name" => $listData1[$lang.'name']);
						}
					}
					$final = array();
					$final['code'] = 'success';
					$final['message'] = 'success!';
					$final['statusCode'] = 200;
					$final['success'] = true;
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
	public function getIncomeSource_get()
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
					$lang = $this->lang();
					$listData = $this->API_model->getRows("tbl_income_source", $con);
					if ($listData) {
						foreach ($listData as $row) {
							$listData1 = (array) $row;
							$outData[] = array("id" => $listData1['id'], "name" => $listData1[$lang.'name']);
						}
					}
					$final = array();
					$final['code'] = 'success';
					$final['message'] = 'success!';
					$final['statusCode'] = 200;
					$final['success'] = true;
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
	public function getFoodSource_get()
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
					$lang = $this->lang();
					$listData = $this->API_model->getRows("tbl_food_source", $con);
					if ($listData) {
						foreach ($listData as $row) {
							$listData1 = (array) $row;
							$outData[] = array("id" => $listData1['id'], "name" => $listData1[$lang.'name']);
						}
					}
					$final = array();
					$final['code'] = 'success';
					$final['message'] = 'success!';
					$final['statusCode'] = 200;
					$final['success'] = true;
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
	public function getBusiness_get()
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
					$lang = $this->lang();
					$listData = $this->API_model->getRows("tbl_business", $con);
					if ($listData) {
						foreach ($listData as $row) {
							$listData1 = (array) $row;
							$outData[] = array("id" => $listData1['id'], "name" => $listData1[$lang.'name']);
						}
					}
					$final = array();
					$final['code'] = 'success';
					$final['message'] = 'success!';
					$final['statusCode'] = 200;
					$final['success'] = true;
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
	public function getCommunity_get()
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
					$lang = $this->lang();
					$listData = $this->API_model->getRows("tbl_community", $con);
					if ($listData) {
						foreach ($listData as $row) {
							$listData1 = (array) $row;
							$outData[] = array("id" => $listData1['id'], "name" => $listData1[$lang.'name']);
						}
					}
					$final = array();
					$final['code'] = 'success';
					$final['message'] = 'success!';
					$final['statusCode'] = 200;
					$final['success'] = true;
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
	public function getEducation_get()
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
					$lang = $this->lang();
					$listData = $this->API_model->getRows("tbl_education", $con);
					if ($listData) {
						foreach ($listData as $row) {
							$listData1 = (array) $row;
							$outData[] = array("id" => $listData1['id'], "name" => $listData1[$lang.'name']);
						}
					}
					$final = array();
					$final['code'] = 'success';
					$final['message'] = 'success!';
					$final['statusCode'] = 200;
					$final['success'] = true;
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
	public function getMaritalStatus_get()
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
					$lang = $this->lang();
					$listData = $this->API_model->getRows("tbl_marital_status", $con);
					if ($listData) {
						foreach ($listData as $row) {
							$listData1 = (array) $row;
							$outData[] = array("id" => $listData1['id'], "name" => $listData1[$lang.'name']);
						}
					}
					$final = array();
					$final['code'] = 'success';
					$final['message'] = 'success!';
					$final['statusCode'] = 200;
					$final['success'] = true;
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
	public function getReligion_get()
	{
		$headers = $this->input->request_headers();
		// print_r($headers['Authorization-Token']);
		// exit;
		try {
			if (isset($headers['Authorization-Token'])) {
				$decodedToken = $this->authorization_token->validateToken($headers['Authorization-Token']);
				if ($decodedToken['status']) {
					$sessionData = $decodedToken["data"];
					$createdBy = $sessionData->uid;
					$con = array();
					$outData = array();
					$lang = $this->lang();
					$listData = $this->API_model->getRows("tbl_religion", $con);
					if ($listData) {
						foreach ($listData as $row) {
							$listData1 = (array) $row;
							$outData[] = array("id" => $listData1['id'], "name" => $listData1[$lang.'name']);
						}
					}
					$final = array();
					$final['code'] = 'success';
					$final['message'] = 'success!';
					$final['statusCode'] = 200;
					$final['success'] = true;
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
	public function getSchoolType_get()
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
					$lang = $this->lang();
					$listData = $this->API_model->getRows("tbl_school_type", $con);
					if ($listData) {
						foreach ($listData as $row) {
							$listData1 = (array) $row;
							$outData[] = array("id" => $listData1['id'], "name" => $listData1[$lang.'name']);
						}
					}
					$final = array();
					$final['code'] = 'success';
					$final['message'] = 'success!';
					$final['statusCode'] = 200;
					$final['success'] = true;
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
	public function getVulnerableCategory_get()
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
					$lang = $this->lang();
					$listData = $this->API_model->getRows("tbl_vulnerable_category", $con);
					if ($listData) {
						foreach ($listData as $row) {
							$listData1 = (array) $row;
							$outData[] = array("id" => $listData1['id'], "name" => $listData1[$lang.'name']);
						}
					}
					$final = array();
					$final['code'] = 'success';
					$final['message'] = 'success!';
					$final['statusCode'] = 200;
					$final['success'] = true;
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
	public function getSettlementType_get()
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
					$lang = $this->lang();
					$listData = $this->API_model->getRows("tbl_settlement_type", $con);
					if ($listData) {
						foreach ($listData as $row) {
							$listData1 = (array) $row;
							$outData[] = array("id" => $listData1['id'], "name" => $listData1[$lang.'name']);
						}
					}
					$final = array();
					$final['code'] = 'success';
					$final['message'] = 'success!';
					$final['statusCode'] = 200;
					$final['success'] = true;
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
	public function changePassword_post()
	{
		$headers = $this->input->request_headers();
		try {
			if (isset($headers['Authorization-Token'])) {
				$decodedToken = $this->authorization_token->validateToken($headers['Authorization-Token']);
				if ($decodedToken['status']) {
					$sessionData = $decodedToken["data"];
					$createdBy = $sessionData->uid;
					$requestData = json_decode(file_get_contents('php://input'), true);
					$userId = $requestData["userId"];
					//
					$currentPassword = trim($requestData["currentPassword"]);
					$newPassword = trim($requestData["newPassword"]);
					$confirmPassword = trim($requestData["confirmPassword"]);
					if ($userId != "" && $currentPassword != "" && $newPassword != "" && $confirmPassword != "") {
						$con = array();
						$con['returnType'] = 'single';
						$con['conditions'] = array(
							'id' => $userId
						);
						$dbname = "users";
						$selectedData = $this->API_model->getRows($dbname, $con);
						if ($selectedData != '') {
							$run = true;
							$username = $selectedData->username;
						} else {
							$run = false;
						}
						if ($this->user_model->resolve_user_login($username, $currentPassword) && $run) {
							if ($newPassword == $confirmPassword) {
								$where = array();
								$where["id"] = $userId;
								$updateData = array();
								$updateData["password"] = $this->hash_password($newPassword);
								$dbname = "users";
								$appUpdateData = $this->API_model->appUpdate($dbname, $updateData, $where);
								if ($appUpdateData) {
									$outData = array();
									$outData["userId"] = $selectedData->id;
									$outData["phone"] = $selectedData->phone;
									$outData["email"] = $selectedData->email;
									//
									$final = array();
									$final['status'] = true;
									$final['statusCode'] = 200;
									$final['message'] = 'Change password successfully';
									$final['data'] = $outData;
									$this->response($final, REST_Controller::HTTP_OK);
								} else {
									$final = array();
									$final['status'] = false;
									$final['statusCode'] = 500;
									$final['message'] = 'Change password failed';
									$final['data'] = null;
									$this->response($final, REST_Controller::HTTP_OK);
								}
							} else {
								$final = array();
								$final['status'] = false;
								$final['statusCode'] = 500;
								$final['message'] = 'Your confirmation password does not match the new password.';
								$final['data'] = null;
								$this->response($final, REST_Controller::HTTP_OK);
							}
						} else {
							$final = array();
							$final['status'] = false;
							$final['statusCode'] = 500;
							$final['message'] = 'Invalid old Password!';
							$final['data'] = null;
							$this->response($final, REST_Controller::HTTP_OK);
						}
					} else {
						$final = array();
						$final['status'] = false;
						$final['statusCode'] = 500;
						$final['message'] = 'Please enter required fields.';
						$final['data'] = null;
						$this->response($final, REST_Controller::HTTP_OK);
						//
					}
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
	private function hash_password($password)
	{
		return password_hash($password, PASSWORD_BCRYPT);
	}
	//
	public function addFeedback_post()
	{
		// $headers = $this->input->request_headers();
		// if (isset($headers['Authorization-Token'])) {
		//     $decodedToken = $this->authorization_token->validateToken($headers['Authorization-Token']);
		//     if ($decodedToken['status']) {
		$requestData = json_decode(file_get_contents('php://input'), true);
		if ($requestData) {
			// // // //
			//tbl_family_details form 1
			// // // //
			$insertArr['head_name'] = $requestData['headName'];
			$insertArr['guardian_name'] = $requestData['guardianName'];
			$insertArr['address'] = $requestData['address'];
			$insertArr['road_residence'] = $requestData['roadResidence']; //int 0,1
			$insertArr['temp_address'] = $requestData['tempAddress'];
			$insertArr['perm_address'] = $requestData['permAddress'];
			$insertArr['year_of_residence'] = $requestData['yearOfResidence'];
			$insertArr['immigrated'] = $requestData['immigrated']; //int 0,1
			$insertArr['state_name'] = $requestData['stateName'];
			$insertArr['district_name'] = $requestData['districtName'];
			$insertArr['city_name'] = $requestData['cityName'];
			$insertArr['municipal_area_name'] = $requestData['municipalAreaName'];
			$insertArr['municipal_city_name'] = $requestData['municipalCityName'];
			$insertArr['muncipality_name'] = $requestData['muncipalityName'];
			$insertArr['aadhaar_is'] = $requestData['isAadhaar']; //int 0,1
			$insertArr['aadhaar_number'] = $requestData['aadhaarNumber'];
			$insertArr['aadhaar_regd_mobile'] = $requestData['aadhaarRegMobile'];
			$insertArr['family_card_type'] = $requestData['familyCardType'];
			$insertArr['family_card_no'] = $requestData['familyCardNo'];
			$insertArr['marrital_status'] = $requestData['marritalStatus'];
			$insertArr['religion'] = $requestData['religion'];
			$insertArr['caste'] = $requestData['caste'];
			// // // //
			//tbl_personal_details form 2
			// // // //
			$insertArr['permanent_mobile_no'] = $requestData['permanentMobile'];
			$insertArr['municipal_emp_card_no'] = $requestData['municipalEmpCard']; //int 0,1
			$insertArr['insurance_plan'] = $requestData['insurancePlan']; //int 0,1
			$insertArr['insurance_plan_detail'] = $requestData['insurancePlanDetail'];
			$insertArr['insurance_plan_others'] = $requestData['insurancPlanOthers'];
			$insertArr['bank_name'] = $requestData['bankName'];
			$insertArr['branch_name'] = $requestData['branchName'];
			$insertArr['account_number'] = $requestData['accountNumber'];
			$insertArr['ifsc'] = $requestData['ifsc'];
			// // // //
			//tbl_family_basic_details form 3
			// // // //
			$insertArr['female_head'] = $requestData['femaleHead']; //int 0,1
			$insertArr['toilet_available'] = $requestData['toiletAvailable']; //int 0,1
			$insertArr['public_toilet'] = $requestData['publicToilet']; //int 0,1
			$insertArr['water_facility'] = $requestData['waterFacility']; //int 0,1
			$insertArr['public_water'] = $requestData['publicWater']; //int 0,1
			$insertArr['cylinder_available'] = $requestData['cylinderAvailable']; //int 0,1
			$insertArr['cylinder_count'] = $requestData['cylinderCount'];
			$insertArr['electricity_available'] = $requestData['electricityAvailable']; //int 0,1
			$insertArr['eb_number'] = $requestData['ebNumber'];
			$insertArr['male'] = $requestData['male'];
			$insertArr['female'] = $requestData['female'];
			$insertArr['transgender'] = $requestData['transgender'];
			$insertArr['children'] = $requestData['children'];
			$insertArr['senior_citizen'] = $requestData['seniorCitizen'];
			// // // //
			//tbl_government_scheme form 4
			// // // //
			$insertArr['female_scheme_member'] = $requestData['femaleSchemeMember']; //int 0,1
			$insertArr['female_scheme'] = $requestData['femaleScheme']; //int 0,1
			$insertArr['female_scheme_name'] = $requestData['femaleSchemeName']; //int 0,1
			$insertArr['scheme_type'] = $requestData['schemeType']; //int 0,1
			$insertArr['non_mathi_name'] = $requestData['nonMathiName']; //int 0,1
			$insertArr['nulm_code'] = $requestData['nulmCode']; //int 0,1
			$insertArr['kalainagar_female_scheme'] = $requestData['kalainagarFemale_scheme'];
			$insertArr['puthumaipenn_scheme'] = $requestData['puthumaipennScheme']; //int 0,1
			$insertArr['puthumaipenn_details'] = $requestData['puthumaipennDetails'];
			$insertArr['tamil_puthalvan_scheme'] = $requestData['tamilPuthalvanScheme'];
			$insertArr['tamil_puthalvan_scheme_number'] = $requestData['PuthalvanSchemeNo'];
			// // // //
			//tbl_property_details form 5
			// // // //
			$insertArr['resident_type'] = $requestData['residentType'];
			$insertArr['house_details'] = $requestData['houseDetails'];
			$insertArr['house_rent'] = $requestData['houseRent'];
			$insertArr['house_sqft'] = $requestData['houseSqft'];
			$insertArr['house_type'] = $requestData['houseType'];
			$insertArr['location_type'] = $requestData['locationType'];
			$insertArr['other_own_house'] = $requestData['otherOwnHouse']; //int 0,1
			$insertArr['other_house_sqft'] = $requestData['otherHouseSqft'];
			$insertArr['own_empty_land'] = $requestData['ownEmptyLand'];
			$insertArr['own_nansei'] = $requestData['ownNansei'];
			$insertArr['own_punsei'] = $requestData['ownPunsei'];
			$insertArr['lease_nansei'] = $requestData['leaseNansei'];
			$insertArr['lease_punsei'] = $requestData['leasePunsei'];
			$insertArr['cattle_goat'] = $requestData['cattleGoat'];
			$insertArr['cattle_cow'] = $requestData['cattleCow'];
			$insertArr['cattle_buffalow'] = $requestData['cattleBuffalow'];
			$insertArr['cattle_hen'] = $requestData['cattleHen'];
			// // // //
			//tbl_household_things form 6
			// // // //
			$insertArr['tv'] = $requestData['tv']; //int 0,1
			$insertArr['fridge'] = $requestData['fridge']; //int 0,1
			$insertArr['washing_machine'] = $requestData['washingMachine']; //int 0,1
			$insertArr['ac'] = $requestData['ac']; //int 0,1
			$insertArr['laptop_pc'] = $requestData['laptopPc']; //int 0,1
			$insertArr['phone'] = $requestData['phone']; //int 0,1
			$insertArr['cycle'] = $requestData['cycle']; //int 0,1
			$insertArr['two_wheeler'] = $requestData['twoWheeler']; //int 0,1
			$insertArr['three_wheeler'] = $requestData['threeWheeler']; //int 0,1
			$insertArr['four_wheeler'] = $requestData['fourWheeler']; //int 0,1
			$insertArr['tractor'] = $requestData['tractor']; //int 0,1
			$insertArr['heavy_vehicle'] = $requestData['heavyVehicle']; //int 0,1
			$insertArr['heavy_vehicle_type'] = $requestData['heavyVehicleType'];
			$insertArr['agri_equipements'] = $requestData['agriEquipements']; //int 0,1
			$insertArr['agri_equipements_details'] = $requestData['agriEquipementsDetails'];
			$insertArr['fishery_boat'] = $requestData['fisheryBoat']; //int 0,1
			$insertArr['fishery_boat_details'] = $requestData['fisheryBoatDetails'];
			// // // //
			//tbl_household_things form 6
			// // // //
			$insertArr['tv'] = $requestData['tv']; //int 0,1
			$insertArr['fridge'] = $requestData['fridge']; //int 0,1
			$insertArr['washing_machine'] = $requestData['washingMachine']; //int 0,1
			$insertArr['ac'] = $requestData['ac']; //int 0,1
			$insertArr['laptop_pc'] = $requestData['laptopPc']; //int 0,1
			$insertArr['phone'] = $requestData['phone']; //int 0,1
			$insertArr['cycle'] = $requestData['cycle']; //int 0,1
			$insertArr['two_wheeler'] = $requestData['twoWheeler']; //int 0,1
			$insertArr['three_wheeler'] = $requestData['threeWheeler']; //int 0,1
			$insertArr['four_wheeler'] = $requestData['fourWheeler']; //int 0,1
			$insertArr['tractor'] = $requestData['tractor']; //int 0,1
			$insertArr['heavy_vehicle'] = $requestData['heavyVehicle']; //int 0,1
			$insertArr['heavy_vehicle_type'] = $requestData['heavyVehicleType'];
			$insertArr['agri_equipements'] = $requestData['agriEquipements']; //int 0,1
			$insertArr['agri_equipements_details'] = $requestData['agriEquipementsDetails'];
			$insertArr['fishery_boat'] = $requestData['fisheryBoat']; //int 0,1
			$insertArr['fishery_boat_details'] = $requestData['fisheryBoatDetails'];
			// // // //
			//tbl_family_member_details form 7 array();
			// // // //
			$insertArr['member_name'] = $requestData['memberName'];
			$insertArr['member_relation'] = $requestData['memberRelation'];
			$insertArr['dob'] = $requestData['dob'];
			$insertArr['gender'] = $requestData['gender'];
			$insertArr['education_details'] = $requestData['educationDetails'];
			$insertArr['educated'] = $requestData['educated']; //int 0,1
			$insertArr['vitamin_deficiency'] = $requestData['vitaminDeficiency']; //int 0,1
			$insertArr['school_discontinued'] = $requestData['schoolDiscontinued'];
			$insertArr['school_again'] = $requestData['schoolAgain']; //int 0,1
			$insertArr['school_type'] = $requestData['schoolType'];
			$insertArr['young_tech_practice'] = $requestData['youngTechPractice'];
			$insertArr['young_tech_practice_name'] = $requestData['youngTechPracticeName'];
			$insertArr['young_tech_practice_need'] = $requestData['youngTechPracticeNeed'];
			$insertArr['first_graduate'] = $requestData['firstGraduate'];
			$insertArr['current_job'] = $requestData['currentJob'];
			$insertArr['job_type'] = $requestData['jobType'];
			$insertArr['monthly_income'] = $requestData['monthlyIncome'];
			$insertArr['incom_tax_pay'] = $requestData['incomTaxPay'];
			$insertArr['physically_challenged'] = $requestData['physicallyChallenged'];
			$insertArr['national_physically_challenged'] = $requestData['nationalPhyChallenged'];
			$insertArr['national_physically_challenged_no'] = $requestData['nationalPhyChallengedNo'];
			$insertArr['nalivutror'] = $requestData['nalivutror'];
			$insertArr['nalivutror_type'] = $requestData['nalivutrorType'];
			$insertArr['affected_disease'] = $requestData['affectedDisease'];
			$insertArr['disease_medicine'] = $requestData['diseaseMedicine'];
			$insertArr['govt_help'] = $requestData['govtHelp'];
			$insertArr['self_help_scheme'] = $requestData['selfHelpScheme'];
			// ------- Main Logic part -------
			$runQuery = $this->API_model->appInsert("tbl_family_details", $insertArr);
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
		// } else {
		// $this->response($decodedToken);
		// }
		// } else {
		//     $this->response(['Authentication failed'], REST_Controller::HTTP_OK);
		// }
	}
	//
	public function addOwnHouse_post()
	{
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization-Token'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization-Token']);
			if ($decodedToken['status']) {
				// $requestData = json_decode(file_get_contents('php://input'), true);
				$requestData = $this->input->post();
				if ($requestData) {
					$requiredKeys = [
						'personType', 'name', 'gender', 'age', 'ward', 'street',
						'dob', 'currentAddress', 'idType', 'idNumber', 'houseMembers',
						'totalChildren', 'contactNumber', 'isGas', 'gasNo', 'isRationCard',
						'commentsRemarks'
					];
					$requestDataset = $this->missingKeys($requiredKeys);
					// // // //
					//tbl_own_house_details
					// // // //
					$insertArr['type'] = 1;
					$insertArr['person_type'] = $requestData['personType'];
					$insertArr['name'] = $requestData['name'];
					$insertArr['gender'] = $requestData['gender'];
					$insertArr['age'] = $requestData['age']; //int 0,1
					$insertArr['ward'] = $requestData['ward'];
					$insertArr['street'] = $requestData['street'];
					$insertArr['dob'] = $requestData['dob'];
					$insertArr['current_address'] = $requestData['currentAddress']; //int 0,1
					$insertArr['id_type'] = $requestData['idType'];
					$insertArr['id_number'] = $requestData['idNumber'];
					$insertArr['house_members'] = $requestData['houseMembers'];
					$insertArr['total_children'] = $requestData['totalChildren'];
					$insertArr['contact_number'] = $requestData['contactNumber'];
					$insertArr['is_gas'] = $requestData['isGas']; //int 0,1
					$insertArr['gas_no'] = $requestData['gasNo'];
					$insertArr['is_ration_card'] = $requestData['isRationCard'];
					// $insertArr['image'] = $_FILES['image'];
					$insertArr['comments_remarks'] = $requestData['commentsRemarks'];
					// $insertArr['voice_rec'] = $_FILES['voiceRec'];
					$base_dir = __DIR__;
					if (isset($_FILES['image'])) {
						$filePath = $base_dir . "/../../../public/upload-files/ownHouse/1/ward_1/image/";
						$uploadData = $this->uploadFiles($filePath, $_FILES['image']);
						$insertArr['image'] = $uploadData;
					}
					if (isset($_FILES['voiceRec'])) {
						$filePath = $base_dir . "/../../../public/upload-files/ownHouse/1/ward_1/audio/";
						$uploadData = $this->uploadFiles($filePath, $_FILES['voiceRec']);
						$insertArr['voice_rec'] = $uploadData;
					}
					// ------- Main Logic part -------
					$runQuery = $this->API_model->appInsert("tbl_own_house_details", $insertArr);
					if ($runQuery) {
						$final = array();
						$final['code'] = 'success';
						$final['message'] = 'Data Inserted successfully';
						$final['statusCode'] = 200;
						$final['success'] = true;
						$final['data'] = $requestData;
					} else {
						$final = array();
						$final['code'] = 'failed';
						$final['message'] = 'Data Insert failed!';
						$final['statusCode'] = 500;
						$final['success'] = false;
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
	public function addHouseLess_post()
	{
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization-Token'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization-Token']);
			if ($decodedToken['status']) {
				// $requestData = json_decode(file_get_contents('php://input'), true);
				$requestData = $this->input->post();
				if ($requestData) {
					$requiredKeys = [
						'personType', 'name', 'gender', 'age', 'dob', 'currentAddress', 'residenceYear', 'stayType', 'incomeSource', 'foodSource', 'commentsRemarks'
					];
					$requestDataset = $this->missingKeys($requiredKeys);
					// // // //
					//tbl_own_house_details
					// // // //
					$insertArr['type'] = 0;
					$insertArr['person_type'] = $requestData['personType'];
					$insertArr['name'] = $requestData['name'];
					$insertArr['gender'] = $requestData['gender'];
					$insertArr['age'] = $requestData['age'];
					$insertArr['dob'] = $requestData['dob'];
					$insertArr['current_address'] = $requestData['currentAddress'];
					$insertArr['residence_year'] = $requestData['residenceYear'];
					$insertArr['stay_type'] = $requestData['stayType'];
					$insertArr['income_source'] = $requestData['incomeSource'];
					$insertArr['food_source'] = $requestData['foodSource'];
					$insertArr['comments_remarks'] = $requestData['commentsRemarks'];
					$base_dir = __DIR__;
					//
					if (isset($_FILES['image'])) {
						$filePath = $base_dir . "/../../../public/upload-files/HouseLess/1/image/";
						$uploadData = $this->uploadFiles($filePath, $_FILES['image']);
						$insertArr['image'] = $uploadData;
					}
					if (isset($_FILES['voiceRec'])) {
						$filePath = $base_dir . "/../../../public/upload-files/HouseLess/1/audio/";
						$uploadData = $this->uploadFiles($filePath, $_FILES['voiceRec']);
						$insertArr['voice_rec'] = $uploadData;
					}
					// ------- Main Logic part -------
					$runQuery = $this->API_model->appInsert("tbl_home_less_details", $insertArr);
					if ($runQuery) {
						$final = array();
						$final['code'] = 'success';
						$final['message'] = 'Data Inserted successfully';
						$final['statusCode'] = 200;
						$final['success'] = true;
						$final['data'] = $requestData;
					} else {
						$final = array();
						$final['code'] = 'failed';
						$final['message'] = 'Data Insert failed!';
						$final['statusCode'] = 500;
						$final['success'] = true;
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
	public function missingKeys($requiredKeys)
	{
		$errors = [];
		if (isset($requiredKeys)) {
			foreach ($requiredKeys as $key) {
				if (!isset($_POST[$key])) {
					$errors[] = "Error: Missing required key '$key'.";
				}
			}
		}
		if (!empty($errors)) {
			echo json_encode(['status' => 'error', 'messages' => $errors]);
			exit;
		}
	}
	//
	public function uploadFiles($filePath, $fileName)
	{
		$fileInputData = $fileName;
		// print_r($fileInputData);
		// exit;
		// foreach($fileInputCheck as $fKey => $_FILES['name']){
		//
		if (!file_exists($filePath) && !is_dir($filePath)) {
			mkdir($filePath, 0777, true);
		}
		$fileNameRequest = $fileName;
		if (isset($fileNameRequest['name'])) {
			$fileName = $fileNameRequest['name'];
			// $fileSize=$fileInputData[$fileNameRequest]['size'];
			$fileNameCmps = explode(".", $fileNameRequest['name']);
			$fileExtension = strtolower(end($fileNameCmps));
			$fileNameGen = strtotime($this->now) . "" . rand(1111, 9999) . "." . $fileExtension;
			$inputFileArray = array();
			$inputFileArray["fileName"] = $fileName;
			// $inputFileArray["fileExt"] = $fileExtension;
			// $inputFileArray["filePathName"] = $fileNameGen;
			// $inputFileArray["fileSize"] = $fileSize;
			if (move_uploaded_file($fileNameRequest["tmp_name"], $filePath . $fileNameGen)) {
				return $fileNameGen;
				// $insertArr['image'] = $fileNameGen;
				//
			}
		}
	}
	//
	public function getusersList_get()
	{
		//  $headers = $this->input->request_headers(); 
		try {
			// if (isset($headers['Authorization-Token'])) {
			// $decodedToken = $this->authorization_token->validateToken($headers['Authorization-Token']);
			// if ($decodedToken['status'])
			// {
			// $sessionData = $decodedToken["data"];
			// $createdBy = $sessionData->uid;
			$con = array();
			$outData = array();
			$listData = $this->API_model->getRows("tbl_users_log", $con);
			if ($listData) {
				foreach ($listData as $row) {
					$listData1 = (array) $row;
					$outData[] = array("id" => $listData1['id'], "name" => $listData1['name'], "age" => $listData1['age'], "gender" => $listData1['gender']);
				}
			}
			$final = array();
			$final['code'] = 'success';
			$final['message'] = 'success!';
			$final['statusCode'] = 200;
			$final['success'] = true;
			$final['data'] = $outData;
			$this->response($final, REST_Controller::HTTP_OK);
			// ------------- End -------------
			// } 
			// else {
			//     $this->response($decodedToken);
			// }
			// } else {
			// $this->response(['Authentication failed'], REST_Controller::HTTP_OK);
			// }
		} catch (Exception $e) {
			$this->set_error($e->getMessage());
			$this->response($e->getMessage(), REST_Controller::HTTP_OK);
			//return false;
		}
	}
	//
	public function addUserLog_post()
	{
		// $headers = $this->input->request_headers();
		// if (isset($headers['Authorization-Token'])) {
		//     $decodedToken = $this->authorization_token->validateToken($headers['Authorization-Token']);
		//     if ($decodedToken['status']) {
		$requestData = json_decode(file_get_contents('php://input'), true);
		if ($requestData) {
			foreach ($requestData as $key => $value) {
				$insertArr[$key] = $value;
			}
			// ------- Main Logic part -------
			$runQuery = $this->API_model->appInsert("tbl_users_log", $insertArr);
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
		// } else {
		// $this->response($decodedToken);
		// }
		// } else {
		//     $this->response(['Authentication failed'], REST_Controller::HTTP_OK);
		// }
	}
	//
	public function getSchemeDetail_post()
	{
		try {
			$requestData = json_decode(file_get_contents('php://input'), true);
			$SchemeId = $requestData['SchemeId'];
			// 			$SchemeListId = $requestData['SchemeListId'];
			if ($requestData['SchemeId']) {
				$resData = array();
				$con = array();
				$con['conditions'] = array('is_active' => 1, 'id' => $SchemeId);
				//
				$outData = array();
				$listData = $this->API_model->getRows('tbl_scheme_dtl', $con);
				// $base_dir = __DIR__;
				// $fileUrl = base_url();
				//
				if ($listData) {
					foreach ($listData as $row) {
						$listData1 = (array) $row;
						$retArr = $listData1['name'];
						// 		$outData = (array) json_decode($retArr);
						//print_r($outData);
						if (!empty($listData1['document'])) {
							// 		if (is_array($outData)) {
							//echo "Array";
							$fileUrl = base_url() . "public/upload-files/scheme/" . $listData1['document'];
							$outData["title"] = $listData1['name'];
							$outData["ObjectiveScheme"] = $listData1['objective_scheme'];
							$outData["EligiblePersons"] = $listData1['eligible_persons'];
							$outData["document"] = $fileUrl;
						} else {
							//echo "Not Array";
							$outData["title"] = $listData1['name'];
							$outData["ObjectiveScheme"] = $listData1['objective_scheme'];
							$outData["EligiblePersons"] = $listData1['eligible_persons'];
						}
						//	$outData = array('document'=>$fileUrl);	
					}
				}
				// $resData[] = $outData;
				//
				$final = array();
				$final['status'] = true;
				$final['statusCode'] = 200;
				$final['message'] = 'success!';
				$final['data'] = $outData;
				$this->response($final, REST_Controller::HTTP_OK);
				// ------------- End -------------//
			}
		} catch (Exception $e) {
			$this->set_error($e->getMessage());
			$this->response($e->getMessage(), REST_Controller::HTTP_OK);
			//return false;
		}
	}
	//
	public function getUserDashboard_get()
	{
		// 		$headers = $this->input->request_headers();
		// 		if (isset($headers['Authorization-Token'])) {
		// 			$decodedToken = $this->authorization_token->validateToken($headers['Authorization-Token']);
		// 			if ($decodedToken['status']) {
		$sqlAccessCondi = " AND `is_active`=1";
		$sql = "SELECT COUNT(`id`) as `total` FROM `tbl_hopital_dtl` WHERE 1 ";
		$sql .= $sqlAccessCondi;
		$hopitalData = $this->API_model->getCustomRows($sql, "single");
		$hopitalCount = $hopitalData->total;
		//
		$sqlAccessCondi = " AND `is_active`=1";
		$sql = "SELECT COUNT(`id`) as `total` FROM `tbl_scheme_dtl` WHERE 1 ";
		$sql .= $sqlAccessCondi;
		$schemeData = $this->API_model->getCustomRows($sql, "single");
		$schemeCount = $schemeData->total;
		//
		$sql = "SELECT COUNT(`id`) as `total` FROM `tbl_oah_dtl` WHERE 1 ";
		$sql .= $sqlAccessCondi;
		$oahData = $this->API_model->getCustomRows($sql, "single");
		$oahCount = $oahData->total;
		//
		$sql = "SELECT COUNT(CASE WHEN UPPER(gender) = 'MALE' THEN 1 END) Male, COUNT(CASE WHEN UPPER(gender) = 'FEMALE' THEN 1 END) Female, COUNT(CASE WHEN gender IS NULL THEN 1 END) 'Not Assigned',COUNT(id) AS 'usersCount' FROM tbl_users WHERE 1 ";
		$sql .= $sqlAccessCondi;
		$mfData = $this->API_model->getCustomRows($sql);
		if ($mfData) {
			foreach ($mfData as $listData) {
				$listData = (array) $listData;
			}
		}
		$seniorCitizenarray = array('usersCount' => $listData['usersCount'], "Male" => $listData['Male'], "Female" => $listData['Female']);
		$datasets = array(
			"hopitalCount" => $hopitalCount,
			"schemeCount" => $schemeCount,
			"oahCount" => $oahCount,
			"seniorCitizen" => $seniorCitizenarray,
			"mobappCount" => 12,
			"schemeDownload" => 34,
			"helplineCount" => 40
		);
		// }
		$final = array();
		$final['status'] = true;
		$final['statusCode'] = 200;
		$final['message'] = 'Data updated successfully';
		$final['data'] = $datasets;
		$this->response($final, REST_Controller::HTTP_OK);
		// ------------- End -------------
		// 			} else {
		// 				$this->response($decodedToken);
		// 			}
		// 		} else {
		// 			$this->response(['Authentication failed'], REST_Controller::HTTP_OK);
		// 		}
	}
	//
	//
	public function getTermPrivacy_get()
	{
		//  $headers = $this->input->request_headers(); 
		try {
			// if (isset($headers['Authorization-Token'])) {
			// $decodedToken = $this->authorization_token->validateToken($headers['Authorization-Token']);
			// if ($decodedToken['status'])
			// {
			// $sessionData = $decodedToken["data"];
			// $createdBy = $sessionData->uid;
			$con = array();
			$outData = array();
			$listData = $this->API_model->getRows("tbl_terms", $con);
			if ($listData) {
				foreach ($listData as $row) {
					$listData1 = (array) $row;
					$outData[] = array("id" => $listData1['id'], "terms_name" => $listData1['terms_name'], "privacy_name" => $listData1['privacy_name'], "session" => $_SESSION);
				}
			}
			$final = array();
			$final['code'] = 'success';
			$final['message'] = 'success!';
			$final['statusCode'] = 200;
			$final['success'] = true;
			$final['data'] = $outData;
			$this->response($final, REST_Controller::HTTP_OK);
			// ------------- End -------------
			// } 
			// else {
			//     $this->response($decodedToken);
			// }
			// } else {
			// $this->response(['Authentication failed'], REST_Controller::HTTP_OK);
			// }
		} catch (Exception $e) {
			$this->set_error($e->getMessage());
			$this->response($e->getMessage(), REST_Controller::HTTP_OK);
			//return false;
		}
	}
	public function ChangeLanguage_post()
	{
		$requestData = json_decode(file_get_contents('php://input'), true);
		// 	$id=session_id();
		$defaultLang = 'en';
		//Checking, if the $_GET["language"] has any value
		//if the $_GET["language"] is not empty
		if (!empty($requestData["lang"])) { //<!-- see this line. checks 
			//Based on the lowecase $_GET['language'] value, we will decide,
			//what lanuage do we use
			switch (strtolower($requestData["lang"])) {
				case "en":
					//If the string is en or EN
					$_SESSION['lang'] = 'en';
					break;
				case "ta":
					//If the string is tr or TR
					$_SESSION['lang'] = 'ta';
					break;
				default:
					//IN ALL OTHER CASES your default langauge code will set
					//Invalid languages
					$_SESSION['lang'] = $defaultLang;
					break;
			}
		}
		// echo $r;
		// 			$id = $requestData['id'];
		$final = array();
		$final['status'] = true;
		$final['statusCode'] = 200;
		$final['message'] = 'success!';
		$final['data'] = $_SESSION;
		$this->response($final, REST_Controller::HTTP_OK);
	}
	//
	public function lang()
	{
		if (!empty($_SESSION['lang'])) {
			// $_SESSION['lang']='en';
		} else {
			$_SESSION['lang'] = 'en';
		}
		// 			$_SESSION['lang'] = 'en';
		switch ($_SESSION['lang']) {
			case 'ta':
				$lang =  $_SESSION['lang'];
				$lang .=  '_';
				break;
			default;
				$lang =  '';
		}
		return $lang;
	}
}
