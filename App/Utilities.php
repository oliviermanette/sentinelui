<?php

namespace App;

/**
 * Utilities function
 *
 * PHP version 7.0
 */
class Utilities
{
  public static function hexStr2bin($hex){
    $maxchars = 8;
    $hex = base_convert($hex, 16, 2);
    $hex = str_pad($hex, $maxchars, "0", STR_PAD_LEFT);

    return $hex;
  }

  public static function hex2dec($hex, $signed = true){
    if ($signed){
      if (strlen($hex) % 2 != 0){
        $hex = "0" + hex;
      }

      $num = hexdec($hex);
      $maxVal = 0;
      $maxVal = pow(2, strlen($hex) / 2 * 8);

      if ($num > $maxVal / 2 - 1){
        $num = $num - $maxVal;
      }

      return $num;
    }
    else {
      $dec = hexdec($hex);

      return $dec;
    }
  }

  public static function accumulatedTable16($decimal){
    $numb = intval($decimal);
    if ($numb > 127 || $numb < - 127){
      echo "$number outside range";
      return false;
    }
    if ($numb/127 == 1){
      $res  = 16 * 12.5 + 16 * 25 + 16 * 50 + 16 * 100 + 16 * 200 + 16 * 400  + 16 * 800 + 16 * 1600;
      return $res ;
    }
    if ($numb/112 > 1) {
      $res  = 16 * 12.5 + 16 * 25 + 16 * 50 + 16 * 100 + 16 * 200 + 16 * 400  + 16 * 800 + ($numb - 112) * 1600;
      return $res ;
    }
    if ($numb/96 > 1){
      $res  = 16 * 12.5 + 16 * 25 + 16 * 50 + 16 * 100 + 16 * 200 + 16 * 400 + ($numb - 96) * 800;
      return $res ;
    }
    if ($numb/80 > 1){
      $res  = 16 * 12.5 + 16 * 25 + 16 * 50 + 16 * 100  + 16 * 200 + ($numb - 80) * 400;
      return $res;
    }
    if ($numb/64 > 1){
      $$res  = 16 * 12.5 + 16 * 25 + 16 * 50 + 16 * 100 + ($numb - 64) * 200;
      return $res;
    }
    if ($numb/48 > 1){
      $res  = 16 * 12.5 + 16 * 25 + 16 * 50  + ($numb - 48) * 100;
      return $res;
    }
    if ($numb/32 > 1){
      $res  = 16 * 12.5 + 16 * 25 + ($numb - 32) * 50;
      return $res;
    }
    if ($numb/16 > 1){
      $res  = 16 * 12.5 + ($numb - 16) * 25;
      return $res;
    }
    if ($numb/16 == 1){
      $res  = 16 * 12.5;
      return $res;
    }
    if ($numb < 16 and $numb > 0){
      $res  = 16 * 12.5;
      return $res ;
    }
    if ($numb == 0){
      $res  =0;
      return $res ;
    }

    ## NEGATIF PART
    if ($numb/127 == -1){
      $res  = -(16 * 12.5 + 16 * 25 + 16 * 50 + 16 * 100 + 16 * 200 + 16 * 400  + 16 * 800 + 16 * 1600);
      return $res;
    }
    if ($numb/112 < -1){
      $res  = -(16 * 12.5 + 16 * 25 + 16 * 50 + 16 * 100 + 16 * 200 + 16 * 400  + 16 * 800 + (-$numb - 112) * 1600);
      return $res;
    }
    if ($numb/96 < -1){
      $res  = -(16 * 12.5 + 16 * 25 + 16 * 50 + 16 * 100 + 16 * 200 + 16 * 400 + (-$numb - 96) * 800);
      return $res;
    }
    if ($numb/80 < -1){
      $res  = -(16 * 12.5 + 16 * 25 + 16 * 50 + 16 * 100  + 16 * 200 + (-$numb - 80) * 400);
      return $res;
    }
    if ($numb/64 < -1){
      $res  = -(16 * 12.5 + 16 * 25 + 16 * 50 + 16 * 100 + (-$numb - 64) * 200);
      return $res;
    }
    if ($numb/48 < -1){
      $res  = -(16 * 12.5 + 16 * 25 + 16 * 50  + (-$numb - 48) * 100);
      return $res;
    }
    if ($numb/32 < -1){
      $res  = -(16 * 12.5 + 16 * 25 + (-$numb - 32) * 50);
      return $res;
    }
    if ($numb/16 < -1){
      $res  = -(16 * 12.5 + (-$numb - 16) * 25);
      return $res;
    }
    if ($numb/16 == -1){
      $res  = -16 * 12.5;
      return $res;
    }
    if ($numb > -16 and $numb < 0){
      $res  = $numb * 12.5;
      return $res;
    }
  }

