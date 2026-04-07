<?php
class PbRound_model extends CI_Model {
	
	private $mStrPowballRoundURL ;
    private $mTableName ;

    function __construct()
    {
        parent::__construct();

        $this->mTableName = "round_pball";
    }

    function gets($gameId, $nCount)
    {
        $sql = "SELECT round_fid, round_date, round_num, round_time, round_state, ";
        $sql.= " round_result_1, round_result_2, round_result_3, round_result_4, round_result_5, ";
        $sql.= "round_power, round_normal, round_hash  FROM ".$this->mTableName;
        $sql.= " WHERE round_game = ".$gameId;
        $sql.= " ORDER BY round_date DESC, round_num DESC LIMIT 0, ".$nCount;
        $query = $this -> db -> query($sql);
        $result = $query -> result();
        
        return $result;  
    }

    /** $id = round_fid (클라이언트 round_id / bet_round_fid와 동일 키) */
    public function getById($gameId, $id){
        
        return $this->db->get_where($this->mTableName, array('round_game'=>$gameId, 'round_fid'=>$id))->row();
    }

    public function getByHash($gameId, $hash){
        
        return $this->db->get_where($this->mTableName, array('round_game'=>$gameId, 'round_hash'=>$hash))->row();
    }

    public function getByNum($gameId, $strDate, $nRoundNo){
        
        return $this->db->get_where($this->mTableName, array('round_game'=>$gameId, 'round_date'=>$strDate, 'round_num'=>$nRoundNo))->row();
    }
    
}