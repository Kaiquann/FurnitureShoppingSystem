<?php

/**
 * @author: Chong Jun Xiang 
 * This function is used to compress an image.
 */
function compress_image($file)
{
    $file_info = getimagesize($file); // Get the image info
    $file_mime = $file_info['mime']; // Get the image mime type

    if (strpos($file_mime, 'image/') !== 0) { // Check if the image is valid
        return;
    }

    $image = null;
    switch ($file_mime) {
        case 'image/jpeg':
        case 'image/jpg':
            $image = imagecreatefromjpeg($file);
            break;
        case 'image/png':
            $image = imagecreatefrompng($file);
            break;
        case 'image/gif':
            $image = imagecreatefromgif($file);
            break;
        case 'image/webp':
            $image = imagecreatefromwebp($file);
            break;
        default:
            return;
    }

    $width  = imagesx($image);
    $height = imagesy($image);
    $image  = imagescale($image, $width * 0.8, $height * 0.8); // Scale the image to 80%
    imagejpeg($image, $file, 75); // Compress the image to 75% quality
    imagedestroy($image);
    return;
}

?>
