<?php
class MoneyHistory_model extends CI_Model {
	
	private $mTableName ;
    private $mMemberTable ;

    function __construct()
    {
        parent::__construct();

        $this->mTableName = "money_history";
        $this->mMemberTable = "member";
    }

    
    function registerGiveCharge($objUser, $nChargeMoney)
    {
        if(is_null($objUser)) return false;    
        if($nChargeMoney < 1) return false;

        $this->db->set('money_mb_fid', $objUser->mb_fid);
        $this->db->set('money_mb_uid', $objUser->mb_uid);
        $this->db->set('money_mb_emp_fid', $objUser->mb_emp_fid);   
        if(isset($objUser->mb_emp_uid))
            $this->db->set('money_mb_ech_uid', $objUser->mb_emp_uid);   
        $this->db->set('money_amount', $nChargeMoney);
        $this->db->set('money_before', $objUser->mb_money);
        $this->db->set('money_after', $objUser->mb_money+$nChargeMoney);
        $this->db->set('money_change_type', MONEYCHANGE_PRESENT);     //직충전일때
        $this->db->set('money_update_time', 'NOW()', false);
        
        return $this->db->insert($this->mTableName);
    }
    
    function registerCharge($objUser, $nChargeMoney)
    {
        if(is_null($objUser)) return false;    
        if($nChargeMoney < 1) return false;

        $this->db->set('money_mb_fid', $objUser->mb_fid);
        $this->db->set('money_mb_uid', $objUser->mb_uid);
        $this->db->set('money_mb_emp_fid', $objUser->mb_emp_fid); 
        if(isset($objUser->mb_emp_uid))
            $this->db->set('money_mb_ech_uid', $objUser->mb_emp_uid);         
        $this->db->set('money_amount', $nChargeMoney);
        $this->db->set('money_before', $objUser->mb_money);
        $this->db->set('money_after', $objUser->mb_money+$nChargeMoney);
        $this->db->set('money_change_type', MONEYCHANGE_CHARGE);     //충전일때
        $this->db->set('money_update_time', 'NOW()', false);
        
        return $this->db->insert($this->mTableName);
    }


    function registerDischarge($objUser, $nExchangeMoney)
    {
        if(is_null($objUser)) return false;    
        if($nExchangeMoney < 1) return false;

        $this->db->set('money_mb_fid', $objUser->mb_fid);
        $this->db->set('money_mb_uid', $objUser->mb_uid);
        $this->db->set('money_mb_emp_fid', $objUser->mb_emp_fid);   
        if(isset($objUser->mb_emp_uid))
            $this->db->set('money_mb_ech_uid', $objUser->mb_emp_uid);       
        $this->db->set('money_amount', (-1) * $nExchangeMoney);
        $this->db->set('money_before', $objUser->mb_money);
        $this->db->set('money_after', $objUser->mb_money-$nExchangeMoney);
        $this->db->set('money_change_type', MONEYCHANGE_EXCHANGE);     //환전일때
        $this->db->set('money_update_time', 'NOW()', false);
        
        return $this->db->insert($this->mTableName);
    }


        
    
