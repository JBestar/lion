<?php

class ConfGame_Model {

	private $mDbConn ;
	private $mTableName ;
	
	function __construct($dbConn)
	{
		$this->mDbConn = $dbConn;
		$this->mTableName = "conf_game";
	

	}

	public function getByIndex($strIndex){
		
        $strSql = "SELECT * FROM ".$this->mTableName;
    	$strSql.= " WHERE game_index = '".$strIndex."' ";
    	
    	$objConfig = null;
    	if($objResult = $this->mDbConn->query($strSql)){
	    	if ($objResult->num_rows > 0) {
			  	while($arrRow = $objResult->fetch_assoc()) {
			    	$objConfig = (object)$arrRow;
			    	break;
		  		}
			}
			$objResult->free();
		}
		return $objConfig;
    }


    

}

?>