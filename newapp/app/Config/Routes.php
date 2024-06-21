<?php

use CodeIgniter\Router\RouteCollection;
use App\Controllers\install;
/**
 * @var RouteCollection $routes
 */

$routes->get('/register', 'Home::index');
//$routes->get('/register', 'Home::register');
$routes->post('/register', 'Home::insert');
$routes->get('/install','Install::installApp');
$routes->get('/verifyInstall','Install::getAuthorizationCode');