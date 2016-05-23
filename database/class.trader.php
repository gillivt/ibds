<?php
// If it's going to need the database, then it's 
// probably smart to require it before we start.
require_once(LIB_PATH . DS . 'class.database_object.php');

class Trader extends DatabaseObject {

    protected static $table_name = "trader";
    protected static $db_fields = array('id', 'firstname', 'lastname', 'address1', 
        'address2', 'town', 'county', 'postcode', 'telephone', 'tradingname',
        'vatnumber', 'email', 'bankaccountnumber', 'banksortcode', 'bankname',
        'bankaccountname', 'stripereference', 'logourl', 'lastinvoicenumber', 'databaseuserid');
    public $id;
    public $firstname;
    public $lastname;
    public $address1;
    public $address2;
    public $town;
    public $county;
    public $postcode;
    public $telephone;
    public $tradingname;
    public $vatnumber;
    public $email;
    public $bankaccountnumber;
    public $banksortcode;
    public $bankname;
    public $bankaccountname;
    public $stripereference;
    public $logourl;
    public $lastinvoicenumber;
    public $databaseuserid;
}
?>