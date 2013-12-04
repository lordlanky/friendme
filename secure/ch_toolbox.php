<?php

################################################################################
# TOOLBOX
################################################################################

class Toolbox {

    private $db;
    private $e;

    /*
     * This function will round down a number to x decimal places
     */

    function floorp($flVal, $intDP) {

        $res = floor($flVal * (10 * $intDP)) / (10 * $intDP);

        return $res;
    }

    /*
     * This function will send an email in HTML format
     * $arrTo = ARRAY of email addresses to send to (accepts a string)
     * $strToRedirect = STRING of email addresses to redirect the email to - used for testing
     */

    function sendEmail($strBody, $strSubject, $arrTo, $strFrom, $strToRedirect = false) {

        $headers = "From: " . strip_tags($strFrom) . "\r\n";
        $headers .= "Reply-To: " . strip_tags($strFrom) . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
        $message = '<html><body>' . $strBody . "</body></html>";

        if (is_string($arrTo)) {

            $arrTo = array(
                $arrTo
            );
        }

        foreach ($arrTo AS $strTo) {

            #Modify the to if it's not a live environment
            if ($strToRedirect) {

                if (strstr($strToRedirect, ",")) {

                    $arrRedirects = explode(",", $strToRedirect);
                } else {

                    $arrRedirects = array($strToRedirect);
                }

                foreach ($arrRedirects AS $strRed) {

                    $strSubjectRed = "SENT TO: " . $strTo . "  --  " . $strSubject;
                    mail($strRed, $strSubjectRed, $message, $headers);
                }
            } else {

                mail($strTo, $strSubject, $message, $headers);
            }
        }
    }

    # -------------------------------------- FUNCTION -
    # Convert SQL results to PHP array
    # -------------------------------------- FUNCTION -

    function sqlToArr($sqlRes, $arrParams = false) {

        $intCount = mysql_num_rows($sqlRes);

        if ($intCount < 1) {

            return false;
        }

        #Create the final array
        $arrFinal = array();
        $varName = "arrFinal";

        #Rewind the pointer
        mysql_data_seek($sqlRes, 0);

        #If there is a target field (i.e. make the last dimension a value and not an array), set final value variable variable
        if ($arrParams['target']) {

            $varTarget = "row[\$arrParams['target']]";
        } else {

            $varTarget = "row";
        }

        for ($i = 0; $i < $intCount; $i++) {

            $row = mysql_fetch_assoc($sqlRes);

            #Destruct the array by stripping out data that intends to be a level
            if ($arrParams['level1']) {

                #Get the level 1 value
                $dim1 = $row[$arrParams['level1']];
                unset($row[$arrParams['level1']]);
            }

            if ($arrParams['level2']) {

                #Get the level 2 value
                $dim2 = $row[$arrParams['level2']];
                unset($row[$arrParams['level2']]);
            }

            if ($arrParams['level3']) {

                #Get the level 3 value
                $dim3 = $row[$arrParams['level3']];
                unset($row[$arrParams['level3']]);
            }

            #Reconstruct the array into appropriate levels
            if ($arrParams['level3']) {

                #If there is a target
                if ($arrParams['target']) {

                    ${$varName}{$dim1}{$dim2}{$dim3}[] = $row[$arrParams['target']];
                } else {

                    ${$varName}{$dim1}{$dim2}{$dim3}[] = $$varTarget;
                }
            } else if ($arrParams['level2']) {

                #If there is a target
                if ($arrParams['target']) {

                    ${$varName}{$dim1}{$dim2}[] = $row[$arrParams['target']];
                } else {

                    ${$varName}{$dim1}{$dim2}[] = $$varTarget;
                }
            } else if ($arrParams['level1']) {

                #If there is a target
                if ($arrParams['target']) {

                    #array_push(${$varName}{$dim1}, $row[$arrParams['target']]);
                    ${$varName}{$dim1}[] = $row[$arrParams['target']];
                } else {

                    #array_push(${$varName}{$dim1}, $$varTarget);
                    ${$varName}{$dim1}[] = $$varTarget;
                }
            } else {

                #If there is a target
                if ($arrParams['target']) {

                    #array_push(${$varName}, $row[$arrParams['target']]);
                    ${$varName}[] = $row[$arrParams['target']];
                } else {

                    #array_push(${$varName}, $$varTarget);
                    ${$varName}[] = $$varTarget;
                }
            }
        }

        return $arrFinal;
    }

    function makeDBSafe($strIn) {

        $strOut = mysql_real_escape_string($strIn, $this->$db);
        return $strOut;
    }

    /*
     * Quickly connect to a database
     */

