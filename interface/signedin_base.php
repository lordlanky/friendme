<?php

#Determine the page we are on. If nothing, default to 'Me'
$friend->show($fb_user);
?>

SIGNED IN

<a href="<?php echo $logoutUrl;?>">LOG OUT</a>