    function registerGiveChargeFrom($objUser, $nChargeMoney)
    {
        if(is_null($objUser)) return false;    
        if($nChargeMoney < 1) return false;

        $this->db->set('money_mb_fid', $objUser->mb_fid);
        $this->db->set('money_mb_uid', $objUser->mb_uid);
        $this->db->set('money_mb_emp_fid', $objUser->mb_emp_fid);   
        if(isset($objUser->mb_emp_uid))
            $this->db->set('money_mb_ech_uid', $objUser->mb_emp_uid);   
        $this->db->set('money_amount', (-1) *$nChargeMoney);
        $this->db->set('money_before', $objUser->mb_money);
        $this->db->set('money_after', $objUser->mb_money-$nChargeMoney);
        $this->db->set('money_change_type', MONEYCHANGE_PRESENT_FROM);     //직충전송금
        $this->db->set('money_update_time', 'NOW()', false);
        
        return $this->db->insert($this->mTableName);
    }

        
    function registerChargeFrom($objUser, $nChargeMoney)
    {
        if(is_null($objUser)) return false;    
        if($nChargeMoney < 1) return false;

        $this->db->set('money_mb_fid', $objUser->mb_fid);
        $this->db->set('money_mb_uid', $objUser->mb_uid);
        $this->db->set('money_mb_emp_fid', $objUser->mb_emp_fid); 
        if(isset($objUser->mb_emp_uid))
            $this->db->set('money_mb_ech_uid', $objUser->mb_emp_uid);         
        $this->db->set('money_amount', (-1) * $nChargeMoney);
        $this->db->set('money_before', $objUser->mb_money);
        $this->db->set('money_after', $objUser->mb_money-$nChargeMoney);
        $this->db->set('money_change_type', MONEYCHANGE_CHARGE_FROM);     //충전 송금
        $this->db->set('money_update_time', 'NOW()', false);
        
        return $this->db->insert($this->mTableName);
    }

    function registerDischargeFrom($objUser, $nExchangeMoney)
    {
        if(is_null($objUser)) return false;    
        if($nExchangeMoney < 1) return false;

        $this->db->set('money_mb_fid', $objUser->mb_fid);
        $this->db->set('money_mb_uid', $objUser->mb_uid);
        $this->db->set('money_mb_emp_fid', $objUser->mb_emp_fid);   
        if(isset($objUser->mb_emp_uid))
            $this->db->set('money_mb_ech_uid', $objUser->mb_emp_uid);       
        $this->db->set('money_amount', $nExchangeMoney);
        $this->db->set('money_before', $objUser->mb_money);
        $this->db->set('money_after', $objUser->mb_money+$nExchangeMoney);
        $this->db->set('money_change_type', MONEYCHANGE_EXCHANGE_TO);     //환전입금
        $this->db->set('money_update_time', 'NOW()', false);
        
        return $this->db->insert($this->mTableName);
    }
    
    function registerCancelBet($objUser, $objBet)
    {
        if(is_null($objUser)) return false;    

        $this->db->set('money_mb_fid', $objUser->mb_fid);
        $this->db->set('money_mb_uid', $objUser->mb_uid);
        $this->db->set('money_mb_emp_fid', $objUser->mb_emp_fid);   
        $this->db->set('money_amount', $objBet->bet_money);
        $this->db->set('money_before', $objUser->mb_money);
        $this->db->set('money_after', $objUser->mb_money-$objBet->bet_money);
        $this->db->set('money_change_type', MONEYCHANGE_CANCEL);     //베팅취소
        $this->db->set('money_bet_round', $objBet->bet_round_no);     
        $this->db->set('money_bet_mode', $objBet->bet_mode);     
        $this->db->set('money_bet_target', $objBet->bet_target);     
        $this->db->set('money_update_time', 'NOW()', false);
        
        return $this->db->insert($this->mTableName);
    }

