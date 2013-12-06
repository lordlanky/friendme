<?php

class Icoe extends Toolbox {

    protected $icoe_id;
    protected $fb_id;
    protected $arrUser;
    protected $facebook;

    public function __construct($facebook, $fb_user) {
        parent::__construct();

        #Instantiate the database
        $this->determineDatabase();
        $this->facebook = $facebook;

        #Test if someone is logged in
        {
            if ($fb_user) {

                $this->fb_id = $fb_user;
                $this->icoe_id = $this->determineUserID($this->fb_id, 'FACEBOOK');
            }
        }

        #If we have a logged in user, get their details
        if ($this->icoe_id) {

            $arrUser = $this->getUser($this->icoe_id, "FULL");
            $this->arrUser = $arrUser;
        }
    }

    function showUser() {

        $this->show($this->arrUser);
    }

    function determineDatabase() {

        #what is our server name?
        switch ($this->domain) {

            case "iamtheoptimist.com":
            case "www.iamtheoptimist.com":

                $dbHost = "incase.db.11748806.hostedresource.com";
                $dbName = "incase";
                $dbUser = "incase";
                $dbPass = "ib!8Eighteen";
                break;
        }

        $this->setDatabase($dbHost, $dbUser, $dbPass, $dbName);
    }

    function getUser($intUserID, $strType = "FULL") {

        $arrReturn = array();

        $arrBasic = $this->getBasicUser($intUserID);
        if (!$arrBasic) {

            $this->e[] = "Invalid user id";
            return false;
        }

        $arrReturn['basic'] = $arrBasic;

        #If this is a full request, get everything else too
        if ($strType == "FULL") {

            #Get their proxies
            $arrReturn['proxies'] = $this->getUserProxies($intUserID);

            #Get their emergencies
            $arrReturn['emergencies'] = $this->getUserEmergencies($intUserID);

            #Get their connections
            $arrReturn['connections'] = $this->getUserConnections($intUserID);

            #Get their ignores
            $arrReturn['ignores'] = $this->getUserIgnores($intUserID);
        }

        return $arrReturn;
    }

    function determineUserID($strID, $strType) {

        $sqlStr = "SELECT person_id AS 'answer' FROM people_ids WHERE type = '$strType' AND social_id = '$strID'";
        $arrRes = $this->doSQLRes($sqlStr);
        return $arrRes[0]['answer'];
    }

    function getBasicUser($intUserID) {

        #Test this person is real
        $sqlStr = "SELECT * FROM people WHERE person_id = $intUserID";
        $arrRes = $this->doSQLRes($sqlStr);

        if (count($arrRes) != 1) {

            return false;
        }

        return $arrRes[0];
    }

    function getUserProxies($intUserID) {

        $sqlStr = "SELECT * FROM proxies WHERE patient_id = $intUserID";
        $sqlRes = $this->doSQL($sqlStr);
        $arrRes = $this->sqlToArr($sqlRes, array('level1' => 'person_id'));

        if (count($arrRes) < 1) {

            return false;
        }

        return $arrRes;
    }

    function getUserEmergencies($intUserID) {

        $sqlStr = "SELECT * FROM emergencies WHERE emergency_id IN "
                . "(SELECT emergency_id FROM emergency_patients WHERE patient_id = $intUserID"
                . ")";
        $sqlRes = $this->doSQL($sqlStr);

        $arrParams = array(
            "level1" => "emergency_id"
        );

        $arrRes = $this->sqlToArr($sqlRes, $arrParams);

        return $arrRes;
    }

    function getUserConnections($intUserID) {

        $sqlStr = "SELECT * FROM connections WHERE patient_id = $intUserID";
        $sqlRes = $this->doSQL($sqlStr);
        $arrRes = $this->sqlToArr($sqlRes, array('level1' => 'person_id'));

        if (count($arrRes) < 1) {

            return false;
        }

        return $arrRes;
    }

    function getUserIgnores($intUserID) {

        $sqlStr = "SELECT * FROM ignores WHERE patient_id = $intUserID";
        $sqlRes = $this->doSQL($sqlStr);

        $arrParams = array(
            "level1" => "type",
            "target" => "social_id"
        );

        $arrRes = $this->sqlToArr($sqlRes, $arrParams);

        if (count($arrRes) < 1) {

            return false;
        }

        return $arrRes;
    }

    #Create a comparison list of all publicly known connections to those not yet connected or processed with ICO

    function compareConnections() {
        
        $arrCompare = array();

        #Create a simplified array
        $arrCompareBase = array(
            "proxies" => array(),
            "alerts" => array(),
            "ignores" => array(),
            "unknown" => array()
        );

        #Compare Facebook
        if ($this->fb_id) {
            
            $arrCompare['facebook'] = $arrCompareBase;

            #Get all the Facebook IDs of friends
            $fbFriends = $this->facebook->api('/me/friends');
            
            $arrFBIds = array();

            foreach ($fbFriends['data'] AS $arrFriend) {

                $arrFBIds[] = $arrFriend['id'];
            }

            #Create the filtered list
            $strWhere = $this->createSQLIn($arrFBIds, "people_ids", "social_id", "IN", "WHERE");
            $strWhere .= " AND type = 'FACEBOOK'";
            
            #Determine if any are proxies
            $sqlStr = 
        }
    }

    function createSocialIDList($user_id = false) {

        if ($user_id) {

            $arrUser = $this->getUser($intUserID, $strType);
        } else {

            $user_id = $this->icoe_id;
            $arrUser = $this->arrUser;
        }

        $arrProxies = array_keys($arrUser['proxies']);
        $arrCons = array_keys($arrUser['connections']);

        $arrIDsAll = array_merge($arrCons, $arrProxies);
        $arrIDs = array_filter($arrIDsAll);

        $strWhere = $this->createSQLIn($arrIDs, "people_ids", "person_id", "IN", "WHERE");
        $sqlStr = "SELECT social_id, person_id, type FROM people_ids $strWhere";
        $sqlRes = $this->doSQL($sqlStr);
        $arrRes = $this->sqlToArr($sqlRes, array("level1" => "person_id", "level2" => "type", "target" => "social_id"));

        return $arrRes;
    }

}

?>
