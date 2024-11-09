<?php
// ============================================================================
// PHP Setups
// ============================================================================

date_default_timezone_set('Asia/Kuala_Lumpur');
session_start();

// ============================================================================
// Important Constants
// ============================================================================
define('COMPANY_NAME', 'TARUMT FURNITURE');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // Maximum file size
define('USER_SESSION', 'user_session'); // User session key

// ============================================================================
// PHP Libraries
// ============================================================================

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/lib/IP.php';
require_once __DIR__ . '/lib/Captcha.php';
require_once __DIR__ . '/lib/String.php';
require_once __DIR__ . '/lib/ImageCompress.php';
require_once __DIR__ . '/lib/SimpleImage.php';
require_once __DIR__ . '/lib/SimplePager.php';
require_once __DIR__ . '/lib/Mailer.php';
require_once __DIR__ . '/lib/Stripe.php';

// ============================================================================
// General Page Functions
// ============================================================================

// Is GET request?
function is_get()
{
    return $_SERVER['REQUEST_METHOD'] == 'GET';
}

// Is POST request?
function is_post()
{
    return $_SERVER['REQUEST_METHOD'] == 'POST';
}

// Obtain GET parameter
function get($key, $value = null)
{
    $value = $_GET[$key] ?? $value;
    return is_array($value) ? array_map('trim', $value) : trim($value);
}

// Obtain POST parameter
function post($key, $value = null)
{
    $value = $_POST[$key] ?? $value;
    return is_array($value) ? array_map('trim', $value) : trim($value);
}

// Obtain REQUEST (GET and POST) parameter
function req($key, $value = null)
{
    $value = $_REQUEST[$key] ?? $value;
    return is_array($value) ? array_map('trim', $value) : trim($value);
}

/**
 * @author: Chong Jun Xiang
 * This function supports multiple files and single file upload.
 * It returns null if no files are uploaded
 */
function req_file($key)
{
    $files = $_FILES[$key] ?? null;
    if (!$files) {
        return null;
    }
    if (is_array($files['name'])) {
        $file_objects = [];
        foreach ($files['name'] as $index => $name) {
            if ($files['error'][$index] == 0) {
                $file           = (object) [
                    'name'      => $name,
                    'full_path' => $files['full_path'][$index] ?? $name,
                    'type'      => $files['type'][$index],
                    'tmp_name'  => $files['tmp_name'][$index],
                    'error'     => $files['error'][$index],
                    'size'      => $files['size'][$index]
                ];
                $file_objects[] = $file;
            }
        }
        return $file_objects ?: null;
    }
    if ($files['error'] == 0) {
        return (object) [
            'name'      => $files['name'],
            'full_path' => $files['full_path'] ?? $files['name'],
            'type'      => $files['type'],
            'tmp_name'  => $files['tmp_name'],
            'error'     => $files['error'],
            'size'      => $files['size']
        ];
    }
    return null;
}

/**
 * Redirect to URL
 */
function redirect($url = null)
{
    $url ??= $_SERVER['REQUEST_URI'];
    if (!headers_sent()) {
        header("Location: $url");
        exit();
    }
    echo "<script>window.location.href='$url';</script>";
    echo "<noscript><meta http-equiv='refresh' content='0;url=$url'></noscript>";
    exit();
}

// Set or get temporary session variable
function temp($key, $value = null)
{
    if ($value !== null) {
        $_SESSION["temp_$key"] = $value;
    } else {
        $value = $_SESSION["temp_$key"] ?? null;
        unset($_SESSION["temp_$key"]);
        return $value;
    }
    return '';
}

// Is money?
function is_money($value)
{
    return preg_match('/^\-?\d+(\.\d{1,2})?$/', $value);
}

// Is email?
function is_email($value)
{
    return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
}

// Is date?
function is_date($value, $format = 'Y-m-d')
{
    $d = DateTime::createFromFormat($format, $value);
    return $d && $d->format($format) == $value;
}

// Is time?
function is_time($value, $format = 'H:i')
{
    $d = DateTime::createFromFormat($format, $value);
    return $d && $d->format($format) == $value;
}