  public static function accumulatedTable32($decimal){
    $numb = intval($decimal);
    if ($numb > 255 or $numb < - 255){
      echo "Number outside range";
      return false;
    }
    if ($numb/255 == 1){
      $res = 32 * 0.125 + 32 * 0.25 + 32 * 0.5 + 32 * 1 + 32 * 2 + 32 * 4  + 32 * 8 + 32 * 16;
      return $res;
    }
    if ($numb/224 > 1){
      $res = 32 * 0.125 + 32 * 0.25 + 32 * 0.5 + 32 * 1 + 32 * 2 + 32 * 4  + 32 * 8 + ($numb - 224) * 16;
      return $res;
    }
    if ($numb/192 > 1){
      $res = 32 * 0.125 + 32 * 0.25 + 32 * 0.5 + 32 * 1 + 32 * 2 + 32 * 4 + ($numb - 192) * 8;
      return $res;
    }
    if ($numb/160 > 1){
      $res = 32 * 0.125 + 32 * 0.25 + 32 * 0.5 + 32 * 1 + 32 * 2 + ($numb - 160) * 4;
      return $res;
    }
    if ($numb/128 > 1){
      $res = 32 * 0.125 + 32 * 0.25 + 32 * 0.5 + 32 * 1 + ($numb - 128) * 2;
      return $res;
    }
    if ($numb/96 > 1){
      $res = 32 * 0.125 + 32 * 0.25 + 32 * 0.5 + ($numb - 96) * 1;
      return $res;
    }
    if ($numb/64 > 1){
      $res = 32 * 0.125 + 32 * 0.25 + ($numb - 64) * 0.5;
      return $res;
    }
    if ($numb/32 > 1){
      $res = 32 * 0.125+ ($numb - 32) * 0.25;
      return $res;
    }
    if ($numb/32 == 1){
      $res = 32 * 0.125;
      return $res;
    }
    if ($numb < 32 and $numb > 0){
      $res = $numb * 0.125;
      return $res;
    }
    if ($numb == 0){
      $res = 0;
      return $res;
    }

    ## NEGATIF PART
    if ($numb/255 == -1){
      $res = -(32 * 0.125 + 32 * 0.25 + 32 * 0.5 + 32 * 1 + 32 * 2 + 32 * 4  + 32 * 8 + 32 * 16);
      return $res;
    }
    if ($numb/224 < -1){
      $res = -(32 * 0.125 + 32 * 0.25 + 32 * 0.5 + 32 * 1 + 32 * 2 + 32 * 4  + 32 * 8 + (-$numb - 224) * 16);
      return $res;
    }
    if ($numb/192 < -1){
      $res = -(32 * 0.125 + 32 * 0.25 + 32 * 0.5 + 32 * 1 + 32 * 2 + 32 * 4 + (-$numb - 192) * 8);
      return $res;
    }
    if ($numb/160 < -1){
      $res = -(32 * 0.125 + 32 * 0.25 + 32 * 0.5 + 32 * 1 + 32 * 2 + (-$numb - 160) * 4);
      return $res;
    }
    if ($numb/128 < -1){
      $res = -(132 * 0.125 + 32 * 0.25 + 32 * 0.5 + 32 * 1 + (-$numb - 128) * 2);
      return $res;
    }
    if ($numb/96 < -1){
      $res = -(32 * 0.125 + 32 * 0.25 + 32 * 0.5 + (-$numb - 96) * 1);
      return $res;
    }
    if ($numb/64 < -1){
      $res = -(32 * 0.125 + 32 * 0.25 + (-$numb - 64) * 0.5);
      return $res;
    }
    if ($numb/32 < -1){
      $res = -(32 * 0.125+ (-$numb - 32) * 0.25);
      return $res;
    }
    if ($numb/32 == -1){
      $res = -32 * 0.125;
      return $res;
    }
    if ($numb > -32 and $numb < 0){
      $res = $numb * 0.125;
      return $res;
    }

  }


  public static function microToSecond($valueMicro){
    return $valueMicro/1E6;
  }

  public static function mgToG($valueMg){
    return $valueMg/1E3;
  }

  public static function computeLineEquation($pt1, $pt2){
    $slope =  Utilities::computeSlope($pt1, $pt2);
    $b = Utilities::computeIntersectB($slope, $pt1);

    return array($slope, $b);

  }

  public static function findXItersection($slope, $b, $y){
    return ( $y - $b) / $slope;
  }

  public static function computeDistance($pt1, $pt2){
    return sqrt(pow(($pt2[0] - $pt1[0]), 2) + pow(($pt2[1] - $pt1[1]), 2) );
  }

  public static function computeSlope($pt1, $pt2){
    return (($pt2[1] - $pt1[1]) / ($pt2[0] - $pt1[0]));
  }

  public static function computeAreaTrianglePt($pt1, $pt2, $pt3){
    $base = $pt3[0] - $pt1[0];
    $high = $pt2[1];
    return (($base * $high) / 2);
  }

  public static function computePowerArea($area1, $area2){
    return (abs($area1) + abs($area2));
  }

  public static function computeIntersectB($slope, $ptIntersect){
    return $ptIntersect[1] - $slope * $ptIntersect[0];
  }

}
