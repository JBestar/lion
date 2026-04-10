<?php
class PbBet_model extends CI_Model {
	

    private $mTableName ;
    private $mMemberTable ;

    function __construct()
    {
        parent::__construct();

        $this->mTableName = "bet_powerball";
        $this->mMemberTable = "member";
    }

    function gets($nCount)
    {
        
        $strSql = "SELECT bet_fid, bet_state, bet_round_fid, bet_round_no, bet_time, bet_game, bet_mode, bet_target, bet_money, bet_win_money FROM ".$this->mTableName." ORDER BY bet_time DESC LIMIT 0, ".$nCount;
        $query = $this -> db -> query($strSql);
        $result = $query -> result();
        
        return $result;  
    }

    function getByFid($fid)
    {
        
        $strSql = "SELECT bet_fid, bet_state, bet_emp_fid, bet_mb_uid, bet_mb_name, bet_round_fid, bet_round_no, bet_time, bet_game, bet_mode, bet_target, bet_ratio, bet_money, bet_win_money, bet_account_time, bet_empl_amount, bet_agen_amount  FROM ".$this->mTableName;
        $strSql .=" WHERE bet_fid='".$fid."' ";
        $result = $this -> db -> query($strSql)->row();
        
        return $result;  
    }

    function getByRoundId($strUserId, $nRoundFid, $nGameId = -1){       
        $strSql = "SELECT bet_fid, bet_state, bet_mb_uid, bet_mb_name, bet_round_fid, bet_round_no, bet_game, bet_time, bet_mode, bet_target, bet_ratio, bet_money, bet_win_money  FROM ".$this->mTableName;
        $strSql .=" WHERE bet_round_fid='".$nRoundFid."' AND bet_mb_uid='".$strUserId."' AND bet_state != ".BET_CANCEL;
        if($nGameId >= 0)
            $strSql .=" AND bet_game = '".$nGameId."' ";

        $strSql .=" ORDER BY bet_time"; 
        $query = $this -> db -> query($strSql);
        $result = $query -> result();
        
        return $result;   
        
    }

    function getByRoundNo($strUserId, $strDate, $nRoundNo, $nGameId = -1){       
        $strSql = "SELECT bet_fid, bet_state, bet_mb_uid, bet_mb_name, bet_round_fid, bet_round_no, bet_time, bet_game, bet_mode, bet_target, bet_ratio, bet_money, bet_win_money  FROM ".$this->mTableName;
        $strSql .=" WHERE bet_time >'".$strDate."' AND bet_round_no='".$nRoundNo."' AND bet_mb_uid='".$strUserId."' AND bet_state != ".BET_CANCEL;
        if($nGameId >= 0)
            $strSql .=" AND bet_game = '".$nGameId."' ";

        $strSql .=" ORDER BY bet_time"; 
        $query = $this -> db -> query($strSql);
        $result = $query -> result();
        
        return $result;   
        
    }

    function getOrderById($strUserId, $nBetId)
    {
        
        $strSql = "SELECT bet_fid, bet_state, bet_emp_fid, bet_mb_uid, bet_mb_name, bet_round_fid, bet_round_no, bet_time, bet_game, bet_mode, bet_target, bet_ratio, bet_money, bet_win_money, bet_empl_amount, bet_agen_amount  FROM ".$this->mTableName;
        $strSql .=" WHERE bet_mb_uid='".$strUserId."' AND bet_state=".BET_WAIT." AND bet_fid='".$nBetId."' ";
        $result = $this -> db -> query($strSql)->row();
        
        return $result;  
    }


    function getByUserId($strUserId, $nCount, $nGameId = -1)
    {
        
        $strSql = "SELECT bet_fid, bet_state, bet_round_fid, bet_round_date, bet_round_no, bet_time, bet_game, bet_mode, bet_target, bet_ratio, bet_money, bet_win_money  FROM ".$this->mTableName;
        $strSql .=" WHERE bet_mb_uid='".$strUserId."' AND bet_state !=".BET_CANCEL;
        if($nGameId >= 0)
            $strSql .=" AND bet_game = '".$nGameId."' ";
        
        $strSql .=" ORDER BY bet_time DESC LIMIT 0, ".$nCount;
        $query = $this -> db -> query($strSql);
        $result = $query -> result();
        
        return $result;  
    }

    function deleteByFid($fid){
        $this->db->set('bet_state', BET_CANCEL);
        $this->db->set('bet_account_time', 'NOW()', false);
       
        $this->db->where('bet_fid', $fid);
        
        return $this->db->update($this->mTableName);
    }

