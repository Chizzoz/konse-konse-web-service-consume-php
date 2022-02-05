<?php
/**
 * konse-konse-web-service-consume-php
 *
 * @author Chinyanta Mwenya <chinyanta@oneziko.com>
 * @license https://github.com/Chizzoz/konse-konse-web-service-consume-php/blob/main/LICENSE MIT
 * @link https://github.com/Chizzoz/konse-konse-web-service-consume-php
 */

// Include cgrate function
include 'cgrate.php';

// Call cgrate function
$response = cgrate('260978125791', 12.34);

// Format JSON in header
header('Content-Type: application/json');
// dump response
print_r($response);
