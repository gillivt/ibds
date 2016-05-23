<?php

use \Jacwright\RestServer\RestException;

class InvoiceBuddyController {

    /**
     * Returns a JSON string object to the browser when hitting the root of the domain
     *
     * @url GET /
     */
    public function help() {
        return array("Success"=>"Hello World");
    }

    /**
     * Returns a JSON object - another test
     * 
     * @url GET /hello
     */
    public function hello() {
        return "Hello World";
    }
        
    // Main Service
    
    /**
     * Register a new Trader - returns trader object
     * 
     * @param type $data
     * @return \Trader
     * @throws RestException
     * 
     * @url POST /register
     */
    public function registerTrader($data) {
        // parameters
        $username = $data->username;
        $password = $data->password;
        $email = $data->email;
        $role = 'trader';
        
        //check if user exists
        if (DatabaseUser::exists($username)) {
            throw new RestException(409, 'Username Already Exists');
        } 
        
        //create salted password hash
        $saltLength = 32;
        $bool = true;
        $salt = base64_encode(openssl_random_pseudo_bytes ( $saltLength ,$bool ));
        //salt password
        $saltedPassword = hash('sha256',$salt.$password);
            
        //create user object
        $user = new DatabaseUser();
        $user->username = $username;
        $user->passwordHash = $saltedPassword;
        $user->passwordSalt = $salt;
        $user->role = $role;
            
        //save user to database
        if (!$user->save()) {
            //will probably die before it ever gets here and die will show the mysqli error
            throw new RestException(400, 'Unknown Error - user not created');
        }
            
        // If we are here then user created ok now create trader
        $trader = new Trader();
        $trader->email = $email;
        $trader->databaseuserid = $user->id;
        
        if (!$trader->save()){
            //will probably die before it ever gets here and die will show the mysqli error
            throw new RestException(400, 'Unknown Error - trader not created');
        } 
        return $trader;
    }
    
    /**
     * Read Trader - returns trader object
     * 
     * Returns Trader Object
     * @return type
     * @throws RestException
     * 
     * @url GET /trader
     */
    public function readTrader() {
        //get authorisation from headers
        $headers = getallheaders();
        $auth = $headers["Authorization"];
        
        //check authorization
        $role = $this->authenticate($auth);
        if ($role !== 'trader'){
            throw new RestException(401, "Unauthorized");
        }
        //get trader
        $traderId = $this->getTraderID($auth);
        $trader = Trader::find_by_id($traderId);
        if(!$trader) {
            throw new RestException(400, "trader doesn't exist");
        }
        return $trader;
    }
    
    
   /**
    * Update Trader = Returns Trader object
    * 
    * @param type $data
    * @return type
    * @throws RestException
    * 
    * @url PUT /trader
    */
    public function updateTrader($data) {
        //get authorisation from headers
        $headers = getallheaders();
        $auth = $headers["Authorization"];
        
        //check authorization
        $role = $this->authenticate($auth);
        if ($role !== 'trader'){
            throw new RestException(401, "Unauthorized");
        }
        
        //get trader id from auth so we can compare it agains id in $data
        $traderId = $this->getTraderID($auth);
        if ($traderId !== $data->id) {
            throw new RestException(401, "Unauthorised - Not your ID");
        }
        //authorised now
        $trader = new Trader;
        if (isset($data->id)){
            $trader->id = $data->id;
        } else {
            throw new RestException(400,"id not specified");
        }
        $trader->firstname = isset($data->firstname) ? $data->firstname : null;
        $trader->lastname = isset($data->lastname) ? $data->lastname : null;
        $trader->address1 = isset($data->address1) ? $data->address1 : null;
        $trader->address2 = isset($data->address2) ? $data->address2 : null;
        $trader->town = isset($data->town) ? $data->town : null;
        $trader->county = isset($data->county) ? $data->county : null;
        $trader->postcode = isset($data->postcode) ? $data->postcode : null;
        $trader->telephone = isset($data->telephone) ? $data->telephone : null;
        $trader->tradingname = isset($data->tradingname) ? $data->tradingname : null;
        $trader->vatnumber = isset($data->vatnumber) ? $data->vatnumber : null;
        $trader->email = isset($data->email) ? $data->email : null;
        $trader->bankaccountnumber = isset($data->bankaccountnumber) ? $data->bankaccountnumber : null;
        $trader->banksortcode = isset($data->banksortcode) ? $data->banksortcode : null;
        $trader->bankname = isset($data->bankname) ? $data->bankname : null;
        $trader->bankaccountname = isset($data->bankaccountname) ? $data->bankaccountname : null;
        $trader->stripereference = isset($data->stripereference) ? $data->stripereference : null;
        $trader->logourl = isset($data->logourl) ? $data->logourl : null;
        if(isset($data->lastinvoicenumber)){
            $trader->lastinvoicenumber = $data->lastinvoicenumber;
        } else {
            throw new RestException(400,"lastinvoicenumber not specified");
        }
        $trader->databaseuserid = isset($data->databaseuserid) ? $data->databaseuserid : $this->getUserID($auth);
          
        if($trader->save()) {
            return $trader;
        } else {
            throw new RestException(400, "Unknown error - unable to update");
        }    
    }
    