// Return year list items
function get_years($min, $max, $reverse = false)
{
    $arr = range($min, $max);

    if ($reverse) {
        $arr = array_reverse($arr);
    }

    return array_combine($arr, $arr);
}

// Return month list items
function get_months()
{
    return [
        1  => 'January',
        2  => 'February',
        3  => 'March',
        4  => 'April',
        5  => 'May',
        6  => 'June',
        7  => 'July',
        8  => 'August',
        9  => 'September',
        10 => 'October',
        11 => 'November',
        12 => 'December',
    ];
}

// Return TRUE if ALL array elements meet the condition given
function array_all($arr, $fn)
{
    foreach ($arr as $k => $v) {
        if (!$fn($v, $k)) {
            return false;
        }
    }
    return true;
}

/**
 * @author: Liew Kai Quan
 * 
 * This function is used to set or get the session variable.
 */
function session($key, $value = null)
{
    if ($value !== null) {
        $_SESSION["session_$key"] = $value;
        return true;
    } else {
        return $_SESSION["session_$key"] ?? null;
    }
}

/**
 * @author: Chong Jun Xiang
 * This function is used to unset the session variable.
 */
function unsetSession($key)
{
    unset($_SESSION["session_$key"]);
}

/**
 * @author: Chong Jun Xiang
 * This function is used to get the cookies.
 */
function getCookies($key)
{
    return $_COOKIE[$key] ?? null;
}

/**
 * @author: Chong Jun Xiang
 * This function is used to unset the cookies.
 */
function unsetCookies($key)
{
    setcookie($key, '', time() - 3600, '/');
}

/**
 * @author: Chong Jun Xiang
 * This function is used to destroy the cookies.
 */
function cookie_destroy()
{
    if (isset($_SERVER['HTTP_COOKIE'])) {
        $cookies = explode(';', $_SERVER['HTTP_COOKIE']);
        foreach ($cookies as $cookie) {
            $cookie = trim(explode('=', $cookie)[0]);
            setcookie($cookie, '', time() - 3600, '/');
            unset($_COOKIE[$cookie]);
        }
    }
}

// Return local root path
function root($path = '')
{
    return "$_SERVER[DOCUMENT_ROOT]/$path";
}

/**
 * @author: Chong Jun Xiang
 * This function is used to get the base URL.
 */
function base($path = '')
{
    $scheme = $_SERVER['REQUEST_SCHEME'] ?? 'http';
    return "$scheme://$_SERVER[SERVER_NAME]:$_SERVER[SERVER_PORT]/$path";
}

/**
 * @author: Chong Jun Xiang
 * This function is used to get the URI path.
 */
function uri()
{
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    $uri = parse_url($uri, PHP_URL_PATH);
    $uri = trim($uri, '/');
    return $uri;
}

/**
 * @author: Chong Jun Xiang
 * This function is used to get the base URL with the URI.
 */
function baseUri()
{
    return base(uri());
}
/**
 * @author: Chong Jun Xiang
 * This function is used to get the query string.
 */
