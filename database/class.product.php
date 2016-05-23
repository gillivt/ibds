<?php
// If it's going to need the database, then it's 
// probably smart to require it before we start.
require_once(LIB_PATH . DS . 'class.database_object.php');

class Product extends DatabaseObject {

    protected static $table_name = "product";
    protected static $db_fields = array('id', 'productkey', 'description', 
        'price', 'misc', 'traderid');
    public $id;
    public $productkey;
    public $description;
    public $price;
    public $misc;
    public $traderid;
}
?>