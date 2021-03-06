<?php

/**
 * Get the path to a versioned webpack asset file
 *
 * @param string $name
 *
 * @return string
 * @throws Exception
 */
function hashedAsset($name)
{
    $manifestPath = public_path('manifest.json');

    if ( ! file_exists($manifestPath)) {
        throw new Exception('Unable to locate asset manifest file. '
            .'Please build assets.');
    }

    $manifest = json_decode(file_get_contents($manifestPath));

    return url($manifest->$name);
}

/**
 * Parse a data size string such as PHP INI variables
 *
 * @param string $string
 *
 * @return int
 */
function toBytes($string)
{
    preg_match('/(?<value>\d+)(?<option>.?)/i', trim($string), $matches);
    $inc = array(
        'g' => 1073741824, // (1024 * 1024 * 1024)
        'm' => 1048576, // (1024 * 1024)
        'k' => 1024,
    );

    $value = (int)$matches['value'];
    $key   = strtolower(trim($matches['option']));
    if (isset($inc[$key])) {
        $value *= $inc[$key];
    }

    return $value;
}