    /**
     * DELETE Trader - returns true
     * 
     * @return type
     * @throws RestException
     * 
     * @url DELETE /trader
     */
    public function deleteTrader() {
        //get authorisation from headers
        $headers = getallheaders();
        $auth = $headers["Authorization"];
        
        //check authorization
        $role = $this->authenticate($auth);
        if ($role !== 'trader'){
            throw new RestException(401, "Unauthorized");
        }
        
        $traderId = $this->getTraderID($auth);
        $trader = Trader::find_by_id($traderId);
        $result = $trader->delete();
            
        if (!$result) {
            throw new RestException(400,"Unknown Error - Can not Delete Trader");
        }
            
        $userId = $this->getUserID($auth);
        $user = DatabaseUser::find_by_id($userId);
        $result = $user->delete();
       
        return array("Success"=>$result);
    }
    
    /**
     * Create Client - Returns Lient Object
     * 
     * @param type $data
     * @return \Client
     * @throws RestException
     * 
     * @url POST /trader/client
     */
    public function createClient($data) {
        //get authorisation from headers
        $headers = getallheaders();
        $auth = $headers["Authorization"];
        
        //check authorization
        $role = $this->authenticate($auth);
        if ($role !== 'trader'){
            throw new RestException(401, "Unauthorized");
        }
        
        $client = new Client();
        if (isset($data->firstname)) {
            $client->firstname = $data->firstname;
        } else {
            throw new RestException(400,"firstname not supplied"); 
        }
        if (isset($data->lastname)) {
            $client->lastname = $data->lastname;
        } else {
            throw new RestException(400,"lastname not supplied");
        }
        if (isset($data->housenumber)) {
            $client->lastname = $data->lastname;
        } else {
            throw new RestException(400,"housenumber not supplied");
        }
        $client->address1 = isset($data->address1) ? $data->address1 : null;
        $client->address2 = isset($data->address2) ? $data->address2 : null;
        $client->town = isset($data->town) ? $data->town : null;
        $client->county = isset($data->county) ? $data->county : null;
        if (isset($data->postcode)) {
            $client->postcode = $data->postcode;
        } else {
            throw new RestException(400,"postcode not supplied");
        }
        $client->landline = isset($data->landline) ? $data->landline : null;
        $client->mobile = isset($data->mobile) ? $data->mobile :null;
        $client->email = isset($data->email) ? $data->email : null;
        $client->traderid = $this->getTraderID($auth);
         
        if($client->save()) {
            return $client;
        } else {
            throw new RestException(400,"Unknown Error - Cannot Create Client");
        }
    }
    
    /**
     * Read Client from Client ID
     * 
     * @param type $id
     * @return type
     * @throws RestException
     * 
     * @url GET /trader/client/$id
     */
    public function readClient($id) {
        //get authorisation from headers
        $headers = getallheaders();
        $auth = $headers["Authorization"];
        
        //check authorization
        $role = $this->authenticate($auth);
        if ($role !== 'trader'){
            throw new RestException(401, "Unauthorized");
        }
        
        //get client
        $client = Client::find_by_id($id);
        if ($client) {
            return $client;
        } else {
            throw new RestException(400, "client not found");
        }
    }
    
