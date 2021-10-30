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
    $routes->get('tampilkan-transaksi-dua-bulan-yang-lalu', 'Admin::showTransactionsTwoMonthsAgo');

    $routes->get('kategori-produk', 'ProductCategories::index');
    $routes->get('kategori-produk/membuat', 'ProductCategories::create');
    $routes->post('kategori-produk/menyimpan', 'ProductCategories::store');
    $routes->get('kategori-produk/edit/(:segment)', 'ProductCategories::edit/$1');
    $routes->post('kategori-produk/memperbaharui', 'ProductCategories::update');
    $routes->post('kategori-produk/menghapus', 'ProductCategories::remove');

    $routes->get('produk', 'Products::index');
    $routes->get('produk/tampilkan-detail/(:segment)', 'Products::showDetails/$1');
    $routes->get('produk/membuat', 'Products::create');
    $routes->post('produk/menyimpan', 'Products::store');
    $routes->get('produk/mencari/(:segment)', 'Products::search/$1');
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
