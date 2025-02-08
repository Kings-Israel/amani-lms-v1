<?php

if (! function_exists('uppercase')) {
    function uppercase($string) {
        if ( empty($string) ) return "-";
        
        return strtoupper($string);
    }
}