<?php

#Load all preloading variables
include "includes/inc_preLoad.php";

#Load the interface start
include "includes/inc_interfaceLoad.php";

/*
 * Interface starts here
 */

#If we are not logged in, show the logged out interface
switch ($status) {
    
    case "NOTSIGNEDIN":
        
        include "interface/signedout_base.php";        
        break;
    
    case "SIGNEDIN":
        
        include "interface/signedin_base.php";        
        break;
}

/*
 * Interface ends here
 */

#Close down the interface
include "includes/inc_interfaceEnd.php";
?>
