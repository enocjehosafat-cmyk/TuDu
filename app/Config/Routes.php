<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');

//$routes->get('tudu','tudu::index');
//$routes->get('tudu/new','tudu::create');
//$routes->get('tudu/edit/(:num)','tudu::create/$1');

$routes->presenter('tudu');
$routes->presenter('categoria');