    /**
     * Read all clients for this trader
     * 
     * @return type
     * @throws RestException
     * 
     * @url GET /trader/clients
     */
    public function readClients() {
        //get authorisation from headers
        $headers = getallheaders();
        $auth = $headers["Authorization"];
        
        //check authorization
        $role = $this->authenticate($auth);
        if ($role !== 'trader'){
            throw new RestException(401, "Unauthorized");
        }
        $traderId = $this->getTraderID($auth);
        $sql = "SELECT * FROM client WHERE traderid = '{$traderId}'";
        $clients = Client::find_by_sql($sql);
        if ($clients) {
            return $clients;
        } else {
            throw new RestEception(400, "no clients found");
        }
    }
    
    /**
     * Update Client table
     * 
     * @param type $data
     * @return \Client
     * @throws RestException
     * 
     * @url PUT /trader/client
     */
    public function updateClient($data) {
        //get authorisation from headers
        $headers = getallheaders();
        $auth = $headers["Authorization"];
        
        //check authorization
        $role = $this->authenticate($auth);
        if ($role !== 'trader'){
            throw new RestException(401, "Unauthorized");
        }
        
        $client = new Client();
        if (isset($data->id)) {
            $client->id = $data->id;
        } else {
            throw new RestException(400,"Client id Not Supplied");
        }
        if (isset($data->firstname)){
            $client->firstname =$data->firstname;
        } else {
            throw new RestException(400,"firstname Not Supplied");
        }
        if (isset($data->lastname)) {
            $client->lastname = $data->lastname;
        } else {
            throw new RestException(400,"lastname Not Supplied");
        }
        if (isset($data->housenumber)) {
            $client->housenumber = $data->housenumber;
        } else {
            throw new RestException(400,"housenumber not supplied");
        }
        $client->address1 = isset($data->address1) ? $data->address1 : null;
        $client->address2 = isset($data->address2) ? $data->address2 : null;
        $client->town = isset($data->town) ? $data->town : null;
        $client->county = isset($data->county) ? $data->county : null;
        if (isset($data->postcode)) {
            $client->postcode = $data->postcode;
        } else {
            throw new RestException(400,"postcode Not Supplied");
        }
        $client->landline = isset($data->landline) ? $data->landline : null;
        $client->mobile = isset($data->mobile) ? $data->mobile : null;
        $client->email = isset($data->email) ? $data->email : null;
        if (isset($data->traderid)) {
            $client->traderid = $data->traderid;
        } else {
            throw new RestException(400,"traderid Not Supplied");
        }
          
        if ($client->save()){
            return $client;
        } else {
            throw new RestException(400,"Unknown Error - client not updated");
        }
    }

    /**
     * Delete Client
     * 
     * @param type $id
     * @return type
     * @throws RestException
     * 
     * @url DELETE /trader/client
     */
    public function deleteClient($id) {
        //get authorisation from headers
        $headers = getallheaders();
        $auth = $headers["Authorization"];
        
        //check authorization
        $role = $this->authenticate($auth);
        if ($role !== 'trader'){
            throw new RestException(401, "Unauthorized");
        }

        $client = Client::find_by_id($id);
        if(!$client) {
            throw new RestException(400, "Client not found");
        }
        
        $result = $client->delete();
        return array("Result"=>$result); 
    }

    /**
     * Create Product - Returns Product Object
     * 
     * @param type $data
     * @return \Product
     * @throws RestException
     * 
     * @url POST /trader/product
     */
    public function createProduct($data) {
        //get authorisation from headers
        $headers = getallheaders();
        $auth = $headers["Authorization"];
        
        //check authorization
        $role = $this->authenticate($auth);
        if ($role !== 'trader'){
            throw new RestException(401, "Unauthorized");
        }
        $product = new Product();
        if (isset($data->productkey)) {
            $product->productkey = $data->productkey;
        } else {
            throw new RestException(400, "product key not supplied");
        }
        if (isset($data->description)) {
            $product->description = $data->description;
        } else {
            throw new RestException(400, "description not supplied");
        }
        $product->price = isset($data->price) ? $data->price : null;
        $product->misc = isset($data->misc) ? $data->misc : null;
        $product->traderid = $this->getTraderID($auth);
        
        if ($product->save()) {
            return $product;
        } else {
            throw new RestException(400, "Unknown error - cannot create product");
        }
    }
    
