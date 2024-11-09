<?php

/**
 * @author: Chong Jun Xiang 
 * This function is used to generate a random string of a given length.
 */
function generate_random_string($length = 16)
{
    $characters        = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $characters_length = strlen($characters);
    $random_string     = '';
    for ($i = 0; $i < $length; $i++) {
        $random_string .= $characters[rand(0, $characters_length - 1)]; // Randomly select a character from $characters
    }
    return $random_string;
}

/**
 * @author: Chong Jun Xiang 
 * This function is used to generate a random password of a given length.
 */
function generate_password($length = 16)
{
    $characters        = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()_+';
    $characters_length = strlen($characters);
    $random_string     = '';
    for ($i = 0; $i < $length; $i++) {
        $random_string .= $characters[rand(0, $characters_length - 1)]; // Randomly select a character from $characters
    }
    return $random_string;
}

/**
 * @author: Chong Jun Xiang
 * This function is used to generate a random otp of a given length.
 */
function generate_otp($length = 6)
{
    $characters        = '0123456789';
    $characters_length = strlen($characters);
    $random_string     = '';
    for ($i = 0; $i < $length; $i++) {
        $random_string .= $characters[rand(0, $characters_length - 1)]; // Randomly select a character from $characters
    }
    return $random_string;
}

/**
 * @author: Chong Jun Xiang
 * This function is used to generate a random token.
 */
function generate_token()
{
    return bin2hex(random_bytes(32));
}
?>