function query()
{
    return isset($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '';
}

/**
 * @author: Chong Jun Xiang
 * This function used to get the uri with query string.
 */
function uriQuery()
{
    return uri() . query();
}

/**
 * @author: Chong Jun Xiang
 * This function is used to get the base URL with the query string.
 */
function baseUriQuery()
{
    return baseUri() . query();
}

/**
 * @author: Chong Jun Xiang
 * This function is used to easy get the url for forwarding.
 */
function getForwardUrl()
{
    return urlencode('/' . uriQuery()) ?? '';
}

/**
 * @author: Chong Jun Xiang
 * This function is used to get the current date and time.
 */
function getDateTime($format = 'Y-m-d H:i:s')
{
    return date($format);
}

/**
 * @author: Liew Kai Quan
 * This function is used to compare the page url same or not
 */
function isCurrentPage($comparePageName, $parameterName = "", $parameterValue = "")
{
    $currentPage = baseUri() . $parameterName . $parameterValue;
    return $currentPage == $comparePageName;
}

/**
 * @author: Chong Jun Xiang
 * This function is used to upload, crop, compress, and save the image.
 */
function save_photo($file, $folder = '/images', $width = 200, $height = 200)
{
    $photoName  = uniqid() . '.jpg';
    $fullFolder = root($folder);
    $filePath   = "$folder/$photoName";
    if (!file_exists($fullFolder)) {
        mkdir($fullFolder, 0777, true);
    }
    $tmpName = match (true) {
        is_array($file) && array_key_exists('tmp_name', $file) => $file['tmp_name'],
        is_string($file)                                       => $file,
        is_object($file) && property_exists($file, 'tmp_name') => $file->tmp_name,
        default                                                => throw new InvalidArgumentException('Invalid file input'),
    };
    compress_image($tmpName);
    $img = new SimpleImage();
    $img->fromFile($tmpName)
        ->thumbnail($width, $height)
        ->toFile(root($filePath), 'image/jpeg');
    return $filePath;
}

/**
 * @author: Chong Jun Xiang
 * This function is used to delete the file.
 */
function file_delete($path)
{
    if (!$path) {
        return;
    }
    $path = root($path);
    if (file_exists($path)) {
        unlink($path);
    }
}

// ============================================================================
// HTML Helpers
// ============================================================================

// Encode HTML special characters
function encode($value)
{
    return htmlentities($value);
}

// Generate <input type='text'>
function html_text($key, $attr = '')
{
    $value = encode($GLOBALS[$key] ?? '');
    echo "<input type='text' id='$key' name='$key' value='$value' $attr>";
}

// Generate <input type='radio'> list
function html_radios($key, $items, $br = false)
{
    $value = encode($GLOBALS[$key] ?? '');
    echo '<div>';
    foreach ($items as $id => $text) {
        $state = $id == $value ? 'checked' : '';
        echo "<label><input type='radio' id='{$key}_$id' name='$key' value='$id' $state>$text</label>";
        if ($br) {
            echo '<br>';
        }
    }
    echo '</div>';
}

/**
 * @author: Chong Jun Xiang
 * @description: This function using list of int to generate <select> with default
 */
const SELECT_OPTION_NONE = '';
function html_select($key, $items, $default = SELECT_OPTION_NONE, $attr = '')
{
    $value = encode($GLOBALS[$key] ?? '');
    echo "<select id='$key' name='$key' $attr>";
    if ($default === SELECT_OPTION_NONE) {
        echo "<option value=''>- Select One -</option>";
    } else if (!in_array($default, $items)) {
        echo "<option value='0'>Default</option>";
    }
    foreach ($items as $id => $text) {
        $state = $id == $value ? 'selected' : '';
        if ($text === $default) {
            echo "<option value='$id' selected>$text</option>";
            continue;
        }
        echo "<option value='$id' $state>$text</option>";
    }
    echo '</select>';
}

/**
 * @author: Chong Jun Xiang
 * This function is used to generate an input field with type search.
 */
function html_search($key, $attr = '')
{
    $value = encode($GLOBALS[$key] ?? '');
    echo "<input type='text' id='$key' name='$key' value='$value' placeholder='Search...' $attr>";
}

/**
 * @author: Chong Jun Xiang
 * This function is used to generate an input field with type number.
 */
function html_number($key, $min = '', $max = '', $step = '', $attr = '')
{
    $value = encode($GLOBALS[$key] ?? '');
    echo "<input type='number' id='$key' name='$key' value='$value' min='$min' max='$max' step='$step' $attr>";
}

/**
 * @author: Chong Jun Xiang
 * This function is used to generate a textarea field.
 */
function html_textarea($key, $attr = '')
{
    $value = encode($GLOBALS[$key] ?? '');
    echo "<textarea id='$key' name='$key' $attr>$value</textarea>";
}

/**
 * @author: Chong Jun Xiang
 * This function is used to generate an input field with type email.
 */
function html_email($key, $attr = '')
{
    echo "<input type='email' id='$key' name='$key' $attr>";
}

/**
 * @author: Chong Jun Xiang
 * This function is used to generate an input field with type password.
 */
function html_password($key, $attr = '')
{
    echo "<input type='password' id='$key' name='$key' $attr>";
}

/**
 * @author: Chong Jun Xiang
 * This function is used to generate an input field with type file.
 */
function html_file($key, $accept = '', $attr = '')
{
    echo "<input type='file' id='$key' name='$key' accept='$accept' $attr>";
}

/**
 * @author: Chong Jun Xiang
 * This function is used to generate an input field with type checkbox.
 */
function html_checkbox($key, $value = '1', $label = '', $attr = '')
{
    $status = encode($GLOBALS[$key] ?? '');
    $status = $status == 1 ? 'checked' : '';
    echo "<label><input type='checkbox' id='$key' name='$key' value='$value' $status $attr>$label</label>";
}

// Generate <input type='checkbox'> list
function html_checkboxes($key, $items, $br = false)
{
    $values = $GLOBALS[$key] ?? [];
    if (!is_array($values)) $values = [];

    echo '<div>';
    foreach ($items as $id => $text) {
        $state = in_array($id, $values) ? 'checked' : '';
        echo "<label><input type='checkbox' id='{$key}_$id' name='{$key}[]' value='$id' $state>$text</label>";
        if ($br) {
            echo '<br>';
        }
    }
    echo '</div>';
}

/**
 * @author: Liew Kai Quan
 * This function is used to generate an input field with type hidden.
 */
function html_hidden($key, $value = '1', $attr = '')
{
    $value ??= encode($GLOBALS[$key] ?? '');
    echo "<input type='hidden' id='$key' name='$key' value='$value' $attr>";
}

/**
 * @author Chong Jun Xiang
 * This function is used to generate an input field with type image.
 */
function html_image($key, $value = '/images/noimage.jpg', $attr = '')
{
    echo "<img id='$key' name='$key' src='$value' $attr loading='lazy'>";
}

// Generate <input type='date'>
function html_date($key, $min = '', $max = '', $attr = '')
{
    $value = encode($GLOBALS[$key] ?? '');
    echo "<input type='date' id='$key' name='$key' value='$value'
                 min='$min' max='$max' $attr>";
}

// Generate <input type='time'>
function html_time($key, $attr = '')
{
    $value = encode($GLOBALS[$key] ?? '');
    echo "<input type='time' id='$key' name='$key' value='$value' $attr>";
}

/**
 * @author: Chong Jun Xiang
 * This function is used to generate an input field with type datetime-local.
 */
function html_datetime($key, $attr = '')
{
    $value = encode($GLOBALS[$key] ?? '');
    echo "<input type='datetime-local' id='$key' name='$key' value='$value' $attr>";
}

/**
 * @author: Chong Jun Xiang
 * This function is used to print the value that encode the html entities.
 * Supports multiple arguments
 */
function html_print(...$values) : void
{
    foreach ($values as $value) {
        echo encode($value) ?: 'N/A';
    }
}

/**
 * Take From Practical 5
 * Generate table headers <th>
 */
function table_headers($fields, $sort, $dir, $href = '')
{
    foreach ($fields as $k => $v) {
        $d = 'asc'; // Default direction
        $c = '';    // Default class

        if ($k == $sort) {
            $d = $dir == 'asc' ? 'desc' : 'asc';
            $c = $dir;
        }

        echo "<th><a href='?sort=$k&dir=$d&$href' class='$c'>$v</a></th>";
    }
}

// ============================================================================
// Error Handlings
// ============================================================================

// Generate <span class='err'>
function err($key)
{
    global $_err;
    if ($_err[$key] ?? false) {
        echo "<span class='err' style='color:red';>$_err[$key]</span>";
    } else {
        echo '<span></span>';
    }
}

// ============================================================================
// Database Setups and Functions
// ============================================================================

// Global PDO object
try {
    $_db = new PDO('mysql:dbname=onlineshoppingdb;host=localhost;port=3306', 'root', '', [
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
    ]);
}
catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Is unique?
function is_unique($value, $table, $field)
{
    global $_db;
    $stm = $_db->prepare("SELECT COUNT(*) FROM $table WHERE $field = ?");
    $stm->execute([$value]);
    return $stm->fetchColumn() == 0;
}

// Is exists?
function is_exists($value, $table, $field)
{
    global $_db;
    $stm = $_db->prepare("SELECT COUNT(*) FROM $table WHERE $field = ?");
    $stm->execute([$value]);
    return $stm->fetchColumn() > 0;
}

/**
 * @author: Chong Jun Xiang
 * This function is used to select the data from the database.
 */
function db_select($table, $field, $value)
{
    global $_db;
    $stm = $_db->prepare("SELECT * FROM $table WHERE $field = ?");
    $stm->execute([$value]);
    return $stm->fetchAll();
}

/**
 * @author: Chong Jun Xiang
 * This function is used to select the single data from the database.
 */
function db_select_single($table, $field, $value)
{
    global $_db;
    $stm = $_db->prepare("SELECT * FROM $table WHERE $field = ?");
    $stm->execute([$value]);
    return $stm->fetch();
}

/**
 * @author: Choong Jun Xiang
 * This function is used to select all the data from the database.
 */
function db_select_all($table)
{
    global $_db;
    $stm = $_db->prepare("SELECT * FROM $table");
    $stm->execute();
    return $stm->fetchAll();
}

/**
 * @author: Liew Kai Quan
 * This function is used to search all the data from the database.
 */
function db_search_all($table, $value, $field1, $field2)
{
    global $_db;
    $stm = $_db->prepare("SELECT * FROM $table WHERE ($field1 LIKE ?) OR ($field2 LIKE ?)");
    $stm->execute([$value, $value]);
    return $stm->fetchAll();
}

/**
 * @author: Chong Jun Xiang
 * This function is used to insert the data into the database.
 */
function db_insert($table, $data)
{
    global $_db;
    $fields = array_keys($data);
    $fields = implode(', ', $fields);
    $values = array_fill(0, count($data), '?');
    $values = implode(', ', $values);
    $stm    = $_db->prepare("INSERT INTO $table ($fields) VALUES ($values)");
    return $stm->execute(array_values($data));
}

/**
 * @author: Chong Jun Xiang
 * This function is used to update the data in the database.
 */
function db_update($table, $data, $field, $value)
{
    global $_db;
    $fields   = array_keys($data);
    $fields   = array_map(fn($f) => "$f = ?", $fields);
    $fields   = implode(', ', $fields);
    $stm      = $_db->prepare("UPDATE $table SET $fields WHERE $field = ?");
    $values   = array_values($data);
    $values[] = $value;
    return $stm->execute($values);
}

/**
 * @author: Chong Jun Xiang
 * This function is used to delete the data from the database.
 */
function db_delete($table, $field, $value)
{
    global $_db;
    $stm = $_db->prepare("DELETE FROM $table WHERE $field = ?");
    return $stm->execute([$value]);
}

/**
 * @author: Liew Kai Quan
 * @update: Chong Jun Xiang
 * 
 * This function is used to encrypt the password.
 */
function encrypt($value)
{
    return hash("sha256", $value);
}

/**
 * @author: Chong Jun Xiang & Liew Kai Quan
 * This function is used to store remember user session.
 */
function remember_me($token = null)
{
    if (!$token) {
        $token = generate_token();
    }
    $cookieName   = 'session_token';
    $cookieValue  = $token;
    $cookieExpire = time() + (86400 * 7);
    $cookiePath   = '/';
    setcookie($cookieName, $cookieValue, $cookieExpire, $cookiePath);
    return $token;
}

// Price Range List
const PRICE_RANGE_LIST = [
    1 => '< RM500',
    2 => 'RM500 - RM1000',
    3 => 'RM1000 - RM3000',
    4 => 'RM3000 - RM5000',
    5 => '> RM5000',
];
/**
 * @author: Chong Jun Xiang
 * This function is used to check and get the price range with DB query.
 */
function get_price_range($price_range)
{
    switch ($price_range) {
        case 1:
            $price_range_query = '0 AND 500';
            break;
        case 2:
            $price_range_query = '500 AND 1000';
            break;
        case 3:
            $price_range_query = '1000 AND 3000';
            break;
        case 4:
            $price_range_query = '3000 AND 5000';
            break;
        case 5:
            $price_range_query = '5000 AND 999999';
            break;
        case 0:
        default:
            $price_range_query = '0 AND 999999';
    }
    return $price_range_query;
}

// Table Page Rows
const TABLE_ROWS_LIST = [
    1 => '10',
    2 => '20',
    3 => '50'
];
/**
 * @author: Chong Jun Xiang
 * This function is used to check and get the table rows with DB query.
 */
function get_table_rows($table_rows)
{
    return TABLE_ROWS_LIST[$table_rows] ?? 10;
}

// Stock List
const STOCK_SORT_LIST = [10, 50, 100, 500, 1000, 2000, 5000];
/**
 * @author: Chong Jun Xiang
 * This function is used to check and get the stock limit with DB query.
 */
function get_stock_limit($stock_sort)
{
    return "0 AND " . (STOCK_SORT_LIST[$stock_sort] ?? 10);
}

/**
 * @author: Chong Jun Xiang
 * This function is used to get the roles with the user current permission can be access
 */
function get_roles()
{
    global $_db;
    $roles = $_db->query('SELECT name FROM roles WHERE name != "superadmin"')->fetchAll();
    $roles = array_column($roles, 'name', 'name');
    return $roles;
}

function roles_can_access()
{
    global $_USER_DATA;
    $roles = get_roles();
    switch ($_USER_DATA->role) {
        case 'admin':
            unset($roles['admin']);
            break;
    }
    return $roles;
}

// ============================================================================
// Validation
// ============================================================================

/**
 * @author: Chong Jun Xiang
 * This function is used to check the image.
 */
function check_image($file)
{
    if (empty($file) || empty($file->tmp_name)) {
        return 'Required';
    }
    if (!file_exists($file->tmp_name)) {
        return 'File does not exist';
    }
    $mimeType = mime_content_type($file->tmp_name);
    $fileSize = filesize($file->tmp_name);
    if (strpos($mimeType, 'image/') !== 0) {
        return 'Must be an image';
    }
    if ($fileSize > MAX_FILE_SIZE) {
        return 'Image size must be less than 5MB';
    }
    return '';
}

/**
 * @author: Chong Jun Xiang
 * This function is used to get user local data with auto remove the expired session token.
 */
function getUserLocalData()
{
    global $_db;
    $_db->query('DELETE FROM remember_me WHERE created_at < DATE_SUB(NOW(), INTERVAL 7 DAY)');
    $session_token = getCookies('session_token');
    if (!$session_token) return null;
    $remember_me_data = db_select_single('remember_me', 'token', $session_token);
    if (!$remember_me_data) return null;
    $user_data = db_select_single('users', 'id', $remember_me_data->user_id);
    if (!$user_data) return null;
    return $user_data;
}

/**
 * @author: Chong Jun Xiang
 * This function is used to get user data.
 */
function getUserData()
{
    $user_cookie_data = getUserLocalData();
    if (!$user_cookie_data) {
        unsetCookies('session_token');
    } else {
        session(USER_SESSION, $user_cookie_data);
    }
    $user_session_data = session(USER_SESSION);
    if (!$user_session_data) return null;
    global $_db;
    $stmt = $_db->prepare('
        SELECT * FROM users
        WHERE id = ?
        AND is_blocked = 0
        AND is_deleted = 0
    ');
    $stmt->execute([$user_session_data->id]);
    $user_data = $stmt->fetch();
    if (!$user_data) {
        unsetSession(USER_SESSION);
        unsetCookies('session_token');
        return null;
    }
    return $user_data;
}

/**
 * @author: Chong Jun Xiang
 * This function is used to check user login.
 */
function isLoggedIn()
{
    global $_USER_DATA;
    return $_USER_DATA;
}

function isSuperAdmin()
{
    global $_USER_DATA;
    if (!$_USER_DATA) return false;
    return in_array($_USER_DATA->role, ['superadmin']) && !$_USER_DATA->is_deleted && !$_USER_DATA->is_blocked;
}

/**
 * @author: Chong Jun Xiang
 * This function is used to check admin login.
 */
function isAdmin()
{
    global $_USER_DATA;
    if (!$_USER_DATA) return false;
    return in_array($_USER_DATA->role, ['superadmin', 'admin']) && !$_USER_DATA->is_deleted && !$_USER_DATA->is_blocked;
}

function isMember()
{
    global $_USER_DATA;
    if (!$_USER_DATA) return false;
    return $_USER_DATA->role === 'member' && !$_USER_DATA->is_deleted && !$_USER_DATA->is_blocked;
}

/**
 * @author: Chong Jun Xiang
 * This function is used to check if the value is valid.
 * Default format is /^[a-zA-Z0-9 ]+$/, mean only letters, numbers and spaces are allowed
 */
const DEFAULT_REGEX = '/^[a-zA-Z0-9 ]+$/';
function isValid($value, $format = DEFAULT_REGEX)
{
    return preg_match($format, $value);
}

// ============================================================================
// Global Constants and Variables
// ============================================================================
// OTP Email Design
const OTP_SUBJECT = "OTP";
const OTP_BODY    = "<!DOCTYPE html><html lang='en'><head><meta charset='UTF-8'><meta name='viewport' content='width=device-width,initial-scale=1'><title>Email OTP Design</title><style>body{font-family:Arial,sans-serif}.container{width:100%;max-width:600px;margin:0 auto}.card{border:1px solid #ddd;border-radius:5px;margin-top:50px;padding:20px;text-align:center}.card-title{font-size:24px;margin-bottom:20px}.card-text{font-size:18px;margin-bottom:20px}.otp{font-size:24px;font-weight:700}</style></head><body><div class='container'><div class='card'><h5 class='card-title'>OTP Verification</h5><p class='card-text'>Your One-Time Password (OTP) is:<br><span class='otp'>{otpvalue}</span></p></div></div></body></html>";
// Reset Password Email Design
const RESET_PASSWORD_SUBJECT = "Reset Password";
const RESET_PASSWORD_BODY    = "<!DOCTYPE html><html lang='en'><head><meta charset='UTF-8'><meta name='viewport' content='width=device-width,initial-scale=1'><title>Reset Password</title><style>body{font-family:Arial,sans-serif}.container{width:100%;max-width:600px;margin:0 auto}.card{border:1px solid #ddd;border-radius:5px;margin-top:50px;padding:20px;text-align:center}.card-title{font-size:24px;margin-bottom:20px}.card-text{font-size:18px;margin-bottom:20px}.btn{display:inline-block;color:#fff;background-color:#0d6efd;border-color:#0d6efd;padding:.375rem .75rem;font-size:1rem;line-height:1.5;border-radius:.25rem;text-decoration:none}.btn:hover{color:#fff;background-color:#0b5ed7;border-color:#0a58ca}</style></head><body><div class='container'><div class='card'><h5 class='card-title'>Reset Your Password</h5><p class='card-text'>You requested to reset your password. Click the button below to continue.</p><a href='{{ url }}' class='btn'>Reset Password</a></div></div></body></html>";
// Illegal Login Email Design
const ILLEGAL_LOGIN_SUBJECT = "Illegal Login";
const ILLEGAL_LOGIN_BODY    = "<!DOCTYPE html><html lang='en'><head><meta charset='UTF-8'><meta name='viewport' content='width=device-width,initial-scale=1'><title>Illegal Login</title><style>body{font-family:Arial,sans-serif}.container{width:100%;max-width:600px;margin:0 auto}.card{border:1px solid #ddd;border-radius:5px;margin-top:50px;padding:20px;text-align:center}.card-title{font-size:24px;margin-bottom:20px}.card-text{font-size:18px;margin-bottom:20px}.btn{display:inline-block;color:#fff;background-color:#0d6efd;border-color:#0d6efd;padding:.375rem .75rem;font-size:1rem;line-height:1.5;border-radius:.25rem;text-decoration:none}.btn:hover{color:#fff;background-color:#0b5ed7;border-color:#0a58ca}</style></head><body><div class='container'><div class='card'><h5 class='card-title'>Someone tried to login to your account</h5><p class='card-text'>Someone tried to login to your account.</p><p class='card-text'>If this was you, you can ignore this email.</p><p class='card-text'>Email: {{ email }}<br>IP Address: {{ ip }}</p><p class='card-text'>If this wasn't you, please reset your password.</p><a href='{{ link }}' class='btn'>Reset Password</a></div></div></body></html>";
// Password Changed Email Design
const PASSWORD_CHANGED_SUBJECT = "Password Changed";
const PASSWORD_CHANGED_BODY    = "<!DOCTYPE html><html lang='en'><head><meta charset='UTF-8'><meta name='viewport' content='width=device-width,initial-scale=1'><title>Password Changed</title><style>body{font-family:Arial,sans-serif}.container{width:100%;max-width:600px;margin:0 auto}.card{border:1px solid #ddd;border-radius:5px;margin-top:50px;padding:20px;text-align:center}.card-title{font-size:24px;margin-bottom:20px}.card-text{font-size:18px;margin-bottom:20px}.btn{display:inline-block;color:#fff;background-color:#0d6efd;border-color:#0d6efd;padding:.375rem .75rem;font-size:1rem;line-height:1.5;border-radius:.25rem;text-decoration:none}.btn:hover{color:#fff;background-color:#0b5ed7;border-color:#0a58ca}</style></head><body><div class='container'><div class='card'><h5 class='card-title'>Password Changed</h5><p class='card-text'>You have changed your password on this account.</p><p class='card-text'>If this was you, you can ignore this email.</p><p class='card-text'>Date: {{ date }}<br>Email: {{ email }}<br>IP Address: {{ ip }}</p><p class='card-text'>If this wasn't you, please reset your password.</p><a href='{{ link }}' class='btn'>Reset Password</a></div></div></body></html>";
// Low Stock Alert Email Design
const LOW_STOCK_ALERT_SUBJECT = "Low Stock Alert";
const LOW_STOCK_ALERT_BODY    = "<!DOCTYPE html><html lang='en'><head><meta charset='UTF-8'><meta name='viewport' content='width=device-width,initial-scale=1'><title>Low Stock Alert</title><style>body{font-family:Arial,sans-serif}.container{width:100%;max-width:600px;margin:0 auto}.card{border:1px solid #ddd;border-radius:5px;margin-top:50px;padding:20px;text-align:center}.card-title{font-size:24px;margin-bottom:20px}.card-text{font-size:18px;margin-bottom:20px}.btn{display:inline-block;color:#fff;background-color:#0d6efd;border-color:#0d6efd;padding:.375rem .75rem;font-size:1rem;line-height:1.5;border-radius:.25rem;text-decoration:none}.btn:hover{color:#fff;background-color:#0b5ed7;border-color:#0a58ca}.table{width:100%;border-collapse:collapse;margin-bottom:20px}.table td{padding:10px;border:1px solid #ddd}.table th{padding:10px;border:1px solid #ddd}.table tr:nth-child(even){background-color:#f2f2f2}.table tr:hover{background-color:#ddd}</style></head><body><div class='container'><div class='card'><h3>Low Stock Alert on {{ created_at }}</h3><table class='table'><thead><th>Product</th><th>Quantity</th></thead><tbody>{{ product_list }}</tbody></table></div></div></body></html>";
// Global error array
$_err       = [];
$_USER_DATA = getUserData();
// Global Roles
$_ROLES = get_roles();
// Discount Type List
const DISCOUNT_TYPE_LIST = [
    'percentage' => 'percentage',
    'fixed'      => 'fixed'
];
// Voucher Available List
const VOUCHER_AVAILABLE_LIST = [
    'available'   => 'available',
    'unavailable' => 'unavailable'
];
