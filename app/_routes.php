<?php
/**
 * @author: Chong Jun Xiang
 * 
 * This file is used to define the routes for the application.
 * 
 * Included check for admin, member and guest to protect the pages.
 */
define('PAGE_PATH', __DIR__ . '/page/');
define("ADMIN_PAGES", ["admin/*"]);
define("MEMBER_PAGES", ["cart/*", "payment/*", "order/*", "wishlist/*", "user/voucher"]);
define("GUEST_PAGES", ["login", "register", "forgot_password"]);
define('ROUTER', [
    "admin"  => ADMIN_PAGES,
    "member" => MEMBER_PAGES,
    "guest"  => GUEST_PAGES
]);

$requestPath = uri();
$request     = explode('/', $requestPath);

// Modified function to account for both the exact match and wildcard
function matchRoute($path, $routes)
{
    foreach ($routes as $route) {
        // Automatically check the exact match and the wildcard
        if (fnmatch($route, $path) || fnmatch(str_replace('/*', '', $route), $path)) {
            return true;
        }
    }
    return false;
}

if (matchRoute($requestPath, ROUTER['admin'])) {
    if (!isAdmin()) {
        temp("danger", "Only admins can access this page");
        return redirect('/');
    }
} else if (matchRoute($requestPath, ROUTER['member'])) {
    if (!isMember()) {
        temp("danger", "Only members can access this page");
        return redirect('/');
    }
} else if (matchRoute($requestPath, ROUTER['guest'])) {
    if (isLoggedIn()) {
        temp("warning", "You are already logged in");
        return redirect('/');
    }
}

// Default page
$page = 'home';
if (!empty($requestPath)) {
    $page = $requestPath;
}

// Convert dashes to spaces for the title, and make the first letter of each word uppercase
$_title = ucwords(str_replace(['-', '/', '_'], ' ', $page));

$indexFilePath = PAGE_PATH . $page . '/index.php';
$filePath      = PAGE_PATH . $page . '.php';

ob_start();

if (file_exists($indexFilePath)) {
    $view = $indexFilePath;
} else if (file_exists($filePath)) {
    $view = $filePath;
} else {
    $view = '_404.php';
}

require '_head.php';
require $view;
require '_foot.php';

require_once '_app.php';
if (isAdmin()) {
    require_once '_admin.php';
}

ob_end_flush();
