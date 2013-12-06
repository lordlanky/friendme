<?php
################################################################################
# Purpose: In Case of Emergeny Main Page
################################################################################

################################################################################
# SET BASE DEFINITIONS
################################################################################
$strWhere = dirname(__FILE__);
$strWhere = str_replace('includes', '', $strWhere);
ini_set('include_path', $strWhere);

################################################################################
# COMMON INCLUDES
################################################################################

include "secure/ch_toolbox.php";
include "secure/friend_toolbox.php";
include "includes/facebook.php";

################################################################################
# INSTANTIATIONS
################################################################################

#instantiate Facebook
$facebook = new Facebook(array(
	'appId'		=> '260910230726320',
	'secret'	=> 'd45f83dee1f9a94b6ab9a15d0de7f3ee',
	));

#Get the logged in user
$fb_user = $facebook->getUser();

#Test if the user is logged in
if (!$fb_user) {
    
    #If no user, then create a login url
    $status = "NOTSIGNEDIN";
    $loginUrl = $facebook->getLoginUrl(array(
		'scope'		=> 'email,read_friendlists,friends_online_presence,user_hometown,friends_hometown,user_relationships', // Permissions to request from the user
		'redirect_uri'	=> $_SERVER['SCRIPT_URI'],
		));
} else {
    
    #If user, then create a logout url
    $status = "SIGNEDIN";
    $logoutUrl = $facebook->getLogoutUrl(array(
		'next'	=> $_SERVER['SCRIPT_URI'],
		));
}

#Create an Icoe Toolbox
$friend = new Friend($facebook, $fb_user);

################################################################################
# COMMON ADDRESS BAR DATA
################################################################################

################################################################################
# DETERMINE PAGE
################################################################################
?>
