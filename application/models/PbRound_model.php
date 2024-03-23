<?php
class PbRound_model extends CI_Model {
	
	private $mStrPowballRoundURL ;
    private $mTableName ;

    function __construct()
    {
        parent::__construct();

        $this->mTableName = "round_pball";
    }

    function gets($nCount)
    {
        $strSql = "SELECT round_fid, round_date, round_num, round_time, round_state, round_result_1, round_result_2, round_result_3, round_result_4, round_result_5, round_power, round_normal, round_hash  FROM ".$this->mTableName." ORDER BY round_date DESC, round_num DESC LIMIT 0, ".$nCount;
        $query = $this -> db -> query($strSql);
        $result = $query -> result();
        
        return $result;  

    }

    public function getById($nRoundId){
        
        return $this->db->get_where($this->mTableName, array('round_hash'=>$nRoundId))->row();
    }

    
}