    function addBetRound($arrBetData, $objUser, $arrEmpRatio)
    {

        
        $this->db->set('bet_state', 1);
        $this->db->set('bet_emp_fid', $objUser->mb_emp_fid);
        $this->db->set('bet_mb_uid', $objUser->mb_uid);
        $this->db->set('bet_mb_name', $arrBetData['name']);
        $this->db->set('bet_round_fid', $arrBetData['roundid']);
        $this->db->set('bet_round_date', $arrBetData['round_date']);
        $this->db->set('bet_round_no', $arrBetData['roundno']);
        $this->db->set('bet_time', 'NOW()', false);
        $this->db->set('bet_game', $arrBetData['game']);
        $this->db->set('bet_mode', $arrBetData['mode']);
        $this->db->set('bet_target', $arrBetData['target']);        
        $this->db->set('bet_ratio', $arrBetData['ratio']);
        $this->db->set('bet_money', $arrBetData['amount']);
        $this->db->set('bet_before_money', $objUser->mb_money);
        $this->db->set('bet_empl_amount', $arrEmpRatio[1][2]);
        $this->db->set('bet_agen_amount', $arrEmpRatio[0][2]);
        //$this->db->set('bet_comp_amount', 0);
        
        $this->db->insert($this->mTableName);

        return $this->db->insert_id();
        
    }

    function updateBetObj($objBet)
    {
		$this->db->set('bet_mode', $objBet->bet_mode);
        $this->db->set('bet_target', $objBet->bet_target);  
        $this->db->set('bet_ratio', $objBet->bet_ratio);
        $this->db->set('bet_state', $objBet->bet_state);
        $this->db->set('bet_win_money', $objBet->bet_win_money);
        $this->db->set('bet_after_money', $objBet->bet_after_money);


		$this->db->where('bet_fid', $objBet->bet_fid);
    	return $this->db->update($this->mTableName);
    }
    
    
    function search($arrReqData)
    {
        $strSql = "SELECT *, CAST(bet_time AS DATE) AS bet_date FROM ".$this->mTableName;
        
        $strSql.=" WHERE bet_view_state = '0' ";
        if(array_key_exists('mb_uid', $arrReqData) && strlen($arrReqData['mb_uid']) > 0){
            $strSql.=" AND bet_mb_uid = '".$arrReqData['mb_uid']."' ";
        }
        if(array_key_exists('state', $arrReqData)){
            $strSql.=" AND bet_state = ".$arrReqData['state'];
        }
        else $strSql.=" AND bet_state != ".BET_CANCEL;

        if(array_key_exists('game', $arrReqData) && intval($arrReqData['game']) >= 0) {
            $strSql.=" AND bet_game = '".$arrReqData['game']."' ";
        }
        if(array_key_exists('mb_name', $arrReqData) && strlen($arrReqData['mb_name']) > 0) {
            $strSql.=" AND bet_mb_name = '".$this->db->escape_str($arrReqData['mb_name'])."' ";
        }
        if(strlen($arrReqData['round_fid']) > 0) {

            if(intval($arrReqData['round_fid']) <= 288){
                $strSql.=" AND bet_round_no = '".$arrReqData['round_fid']."' ";
            }
            else $strSql.=" AND bet_round_fid = '".$arrReqData['round_fid']."' ";
        }
        if(array_key_exists('start', $arrReqData) && strlen($arrReqData['start']) > 0 && strlen($arrReqData['end']) > 0 ){
            $strSql.=" AND bet_time >= '".$arrReqData['start']." 0:0:0' AND bet_time <= '".$arrReqData['end']." 23:59:59'" ;                     
        }
        if(array_key_exists('emp_fid', $arrReqData)) {
            $strSql.=" AND bet_emp_fid = '".$arrReqData['emp_fid']."' ";
        }
        // $strSql.=" ORDER BY bet_date DESC, bet_round_no DESC, bet_game DESC, bet_account_time DESC, bet_fid DESC ";
        $strSql.=" ORDER BY bet_fid DESC ";
        if(array_key_exists('max_count', $arrReqData)) {
            $strSql.=" LIMIT 0, ".$arrReqData['max_count']." ";
        }

        $query = $this -> db -> query($strSql);
        $result = $query -> result();
        
        return $result; 

    }


    
    function searchByAgent($objEmp, $arrReqData)
    {
        if(is_null($objEmp)) return null;
        
        $strSql = "SELECT bet_emp_fid, bet_mb_uid, SUM(bet_money) AS bet_money_sum, SUM(bet_win_money) AS bet_win_sum, COUNT(CASE WHEN bet_win_money > 0 THEN 1 END) AS bet_win_count, SUM( bet_empl_amount) AS bet_empl_amount, SUM( bet_agen_amount) AS bet_agen_amount FROM ".$this->mTableName;
        $strSql .= " INNER JOIN MEMBER ON member.mb_uid = bet_powerball.bet_mb_uid AND member.mb_state_delete = 0 ";
        $strSql .= " WHERE bet_emp_fid = ".$objEmp->mb_fid." AND bet_state != ".BET_CANCEL;
        if(strlen($arrReqData['start']) > 0 && strlen($arrReqData['end']) > 0 ){
            $strSql.=" AND bet_time >= '".$arrReqData['start']." 0:0:0' AND bet_time <= '".$arrReqData['end']." 23:59:59'" ;
        }

        $strSql.="  GROUP BY bet_mb_uid ";
        $query = $this -> db -> query($strSql);
        $result = $query -> result();
        
        return $result; 

    }

    
    function searchByCompany($arrReqData)
    {
        $strSql = "SELECT bet_emp_fid, mb_uid, mb_nickname, SUM(bet_money) AS bet_money_sum, SUM(bet_win_money) AS bet_win_sum, COUNT(CASE WHEN bet_win_money > 0 THEN 1 END) AS bet_win_count, SUM( bet_empl_amount) AS bet_empl_amount, SUM( bet_agen_amount) AS bet_agen_amount FROM ".$this->mTableName;
        $strSql .= " INNER JOIN MEMBER ON member.mb_fid = bet_powerball.bet_emp_fid AND member.mb_state_delete = 0 ";
        $strSql .= " WHERE bet_state != ".BET_CANCEL;
        if(strlen($arrReqData['start']) > 0 && strlen($arrReqData['end']) > 0 ){
            $strSql.=" AND bet_time >= '".$arrReqData['start']." 0:0:0' AND bet_time <= '".$arrReqData['end']." 23:59:59'" ;
        }
        $strSql.="  GROUP BY bet_emp_fid ";
        $query = $this -> db -> query($strSql);
        $result = $query -> result();
        
        return $result; 

    }

