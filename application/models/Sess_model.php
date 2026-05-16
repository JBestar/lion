
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

    /** 동일 계정의 기존 세션 전부 제거 (매장 중복 로그인 시 이전 PC 세션 무효화) */
    public function logoutByMbUid($strUid){
        if ($strUid === null || $strUid === '') {
            return false;
        }
        $this->db->where('sess_mb_uid', $strUid);
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

    /**
     * 총판(mb_fid)별로 하부 매장(MEMBER_EMPLOYEE_LEVEL) 세션이 하나 이상 있는지 일괄 조회.
     * @param int[] $arrAgencyFids
     * @return int[] sess_emp_fid 값 중 세션이 존재하는 총판 fid 목록
     */
    public function getAgencyFidsWithActiveEmployeeSession($arrAgencyFids){

        if(!is_array($arrAgencyFids) || count($arrAgencyFids) < 1)
            return array();

        $this->db->distinct();
        $this->db->select('sess_emp_fid');
        $this->db->from($this->mTableName);
        $this->db->where('sess_mb_level', MEMBER_EMPLOYEE_LEVEL);
        $this->db->where_in('sess_emp_fid', $arrAgencyFids);
        $arrRows = $this->db->get()->result();

        $arrOut = array();
        foreach($arrRows as $objRow){
            $arrOut[] = (int) $objRow->sess_emp_fid;
        }
        return $arrOut;
    }



}