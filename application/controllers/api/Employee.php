<?php
/**
 * Admin class.
 * 
 * @extends REST_Controller
 */
require APPPATH . '/libraries/REST_Controller.php';
use Restserver\Libraries\REST_Controller;
     
class Employee extends REST_Controller {
    
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

	public function getDocumentType_get()
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
					$listData = $this->API_model->getRows("document_type", $con);
					if($listData){
						foreach($listData as $row){
							$outData[] = $row;	
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
		}catch (Exception $e) {
            $this->set_error($e->getMessage());
			$this->response($e->getMessage(), REST_Controller::HTTP_OK);
			//return false;
        }	
	}
	//
		public function getZoneType_get()
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
					$listData = $this->API_model->getRows("tbl_zone", $con);
					if($listData){
						foreach($listData as $row){
							$outData[] = $row;	
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
		}catch (Exception $e) {
            $this->set_error($e->getMessage());
			$this->response($e->getMessage(), REST_Controller::HTTP_OK);
			//return false;
        }	
	}
		//
		public function getRtoDropdown_get()
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
					$listData = $this->API_model->getRows("tbl_rto", $con);
					if($listData){
						foreach($listData as $row){
							$outData[] = $row;	
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
		}catch (Exception $e) {
            $this->set_error($e->getMessage());
			$this->response($e->getMessage(), REST_Controller::HTTP_OK);
			//return false;
        }	
	}
	//
			public function getZoneDropdown_get()
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
					$listData = $this->API_model->getRows("tbl_zone", $con);
					if($listData){
						foreach($listData as $row){
							$outData[] = $row;	
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
		}catch (Exception $e) {
            $this->set_error($e->getMessage());
			$this->response($e->getMessage(), REST_Controller::HTTP_OK);
			//return false;
        }	
	}
	 
