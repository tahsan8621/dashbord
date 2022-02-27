<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/
//$router->group(['prefix' => 'api/v1/'], function () use ($router) {

$router->get('/orders', 'OrdersController@index');
$router->post('/orders', 'OrdersController@store');
$router->get('/orders/{id}', 'OrdersController@show');
$router->get('/my-orders/', 'OrdersController@userOrder');
$router->get('/order-user/{user_name}', 'OrdersController@userOrderOld');

$router->get('order-items/{id}', 'OrderItemsController@show');
$router->get('order-details/{order_id}', 'OrderItemsController@orderDetails');
$router->post('order-items/{orderId}/{productId}', 'OrderItemsController@update');
$router->post('order-item-status/{orderId}/{productId}', 'OrderItemsController@statusUpdate');

$router->get('/products', 'ProductsController@allProducts');
$router->get('/user-products', 'ProductsController@index');
$router->post('/products', 'ProductsController@store');
$router->get('/products/{id}', 'ProductsController@show');
$router->get('/products/edit/{id}', 'ProductsController@showEdit');
$router->get('/product-reserved-price/{id}', 'ProductsController@productReservedPriceById');
$router->post('/products/{id}', 'ProductsController@update');
$router->delete('/products/{id}', 'ProductsController@destroy');
$router->get('/search-products/{name}', 'ProductsController@searchProducts');
$router->get('/category-search/{cat_id}', 'ProductsController@categorySearch');
$router->get('/search-keys/{name}', 'ProductsController@searchKeys');
$router->get('/bid-product-info/{id}', 'ProductsController@bidProducts');


$router->get('/categories', 'CategoriesController@index');
$router->post('/categories', 'CategoriesController@store');
$router->get('/categories/{id}', 'CategoriesController@show');
$router->get('/seller-categories/', 'CategoriesController@sellerCategories');

$router->get('/brands', 'BrandsController@index');
$router->post('/brands', 'BrandsController@store');
$router->get('/brands/{id}', 'BrandsController@show');

$router->get('/attributes', 'AttributesController@index');
$router->post('/attributes', 'AttributesController@store');
$router->post('/attribute/create', 'AttributesController@createAttr');
$router->post('/attribute/{id}', 'AttributesController@attributeDelete');
$router->post('/value/{id}', 'AttributesController@valueDelete');
$router->get('/attribute/names', 'AttributesController@createAttrGet');
$router->get('/attributes/{id}', 'AttributesController@show');

$router->get('regular/messages', 'RegularMsgController@index');
$router->get('regular/user-messages/', 'RegularMsgController@userMsgs');
$router->post('regular/messages', 'RegularMsgController@store');
$router->get('regular/messages/{id}', 'RegularMsgController@show');
$router->post('regular/messages/status/{id}', 'RegularMsgController@messageStatusUdate');
$router->get('regular/users/{product_id}', 'RegularMsgController@getUsers');
$router->get('regular/seller/{product_id}/{sender_email}', 'RegularMsgController@getUserMsg');
$router->get('user-offers-list', 'RegularMsgController@myOffers');

$router->get('user-addresses','AddressesController@userAddresses');
$router->post('address','AddressesController@store');
//
