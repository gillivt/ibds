<?php

use \Jacwright\RestServer\RestException;

class InvoiceBuddyController {

    /**
     * Returns a JSON string object to the browser when hitting the root of the domain
     *
     * @url GET /
     */
    public function help() {
        return array("Success" => "Hello World");
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
        $salt = base64_encode(openssl_random_pseudo_bytes($saltLength, $bool));
        //salt password
        $saltedPassword = hash('sha256', $salt . $password);

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

        if (!$trader->save()) {
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
        if ($role !== 'trader') {
            throw new RestException(401, "Unauthorized");
        }
        //get trader
        $traderId = $this->getTraderID($auth);
        $trader = Trader::find_by_id($traderId);
        if (!$trader) {
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
        if ($role !== 'trader') {
            throw new RestException(401, "Unauthorized");
        }

        //get trader id from auth so we can compare it agains id in $data
        $traderId = $this->getTraderID($auth);
        if ($traderId !== $data->id) {
            throw new RestException(401, "Unauthorised - Not your ID");
        }
        //authorised now
        if (isset($data->id)) {
            $trader = Trader::find_by_id($data->id);
        } else {
            throw new RestException(400, "id not specified");
        }

        if (isset($data->firstname)) {
            $trader->firstname = $data->firstname;
        }
        if (isset($data->lastname)) {
            $trader->lastname = $data->lastname;
        }
        if (isset($data->address1)) {
            $trader->address1 = $data->address1;
        }
        if (isset($data->address2)) {
            $trader->address2 = $data->address2;
        }
        if (isset($data->town)) {
            $trader->town = $data->town;
        }
        if (isset($data->county)) {
            $trader->county = $data->county;
        }
        if (isset($data->postcode)) {
            $trader->postcode = $data->postcode;
        }
        if (isset($data->telephone)) {
            $trader->telephone = $data->telephone;
        }
        if (isset($data->tradingname)) {
            $trader->tradingname = $data->tradingname;
        }
        if (isset($data->vatnumber)) {
            $trader->vatnumber = $data->vatnumber;
        }
        if (isset($data->email)) {
            $trader->email = $data->email;
        }
        if (isset($data->bankaccountnumber)) {
            $trader->bankaccountnumber = $data->bankaccountnumber;
        }
        if (isset($data->banksortcode)) {
            $trader->banksortcode = $data->banksortcode;
        }
        if (isset($data->bankname)) {
            $trader->bankname = $data->bankname;
        }
        if (isset($data->bankaccountname)) {
            $trader->bankaccountname = $data->bankaccountname;
        }
        if (isset($data->stripereference)) {
            $trader->stripereference = $data->stripereference;
        }
        if (isset($data->logourl)) {
            $trader->logourl = $data->logourl;
        }
        if (isset($data->lastinvoicenumber)) {
            $trader->lastinvoicenumber = $data->lastinvoicenumber;
        }
        $trader->databaseuserid = $this->getUserID($auth);

        if ($trader->save()) {
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
        if ($role !== 'trader') {
            throw new RestException(401, "Unauthorized");
        }

        $traderId = $this->getTraderID($auth);
        $trader = Trader::find_by_id($traderId);
        $result = $trader->delete();

        if (!$result) {
            throw new RestException(400, "Unknown Error - Can not Delete Trader");
        }

        $userId = $this->getUserID($auth);
        $user = DatabaseUser::find_by_id($userId);
        $result = $user->delete();

        return array("Success" => $result);
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
        if ($role !== 'trader') {
            throw new RestException(401, "Unauthorized");
        }

        $client = new Client();
        if (isset($data->firstname)) {
            $client->firstname = $data->firstname;
        } else {
            throw new RestException(400, "firstname not supplied");
        }
        if (isset($data->lastname)) {
            $client->lastname = $data->lastname;
        } else {
            throw new RestException(400, "lastname not supplied");
        }
        if (isset($data->housenumber)) {
            $client->lastname = $data->lastname;
        } else {
            throw new RestException(400, "housenumber not supplied");
        }
        $client->address1 = isset($data->address1) ? $data->address1 : null;
        $client->address2 = isset($data->address2) ? $data->address2 : null;
        $client->town = isset($data->town) ? $data->town : null;
        $client->county = isset($data->county) ? $data->county : null;
        if (isset($data->postcode)) {
            $client->postcode = $data->postcode;
        } else {
            throw new RestException(400, "postcode not supplied");
        }
        $client->landline = isset($data->landline) ? $data->landline : null;
        $client->mobile = isset($data->mobile) ? $data->mobile : null;
        $client->email = isset($data->email) ? $data->email : null;
        $client->traderid = $this->getTraderID($auth);

        if ($client->save()) {
            return $client;
        } else {
            throw new RestException(400, "Unknown Error - Cannot Create Client");
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
        if ($role !== 'trader') {
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
        if ($role !== 'trader') {
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
        if ($role !== 'trader') {
            throw new RestException(401, "Unauthorized");
        }

        if (isset($data->id)) {
            $client = Client::find_by_id($data->id);
        } else {
            throw new RestException(400, "Client id Not Supplied");
        }
        if (isset($data->firstname)) {
            $client->firstname = $data->firstname;
        }
        if (isset($data->lastname)) {
            $client->lastname = $data->lastname;
        }
        if (isset($data->housenumber)) {
            $client->housenumber = $data->housenumber;
        }
        if (isset($data->address1)) {
            $client->address1 = $data->address1;
        }
        if (isset($data->address2)) {
            $client->address2 = $data->address2;
        }
        if (isset($data->town)) {
            $client->town = $data->town;
        }
        if (isset($data->county)) {
            $client->county = $data->county;
        }
        if (isset($data->postcode)) {
            $client->postcode = $data->postcode;
        }
        if (isset($data->landline)) {
            $client->landline = $data->landline;
        }
        if (isset($data->mobile)) {
            $client->mobile = $data->mobile;
        }
        if (isset($data->email)) {
            $client->email = $data->email;
        }
        if (isset($data->traderid)) {
            $client->traderid = $data->traderid;
        } else {
            throw new RestException(400, "traderid Not Supplied");
        }

        if ($client->save()) {
            return $client;
        } else {
            throw new RestException(400, "Unknown Error - client not updated");
        }
    }

    /**
     * Delete Client
     * 
     * @param type $data
     * @return type
     * @throws RestException
     * 
     * @url DELETE /trader/client
     */
    public function deleteClient($data) {
        //get authorisation from headers
        $headers = getallheaders();
        $auth = $headers["Authorization"];

        //check authorization
        $role = $this->authenticate($auth);
        if ($role !== 'trader') {
            throw new RestException(401, "Unauthorized");
        }

        $client = Client::find_by_id($data->id);
        if (!$client) {
            throw new RestException(400, "Client not found");
        }

        $result = $client->delete();
        return array("Result" => $result);
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
        if ($role !== 'trader') {
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
        if ($role !== 'trader') {
            throw new RestException(401, "Unauthorized");
        }
        $product = Product::find_by_id($id);
        if ($product) {
            return $product;
        } else {
            throw new RestException(400, 'productct not found');
        }
    }

    /**
     * Read Products - Returns array of Products
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
        if ($role !== 'trader') {
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
        if ($role !== 'trader') {
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
        if (isset($data->misc)) {
            $product->misc = $data->misc;
        }
        if ($product->save()) {
            return $product;
        } else {
            throw new RestException(400, "Unknown Error - can not update product");
        }
    }

    /**
     * Delete Product
     * 
     * @param type $id
     * @return type
     * @throws RestException
     * 
     * @url DELETE /trader/product
     */
    public function deleteProduct($data) {
        //get authorisation from headers
        $headers = getallheaders();
        $auth = $headers["Authorization"];

        //check authorization
        $role = $this->authenticate($auth);
        if ($role !== 'trader') {
            throw new RestException(401, "Unauthorized");
        }

        $product = Product::find_by_id($data->id);

        if (!$product) {
            throw new RestException(400, "product doesn't exist");
        }
        $result = $product->delete();
        return array("result" => $result);
    }

    /**
     * Create Invoice - Returns Invoice Object
     * 
     * @param type $data
     * @return \Invoice
     * @throws RestException
     * 
     * @url POST /trader/invoice
     */
    public function createInvoice($data) {
        //get authorisation from headers
        $headers = getallheaders();
        $auth = $headers["Authorization"];

        //check authorization
        $role = $this->authenticate($auth);
        if ($role !== 'trader') {
            throw new RestException(401, "Unauthorized");
        }

        $invoice = new Invoice();

        $invoice->invoicedate = isset($data->invoicedate) ? $data->invoicedate : date('Y-m-d');
        if (isset($data->amount)) {
            $invoice->amount = $data->amount;
        } else {
            throw new RestException(400, "amount not specified");
        }
        $invoice->vatamount = isset($data->vatamount) ? $data->vatamount : null;
        $invoice->paymenttype = isset($data->paymenttype) ? $data->paymenttype : 'cash';
        $invoice->chequenumber = isset($data->chequenumber) ? $data->chequenumber : null;
        if (isset($data->clientid)) {
            $invoice->clientid = $data->clientid;
        } else {
            throw new RestException(400, "clientid not specified");
        }
        if (isset($data->productid)) {
            $invoice->productid = $data->productid;
        } else {
            throw new RestException(400, "productid not specified");
        }

        $result = $invoice->save();
        if ($result) {
            return $invoice;
        } else {
            throw new RestException(400, "Unknown error - Invoice not created");
        }
    }

    /**
     * Read Invoice - Returns Invoice Object
     * 
     * @param type $id
     * @return type
     * @throws RestException
     * 
     * @url GET /trader/invoice/$id
     */
    public function readInvoice($id) {
        //get authorisation from headers
        $headers = getallheaders();
        $auth = $headers["Authorization"];

        //check authorization
        $role = $this->authenticate($auth);
        if ($role !== 'trader') {
            throw new RestException(401, "Unauthorized");
        }
        $invoice = Invoice::find_by_id($id);
        if (!$invoice) {
            throw new RestException(400, "no such invoice");
        }
        return $invoice;
    }

    /**
     * Read Client Invoices - Returns an array of objects
     * 
     * @param type $id
     * @return type
     * @throws RestException
     * 
     * @url GET /trader/invoices/client/$id
     */
    public function readClientInvoices($id) {
        //get authorisation from headers
        $headers = getallheaders();
        $auth = $headers["Authorization"];

        //check authorization
        $role = $this->authenticate($auth);
        if ($role !== 'trader') {
            throw new RestException(401, "Unauthorized");
        }
        $sql = "SELECT * FROM invoice WHERE clientid = '{$id}'";
        $invoices = Invoice::find_by_sql($sql);
        if (!$invoices) {
            throw new RestException(400, "no invoices");
        }
        return $invoices;
    }

    /**
     * Read all invoices - returns an array of objects
     * 
     * @return type
     * @throws RestException
     * 
     * @url GET /trader/invoices
     */
    public function readAllInvoices() {
        //get authorisation from headers
        $headers = getallheaders();
        $auth = $headers["Authorization"];

        //check authorization
        $role = $this->authenticate($auth);
        if ($role !== 'trader') {
            throw new RestException(401, "Unauthorized");
        }

        $sql = "SELECT * FROM invoice WHERE traderid = '{$this->getTraderID($auth)}'";
        $invoices = Invoice::find_by_sql($sql);
        if (!$invoices) {
            throw new RestException(400, "no invoices");
        }
        return $invoices;
    }

    /**
     * Update Invoice - Returns Invoice Object
     * 
     * @param type $data
     * @return type
     * @throws RestException
     * 
     * @url PUT /trader/invoice
     */
    public function updateInvoice($data) {
        //get authorisation from headers
        $headers = getallheaders();
        $auth = $headers["Authorization"];

        //check authorization
        $role = $this->authenticate($auth);
        if ($role !== 'trader') {
            throw new RestException(401, "Unauthorized");
        }
        if ($data->id) {
            $invoice = Invoice::find_by_id($data->id);
        } else {
            throw new RestException(400, "id not received");
        }
        if (!$invoice) {
            throw new RestException(400, "no such invoice");
        }
        if (isset($data->invoicedate)) {
            $invoice->invoicedate = $data->invoicedate;
        }
        if (isset($data->amount)) {
            $invoice->amount = $data->amount;
        }
        if (isset($data->vatamount)) {
            $invoice->vatamount = $data->vatamount;
        }
        if (isset($data->paymenttype)) {
            $invoice->paymenttype = $data->paymenttype;
        }
        if (isset($data->chequenumber)) {
            $invoice->chequenumber = $data->chequenumber;
        }
        if (isset($data->clientid)) {
            $invoice->clientid = $data->clientid;
        }
        if (isset($data->productid)) {
            $invoice->productid = $data->productid;
        }

        if ($invoice->save()) {
            return $invoice;
        } else {
            throw new RestException(400, "Unknown Error - unable to update invoice");
        }
    }

    /**
     * Delete Invoice
     * 
     * @param type $data
     * @return type
     * @throws RestException
     * 
     * @url DELETE /trader/invoice
     */
    public function deleteInvoice($data) {
        //get authorisation from headers
        $headers = getallheaders();
        $auth = $headers["Authorization"];

        //check authorization
        $role = $this->authenticate($auth);
        if ($role !== 'trader') {
            throw new RestException(401, "Unauthorized");
        }
        $invoice = Invoice::find_by_id($data->id);
        if (!$invoice) {
            throw new RestException(400, "no such invoice");
        }
        $result = $invoice->delete();
        return array("result" => $result);
    }

    /**
     * Create Diary Event
     * 
     * @param type $data
     * @return \Diary
     * @throws RestException
     * 
     * @url POST /trader/diary
     */
    public function createDiary($data) {
        //get authorisation from headers
        $headers = getallheaders();
        $auth = $headers["Authorization"];

        //check authorization
        $role = $this->authenticate($auth);
        if ($role !== 'trader') {
            throw new RestException(401, "Unauthorized");
        }
        $diary = new Diary();
        if (isset($data->date) && $this->validMySQLDate($data->date)) {
            $diary->date = $data->date;
        } else {
            throw new RestException(400, "invalid date");
        }
        if (isset($data->time) && $this->isValidMySQLTIME($data->time)) {
            $diary->time = $data->time;
        } else {
            throw new RestException(400, "invalid time");
        }
        if (isset($data->description)) {
            $diary->description = $data->description;
        } else {
            throw new RestException(400, "no description given");
        }
        if (isset($data->clientid)) {
            $diary->clientid = $data->clientid;
        } else {
            throw new RestException(400, " no clientid given");
        }
        if ($diary->save()) {
            return $diary;
        } else {
            throw new RestException(400, "Unknown Error - Can not create diary event");
        }
    }
    
    /**
     * Return array of Diary events between two dates
     * 
     * @param type $from
     * @param type $to
     * @return type
     * @throws RestException
     * 
     * @url GET /trader/diary/$from/$to
     */
    public function readDiary($from,$to) {
        //get authorisation from headers
        $headers = getallheaders();
        $auth = $headers["Authorization"];

        //check authorization
        $role = $this->authenticate($auth);
        if ($role !== 'trader') {
            throw new RestException(401, "Unauthorized");
        }
        if (!$this->validMySQLDate($to) || (!$this->validMySQLDate($from))) {
            throw new RestException(400, "invalid dates");
        }
        $traderid = $this->getTraderID($auth);
        
        //make sql string
        $sql = "SELECT * FROM diary WHERE traderid = '{$traderid}' AND ";
        $sql .= "date >= '{$from}' AND ";
        $sql .= "date <= '{$to}'";
        
        $diary = Diary::find_by_sql($sql);
        
        return $diary;
    }
    
    /**
     * Read Todays Diary Events
     * 
     * @return type
     * @throws RestException
     * 
     * @url GET /trader/diary/today
     */
    public function readTodaysDiary() {
        //get authorisation from headers
        $headers = getallheaders();
        $auth = $headers["Authorization"];

        //check authorization
        $role = $this->authenticate($auth);
        if ($role !== 'trader') {
            throw new RestException(401, "Unauthorized");
        }
        $today = $date('Y-m-d');
        $traderid = $this->getTraderID($auth);
        
        $sql = "SELECT * FROM diary WHERE traderid= '{$traderid}' AND ";
        $sql .= "date = '{$today}'";
        
        $diary = Diary::find_by_sql($sql);
        
        return $diary;
    }
    
    public function updateDiary($data) {
        //get authorisation from headers
        $headers = getallheaders();
        $auth = $headers["Authorization"];

        //check authorization
        $role = $this->authenticate($auth);
        if ($role !== 'trader') {
            throw new RestException(401, "Unauthorized");
        }
        
    }
    
    public function deleteDiary($data) {
        //get authorisation from headers
        $headers = getallheaders();
        $auth = $headers["Authorization"];

        //check authorization
        $role = $this->authenticate($auth);
        if ($role !== 'trader') {
            throw new RestException(401, "Unauthorized");
        }
        
    }

    // Private Helper Functions

    /**
     * Returns trader id from authorisation string
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
     * returns user id from authorisation string
     * 
     * @param type $auth
     * @return type
     */
    private function getUserID($auth) {
        $username = explode(":", base64_decode(substr($auth, 6)))[0];
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
        $userpass = explode(":", base64_decode(substr($auth, 6)));
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

    /**
     * Checks date string as valid MySQL Date
     * 
     * @param type $date
     * @return boolean
     */
    private function validMySQLDate($date) {
        if (!$this->validMySQLDateFormat) {
            return false;
        }
        $day = substr($date, 8);
        $month = substr($date, 5, 2);
        $year = substr($date, 0, 4);
        return (checkdate($month, $day, $year));
    }

    /**
     * Checks for valid MySQL date format ('yyyy-mm-dd') returns true or false
     * DOES NOT check if date is valid
     * 
     * @param type $date
     * @return type
     */
    private function validMySQLDateFormat($date) {
        return (preg_match("/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/", $date));
    }

    /**
     * Validates time in format HH:MM:SS
     * @param type $time
     * @return boolean
     */
    private function validMySQLTIME($time) {
        if (!$this->validMySQLTimeFormat($time)) {
            return false;
        }
        $time_array = explode(':', $time);
        $hour = $time_array[0];
        $min = $time_array[1];
        $sec = $time_array[2];
        if ($hour < 0 || $hour > 23 || !is_numeric($hour)) {
            return false;
        }
        if ($min < 0 || $min > 59 || !is_numeric($min)) {
            return false;
        }
        if ($sec < 0 || $sec > 59 || !is_numeric($sec)) {
            return false;
        }
        return true;
    }

    /**
     * 
     * @param type $time
     * @return type
     */
    private function validMySQLTimeFormat($time) {
        return (preg_match("/^([0-9]{2}:[0-9]{2}:[0-9]{2})$/", $time));
    }

}