    /**
     * Read Product by id - Returns Product Object
     * 
     * @param type $id
     * @return type
     * @throws RestException
     * 
     * @url GET /trader/product/$id
     */
    public function readProduct($id) {
        //get authorisation from headers
        $headers = getallheaders();
        $auth = $headers["Authorization"];
        
        //check authorization
        $role = $this->authenticate($auth);
        if ($role !== 'trader'){
            throw new RestException(401, "Unauthorized");
        }
        $product = Product::find_by_id($id);
        if($product) {
            return $product;
        } else {
            throw new RestException(400, 'productct not found');
        }
    }
    
    /**
     *Read Products - Returns array of Products
     *  
     * @return type
     * @throws RestException
     * @throws RestEception
     * 
     * @url GET /trader/products
     */
    public function readProducts() {
        //get authorisation from headers
        $headers = getallheaders();
        $auth = $headers["Authorization"];
        
        //check authorization
        $role = $this->authenticate($auth);
        if ($role !== 'trader'){
            throw new RestException(401, "Unauthorized");
        }
        $traderId = $this->getTraderID($auth);
        $sql = "SELECT * FROM product WHERE traderid = '{$traderId}'";
        $products = Product::find_by_sql($sql);
        if ($products) {
            return $products;
        } else {
            throw new RestEception(400, "no products found");
        }
    }

    /**
     * Update Product - Return Product Object
     * 
     * @param type $data
     * @return type
     * @throws RestException
     * 
     * @url PUT /trader/product
     */
    public function updateProduct($data) {
        //get authorisation from headers
        $headers = getallheaders();
        $auth = $headers["Authorization"];
        
        //check authorization
        $role = $this->authenticate($auth);
        if ($role !== 'trader'){
            throw new RestException(401, "Unauthorized");
        }
        if (isset($data->id)) {
            $product = Product::find_by_id($data->id);
        } else {
            throw new RestException(400, "product id not supplied");
        }
        if (isset($data->productkey)) {
            $product->productkey = $data->productkey;
        }
        if (isset($data->description)) {
            $product->description = $data->description;
        }
        if (isset($data->price)) {
            $product->price = $data->price;
        }
        if (isset ($data->misc)) {
            $product->misc = $data->misc;
        }
        if ($product->save()) {
            return $product;
        } else {
            throw new RestException(400,"Unknown Error - can not update product");
        }
    }
    
    public function deleteProduct($id){
        
    }
    
    // Private Helper Functions
    
    /**
     * 
     * @param type $auth
     * @return type
     */
    private function getTraderID($auth) {
        $userId = $this->getUserID($auth);
        $sql = "SELECT * FROM trader WHERE databaseuserid = '{$userId}' LIMIT 1";
        $result_array = Trader::find_by_sql($sql);
        $traderId = array_shift($result_array)->id;
        return $traderId;
    }
    
    /**
     * 
     * @param type $auth
     * @return type
     */
    private function getUserID($auth) {
        $username = explode(":", base64_decode(substr($auth,6)))[0] ;
        $sql = "SELECT * FROM databaseuser WHERE username = '{$username}' LIMIT 1";
        $result_array = DatabaseUser::find_by_sql($sql);
        $userId = array_shift($result_array)->id;
        return $userId;
    }
    
    /**
     * Authenticate existing user
     * 
     * @param type $auth
     * @return boolean
     */
    private function authenticate($auth) {
        $userpass = explode(":",base64_decode(substr($auth,6)));
        $username = $userpass[0];
        $password = $userpass[1];
         if (!empty($username) && (!empty($password))) {
            $role = DatabaseUser::authenticate($username, $password);
            if ($role === false) {
                return false;
            } else {
                return $role;
            }
        } else {
            return false;
        }
    }
}
