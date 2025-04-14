<?php

namespace App\Helpers;


class NumberFormat
{

    /**
     * Convert amount to the crore format
     */
    public function change($number, $decimal = 0)
    {

        //$minNum = 10000000, $sym = 'Cr'
        // if ($number >= $minNum) {
        //     $number = number_format($number / $minNum, $decimal) . ' ' . $sym;
        // } else 

        if ($number >= 10000000) {
            //if amount not >= 1 Cr.
            $number = number_format($number / 10000000, 2) . ' Cr';
        } else if ($number >= 100000) {
            //if amount not >= 1 Cr.
            $number = number_format($number / 100000, 2) . ' Lac';
        } else if ($number >= 1000) {
            //if amount not >= 1 Lac.
            $number = number_format($number / 1000, 2) . ' K';
        } else {
            $number = number_format($number, $decimal);
        }

        return $number;
    }






    /**
     * Initialize object of current class
     */
    public static function init()
    {
        return new self;
    }
}
