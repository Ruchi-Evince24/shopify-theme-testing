<?php

namespace App\Models;

use CodeIgniter\Model;
use CodeIgniter\Exceptions\FrameworkException;
class StoreModel extends Model
{
    
    protected $DBGroup          = 'default';
    protected $table            = 'eapp1_store';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['shop', 'access_token'];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    /*
    Controller: Install
    Check Whether Store data is exist or not in table 'eapp1_store'
     */
    public function verifyShop(float $shop_id, string $shop, string $access_token): array
    {
        if (!empty($shop_id) || !empty($shop) || !empty($access_token)) {
            $key = [
                'shop_id' => $shop_id,
                'shop' => $shop
            ];
            $result = $this->where($key)->findAll(1);

            if (count($result) == 0) {
                //Insert Store in Table
                if ($this->registerStore($shop_id, $shop, $access_token)) {
                    return ['code' => 1, 'mes' => 'Insert Pass'];
                } else {
                    return ['code' => 2, 'mes' => 'Insert Failed'];
                }
            } else {
                //Update access token
                if ($this->updateAccessToken($result[0]['id'], $access_token)) //Send id of row to be update
                {
                    return ['code' => 3, 'mes' => 'Update Pass'];
                } else {
                    return ['code' => 4, 'mes' => 'Update Failed'];
                }
            }
        } else {
            return ['code' => 4, 'mes' => 'Empty Parameters'];
        }
    }

    /*
    While installing app in shopify store, if store name and access token is not exits in table then
    inserting store name and access token in table.

    return 'true' if insert successful
    return 'false' if insert failed
     */
    private function registerStore(float $shop_id, string $shop, string $access_token): bool
    {
        // integer or float value cannot be inserted using $this->insert($data,false);
        /* echo $shop_id;
        exit; */
        return $this->db->simpleQuery("INSERT INTO `eapp1_store`( `shop_id`, `shop`, `access_token`) VALUES ('{$shop_id}','{$shop}','{$access_token}');");
    }

    /* 
    if shop owner or client again reinstalling app

    return 'true' if update successful
    return 'false' if update failed
     */
    private function updateAccessToken(int $id, string $access_token): bool
    {
        return $this->update($id, ['access_token' => $access_token]);
    }

    /* 
    Controller: Dashboard
    ---------------------
    get Shop Access token
    ---------------------
    return 'access token' if found in database
    return 'failed' not found
    */
    public function getShopAccessToken(string $shop): string
    {
        $key = [
            'shop' => $shop
        ];
        $access_token = $this->where($key)->first()['access_token'];
        if ($access_token != null) {
            return $access_token;
        } else {
            return 'failed';
        }
    }

    /* 
    Controller: Dashboard, Install
    ---------------------------
    HMAC-SHA256 verification
    ---------------------------
    Link: https://shopify.dev/docs/apps/auth/oauth/getting-started#verify-a-request

    return 'true' generated hexdigest is equal to the value of the hmac parameter.
    return 'false' generated hexdigest is not equal to the value of the hmac parameter.
    */
    public function verifyHmac(array $params, string $shared_secret): bool
    {
        $hmac = $params['hmac'];  // Retrieve HMAC request parameter
        unset($params['hmac']);   // Remove the HMAC parameter from the query string
        ksort($params);           // Sort params lexographically
        $computed_hmac = hash_hmac('sha256', http_build_query($params), $shared_secret);

        return hash_equals($hmac, $computed_hmac);
    }

    /*
    Controller: Dashboard, Install
    ------------------------
    CURL Operation
    ------------------------
    We Cannot show custom output if there is error in curl or in curl response when
    using Codeigniter4 inbuild CURL function, it throws Exception which stop's flow of app.
    Therefore Defining Custom CURL function.

     */
  public function DoCurl(string $method = 'GET', string $url, array $header = [], array $postFields = [], bool $returntransfer = true): array
{
    $ch = curl_init();

    $method_type = (strtolower($method) != 'get');
    switch ($method_type) {
        case true:
            $postFields = http_build_query($postFields);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        case false:
            curl_setopt($ch, CURLOPT_POST, $method_type);
    }

    if (!empty($header)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    }

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, $returntransfer);

    $response = curl_exec($ch);

    if ($response === false) {
        // Handle cURL error
        $error = curl_error($ch);
        curl_close($ch);
        throw new \CodeIgniter\Exceptions\FrameworkException("cURL request failed: $error");
    }

    curl_close($ch);

    $decoded_response = json_decode($response, true);

    if ($decoded_response === null && json_last_error() !== JSON_ERROR_NONE) {
        // Handle JSON decoding error using CodeIgniter's Exceptions class
        throw new Exception("Error decoding JSON response: " . json_last_error_msg());
    }

    return $decoded_response;
}


 public function generalcurlCall(string $method, string $url, array $header = [], array $postFields = [], bool $returntransfer = true): array
{
    $ch = curl_init();
    // Set the HTTP method and post fields if the method is not GET
    if (strtoupper($method) === 'POST' || strtoupper($method) === 'PUT' || strtoupper($method) === 'DELETE') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
        if (!empty($postFields)) {
            $postFields = http_build_query($postFields);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        }
    } elseif (strtoupper($method) === 'GET') {
        curl_setopt($ch, CURLOPT_HTTPGET, true);
    }
 
    // Set the request headers
    if (!empty($header)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    }
 
    // Set the URL and return transfer option
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, $returntransfer);
 
    // Execute the request
    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
 
    // Check for CURL errors
    if ($error) {
        return ['error' => $error];
    }
 
    // Decode the JSON response into an associative array
    $decodedResponse = json_decode($response, true);
 
    // Check if the response is null (invalid JSON) and handle it
    if ($decodedResponse === null && json_last_error() !== JSON_ERROR_NONE) {
        return ['error' => 'Invalid JSON response', 'response' => $response];
    }
 
    // Ensure the function always returns an array
    return $decodedResponse ?? ['error' => 'Empty response'];
}
}