    function setDatabase($dbHost, $dbUser, $dbPass, $dbName) {

        $this->db = mysql_connect($dbHost, $dbUser, $dbPass) OR DIE("Unable to 
            connect to database! Please try again later.");
        mysql_select_db($dbName);
    }

    /*
     * This function will do inserts (one or many)
     */

    function autoInsert($strTable, $arrInserts) {

        $arrProc = array();

        #Check if this is a multi-array. if not, force it so we can use the sae code
        if (!is_array($arrInserts[0])) {

            $arrProc[] = $arrInserts;
        } else {

            $arrProc = $arrInserts;
        }

        $intInsert = 0;
        foreach ($arrProc AS $arrInsert) {

            $sqlStrStart = "INSERT INTO $strTable (";
            $sqlStrEnd = " VALUES (";

            $intCol = 0;

            foreach ($arrInsert AS $strField => $strVal) {

                $arrInsertIDs = array();

                #If there is no value, continue
                if (($strVal === false) || ($strVal == '') || (is_null($strVal))) {

                    continue;
                }

                if ($intCol > 0) {

                    $sqlStrStart .= ", ";
                    $sqlStrEnd .= ", ";
                }

                $strVal = $this->makeDBSafe($strVal);
                $sqlStrStart .= " $strField";
                $sqlStrEnd .= " '$strVal'";

                $intCol++;
            }

            $sqlStr = $sqlStrStart . ") " . $sqlStrEnd . ")";
            $this->doSQL($sqlStr);

            $arrInsertIDs[$intInsert] = mysql_insert_id($this->db);
            $intInsert++;
        }

        return $arrInsertIDs;
    }

    /*
     * This function will create a UUID
     */

    function UUID() {

        $sqlStr = 'SELECT UUID() AS answer FROM DUAL';
        $arrRes = $this->doSQLRes($sqlStr);
        $uuid = $arrRes[0]['answer'];

        return $uuid;
    }

    /*
     * This function will return a true or false if the query returns results
     */

    function checkSQL($strTable, $strWhere) {

        $sqlStr = "SELECT 1 FROM $strTable WHERE $strWhere LIMIT 1";
        $sqlRes = $this->doSQL($sqlStr);
        $intRows = mysql_num_rows($sqlRes);

        if ($intRows != 1) {

            return false;
        }

        return true;
    }

    /*
     * This function will perform a query and ten return the php array result
     */

    function doSQLRes($sqlStr) {

        $sqlRes = doSQL($sqlStr);
        $arrRes = sql2arr($sqlRes);

        return $arrRes;
    }

    /*
     * Create a prettier output of text
     */

    function show($strText, $strHeader = false) {

        if ($strHeader) {

            echo "<h2> $strHeader </h2>";
        }

        echo "<pre>";
        $str = print_r($strText, 1);
        echo $str;
        echo "</pre>";
    }

    /*
     * This function will do a sql query
     */

    function doSQL($sqlStr, $blTrace = true) {

        $sqlRes = mysql_query($sqlStr, $this->db) or die(showTrace(mysql_error($this->db), $blTrace));

        return $sqlRes;
    }

    function showTrace($sqlErr, $blTrace = true) {

        if ($blTrace) {
            $arrTrace = debug_backtrace();
            $arrTrace['POST'] = $_POST;
            $arrTrace['GET'] = $_GET;
            $arrTrace['SESSION'] = $_SESSION;
            $arrTrace['COOKIES'] = $_COOKIE;
            show($arrTrace);
        }
    }

    /*
     * Test if an email address seems valid
     */

    function testEmail($strEmail) {

        if (filter_var($strEmail, FILTER_VALIDATE_EMAIL)) {
            return true;
        }
        return false;
    }

    # -------------------------------------- FUNCTION -
    # Make a string safe for mysql injection etc.
    # -------------------------------------- FUNCTION -

    function secureString($strIn, $blScriptOut = true) {

        if (is_array($strIn)) {

            foreach ($strIn as $key => $value) {

                $strIn[$key] = secureString($value);
            }
        }

        $strIn = preg_replace("#<script[^>]*>.*?</script[^>]*>#is", "", $strIn);
        $strIn = preg_replace("#<script[^>]*>#i", "", $strIn);
        $strIn = (get_magic_quotes_gpc()) ? (stripslashes($strIn)) : ($strIn);
        $strIn = htmlspecialchars($strIn);
        $strIn = urlencode($strIn);

        return $strIn;
    }

    # -------------------------------------- FUNCTION -
    # Remove mysql safety from a string
    # -------------------------------------- FUNCTION -

    function unsecureString($strin, $blKeepHTML = false) {

        if (is_array($strIn)) {

            foreach ($strIn as $key => $value) {

                $strIn[$key] = unsecureString($value);
            }
        }

        $strIn = urldecode($strIn);
        if (!blKeepHTML) {

            $strIn = htmlspecialchars_decode($strIn);
        }

        return $strIn;
    }

}

?>