    function registerMoneyAcc($objMember, $objBet)
    {
        $money_amount = $objBet->bet_win_money;
        
        $this->db->set('money_mb_fid', $objMember->mb_fid);
        $this->db->set('money_mb_uid', $objMember->mb_uid);
        $this->db->set('money_mb_emp_fid', $objMember->mb_emp_fid);
        $this->db->set('money_amount', $money_amount);
        $this->db->set('money_before', $objMember->mb_money);
        $this->db->set('money_after', $objMember->mb_money + $money_amount );
        $this->db->set('money_change_type', MONEYCHANGE_WIN); 

        $this->db->set('money_bet_round', $objBet->bet_round_no);
        $this->db->set('money_bet_mode', $objBet->bet_mode);
        $this->db->set('money_bet_target', $objBet->bet_target);     
        $this->db->set('money_update_time', $objBet->bet_account_time);
        
        return $this->db->insert($this->mTableName);

    }
    //충환전내역
    function getTransferByAgent($objEmp, $arrReqData)
    {
        if(is_null($objEmp)) return null;
        
        $strSql = " SELECT money_mb_fid, money_mb_uid ";
        $strSql.= ", SUM(CASE WHEN money_change_type = 0 THEN money_amount END) AS money_give ";
        $strSql.= ", SUM(CASE WHEN money_change_type = 1 THEN money_amount END) AS money_charge ";
        $strSql.= ", SUM(CASE WHEN money_change_type = 2 THEN money_amount END) AS money_discharge ";
        $strSql.= ", SUM(CASE WHEN money_change_type = 3 THEN money_amount END) AS money_mileage ";
        $strSql.= " FROM ".$this->mTableName;
        $strSql.= " INNER JOIN MEMBER ON member.mb_uid = money_history.money_mb_uid AND member.mb_state_delete = 0 ";
        $strSql .= " WHERE money_mb_emp_fid = ".$objEmp->mb_fid." AND money_change_type < 4";
        if(strlen($arrReqData['start']) > 0 && strlen($arrReqData['end']) > 0 ){
            $strSql.=" AND money_update_time >= '".$arrReqData['start']." 0:0:0' AND money_update_time <= '".$arrReqData['end']." 23:59:59'" ;
        }

        $strSql.="  GROUP BY money_mb_uid ";
        $query = $this -> db -> query($strSql);
        $result = $query -> result();
        
        return $result; 

    }

    //충환전내역
    function getTranferByUid($arrReqData){
        $strSql = " SELECT money_fid, money_mb_fid, money_mb_uid, money_mb_emp_fid, money_mb_ech_uid, money_amount, money_before, money_change_type, money_update_time, mb_money FROM ".$this->mTableName;
        $strSql.= " INNER JOIN MEMBER ON member.mb_uid = money_history.money_mb_uid AND member.mb_state_delete = 0 ";
        $strSql .= " WHERE money_mb_uid = '".$arrReqData['mb_uid']."' ";
        if(isset($arrReqData['type']))
            $strSql .= " AND money_change_type < ".$arrReqData['type'];
        else $strSql .= " AND money_change_type < 4 ";

        if(strlen($arrReqData['emp_fid']) > 0)
            $strSql .= " AND money_mb_emp_fid = '".$arrReqData['emp_fid']."' ";
        if(strlen($arrReqData['start']) > 0 && strlen($arrReqData['end']) > 0 ){
            $strSql.=" AND money_update_time >= '".$arrReqData['start']." 0:0:0' AND money_update_time <= '".$arrReqData['end']." 23:59:59'" ;
        }

        $strSql.="  ORDER BY money_fid DESC ";
        if(array_key_exists('max_count', $arrReqData)){
            $strSql.="  LIMIT 0, ".$arrReqData['max_count'];
        }

        $query = $this -> db -> query($strSql);
        $result = $query -> result();
        
        return $result; 
    }

    //거래내역
    function getExchangeByUid($arrReqData){
        $strSql = " SELECT money_fid, money_mb_fid, money_mb_uid, money_mb_emp_fid, money_mb_ech_uid, money_amount, money_before, money_change_type, money_update_time, mb_money FROM ".$this->mTableName;
        $strSql.= " INNER JOIN MEMBER ON member.mb_uid = money_history.money_mb_uid AND member.mb_state_delete = 0 ";
        $strSql .= " WHERE money_mb_uid = '".$arrReqData['mb_uid']."' ";
        
        $strSql .= " AND (money_change_type < 4 OR money_change_type > 9 )";
        
        if(strlen($arrReqData['start']) > 0 && strlen($arrReqData['end']) > 0 ){
            $strSql.=" AND money_update_time >= '".$arrReqData['start']." 0:0:0' AND money_update_time <= '".$arrReqData['end']." 23:59:59'" ;
        }

        $strSql.="  ORDER BY money_fid DESC ";
        if(array_key_exists('max_count', $arrReqData)){
            $strSql.="  LIMIT 0, ".$arrReqData['max_count'];
        }
        $query = $this -> db -> query($strSql);
        $result = $query -> result();
        
        return $result; 
    }






}