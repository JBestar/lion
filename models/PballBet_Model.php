<?php

class PballBet_Model {

	private $mDbConn ;
	private $mTableName ;
	private $mMemberTable;

	function __construct($dbConn)
	{
		$this->mDbConn = $dbConn;
		$this->mTableName = "bet_powerball";
        $this->mMemberTable = "member";

	}



    public function getWaits($objRoundInfo, $gameId){
    	$strSql = "SELECT * FROM ".$this->mTableName;
    	$strSql.= " WHERE bet_state = '1' ";
    	$strSql.= " AND bet_round_no = '".$objRoundInfo['round_no']."' ";
    	$strSql.= " AND bet_time >= '".$objRoundInfo['round_start']."' ";
    	$strSql.= " AND bet_time <= '".$objRoundInfo['round_end']."' ";
        $strSql.= " AND bet_game = '".$gameId."' ";
        $strSql.= " ORDER BY bet_mb_uid, bet_time ASC ";
    	$arrResult = array();
    	if($objResult = $this->mDbConn->query($strSql)){
	    	if ($objResult->num_rows > 0) {
			  	while($arrRow = $objResult->fetch_assoc()) {
			    	array_push($arrResult, $arrRow);
		  		}
			}
			$objResult->free();
		}
		return $arrResult;

    }



