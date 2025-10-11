<?php
/**
 * Color Helper Functions
 * Lighten/darken hex colors
 * 
 * @package DealsIndia
 */

if (!defined('ABSPATH')) exit;

/**
 * Lighten a hex color
 * 
 * @param string $hex Hex color code
 * @param int $percent Percentage to lighten (0-100)
 * @return string Lightened hex color
 */
function dealsindia_lighten_color($hex, $percent) {
    $hex = str_replace('#', '', $hex);
    
    if (strlen($hex) == 3) {
        $hex = str_repeat(substr($hex, 0, 1), 2) . 
               str_repeat(substr($hex, 1, 1), 2) . 
               str_repeat(substr($hex, 2, 1), 2);
    }
    
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    
    $r = min(255, $r + ($percent / 100 * 255));
    $g = min(255, $g + ($percent / 100 * 255));
    $b = min(255, $b + ($percent / 100 * 255));
    
    $r = str_pad(dechex(round($r)), 2, '0', STR_PAD_LEFT);
    $g = str_pad(dechex(round($g)), 2, '0', STR_PAD_LEFT);
    $b = str_pad(dechex(round($b)), 2, '0', STR_PAD_LEFT);
    
    return '#' . $r . $g . $b;
}

/**
 * Darken a hex color
 * 
 * @param string $hex Hex color code
 * @param int $percent Percentage to darken (0-100)
 * @return string Darkened hex color
 */
function dealsindia_darken_color($hex, $percent) {
    $hex = str_replace('#', '', $hex);
    
    if (strlen($hex) == 3) {
        $hex = str_repeat(substr($hex, 0, 1), 2) . 
               str_repeat(substr($hex, 1, 1), 2) . 
               str_repeat(substr($hex, 2, 1), 2);
    }
    
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    
    $r = max(0, $r - ($percent / 100 * 255));
    $g = max(0, $g - ($percent / 100 * 255));
    $b = max(0, $b - ($percent / 100 * 255));
    
    $r = str_pad(dechex(round($r)), 2, '0', STR_PAD_LEFT);
    $g = str_pad(dechex(round($g)), 2, '0', STR_PAD_LEFT);
    $b = str_pad(dechex(round($b)), 2, '0', STR_PAD_LEFT);
    
    return '#' . $r . $g . $b;
}

/**
 * Adjust brightness of hex color
 * 
 * @param string $hex Hex color code
 * @param int $steps Brightness steps (-255 to 255)
 * @return string Adjusted hex color
 */
function dealsindia_adjust_brightness($hex, $steps) {
    // Remove # if present
    $hex = str_replace('#', '', $hex);
    
    // Expand shorthand hex
    if (strlen($hex) == 3) {
        $hex = str_repeat(substr($hex, 0, 1), 2) . 
               str_repeat(substr($hex, 1, 1), 2) . 
               str_repeat(substr($hex, 2, 1), 2);
    }
    
    // Convert to RGB
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    
    // Adjust brightness
    $r = max(0, min(255, $r + $steps));
    $g = max(0, min(255, $g + $steps));
    $b = max(0, min(255, $b + $steps));
    
    // Convert back to hex
    $r = str_pad(dechex(round($r)), 2, '0', STR_PAD_LEFT);
    $g = str_pad(dechex(round($g)), 2, '0', STR_PAD_LEFT);
    $b = str_pad(dechex(round($b)), 2, '0', STR_PAD_LEFT);
    
    return '#' . $r . $g . $b;
}