    function getBetStatist($objUser, $arrRoundData, $gameId = -1){
        $arrBetSum = [0, 0, 0];
        if(is_null($objUser) || is_null($arrRoundData))
            return $arrBetSum;

        //단폴한도
        $strSql = " SELECT SUM(bet_money) AS bet_sum FROM ".$this->mTableName;
        $strSql.= " WHERE bet_state = ".BET_WAIT." AND CHAR_LENGTH(bet_target) = 1 AND bet_mb_uid = '".$objUser->mb_uid."' AND bet_round_no = ".$arrRoundData['round_no'];
        $strSql.= " AND bet_time > '".$arrRoundData['round_date']."' ";
        if($gameId >= 0)
            $strSql.= " AND bet_game = '".$gameId."' ";
        $objResult = $this -> db -> query($strSql)->row();
        if(!is_null($objResult->bet_sum))
            $arrBetSum[0] = $objResult->bet_sum;
        //조합한도
        $strSql = " SELECT SUM(bet_money) AS bet_sum FROM ".$this->mTableName;
        $strSql.= " WHERE bet_state = ".BET_WAIT." AND CHAR_LENGTH(bet_target) > 1 AND bet_mb_uid = '".$objUser->mb_uid."' AND bet_round_no = ".$arrRoundData['round_no'];
        $strSql.= " AND bet_time > '".$arrRoundData['round_date']."' ";
        if($gameId >= 0)
            $strSql.= " AND bet_game = '".$gameId."' ";
        $objResult = $this -> db -> query($strSql)->row();
        if(!is_null($objResult->bet_sum))
            $arrBetSum[1] = $objResult->bet_sum;
        //회차한도
        $strSql = " SELECT SUM(bet_money) AS bet_sum FROM ".$this->mTableName;
        $strSql.= " WHERE bet_state = ".BET_WAIT." AND bet_mb_uid = '".$objUser->mb_uid."' AND bet_round_no = ".$arrRoundData['round_no'];
        $strSql.= " AND bet_time > '".$arrRoundData['round_date']."' ";
        if($gameId >= 0)
            $strSql.= " AND bet_game = '".$gameId."' ";
        $objResult = $this -> db -> query($strSql)->row();
        if(!is_null($objResult->bet_sum))
            $arrBetSum[2] = $objResult->bet_sum;
        
        return $arrBetSum;
        
    }

}
