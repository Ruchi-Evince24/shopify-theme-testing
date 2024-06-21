<?php

namespace App\Controllers;

use App\Models\StoreModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class Install extends BaseController
{
    
    protected $storeModel;
 
    /**
     * Undocumented function
     *
     * @param StoreModel $storeModel
     */
    public function __construct() {
        $this->storeModel = new StoreModel();
    }
   // Installing Eapp1 in StoreDoCurl
      public function installApp()
    {
        // Check if 'shop' parameter is present in the GET request
          if (empty($_GET['shop'])) {
            throw new PageNotFoundException('Invalid Shop parameter');
        }

        $shop = $_GET['shop'];
        $config = config('App');
        $api_key = $config->api_key;
        $scopes = 'write_orders';  // Correct scope should be a string
        $redirect_uri = $config->redirect_uri;
        
        if ($shop) {
            $install_url = "https://{$shop}.myshopify.com/admin/oauth/authorize?client_id={$api_key}&scope={$scopes}&redirect_uri={$redirect_uri}&state=123456&grant_options[]=offline";
            return redirect()->to($install_url); // Use redirect()->to() for proper redirect
        } else {
            throw new PageNotFoundException('Invalid Shop parameter');
        }
    }

    public function getAuthorizationCode()
    {
      {
        $params = $_GET;
 
        $config = config('App', true);
        $client_id = $config->api_key;
        $client_secret = $config->shared_secret;
        $shop = $params['shop'];
        $authorization_code = $params['code'];
        $api_version = $config->shopify_api_version;
 
        /*
        ---------------------------------------------------------------------
        Verify the installation request
        ---------------------------------------------------------------------
        Link: https://shopify.dev/docs/apps/auth/oauth/getting-started#verify-a-request
        When a user installs your app through the Shopify App Store or using an installation link, 
        your app receives a GET request to the App URL path that you specify in the Partner Dashboard. 
        The request includes the shop, timestamp, and hmac query parameters. 
        You need to verify the authenticity of these requests using the provided hmac parameter. 
        */
        // $model = model(StoreModel::class);
 
        if ($this->storeModel->verifyHmac($params, $client_secret)) {
            $request = array(
                "client_id" => $client_id,
                "client_secret" => $client_secret,
                "code" => $authorization_code
            );
     //  print_r( $request);exit();
            #Generate access token URL
            $access_token_url = "https://" . $shop . "/admin/oauth/access_token";
        // print_r( $access_token_url);exit();
            $header = array();
 
            $response = $this->storeModel->generalcurlCall('POST', $access_token_url, $header, $request);
           //print_r($access_token_url); print_r($request);exit();
         //print_r($response);
             if (!empty($response)) {
                      $config->access_token = $response['access_token'];
                 //print_r($response);exit();
                #Get Shop Id using Access Token
                $shop_url  = "https://" . $shop;
                $store_api_url = $shop_url . "/admin/api/{$api_version}/shop.json";
               
                $header = array('Content-Type: application/x-www-form-urlencoded', 'X-Shopify-Access-Token: ' . $config->access_token);
                $store_data = $this->storeModel->generalcurlCall('GET', $store_api_url, $header);
 
                if (isset($store_data['shop']['id'])) {
                    $shop_id = $store_data['shop']['id'];
                    $verify_shop = $this->storeModel->verifyShop($shop_id, $shop, $config->access_token);
                    if (!in_array($verify_shop['code'], array(1, 3))) {
                        throw new PageNotFoundException('Code ' . $verify_shop['code'] . ' ' . $verify_shop['message']);
                    }
                    $shop = str_replace('.myshopify.com', '', $shop);
                    $this->response->redirect("https://admin.shopify.com/store/{$shop}/apps/");
                } else {
                    throw new PageNotFoundException("Shop Id not found.");
                }
            } else {
            }
        } else {
            throw new PageNotFoundException("This request is not from Shopify.");
        }
    }
}
}
