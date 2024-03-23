<?php
class DelHist_model extends CI_Model {
	
	private $mTableName ;

    function __construct()
    {
        parent::__construct();

        $this->mTableName = "del_history";
    }

    
    function register($emp_uid, $mb_uid)
    {
        
        $this->db->set('del_emp_uid', $emp_uid);
        $this->db->set('del_mb_uid', $mb_uid);        
        $this->db->set('del_time', 'NOW()', false);
        
        return $this->db->insert($this->mTableName);
    }

}