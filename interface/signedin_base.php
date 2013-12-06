<?php

#Determine the page we are on. If nothing, default to 'Me'
$icoe->show($fb_user);
$icoe->showUser();
$icoe->compareConnections();
?>

SIGNED IN

<a href="<?php echo $logoutUrl;?>">LOG OUT</a>