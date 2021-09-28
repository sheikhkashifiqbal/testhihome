<?php

//Product kind
define('SC_PRODUCT_SINGLE', 0);
define('SC_PRODUCT_BUILD', 1);
define('SC_PRODUCT_GROUP', 2);
//Product virtual
define('SC_VIRTUAL_PHYSICAL', 0);
define('SC_VIRTUAL_DOWNLOAD', 1);
define('SC_VIRTUAL_ONLY_VIEW', 2);
define('SC_VIRTUAL_SERVICE', 3);
//Product type
define('SC_PRODUCT_NORMAL', 0);
define('SC_PRODUCT_NEW', 1);
define('SC_PRODUCT_HOT', 2);
// list ID admin guard
define('SC_GUARD_ADMIN', ['1']);
// list ID language guard
define('SC_GUARD_LANGUAGE', ['1', '2']);
// list ID currency guard
define('SC_GUARD_CURRENCY', ['1', '2']);
// list ID ROLES guard
define('SC_GUARD_ROLES', ['1', '2']);
// list ID Page guard
define('SC_GUARD_PAGES', ['1', '2']);

/**
 * Admin define
 */
define('SC_ADMIN_MIDDLEWARE', ['web', 'admin', 'localization']);
define('SC_ADMIN_PREFIX', config('app.admin_prefix'));

define('SC_CONNECTION', 'mysql');
define('SC_CONNECTION_LOG', 'mysql');

/*
* store approval statuses
*/
define('STORE_PENDING', 0);
define('STORE_ARPROVED', 1);
define('STORE_REJECTED', 2);

/*
* HiHome Percentage
*/
define('HIHOME_PERCENTAGE', 8);

/*
* Delivery price
*/
define('DELIVERY_RATE', 30);

/*
* feedback user type
*/
define('SELLER_TYPE', 'seller');
define('CUSTOMER_TYPE', 'customer');

/*
*  CDN PATH
*/
define('CDN_URL', 'https://d2wirx2fde5hbu.cloudfront.net/fit-in/');

/*
* payment types
*/
define('COD', 'cod');
define('CARD', 'card');
