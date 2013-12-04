<?php

#Load all preloading variables
include "includes/inc_preLoad.php";

#Load the interface start
include _DIRBASE."includes/inc_interfaceLoad.php";

/*
 * Interface starts here
 */
?>

<fb:login-button show-faces="true" width="200" max-rows="1"></fb:login-button>

<?php

/*
 * Interface ends here
 */

#Close down the interface
include _DIRBASE."includes/inc_interfaceEnd.php";
?>