    function updateBetRound($objRoundInfo, &$objBetInfo, &$nBeforeMoney)
    {
        $nWinMoney = 0;
        $isWin = false;
        if($objRoundInfo->round_state == 0)
            return false;
        //bet_state=2:Betting-loss 3:Betting-Earn 
        if($objBetInfo->bet_mode == "1" || $objBetInfo->bet_mode == "2"){

            $objBetInfo->bet_result = $objRoundInfo->round_result_1;
            if($objBetInfo->bet_target == $objRoundInfo->round_result_1){
                $isWin = true;                            
            }
        } else if($objBetInfo->bet_mode == "3" || $objBetInfo->bet_mode == "4"){
            $objBetInfo->bet_result = $objRoundInfo->round_result_2;
            if($objBetInfo->bet_target == $objRoundInfo->round_result_2){
                $isWin = true;
            }
        } else if($objBetInfo->bet_mode == "5"){
            $objBetInfo->bet_result = $objRoundInfo->round_result_1.$objRoundInfo->round_result_2;
            if($objRoundInfo->round_result_1 == 'P' && $objRoundInfo->round_result_2 == 'P' ){
                $isWin = true;
            }
        } else if($objBetInfo->bet_mode == "6"){
            $objBetInfo->bet_result = $objRoundInfo->round_result_1.$objRoundInfo->round_result_2;
            if($objRoundInfo->round_result_1 == 'B' && $objRoundInfo->round_result_2 == 'P' ){
                $isWin = true;
            }
        }  else if($objBetInfo->bet_mode == "7"){
            $objBetInfo->bet_result = $objRoundInfo->round_result_1.$objRoundInfo->round_result_2;
            if($objRoundInfo->round_result_1 == 'P' && $objRoundInfo->round_result_2 == 'B' ){
                $isWin = true;
            }
        } else if($objBetInfo->bet_mode == "8"){
            $objBetInfo->bet_result = $objRoundInfo->round_result_1.$objRoundInfo->round_result_2;
            if($objRoundInfo->round_result_1 == 'B' && $objRoundInfo->round_result_2 == 'B' ){
                $isWin = true;
            }
        } else if($objBetInfo->bet_mode == "9" || $objBetInfo->bet_mode == "10"){
            $objBetInfo->bet_result = $objRoundInfo->round_result_3;
            if($objBetInfo->bet_target == $objRoundInfo->round_result_3){
                $isWin = true;
            }
        } else if($objBetInfo->bet_mode == "11" || $objBetInfo->bet_mode == "12"){
            $objBetInfo->bet_result = $objRoundInfo->round_result_4;
            if($objBetInfo->bet_target == $objRoundInfo->round_result_4){
                $isWin = true;
            }
        } else if($objBetInfo->bet_mode == "13"){
            $objBetInfo->bet_result = $objRoundInfo->round_result_3.$objRoundInfo->round_result_4;
            if($objRoundInfo->round_result_3 == 'P' && $objRoundInfo->round_result_4 == 'P' ){
                $isWin = true;
            }
        } else if($objBetInfo->bet_mode == "14"){
            $objBetInfo->bet_result = $objRoundInfo->round_result_3.$objRoundInfo->round_result_4;
            if($objRoundInfo->round_result_3 == 'B' && $objRoundInfo->round_result_4 == 'P' ){
                $isWin = true;
            }
        }  else if($objBetInfo->bet_mode == "15"){
            $objBetInfo->bet_result = $objRoundInfo->round_result_3.$objRoundInfo->round_result_4;
            if($objRoundInfo->round_result_3 == 'P' && $objRoundInfo->round_result_4 == 'B' ){
                $isWin = true;
            }
        } else if($objBetInfo->bet_mode == "16"){
            $objBetInfo->bet_result = $objRoundInfo->round_result_3.$objRoundInfo->round_result_4;
            if($objRoundInfo->round_result_3 == 'B' && $objRoundInfo->round_result_4 == 'B' ){
                $isWin = true;
            }
        } else if($objBetInfo->bet_mode == "17" || $objBetInfo->bet_mode == "18" || $objBetInfo->bet_mode == "19"){
            $objBetInfo->bet_result = $objRoundInfo->round_result_5;
            if($objBetInfo->bet_target == $objRoundInfo->round_result_5){
                $isWin = true;
            }
        } else if($objBetInfo->bet_mode == "20"){
            $objBetInfo->bet_result = $objRoundInfo->round_result_3.$objRoundInfo->round_result_5;
            if($objRoundInfo->round_result_3 == 'P' && $objRoundInfo->round_result_5 == 'L' ){
                $isWin = true;
            }
        } else if($objBetInfo->bet_mode == "21"){
            $objBetInfo->bet_result = $objRoundInfo->round_result_3.$objRoundInfo->round_result_5;
            if($objRoundInfo->round_result_3 == 'P' && $objRoundInfo->round_result_5 == 'M' ){
                $isWin = true;
            }
        } else if($objBetInfo->bet_mode == "22"){
            $objBetInfo->bet_result = $objRoundInfo->round_result_3.$objRoundInfo->round_result_5;
            if($objRoundInfo->round_result_3 == 'P' && $objRoundInfo->round_result_5 == 'S' ){
                $isWin = true;
            }
        } else if($objBetInfo->bet_mode == "23"){
            $objBetInfo->bet_result = $objRoundInfo->round_result_3.$objRoundInfo->round_result_5;
            if($objRoundInfo->round_result_3 == 'B' && $objRoundInfo->round_result_5 == 'L' ){
                $isWin = true;
            }
        } else if($objBetInfo->bet_mode == "24"){
            $objBetInfo->bet_result = $objRoundInfo->round_result_3.$objRoundInfo->round_result_5;
            if($objRoundInfo->round_result_3 == 'B' && $objRoundInfo->round_result_5 == 'M' ){
                $isWin = true;
            }
        } else if($objBetInfo->bet_mode == "25"){
            $objBetInfo->bet_result = $objRoundInfo->round_result_3.$objRoundInfo->round_result_5;
            if($objRoundInfo->round_result_3 == 'B' && $objRoundInfo->round_result_5 == 'S' ){
                $isWin = true;
            }
        }
        else return false;

        if($nBeforeMoney < (int)$objBetInfo->bet_money){
            $nBeforeMoney = $objBetInfo->bet_before_money;
        }

        if($isWin){
            $objBetInfo->bet_state = 3;
            //bet_win_money
            $nWinMoney = $objBetInfo->bet_money * $objBetInfo->bet_ratio;
            $objBetInfo->bet_win_money = (int)$nWinMoney;
            //user_after_money
            $objBetInfo->bet_after_money = $nBeforeMoney + $objBetInfo->bet_win_money - $objBetInfo->bet_money;
            
            
        } else {
            $objBetInfo->bet_state = 2;
            $objBetInfo->bet_win_money = 0;
            //user_after_money
            $objBetInfo->bet_after_money = $nBeforeMoney - $objBetInfo->bet_money;
            
        }


        $strSql = "UPDATE ".$this->mTableName." SET ";
		$strSql.= " bet_state = '".$objBetInfo->bet_state."', ";	
		$strSql.= " bet_result = '" .$objBetInfo->bet_result."', ";
		$strSql.= " bet_win_money = '" .$objBetInfo->bet_win_money."', ";
		$strSql.= " bet_account_time = NOW(), ";
        $strSql.= " bet_after_money = '" .$objBetInfo->bet_after_money."' ";
		$strSql.= " WHERE bet_fid = '".$objBetInfo->bet_fid."' ";

        $bResult = $this->mDbConn->query($strSql);
        if($bResult)
            $nBeforeMoney = $objBetInfo->bet_after_money;
        
        return $bResult;
    }




    




}


?>