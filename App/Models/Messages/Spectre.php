<?php


namespace App\Models\Messages;

use App\Utilities;

/**
 * 
 *
 * PHP version 7.0
 */
class Spectre
{

    public function __construct($data = [])
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }
}