    /**
     * INSERT | POST method.
     *
     * @return Response
    */
    public function employeeSubmit_post()
    {
        $headers = $this->input->request_headers(); 
		if (isset($headers['Authorization-Token'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization-Token']);
            if ($decodedToken['status'])
            {
				$requestData = json_decode(file_get_contents('php://input'), true);
				if($requestData){
					$userId = $requestData["userId"];
					$updateArr = [];
					$skipInput = array("userId");
					foreach($requestData as $key=> $value){
						if(!(in_array($key, $skipInput))) {
							$updateArr[$key] = $value;
						}
					}
					// ------- Main Logic part -------
					$con = array("users_id"=>$userId);
					$runQuery = $this->API_model->appUpdate("emp_details", $updateArr, $con);
					if($runQuery){
						$final = array();
						$final['status'] = true;
						$final['statusCode'] = 200;
						$final['message'] = 'Data updated successfully';
						$final['data'] = null;
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
		public function employeeSubmitFile_post()
    {
        $headers = $this->input->request_headers(); 
		if (isset($headers['Authorization-Token'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization-Token']);
            if ($decodedToken['status'])
            {
				$requestData = $this->input->post();
				if($requestData){
					$userId = $requestData["userId"];
					//
					$dateInputArr = array("dob");
					$fileInputCheck = $fileFolderArr = array();
					$sql = "SELECT `id`, `name`, `folder_name`, `file_name` FROM `document_type` WHERE 1";
					$typeData = $this->API_model->getCustomRows($sql);
					if($typeData){
						foreach($typeData as $tData){
							$fileInputCheck[$tData->id] = $tData->file_name;
							$fileFolderArr[$tData->id] = $tData->folder_name;
						}
					}
					$base_dir = __DIR__;
					$fileInputData = array();
					
						$fileInputData = $_FILES;
					
					//
					//
					foreach($fileInputCheck as $fKey => $fileData){
						//
						$fileFolder = $fileFolderArr[$fKey];
						$filePath = $base_dir."/../../../public/upload-files/empdoc/".$fileFolder."/";
						if (!file_exists($filePath) && !is_dir($filePath)) {
							mkdir($filePath, 0777, true);
						}
						$fileNameRequest = $fileData;
						$updateData[$fileNameRequest] = '';
						
						if(isset($fileInputData[$fileNameRequest]['name'])){
							$fileName=$fileInputData[$fileNameRequest]['name'];
							$fileSize=$fileInputData[$fileNameRequest]['size'];
							$fileNameCmps = explode(".", $fileName);
							$fileExtension = strtolower(end($fileNameCmps));
							$fileNameGen = $userId."".strtotime($this->now)."".rand(1111,9999).".".$fileExtension;
							$inputFileArray = array();
							$inputFileArray["fileName"] = $fileName;
							$inputFileArray["fileExt"] = $fileExtension;
							$inputFileArray["filePathName"] = $fileNameGen;
							$inputFileArray["fileSize"] = $fileSize;
							if(move_uploaded_file($fileInputData[$fileNameRequest]["tmp_name"],$filePath.$fileNameGen)){
								$insertData = array(
									"users_id" => $userId,
									"created_by" => $userId,
									"doc_type_id" => $fKey,
								);
								$insertData['file_name'] = json_encode($inputFileArray, true);
								//
							
								$this->API_model->appInsert("emp_document", $insertData);
								//
							}
						}
					}
					//
					$updateArr = ['users_id'=>$userId];
					$skipInput = array("userId");
					foreach($requestData as $key=> $value){
					    if(in_array($key, $dateInputArr)){
					        if($value<>''){
					           $value = date("Y-m-d", strtotime($value));
					        }else{
					            $value = null;
					        }
					    }
					    if(!(in_array($key, $skipInput))) {
							$updateArr[$key] = $value;
						}
					}
					// ------- Main Logic part -------
					$con = array("users_id"=>$userId);
					$runQuery = $this->API_model->appUpdate("emp_details", $updateArr, $con);
					if($runQuery){
						$final = array();
						$final['status'] = true;
						$final['statusCode'] = 200;
						$final['message'] = 'Data updated successfully';
						$final['data'] = $_FILES;
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
// 				public function getUsersprofile_get()
// 	{
//         $headers = $this->input->request_headers(); 
// 		try {
// 			if (isset($headers['Authorization-Token'])) {
// 				$decodedToken = $this->authorization_token->validateToken($headers['Authorization-Token']);
// 				if ($decodedToken['status'])
// 				{
// 					$sessionData = $decodedToken["data"];
// 					$createdBy = $sessionData->uid;
// 					$user_id = $createdBy;
// 					$fileInputCheck = $fileDisply = $fileFolderArr = array();
// 					$sql = "SELECT `id`, `name`, `folder_name`, `file_name` FROM `document_type` WHERE 1";
// 					$typeData = $this->API_model->getCustomRows($sql);
// 					if($typeData){
// 						foreach($typeData as $tData){
// 							$fileInputCheck[$tData->id] = $tData->file_name;
// 							$fileDisply[$tData->id] = $tData->name;
// 							$fileFolderArr[$tData->id] = $tData->folder_name;
// 						}
// 					}
// 					// $con = array();
// 					$con = array(
// 						"returnType" => "single",
// 						"conditions" => array("users_id"=>$user_id)
// 					);
// 					$outData = array();
// 					$listData = $this->API_model->getRows("emp_details", $con);
// 					if($listData){						
// 							$listData  = (array)$listData;					
// 					}
// 					$outData1 = array();
// 					$con = array(
// 						"conditions" => array("users_id"=>$user_id)
// 					);
// 					$attachmentArr = $this->API_model->getRows("emp_document", $con);
// 					foreach($attachmentArr as $pkey =>$attachmentData){
// 						if($attachmentData){
// 							$fileData = json_decode($attachmentData->file_name, true);
// 							$doc_type_id = $attachmentData->doc_type_id;

// 							$folderName = $fileFolderArr[$doc_type_id];
// 							$fileOut = array();	
// 							if(is_array($fileData)){								
// 									if(isset($fileData["filePathName"])){
// 										$filePathName = $fileData["filePathName"];
// 										$base_dir = __DIR__;
// 										$fileData["base"] = "public/upload-files/empdoc/".$folderName."/".$filePathName;
// 										if(file_exists($base_dir."/../../../public/upload-files/empdoc/".$folderName."/".$filePathName)){
// 											$fileUrl = base_url();
// 											$fileUrl.="public/upload-files/empdoc/".$folderName."/".$filePathName;
// 											$inputValue = $fileUrl;
// 											$fileData["fileUrl"] = $fileUrl;
// 											$fileData["docTitle"] = $fileDisply[$doc_type_id];
// 											$fileData["docName"] = $fileInputCheck[$doc_type_id];
// 											$fileOut[] = $fileData;
// 										}
// 									}																	
// 								$outData1[] = $fileData;
// 							}	
// 						}	
// 					}
// 					$listData["attach"] = $outData1;
// 					$outData = $listData;	
					
					
// 					$colData = array();
// 					$colData[] = array("field"=>"name","header"=>"Name"); 
// 					$colData[] = array("field"=>"emp_code","header"=>"Employee Code"); 
// 					$colData[] = array("field"=>"designation","header"=>"Designation");
// 					$colData[] = array("field"=>"gender","header"=>"Gender");
// 					$colData[] = array("field"=>"doj","header"=>"Date of Join");
// 					$data['cols']=$colData;
// 					$data['userData']=$outData;

// 					$final = array();
// 					$final['status'] = true;
// 					$final['statusCode'] = 200;
// 					$final['message'] = 'success!';
// 					$final['data'] = $data;
// 					//$final['data2'] = $outData1;
// 					$this->response($final, REST_Controller::HTTP_OK);
// 					// ------------- End -------------
// 				} 
// 				else {
// 					$this->response($decodedToken);
// 				}
// 			} else {
// 				$this->response(['Authentication failed'], REST_Controller::HTTP_OK);
// 			}
// 		}catch (Exception $e) {
//             $this->set_error($e->getMessage());
// 			$this->response($e->getMessage(), REST_Controller::HTTP_OK);
// 			//return false;
//         }	
// 	}
	//
			public function getUsers_get()
	{
        $headers = $this->input->request_headers(); 
		try {
			if (isset($headers['Authorization-Token'])) {
				$decodedToken = $this->authorization_token->validateToken($headers['Authorization-Token']);
				if ($decodedToken['status'])
				{
					$sessionData = $decodedToken["data"];
					$createdBy = $sessionData->uid;
					$user_id = $createdBy;
					$fileInputCheck = $fileDisply = $fileFolderArr = array();
					$sql = "SELECT `id`, `name`, `folder_name`, `file_name` FROM `document_type` WHERE 1";
					$typeData = $this->API_model->getCustomRows($sql);
					if($typeData){
						foreach($typeData as $tData){
							$fileInputCheck[$tData->id] = $tData->file_name;
							$fileDisply[$tData->id] = $tData->name;
							$fileFolderArr[$tData->id] = $tData->folder_name;
						}
					}
					// $con = array();
					$con = array(
						"returnType" => "single",
						"conditions" => array("users_id"=>$user_id)
					);
					$outData = array();
					$listData = $this->API_model->getRows("emp_details", $con);
					if($listData){						
							$listData  = (array)$listData;					
					}
					$outData1 = array();
					$con = array(
						"conditions" => array(
							"users_id"=>$user_id,
							"doc_type_id"=>'6',
						),
					);

					$attachmentArr = $this->API_model->getRows("emp_document", $con);
					// if($attachmentArr){
						$listData1='null';
						if(is_array($attachmentArr)){
					foreach($attachmentArr as $pkey =>$attachmentData){

						if($attachmentData){
							$fileData = json_decode($attachmentData->file_name, true);
							$doc_type_id = $attachmentData->doc_type_id;

							 $folderName = $fileFolderArr[$doc_type_id];

							$fileOut = array();	
							if(is_array($fileData)){								
									if(isset($fileData["filePathName"])){

										$filePathName = $fileData["filePathName"];
										$base_dir = __DIR__;
										$fileData["base"] = "public/upload-files/empdoc/".$folderName."/".$filePathName;

										 if(file_exists($base_dir."/../../../public/upload-files/empdoc/".$folderName."/".$filePathName)){
											$fileUrl = base_url();
											$fileUrl.="public/upload-files/empdoc/".$folderName."/".$filePathName;
											$inputValue = $fileUrl;
											$fileData["fileUrl"] = $fileUrl;
											$fileData["docTitle"] = $fileDisply[$doc_type_id];
											$fileData["docName"] = $fileInputCheck[$doc_type_id];
											$fileOut[] = $fileData;
										}
										
									}																	
								// $outData1[] = $fileData;
							}	
							
						}	
					}
					$photo = $fileUrl;
					}
					// $outData = $listData;
					$outData[] = array("name"=>$listData['name'],"emp_code"=>$listData['emp_code'],"designation"=>$listData['designation'],"doj"=>$listData['doj'],"photo"=>$photo);
					$colData = array();
					$colData[] = array("field"=>"photo","header"=>"Image","type"=>"image"); 
					$colData[] = array("field"=>"name","header"=>"Name"); 
					$colData[] = array("field"=>"emp_code","header"=>"Employee Code"); 
					$colData[] = array("field"=>"designation","header"=>"Designation");
					$colData[] = array("field"=>"gender","header"=>"Gender");
					$colData[] = array("field"=>"doj","header"=>"Date of Join");
					$data['cols']=$colData;
					$data['userData']=$outData;

					$final = array();
					$final['status'] = true;
					$final['statusCode'] = 200;
					$final['message'] = 'success!';
					$final['data'] = $data;
					//$final['data2'] = $outData1;
					$this->response($final, REST_Controller::HTTP_OK);
					// ------------- End -------------
				} 
				else {
					$this->response($decodedToken);
				}
			} else {
				$this->response(['Authentication failed'], REST_Controller::HTTP_OK);
			}
		}catch (Exception $e) {
            $this->set_error($e->getMessage());
			$this->response($e->getMessage(), REST_Controller::HTTP_OK);
			//return false;
        }	
	}
	//
					public function getUsersprofile_get()
	{
        $headers = $this->input->request_headers(); 
		try {
			if (isset($headers['Authorization-Token'])) {
				$decodedToken = $this->authorization_token->validateToken($headers['Authorization-Token']);
				if ($decodedToken['status'])
				{
					$sessionData = $decodedToken["data"];
					$createdBy = $sessionData->uid;
					$user_id = $createdBy;
					$fileInputCheck = $fileDisply = $fileFolderArr = array();
					$sql = "SELECT `id`, `name`, `folder_name`, `file_name` FROM `document_type` WHERE 1";
					$typeData = $this->API_model->getCustomRows($sql);
					if($typeData){
						foreach($typeData as $tData){
							$fileInputCheck[$tData->id] = $tData->file_name;
							$fileDisply[$tData->id] = $tData->name;
							$fileFolderArr[$tData->id] = $tData->folder_name;
						}
					}
					// $con = array();
					$con = array(
						"returnType" => "single",
						"conditions" => array("users_id"=>$user_id)
					);
					$outData = array();
					$listData = $this->API_model->getRows("emp_details", $con);
					if($listData){						
							$listData  = (array)$listData;					
					}
					$outData1 = array();
					$con = array(
						"conditions" => array("users_id"=>$user_id)
					);
					$attachmentArr = $this->API_model->getRows("emp_document", $con);
					foreach($attachmentArr as $pkey =>$attachmentData){
						if($attachmentData){
							$fileData = json_decode($attachmentData->file_name, true);
							$doc_type_id = $attachmentData->doc_type_id;

							$folderName = $fileFolderArr[$doc_type_id];
							$fileOut = array();	
							if(is_array($fileData)){								
									if(isset($fileData["filePathName"])){
										$filePathName = $fileData["filePathName"];
										$base_dir = __DIR__;
										$fileData["base"] = "public/upload-files/empdoc/".$folderName."/".$filePathName;
										if(file_exists($base_dir."/../../../public/upload-files/empdoc/".$folderName."/".$filePathName)){
											$fileUrl = base_url();
											$fileUrl.="public/upload-files/empdoc/".$folderName."/".$filePathName;
											$inputValue = $fileUrl;
											$fileData["fileUrl"] = $fileUrl;
											$fileData["docTitle"] = $fileDisply[$doc_type_id];
											$fileData["docName"] = $fileInputCheck[$doc_type_id];
											$fileOut[] = $fileData;
										}
									}																	
								$outData1[] = $fileData;
							}	
						}	
					}
					$listData["attach"] = $outData1;
					$outData = $listData;	
					$final = array();
					$final['status'] = true;
					$final['statusCode'] = 200;
					$final['message'] = 'success!';
					$final['data'] = $outData;
					//$final['data2'] = $outData1;
					$this->response($final, REST_Controller::HTTP_OK);
					// ------------- End -------------
				} 
				else {
					$this->response($decodedToken);
				}
			} else {
				$this->response(['Authentication failed'], REST_Controller::HTTP_OK);
			}
		}catch (Exception $e) {
            $this->set_error($e->getMessage());
			$this->response($e->getMessage(), REST_Controller::HTTP_OK);
			//return false;
        }	
	}
	//
		public function employeeinsert_post()
    {
        $headers = $this->input->request_headers(); 
		if (isset($headers['Authorization-Token'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization-Token']);
            if ($decodedToken['status'])
            {
				$requestData = $this->input->post();
				if($requestData){
			    $dateInputArr = array("dob");

				    //
				// 	$updateArr = ['users_id'=>$userId];
					$skipInput = array("doc_upload_1","doc_upload_2","doc_upload_3","doc_upload_4","doc_upload_5","doc_upload_6","doc_upload_7");
					foreach($requestData as $key=> $value){
					    if(in_array($key, $dateInputArr)){
					        if($value<>''){
					           $value = date("Y-m-d", strtotime($value));
					        }else{
					            $value = null;
					        }
					    }
					    if(!(in_array($key, $skipInput))) {
							$updateArr[$key] = $value;
						}
					}
					// ------- Main Logic part -------
				// 	$con = array("users_id"=>$userId);
					$runQuery = $this->API_model->appInsert("emp_details", $updateArr);
				    
				    
				    // SELECT `users_id` FROM `emp_details` ORDER BY `users_id` DESC LIMIT 2
					$getsqluid = "SELECT * FROM `emp_details` ORDER BY `users_id` DESC LIMIT 1";
				    $uid = $this->API_model->getCustomRows($getsqluid,'single');
				    $uiid = ((array)$uid);
				    // print_r($uiid);
				    $userId = $uiid['users_id'];
				// 	exit;
					//
					$dateInputArr = array("dob");
					$fileInputCheck = $fileFolderArr = array();
					$sql = "SELECT `id`, `name`, `folder_name`, `file_name` FROM `document_type` WHERE 1";
					$typeData = $this->API_model->getCustomRows($sql);
					if($typeData){
						foreach($typeData as $tData){
							$fileInputCheck[$tData->id] = $tData->file_name;
							$fileFolderArr[$tData->id] = $tData->folder_name;
						}
					}
					$base_dir = __DIR__;
					$fileInputData = array();
					
						$fileInputData = $_FILES;
					
					//
					foreach($fileInputCheck as $fKey => $fileData){
						//
						$fileFolder = $fileFolderArr[$fKey];
						$filePath = $base_dir."/../../../public/upload-files/empdoc/".$fileFolder."/";
						if (!file_exists($filePath) && !is_dir($filePath)) {
							mkdir($filePath, 0777, true);
						}
						$fileNameRequest = $fileData;
						$updateData[$fileNameRequest] = '';
						
						if(isset($fileInputData[$fileNameRequest]['name'])){
							$fileName=$fileInputData[$fileNameRequest]['name'];
							$fileSize=$fileInputData[$fileNameRequest]['size'];
							$fileNameCmps = explode(".", $fileName);
							$fileExtension = strtolower(end($fileNameCmps));
							$fileNameGen = $userId."".strtotime($this->now)."".rand(1111,9999).".".$fileExtension;
							$inputFileArray = array();
							$inputFileArray["fileName"] = $fileName;
							$inputFileArray["fileExt"] = $fileExtension;
							$inputFileArray["filePathName"] = $fileNameGen;
							$inputFileArray["fileSize"] = $fileSize;
							if(move_uploaded_file($fileInputData[$fileNameRequest]["tmp_name"],$filePath.$fileNameGen)){
								$insertData = array(
									"users_id" => $userId,
									"created_by" => $userId,
									"doc_type_id" => $fKey,
								);
								$insertData['file_name'] = json_encode($inputFileArray, true);
								//
							
								$this->API_model->appInsert("emp_document", $insertData);
								//
							}
						}
					}
					

					if($runQuery){
						$final = array();
						$final['status'] = true;
						$final['statusCode'] = 200;
						$final['message'] = 'Data Inserted successfully';
						$final['data'] = $_FILES;
					}else{
						$final = array();
						$final['status'] = true;
						$final['statusCode'] = 500;
						$final['message'] = 'Data Insert failed!';
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
}