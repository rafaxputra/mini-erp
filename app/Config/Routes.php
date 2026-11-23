<?php
use CodeIgniter\Router\RouteCollection;
/**
 * @var RouteCollection $routes
 */
$routes->get('auth/login', 'Auth::login');
$routes->post('auth/login', 'Auth::loginPost');
$routes->get('auth/logout', 'Auth::logout');
$routes->get('/', 'Dashboard::index', ['filter' => 'auth']);
$routes->group('', ['filter' => 'auth'], function ($routes) {
    $routes->get('dashboard', 'Dashboard::index');
    $routes->get('categories', 'Categories::index');
    $routes->get('categories/create', 'Categories::create');
    $routes->post('categories/store', 'Categories::store');
    $routes->get('categories/edit/(:num)', 'Categories::edit/$1');
    $routes->post('categories/update/(:num)', 'Categories::update/$1');
    $routes->post('categories/delete/(:num)', 'Categories::delete/$1');
    $routes->get('products', 'Products::index');
    $routes->get('products/create', 'Products::create');
    $routes->post('products/store', 'Products::store');
    $routes->get('products/edit/(:num)', 'Products::edit/$1');
    $routes->post('products/update/(:num)', 'Products::update/$1');
    $routes->post('products/delete/(:num)', 'Products::delete/$1');
    $routes->get('suppliers', 'Suppliers::index');
    $routes->get('suppliers/create', 'Suppliers::create');
    $routes->post('suppliers/store', 'Suppliers::store');
    $routes->get('suppliers/edit/(:num)', 'Suppliers::edit/$1');
    $routes->post('suppliers/update/(:num)', 'Suppliers::update/$1');
    $routes->post('suppliers/delete/(:num)', 'Suppliers::delete/$1');
    $routes->get('customers', 'Customers::index');
    $routes->get('customers/create', 'Customers::create');
    $routes->post('customers/store', 'Customers::store');
    $routes->get('customers/edit/(:num)', 'Customers::edit/$1');
    $routes->post('customers/update/(:num)', 'Customers::update/$1');
    $routes->post('customers/delete/(:num)', 'Customers::delete/$1');
    $routes->get('users', 'Users::index');
    $routes->get('users/create', 'Users::create');
    $routes->post('users/store', 'Users::store');
    $routes->get('users/edit/(:num)', 'Users::edit/$1');
    $routes->post('users/update/(:num)', 'Users::update/$1');
    $routes->post('users/toggle/(:num)', 'Users::toggle/$1');
    $routes->get('roles', 'Roles::index');
    $routes->get('roles/create', 'Roles::create');
    $routes->post('roles/store', 'Roles::store');
    $routes->get('roles/edit/(:num)', 'Roles::edit/$1');
    $routes->post('roles/update/(:num)', 'Roles::update/$1');
    $routes->post('roles/delete/(:num)', 'Roles::delete/$1');
    $routes->get('purchase-orders', 'PurchaseOrders::index');
    $routes->get('purchase-orders/create', 'PurchaseOrders::create');
    $routes->post('purchase-orders/store', 'PurchaseOrders::store');
    $routes->get('purchase-orders/show/(:num)', 'PurchaseOrders::show/$1');
    $routes->post('purchase-orders/approve/(:num)', 'PurchaseOrders::approve/$1');
    $routes->post('purchase-orders/cancel/(:num)', 'PurchaseOrders::cancel/$1');
    $routes->get('goods-receipts', 'GoodsReceipts::index');
    $routes->get('goods-receipts/create', 'GoodsReceipts::create');
    $routes->post('goods-receipts/store', 'GoodsReceipts::store');
});

