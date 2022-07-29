<?php

class ConfSite_Model {

	private $mDbConn ;
	private $mTableName ;
	
	function __construct($dbConn)
	{
		$this->mDbConn = $dbConn;
		$this->mTableName = "conf_site";
	

	}

	public function getById($strIndex){
		
        $strSql = "SELECT * FROM ".$this->mTableName;
    	$strSql.= " WHERE conf_id = '".$strIndex."' ";
    	
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