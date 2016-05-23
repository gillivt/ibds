<?php
// If it's going to need the database, then it's 
// probably smart to require it before we start.
require_once(LIB_PATH . DS . 'class.database_object.php');

class Client extends DatabaseObject {

    protected static $table_name = "client";
    protected static $db_fields = array('id', 'firstname', 'lastname', 'housenumber',
        'address1', 'address2', 'town', 'county', 'postcode', 'landline',
        'mobile', 'email', 'traderid');
    public $id;
    public $firstname;
    public $lastname;
    public $housenumber;
    public $address1;
    public $address2;
    public $town;
    public $county;
    public $postcode;
    public $landline;
    public $mobile;
    public $email;
    public $traderid;
}
?>