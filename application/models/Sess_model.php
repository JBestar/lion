
<?php
class Sess_model extends CI_Model {
	
	private $mTableName ;

    function __construct()
    {
        parent::__construct();

        $this->mTableName = "sess_list";
    }

    
    public function getByFid($nFid){

        $this->db->where('sess_fid', $nFid);
        return $this->db->get($this->mTableName)->row();
    }

    
    public function is_login($nFid, $nLevel, $sessId=""){

        if(strlen($nFid) < 1)
            return false;
        
        $strIp = $this->input->ip_address();

        $this->db->where('sess_fid', $nFid);
        $this->db->where('sess_mb_level', $nLevel);
        $this->db->where('sess_ip', $strIp);
        if(strlen($sessId) > 0)
            $this->db->where('sess_ip', $sessId);

        $objSess = $this->db->get($this->mTableName)->row();

        if(!is_null($objSess)){
            $this->update($nFid);
            return true;
        } else return false;

    }

    public function getByIp($nFid, $strIp){

        $this->db->where('sess_fid', $nFid);
        $this->db->where('sess_ip', $strIp);
        return $this->db->get($this->mTableName)->row();
    }

    public function getByUid($strUid){

        $this->db->where('sess_mb_uid', $strUid);
        return $this->db->get($this->mTableName)->row();
    }
    
    public function getsByUid($strUid){

        $this->db->where('sess_mb_uid', $strUid);
        return $this->db->get($this->mTableName)->result();
    }

    public function logout($nFid){

        $this->db->where('sess_fid', $nFid);
        return $this->db->delete($this->mTableName);
    }

      
    public function login($objUser, $sessId=""){
        if(is_null($objUser))
            return false;
        $strIp = $this->input->ip_address();

        if(strlen($sessId) > 0)
            $this->db->set('sess_id', $sessId);
        $this->db->set('sess_mb_fid', $objUser->mb_fid);
        $this->db->set('sess_mb_uid', $objUser->mb_uid);
        $this->db->set('sess_mb_level', $objUser->mb_level);
        $this->db->set('sess_emp_fid', $objUser->mb_emp_fid);
        $this->db->set('sess_ip', $strIp);
        $this->db->set('sess_update_time', 'NOW()', false);

        if($this->db->insert($this->mTableName))
            return $this->db->insert_id();
        else return 0;
    }


    public function update($nFid){

        $this->db->set('sess_update_time', 'NOW()', false);
        $this->db->where('sess_fid', $nFid);
    	return $this->db->update($this->mTableName);
    }
    

    
    public function getUserId($nFid){

        $strUid = "";
        $this->db->where('sess_fid', $nFid);
        $objSess = $this->db->get($this->mTableName)->row();
        if(!is_null($objSess)){
            $strUid = $objSess->sess_mb_uid;
        }
        return $strUid;

    }



}