<?php

class Friend extends Toolbox {

    protected $fb_id;
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
            }
        }
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
}

?>
