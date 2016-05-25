<?php
// If it's going to need the database, then it's 
// probably smart to require it before we start.
require_once(LIB_PATH . DS . 'class.database_object.php');

class Diary extends DatabaseObject {

    protected static $table_name = "diary";
    protected static $db_fields = array('id', 'date', 'time', 'description',
        'clientid', 'traderid');
    public $id;
    public $date;
    public $time;
    public $description;
    public $clientid;
    public $traderid;
}
?>