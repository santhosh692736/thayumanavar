<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class API_model extends CI_model {
	//
	public function appInsert($dbname,$data){
		$this->db->insert($dbname,$data);
		$insert_id = $this->db->insert_id();
		return $insert_id;
	}
	//
	public function appUpdate($dbname, $data , $where = array() )
	{
		foreach($where as $key => $values){
			$this->db->where($key, $values);
		}
		if($this->db->update($dbname, $data)){
			return true;
		}else{
			return false;
		}
	}
	//
	function getRows($dbname, $params = array()){
		//$this->load->database();//for multiple table purpose
		if(array_key_exists("select",$params)){
			$this->db->select($params["select"]);
		}else{
        	$this->db->select('*');
    	}
        $this->db->from($dbname);
        //fetch data by conditions
        if(array_key_exists("conditions",$params)){
            foreach($params['conditions'] as $key => $value){
                $this->db->where($key,$value);
            }
        }
		//fetch data by conditions
        if(array_key_exists("conditions_where_in",$params)){
            foreach($params['conditions_where_in'] as $key => $value){
                $this->db->where_in($key,$value);
            }
        }
		//fetch data by order by
        if(array_key_exists("conditions_order_by",$params)){
            foreach($params['conditions_order_by'] as $key => $value){
                $this->db->order_by($key,$value);
            }
        }
        //set start and limit
		if(array_key_exists("start",$params) && array_key_exists("limit",$params)){
			$this->db->limit($params['limit'],$params['start']);
		}elseif(!array_key_exists("start",$params) && array_key_exists("limit",$params)){
			$this->db->limit($params['limit']);
		}
		//	
		if(array_key_exists("returnType",$params) && $params['returnType'] == 'count'){
			$result = $this->db->count_all_results();    
		}elseif(array_key_exists("returnType",$params) && $params['returnType'] == 'single'){
			$query = $this->db->get();
			$result = ($query->num_rows() > 0)?$query->row():false;
		}else{
			$query = $this->db->get();
			$result = ($query->num_rows() > 0)?$query->result():false;
		}
        //return fetched data
        return $result;
    }
	//
	public function getCustomRows($sql, $return_type = ''){
		$query = $this->db->query($sql);
		$rowCount = $query->num_rows();
		if($return_type == "count"){
			return $rowCount;
		}elseif($return_type == "single"){
			if($rowCount>0){
				return $query->row();
			}
			return '';
		}
		else{
			if($rowCount>0){
				return $query->result();
			}
			return '';
		}
		return '';
	}
	
	public function getDefaultFieldName($dbname, $field_name, $id){
		$this->db->select($field_name);
        $this->db->from($dbname);
        //fetch data by conditions
        $this->db->where("id",$id);
		$query = $this->db->get();	
		if($query->num_rows()>0){
		$res = $query->row();    
		return $res->$field_name;
		}else{
			return '';
		}
	}
	
	public function getDefaultArray($dbname){
		$this->db->select('`id`, `name`');
        $this->db->from($dbname);
        //fetch data by conditions
        $query = $this->db->get();	
		if($query->num_rows()>0){
			$array = array();	
			$array[0] = $array[null]= '';
			$res = $query->result();    
			foreach($res as $data){
				$array[$data->id] = $data->name;
			}
			return $array;
		}else{
			return '';
		}
	}
	//
	public function getUserArray(){
		$dbname = 'users';
		$this->db->select('`id`, `firstname`, `username`');
        $this->db->from($dbname);
        //fetch data by conditions
        $query = $this->db->get();	
		if($query->num_rows()>0){
			$array = array();	
			$array[0] = $array[null]= '';
			$res = $query->result();    
			foreach($res as $data){
				$array[$data->id] = $data->firstname." - ".$data->username;
			}
			return $array;
		}else{
			return '';
		}
	}
}  