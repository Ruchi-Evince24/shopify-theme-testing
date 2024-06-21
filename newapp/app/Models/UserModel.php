<?php

namespace App\Models;

use CodeIgniter\Model;
use CodeIgniter\Exceptions\FrameworkException;

class UserModel extends Model
{
    protected $table = 'users';
    protected $allowedFields = ['firstname', 'lastname', 'email', 'password'];
}
