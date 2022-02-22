<?php

class PballRound_Model {

	private $mDbConn ;
	private $mTableName ;
	

	function __construct($dbConn)
	{
		$this->mDbConn = $dbConn;
		$this->mTableName = "round_powerball";
		
	}

	public function getByFid($nRoundFid){

    	$strSql = "SELECT * FROM ".$this->mTableName;
    	$strSql.= " WHERE round_fid = '".$nRoundFid."' ";
    	
    	$arrResult = null;
    	if($objResult = $this->mDbConn->query($strSql)){
	    	if ($objResult->num_rows > 0) {
			  	while($arrRow = $objResult->fetch_assoc()) {
			    	$arrResult = $arrRow;
			  	}
			}
			$objResult->free();
		}
		return $arrResult;
    }
	

    public function getByDate($nRoundNo, $strDate){

    	$strSql = "SELECT * FROM ".$this->mTableName;
    	$strSql.= " WHERE round_num = '".$nRoundNo."' ";
    	$strSql.= " AND round_date = '".$strDate."' ";

    	$arrResult = null;
    	if($objResult = $this->mDbConn->query($strSql)){
    	
	    	if ($objResult->num_rows > 0) {
			  	while($arrRow = $objResult->fetch_assoc()) {
			    	$arrResult = $arrRow;
			  	}
			}
			$objResult->free();
		}
		return $arrResult;
    }
	

	public function getLast(){
		$strSql = "SELECT * FROM ".$this->mTableName;
    	$strSql.= " ORDER BY round_fid DESC LIMIT 1"; 

    	$objResult = $this->mDbConn->query($strSql);

    	$arrResult = null;
    	if($objResult = $this->mDbConn->query($strSql)){
	    	if ($objResult->num_rows > 0) {
			  	while($arrRow = $objResult->fetch_assoc()) {
			    	$arrResult = $arrRow;
		  		}
			}
			$objResult->free();
		}
		return $arrResult;
	}



}


?>