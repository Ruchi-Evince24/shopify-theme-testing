<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\Controller;
use CodeIgniter\Database\Database;
class Home extends BaseController
{
    public function index()
    {
       
        return view('register');
    }
   
    public function insert()
    {
        $db = \Config\Database::connect();
        //return view('register');
         $data = [ 'firstname' => $this->request->getPost('firstname'),
         'lastname' => $this->request->getPost('lastname'),
         'email' => $this->request->getPost('email'),
         'password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT) // Hashing the password
    ];
       
    $user = new UserModel();
       $user->insert($data); 
       return redirect()->to('/register');
    }
    
    
}
