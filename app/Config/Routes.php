<?php

namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

// Load the system's routing file first, so that the app and ENVIRONMENT
// can override as needed.
if (file_exists(SYSTEMPATH . 'Config/Routes.php')) {
    require SYSTEMPATH . 'Config/Routes.php';
}

/*
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
/* $routes->setDefaultController('Home');
$routes->setDefaultMethod('index'); */
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(false);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.
$routes->group('admin', function ($routes) {
    $routes->get('', 'Admin::index');
    $routes->get('show-transactions-two-months-ago', 'Admin::showTransactionsTwoMonthsAgo');

    $routes->get('product-categories', 'ProductCategories::index');
    $routes->get('product-category/create', 'ProductCategories::create');
    $routes->post('product-category/store', 'ProductCategories::store');
    $routes->get('product-category/edit/(:segment)', 'ProductCategories::edit/$1');
    $routes->post('product-category/update', 'ProductCategories::update');
    $routes->post('product-category/delete', 'ProductCategories::delete');

    $routes->get('products', 'Products::index');
    $routes->get('product/show-details/(:segment)', 'Products::showDetails/$1');
    $routes->get('product/create', 'Products::create');
    $routes->post('product/store', 'Products::store');
    $routes->get('product/search/(:segment)', 'Products::search/$1');
    $routes->get('product/edit/(:segment)', 'Products::edit/$1');
    $routes->post('product/update', 'Products::update');
    $routes->post('product/delete-product-price', 'Products::deleteProductPrice');
    $routes->post('product/deletes', 'Products::deletes');
    
    $routes->get('users', 'Users::index');
    $routes->get('user/create', 'Users::create');
    $routes->post('user/store', 'Users::store');
    $routes->get('user/edit/(:segment)', 'Users::edit/$1');
    $routes->post('user/update', 'Users::update');
    $routes->post('user/delete', 'Users::delete');
    $routes->get('users/trash', 'Users::trash');
    $routes->post('user/restore', 'Users::restore');
});

$routes->get('sign_out', 'SignOut::index');
$routes->post('sign_in', 'SignIn::signIn');
$routes->get('/', 'SignIn::index');

/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (file_exists(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
