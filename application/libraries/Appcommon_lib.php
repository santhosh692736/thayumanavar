<?php 
defined('BASEPATH') OR exit('No direct script access allowed'); 
/** 
 * Stripe Library for CodeIgniter 3.x 
 * 
 * Library for Stripe payment gateway. It helps to integrate Stripe payment gateway 
 * in CodeIgniter application. 
 * 
 * This library requires the Stripe PHP bindings and it should be placed in the third_party folder. 
 * It also requires Stripe API configuration file and it should be placed in the config directory. 
 * 
 * @package     CodeIgniter 
 * @category    Libraries 
 */ 
class Appcommon_lib{ 
    var $CI; 
    var $api_error; 
     
    public function __construct(){ 
        $this->api_error = ''; 
        $this->CI =& get_instance(); 
        $this->CI->load->model('API_model');
    } 
	//
	public function sqlDateFormat($date){
		if($date!='' && strlen($date)>=10){
		return date("Y-m-d H:i:s", strtotime($date));
		}else{
		return null;
		}
	}
	//
	public function sqlTimeFormat($time){
		if($time!='' && strlen($time)>=5){
		return date("H:i:s", strtotime($time));
		}else{
		return null;
		}
	}
	//
	public function getDataType($key){
		$array = array(
			"string" => array("key"=>"string","value"=>"string"),
			"integer" => array("key"=>"number","value"=>"int"),
			"pkid" => array("key"=>"pkid","value"=>"int"),
			"boolean" => array("key"=>"boolean","value"=>"bool"),
		);
		return $array[$key];
	}
	//
	public function randomKey($len)
	{
		//To Pull 7 Unique Random Values Out Of AlphaNumeric
		
		//removed number 0, capital o, number 1 and small L
		//Total: keys = 32, elements = 33
		$characters = array(
		"A","B","C","D","E","F","G","H","J","K","L","M",
		"N","P","Q","R","S","T","U","V","W","X","Y","Z");
		
		//make an "empty container" or array for our keys
		$keys = array();
		$random_chars='';
		//first count of $keys is empty so "1", remaining count is 1-6 = total 7 times
		while(count($keys) < $len) {
			//"0" because we use this to FIND ARRAY KEYS which has a 0 value
			//"-1" because were only concerned of number of keys which is 32 not 33
			//count($characters) = 33
			$x = mt_rand(0, count($characters)-1);
			if(!in_array($x, $keys)) {
			   $keys[] = $x;
			}
		}
		
		foreach($keys as $key){
		   $random_chars .= $characters[$key];
		}
		return $random_chars;
	}
	//
	public function arraySearchData($array, $search_list) { 
		// Create the result array 
		$result = array(); 
	  
		// Iterate over each array element 
		foreach ($array as $key => $value) { 
	  
			// Iterate over each search condition 
			foreach ($search_list as $k => $v) { 
		  
				// If the array element does not meet 
				// the search condition then continue 
				// to the next element 
				if (!isset($value[$k]) || $value[$k] != $v) 
				{ 
					  
					// Skip two loops 
					continue 2; 
				} 
			} 
		  
			// Append array element's key to the 
			//result array 
			$result[] = $value; 
		} 
	  
		// Return result  
		return $result; 
	}	
	//
	public function getGender(){
		return array(
			1=>array("id"=>1,"name"=>"Male"), 
			2=>array("id"=>2,"name"=>"Female"), 
			3=>array("id"=>3,"name"=>"Others"), 
		);
	}
	
	public function getGenderName($gender_id){
		$genderData = $this->getGender();
		$searchId=array("id"=>$gender_id);
		$searchList = $this->arraySearchData($genderData,$searchId);
		$array = $searchList[0];
		return $array["name"];
	}
	
	public function getGenderID($gender){
		$gender = strtolower($gender);
		$genderData = $this->getGender();
		$searchId=array("name"=>ucfirst($gender));
		$searchList = $this->arraySearchData($genderData,$searchId);
		$array = $searchList[0];
		return $array["id"];
	}
	//
	public function elapsedTime( $time ) {
        $time = strtotime($time);
		// Calculate difference between current
		// time and given timestamp in seconds
		$diff = time() - $time;
		  
		if( $diff < 1 ) { 
			return 'less than 1 second ago'; 
		}
		  
		$time_rules = array ( 
					12 * 30 * 24 * 60 * 60 => 'year',
					30 * 24 * 60 * 60       => 'month',
					24 * 60 * 60           => 'day',
					60 * 60                   => 'hour',
					60                       => 'minute',
					1                       => 'second'
		);
	  
		foreach( $time_rules as $secs => $str ) {
			  
			$div = $diff / $secs;
	  
			if( $div >= 1 ) {
				  
				$t = round( $div );
				  
				return $t . ' ' . $str . 
					( $t > 1 ? 's' : '' ) . ' ago';
			}
		}
	}
	//
}