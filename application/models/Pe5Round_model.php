<?php
class Pe5Round_model extends CI_Model {
	
    
    private $mTableName ;

    function __construct()
    {
        parent::__construct();

        $this->mTableName = "round_eos5";
    }

    function gets($nCount)
    {
        $strSql = "SELECT round_fid, round_date, round_num, round_time, round_state, round_result_1, round_result_2, round_result_3, round_result_4, round_result_5, round_power, round_normal, round_hash  FROM ".$this->mTableName." ORDER BY round_date DESC, round_num DESC LIMIT 0, ".$nCount;
        $query = $this -> db -> query($strSql);
        $result = $query -> result();
        
        return $result;  

    }

    public function getById($nRoundId){
        
        return $this->db->get_where($this->mTableName, array('round_fid'=>$nRoundId))->row();
    }

    
    public function getByNum($strDate, $nRoundNo){
        
        return $this->db->get_where($this->mTableName, array('round_date'=>$strDate, 'round_num'=>$nRoundNo))->row();
    }
}