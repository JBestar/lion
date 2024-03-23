<?php
class LogHist_model extends CI_Model {
	
    private $mTableName ;
	
    function __construct()
    {
        parent::__construct();

        $this->mTableName = "log_history";
	}

    function addLog($objUser, $iType){
        if(is_null($objUser))
            return false;

        $strIp = $this->input->ip_address();

        $this->db->set('log_mb_uid', $objUser->mb_uid);
        $this->db->set('log_emp_fid', $objUser->mb_emp_fid);
        $this->db->set('log_type', $iType);                 //1:로그인
        $this->db->set('log_ip', $strIp);
        $this->db->set('log_time', 'NOW()', false);

        return $this->db->insert($this->mTableName);
    }

}