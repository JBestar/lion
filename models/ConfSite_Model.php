<?php

class ConfSite_Model {

	private $mDbConn ;
	private $mTableName ;
	
	function __construct($dbConn)
	{
		$this->mDbConn = $dbConn;
		$this->mTableName = "conf_site";
	

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



    public function getBetSite(){

        $nConfigId = 11;
        $arrSiteInfo = ["", "", "", 0, 0, 0];

        $strSql = "SELECT * FROM ".$this->mTableName;
    	$strSql.= " WHERE conf_id = '".$nConfigId."' ";
    	
    	$arrConfig = null;
    	if($objResult = $this->mDbConn->query($strSql)){
	    	if ($objResult->num_rows > 0) {
			  	while($arrRow = $objResult->fetch_assoc()) {
			    	$arrConfig = $arrRow;
			    	break;
		  		}
			}
			$objResult->free();
		}
		
		if(!is_null($arrConfig)){
            $strSite = $arrConfig['conf_content'];
            $arrInfo = explode('/', $strSite);
            if(count($arrInfo) == 6){
                
                $arrSiteInfo = $arrInfo;
                
            }
        }
        return $arrSiteInfo;
    }


    public function getLiveConf(){

        $nConfigId = 13;
        $arrLiveInfo = ["", 0];

        $strSql = "SELECT * FROM ".$this->mTableName;
    	$strSql.= " WHERE conf_id = '".$nConfigId."' ";
    	
    	$arrConfig = null;
    	if($objResult = $this->mDbConn->query($strSql)){
	    	if ($objResult->num_rows > 0) {
			  	while($arrRow = $objResult->fetch_assoc()) {
			    	$arrConfig = $arrRow;
			    	break;
		  		}
			}
			$objResult->free();
		}
		
		if(!is_null($arrConfig)){
            $arrLiveInfo[0] = trim($arrConfig['conf_content']);
            $arrLiveInfo[1] = $arrConfig['conf_active'];

        }
        return $arrLiveInfo;
    }


    

}

?>