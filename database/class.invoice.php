<?php
// If it's going to need the database, then it's 
// probably smart to require it before we start.
require_once(LIB_PATH . DS . 'class.database_object.php');

class Invoice extends DatabaseObject {

    protected static $table_name = "invoice";
    protected static $db_fields = array('id', 'invoicedate', 'amount', 'vatamount',
        'paymenttype', 'chequenumber', 'clientid', 'productid', 'traderid');
    public $id;
    public $invoicedate;
    public $amount;
    public $vatamount;
    public $paymenttype;
    public $chequenumber;
    public $clientid;
    public $productid;
    public $traderid;
}
?>