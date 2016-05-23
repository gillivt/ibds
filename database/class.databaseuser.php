<?php
// If it's going to need the database, then it's 
// probably smart to require it before we start.
require_once(LIB_PATH . DS . 'class.database_object.php');

class DatabaseUser extends DatabaseObject {

    protected static $table_name = "databaseuser";
    protected static $db_fields = array('id', 'username', 'passwordHash', 'passwordSalt', 'role');
    public $id;
    public $username;
    public $passwordHash;
    public $passwordSalt;
    public $role;
    
    /**
     * 
     * @global type $database
     * @return type boolean
     */
    public static function exists($username="") {
        global $database;
        $username = $database->escape_value($username);
        
        $sql = "SELECT * FROM ".static::$table_name." ";
        $sql .= "WHERE username = '{$username}' ";
        $sql .= "LIMIT 1";
        $result_array = self::find_by_sql($sql);
        return !empty($result_array) ? true : false;
    }
    
    /**
     * 
     * @global type $database
     * @param type $username
     * @param type $password
     * @return type user object
     */
    public static function authenticate($username = "", $password = "") {
        global $database;
        $username = $database->escape_value($username);
        $password = $database->escape_value($password);

        $sql = "SELECT * FROM ".static::$table_name." ";
        $sql .= "WHERE username = '{$username}' ";
        $sql .= "LIMIT 1";
        $result_array = self::find_by_sql($sql);
        $user = !empty($result_array) ? array_shift($result_array) : false;
        if ($user){
            $passwordHash = $user->passwordHash;
            $passwordSalt = $user->passwordSalt;
            $saltedPassword = hash('sha256',$passwordSalt.$password);
            if($saltedPassword === $passwordHash) {
                return $user->role;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}
?>