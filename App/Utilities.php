<?php

namespace App;

use MathPHP\Statistics\Multivariate\PCA;
use MathPHP\LinearAlgebra\MatrixFactory;

include("{$_SERVER['DOCUMENT_ROOT']}/App/pca.php");

/**
 * Utilities function
 *
 * PHP version 7.0
 */
class Utilities
{

  public static function saveJsonObject($data, $path)
  {
    $fp = fopen($path, 'w');
    fwrite($fp, json_encode($data));
    fclose($fp);
  }

  public static function isFirstDateSuperiorToSecondDate($date1, $date2)
  {
    if ($date1 > $date2) {
      return true;
    }
    return false;
  }

  // Find the number of combinaison K among n
  public static function nbreCombinaison($k, $n)
  {
    if ($k > $n / 2) {
      $k = $n - $k;
    }
    $x = 1;
    $y = 1;

    $i = $n - $k + 1;
    while ($i <= $n) {
      $x = ($x * $i) / $y;
      $y += 1;
      $i += 1;
    }
    return $x;
  }

  /*
​
La fonction qui permet de donner toutes les combinaisons de 2 parmi le tableau N
​
Input : N without key or with a numerical index key (counts the rank)
Ex : array(1,2,3)
​
Output : array of different combination
((1,2), (1,3), (2,3))
(with key the first element and $value the second element)
​
function getCombination($n)
​
* CTO: Lirone Samoun
* Author: Olivier Manette
* contact@flod.ai
*/
  public static function getCombinationFromArray($n)
  {
    $idx = 0;
    $arrayCombinations[$idx] = array($n[0] => $n[1]);
    $j = 2;
    foreach ($n as $key => $value) {
      for ($i = $j; $i < count($n); $i++) {
        $idx++;
        $arrayCombinations[$idx] =  array($value => $n[$i]);
      }
      $j = $key + 2;
    }
    return $arrayCombinations;
  }

  public static function getCombinations($k, $n)
  {
    $arrayValue = array();
    for ($i = 1; $i <= $n; $i++) {
      array_push($arrayValue, $i);
    }

    $arrayCombination = Utilities::getCombinationFromArray($arrayValue);
    /*$arrayCombinationOtherPresentation = array();
    foreach ($arrayCombination as $combination) {
      print_r($combination);
      $key = array_keys($combination)[0];
      $value = array_values($combination)[0];
      echo "Key = " . $key . " => Value = " . $value . "\n";
      echo "\n";
    }*/

    return $arrayCombination;
  }


  public static function hexStr2bin($hex)
  {
    $maxchars = 8;
    $hex = base_convert($hex, 16, 2);
    $hex = str_pad($hex, $maxchars, "0", STR_PAD_LEFT);

    return $hex;
  }

  public static function hex2dec($hex, $signed = true)
  {
    if ($signed) {
      if (strlen($hex) % 2 != 0) {
        $hex = "0" + $hex;
      }

      $num = hexdec($hex);
      $maxVal = 0;
      $maxVal = pow(2, strlen($hex) / 2 * 8);

      if ($num > $maxVal / 2 - 1) {
        $num = $num - $maxVal;
      }

      return $num;
    } else {
      $dec = hexdec($hex);

      return $dec;
    }
  }

  public static function accumulatedTable16($decimal)
  {
    $numb = intval($decimal);
    if ($numb > 127 || $numb < -127) {
      echo "$numb outside range";
      return false;
    }
    if ($numb / 127 == 1) {
      $res  = 16 * 12.5 + 16 * 25 + 16 * 50 + 16 * 100 + 16 * 200 + 16 * 400  + 16 * 800 + 16 * 1600;
      return $res;
    }
    if ($numb / 112 > 1) {
      $res  = 16 * 12.5 + 16 * 25 + 16 * 50 + 16 * 100 + 16 * 200 + 16 * 400  + 16 * 800 + ($numb - 112) * 1600;
      return $res;
    }
    if ($numb / 96 > 1) {
      $res  = 16 * 12.5 + 16 * 25 + 16 * 50 + 16 * 100 + 16 * 200 + 16 * 400 + ($numb - 96) * 800;
      return $res;
    }
    if ($numb / 80 > 1) {
      $res  = 16 * 12.5 + 16 * 25 + 16 * 50 + 16 * 100  + 16 * 200 + ($numb - 80) * 400;
      return $res;
    }
    if ($numb / 64 > 1) {
      $res  = 16 * 12.5 + 16 * 25 + 16 * 50 + 16 * 100 + ($numb - 64) * 200;
      return $res;
    }
    if ($numb / 48 > 1) {
      $res  = 16 * 12.5 + 16 * 25 + 16 * 50  + ($numb - 48) * 100;
      return $res;
    }
    if ($numb / 32 > 1) {
      $res  = 16 * 12.5 + 16 * 25 + ($numb - 32) * 50;
      return $res;
    }
    if ($numb / 16 > 1) {
      $res  = 16 * 12.5 + ($numb - 16) * 25;
      return $res;
    }
    if ($numb / 16 == 1) {
      $res  = 16 * 12.5;
      return $res;
    }
    if ($numb < 16 and $numb > 0) {
      $res  = 16 * 12.5;
      return $res;
    }
    if ($numb == 0) {
      $res  = 0;
      return $res;
    }

    ## NEGATIF PART
    if ($numb / 127 == -1) {
      $res  = - (16 * 12.5 + 16 * 25 + 16 * 50 + 16 * 100 + 16 * 200 + 16 * 400  + 16 * 800 + 16 * 1600);
      return $res;
    }
    if ($numb / 112 < -1) {
      $res  = - (16 * 12.5 + 16 * 25 + 16 * 50 + 16 * 100 + 16 * 200 + 16 * 400  + 16 * 800 + (-$numb - 112) * 1600);
      return $res;
    }
    if ($numb / 96 < -1) {
      $res  = - (16 * 12.5 + 16 * 25 + 16 * 50 + 16 * 100 + 16 * 200 + 16 * 400 + (-$numb - 96) * 800);
      return $res;
    }
    if ($numb / 80 < -1) {
      $res  = - (16 * 12.5 + 16 * 25 + 16 * 50 + 16 * 100  + 16 * 200 + (-$numb - 80) * 400);
      return $res;
    }
    if ($numb / 64 < -1) {
      $res  = - (16 * 12.5 + 16 * 25 + 16 * 50 + 16 * 100 + (-$numb - 64) * 200);
      return $res;
    }
    if ($numb / 48 < -1) {
      $res  = - (16 * 12.5 + 16 * 25 + 16 * 50  + (-$numb - 48) * 100);
      return $res;
    }
    if ($numb / 32 < -1) {
      $res  = - (16 * 12.5 + 16 * 25 + (-$numb - 32) * 50);
      return $res;
    }
    if ($numb / 16 < -1) {
      $res  = - (16 * 12.5 + (-$numb - 16) * 25);
      return $res;
    }
    if ($numb / 16 == -1) {
      $res  = -16 * 12.5;
      return $res;
    }
    if ($numb > -16 and $numb < 0) {
      $res  = $numb * 12.5;
      return $res;
    }
  }

  public static function accumulatedTable32($decimal)
  {
    $numb = intval($decimal);
    if ($numb > 255 or $numb < -255) {
      echo "Number outside range";
      return false;
    }
    if ($numb / 255 == 1) {
      $res = 32 * 0.125 + 32 * 0.25 + 32 * 0.5 + 32 * 1 + 32 * 2 + 32 * 4  + 32 * 8 + 32 * 16;
      return $res;
    }
    if ($numb / 224 > 1) {
      $res = 32 * 0.125 + 32 * 0.25 + 32 * 0.5 + 32 * 1 + 32 * 2 + 32 * 4  + 32 * 8 + ($numb - 224) * 16;
      return $res;
    }
    if ($numb / 192 > 1) {
      $res = 32 * 0.125 + 32 * 0.25 + 32 * 0.5 + 32 * 1 + 32 * 2 + 32 * 4 + ($numb - 192) * 8;
      return $res;
    }
    if ($numb / 160 > 1) {
      $res = 32 * 0.125 + 32 * 0.25 + 32 * 0.5 + 32 * 1 + 32 * 2 + ($numb - 160) * 4;
      return $res;
    }
    if ($numb / 128 > 1) {
      $res = 32 * 0.125 + 32 * 0.25 + 32 * 0.5 + 32 * 1 + ($numb - 128) * 2;
      return $res;
    }
    if ($numb / 96 > 1) {
      $res = 32 * 0.125 + 32 * 0.25 + 32 * 0.5 + ($numb - 96) * 1;
      return $res;
    }
    if ($numb / 64 > 1) {
      $res = 32 * 0.125 + 32 * 0.25 + ($numb - 64) * 0.5;
      return $res;
    }
    if ($numb / 32 > 1) {
      $res = 32 * 0.125 + ($numb - 32) * 0.25;
      return $res;
    }
    if ($numb / 32 == 1) {
      $res = 32 * 0.125;
      return $res;
    }
    if ($numb < 32 and $numb > 0) {
      $res = $numb * 0.125;
      return $res;
    }
    if ($numb == 0) {
      $res = 0;
      return $res;
    }

    ## NEGATIF PART
    if ($numb / 255 == -1) {
      $res = - (32 * 0.125 + 32 * 0.25 + 32 * 0.5 + 32 * 1 + 32 * 2 + 32 * 4  + 32 * 8 + 32 * 16);
      return $res;
    }
    if ($numb / 224 < -1) {
      $res = - (32 * 0.125 + 32 * 0.25 + 32 * 0.5 + 32 * 1 + 32 * 2 + 32 * 4  + 32 * 8 + (-$numb - 224) * 16);
      return $res;
    }
    if ($numb / 192 < -1) {
      $res = - (32 * 0.125 + 32 * 0.25 + 32 * 0.5 + 32 * 1 + 32 * 2 + 32 * 4 + (-$numb - 192) * 8);
      return $res;
    }
    if ($numb / 160 < -1) {
      $res = - (32 * 0.125 + 32 * 0.25 + 32 * 0.5 + 32 * 1 + 32 * 2 + (-$numb - 160) * 4);
      return $res;
    }
    if ($numb / 128 < -1) {
      $res = - (132 * 0.125 + 32 * 0.25 + 32 * 0.5 + 32 * 1 + (-$numb - 128) * 2);
      return $res;
    }
    if ($numb / 96 < -1) {
      $res = - (32 * 0.125 + 32 * 0.25 + 32 * 0.5 + (-$numb - 96) * 1);
      return $res;
    }
    if ($numb / 64 < -1) {
      $res = - (32 * 0.125 + 32 * 0.25 + (-$numb - 64) * 0.5);
      return $res;
    }
    if ($numb / 32 < -1) {
      $res = - (32 * 0.125 + (-$numb - 32) * 0.25);
      return $res;
    }
    if ($numb / 32 == -1) {
      $res = -32 * 0.125;
      return $res;
    }
    if ($numb > -32 and $numb < 0) {
      $res = $numb * 0.125;
      return $res;
    }
  }

  public static function normedSquare($nx, $ny, $nz)
  {
    return sqrt(pow($nx, 2) + pow($ny, 2) + pow($nz, 2));
  }

  public static function microToSecond($valueMicro)
  {
    return $valueMicro / 1E6;
  }

  public static function mgToG($valueMg)
  {
    return $valueMg / 1E3;
  }

  public static function computeLineEquation($pt1, $pt2)
  {
    $slope =  Utilities::computeSlope($pt1, $pt2);
    $b = Utilities::computeIntersectB($slope, $pt1);

    return array($slope, $b);
  }

  public static function findXItersection($slope, $b, $y)
  {
    return ($y - $b) / $slope;
  }

  public static function computeDistance($pt1, $pt2)
  {
    return sqrt(pow(($pt2[0] - $pt1[0]), 2) + pow(($pt2[1] - $pt1[1]), 2));
  }

  public static function computeSlope($pt1, $pt2)
  {
    return (($pt2[1] - $pt1[1]) / ($pt2[0] - $pt1[0]));
  }

  public static function computeSlopeArray($xArr, $yArr)
  {
    $n     = count($xArr);     // number of items in the array
    $x_sum = array_sum($xArr); // sum of all X values
    $y_sum = array_sum($yArr); // sum of all Y values

    $xx_sum = 0;
    $xy_sum = 0;

    for ($i = 0; $i < $n; $i++) {
      $xy_sum += ($xArr[$i] * $yArr[$i]);
      $xx_sum += ($xArr[$i] * $xArr[$i]);
    }

    // Slope
    $slope = (($n * $xy_sum) - ($x_sum * $y_sum)) / (($n * $xx_sum) - ($x_sum * $x_sum));

    // calculate intercept
    $intercept = ($y_sum - ($slope * $x_sum)) / $n;

    return array(
      'slope'     => $slope,
      'intercept' => $intercept,
    );
  }

  /**
   * linear regression function
   * @param $x array x-coords
   * @param $y array y-coords
   * @returns array() m=>slope, b=>intercept
   */
  public static function linear_regression($x, $y)
  {

    // calculate number points
    $n = count($x);

    // ensure both arrays of points are the same size
    if ($n != count($y)) {

      trigger_error("linear_regression(): Number of elements in coordinate arrays do not match.", E_USER_ERROR);
    }

    // calculate sums
    $x_sum = array_sum($x);
    $y_sum = array_sum($y);

    $xx_sum = 0;
    $xy_sum = 0;

    for ($i = 0; $i < $n; $i++) {

      $xy_sum += ($x[$i] * $y[$i]);
      $xx_sum += ($x[$i] * $x[$i]);
    }

    // calculate slope
    $m = (($n * $xy_sum) - ($x_sum * $y_sum)) / (($n * $xx_sum) - ($x_sum * $x_sum));

    // calculate intercept
    $b = ($y_sum - ($m * $x_sum)) / $n;

    // return result
    return array("m" => $m, "b" => $b);
  }

  public static function computeAreaTrianglePt($pt1, $pt2, $pt3)
  {
    $base = $pt3[0] - $pt1[0];
    $high = $pt2[1];
    return (($base * $high) / 2);
  }

  public static function computePowerArea($area1, $area2)
  {
    return (abs($area1) + abs($area2));
  }

  public static function computeIntersectB($slope, $ptIntersect)
  {
    return $ptIntersect[1] - $slope * $ptIntersect[0];
  }

  public static function array_find_deep($array, $search, $keys = array())
  {
    foreach ($array as $key => $value) {
      if (is_array($value)) {
        $sub = Utilities::array_find_deep($value, $search, array_merge($keys, array($key)));
        if (count($sub)) {
          return $sub;
        }
      } elseif ($value === $search) {
        return array_merge($keys, array($key));
      }
    }

    return array();
  }

  public static function array_find_value_by_key($dataArr, $search)
  {
    foreach ($dataArr as $data) {
      if (isset($data[$search])) {
        return $data[$search];
      }
    }
  }

  public static function is_key_in_array($dataArr, $search)
  {
    if (isset($dataArr[$search])) {
      return true;
    }
    return false;
  }


  public static function angle_between($pt1, $pt2)
  {
    $ang1 = atan2($pt1[1], $pt1[0]);
    $ang2 = atan2($pt2[1], $pt2[0]);
    $delta = fmod(($ang1 - $ang2), 2 * pi());
    $angle = rad2deg($delta);
    return $angle;
  }

  public static function angle_between_line($slope1, $slope2)
  {
    $teta = atan(($slope2 - $slope1) / (1 + ($slope2 * $slope1)));
    $angle = rad2deg($teta);
    return $angle;
  }

  public static function rotate_point($pt1, $angleDeg)
  {
    $angleRad = $angleDeg * (pi() / 180);
    $xprim = $pt1[0] * cos($angleRad) - $pt1[1] * sin($angleRad);
    $yprim = $pt1[0] * sin($angleRad) + $pt1[1] * cos($angleRad);

    $ptRotate = array($xprim, $yprim);
    return $ptRotate;
  }

  public static function substract_point($pt2, $pt1)
  {
    $deltaX = $pt2[0] - $pt1[0];
    $deltaY = $pt2[1] - $pt1[1];
    $ptSub = array($deltaX, $deltaY);
    return $ptSub;
  }

  public static function computeMeanDirectionArr($variationDirectionArr)
  {
    $sumX = 0;
    $sumY = 0;

    foreach ($variationDirectionArr as $dataArr) {
      $deltaX = $dataArr["delta_x"];
      $deltaY = $dataArr["delta_y"];
      //echo $deltaX . "\n";
      $sumX += $deltaX;
      $sumY += $deltaY;
    }
    $deltaXavg =  $sumX / count($variationDirectionArr);
    $deltaYavg =  $sumY / count($variationDirectionArr);

    $ptMean = array($deltaXavg, $deltaYavg);
    return $ptMean;
  }

  public static function computeRegressionDirectionArr($variationDirectionArr)
  {
    $deltaXarr = array();
    $deltaYarr = array();
    foreach ($variationDirectionArr as $dataArr) {
      $deltaX = $dataArr["delta_x"];
      $deltaY = $dataArr["delta_y"];

      array_push($deltaXarr, $deltaX);
      array_push($deltaYarr, $deltaY);
    }
    //print_r($deltaXarr);
    $regression = Utilities::computeSlopeArray($deltaXarr, $deltaYarr);

    return $regression;
  }

  public static function computePCADirectionArr($variationDirectionArr)
  {
    $deltaXarr = array();
    $deltaYarr = array();
    foreach ($variationDirectionArr as $dataArr) {
      $deltaX = $dataArr["delta_x"];
      $deltaY = $dataArr["delta_y"];

      array_push($deltaXarr, $deltaX);
      array_push($deltaYarr, $deltaY);
    }
    $points = [
      $deltaXarr,
      $deltaYarr,
    ];
    //print_r($points);
    $p = new \PCA\PCA($points);
    $p->changeDimension(2);
    $p->applayingPca();
    echo "Count : " . count($p->getPC()[0]);
    return $p->getEigenVectors();
  }

  public static function applyReferentielData1ToData2($data1Arr, $data2Arr)
  {
    $variationDirectionArr = array();

    //1. Compute Mean M1 and Mean M2
    $M1 = Utilities::computeMeanDirectionArr($data1Arr);
    $M2 = Utilities::computeMeanDirectionArr($data2Arr);
    /*echo "M1 : \n";
    print_r($M1);
    echo "M2 : \n";
    print_r($M2);*/

    //2. Compute angle between M1 and M2
    $angleDeg = Utilities::angle_between($M1, $M2);
    //echo "AngleDeg : " . $angleDeg . "\n";

    //3. Rotate M2 ==> M2'
    $M2prim = Utilities::rotate_point($M2, $angleDeg);
    /*echo "M2prim : \n";
    print_r($M2prim);*/

    //4. Find distance between M1 and M2'
    $diffPt = Utilities::substract_point($M2prim, $M1);
    /*echo "diffPt : \n";
    print_r($diffPt);*/

    //5. Apply rotation and translation to all the points
    foreach ($data2Arr as $array) {
      $deltaX = $array["delta_x"];
      $deltaY = $array["delta_y"];
      $date = $array["date"];

      $ptToTransform = array($deltaX, $deltaY);
      /*echo "Pt to transform : \n";
      print_r($ptToTransform);*/
      $deltaXRotate =  Utilities::rotate_point($ptToTransform, $angleDeg);
      $deltaXTranslate =  Utilities::substract_point($deltaXRotate, $diffPt);
      $deltaXTransfo = $deltaXTranslate;

      /*echo "After transform: \n";
      print_r($deltaXTransfo);*/


      $tmpArr = array(
        "date" => $date, "delta_x" => $deltaXTransfo[0], "delta_y" => $deltaXTransfo[1]
      );
      array_push($variationDirectionArr, $tmpArr);
    }
    $M2 = Utilities::computeMeanDirectionArr($variationDirectionArr);
    /*echo "Mean after transform: \n";
    print_r($M2);*/
    return $variationDirectionArr;
  }

  public static function applyReferentielData1ToData2UsingSlope($data1Arr, $data2Arr, $normalizeSize = True)
  {
    $variationDirectionArr = array();

    if ($normalizeSize) {
      $countM1 = count($data1Arr);
      $countM2 = count($data2Arr);
      $diffCount = $countM1 - $countM2;

      $data1Arr = array_slice($data1Arr, $diffCount);
      /*echo "data1SlicedArr : \n";
      print_r($data1Arr);
      echo "data2Arr : \n";
      print_r($data2Arr);*/
    }


    //0. Compute Mean M1 and Mean M2
    $M1 = Utilities::computeMeanDirectionArr($data1Arr);
    $M2 = Utilities::computeMeanDirectionArr($data2Arr);

    /*echo "M1 : \n";
    print_r($M1);
    echo "M2 : \n";
    print_r($M2);*/

    //1 . Compute slope for M1 and M2
    $regressionM1 = Utilities::computeRegressionDirectionArr($data1Arr);
    $regressionM2 = Utilities::computeRegressionDirectionArr($data2Arr);
    /*echo "Regression M1 : \n";
    print_r($regressionM1);
    echo "\n";
    echo "Regression M2 : \n";
    print_r($regressionM2);*/
    $slopeM1 = $regressionM1['slope'];
    $slopeM2 = $regressionM2['slope'];

    /*echo "Slope M1 : \n";
    print_r($slopeM1);
    echo "\n";
    echo "Slope M2 : \n";
    print_r($slopeM2);
    echo "\n";*/
    //2. Compute angle between M1 and M2
    $angleDeg = Utilities::angle_between_line($slopeM2, $slopeM1);
    //echo "AngleDeg between Slope M1 and Slope M2: " . $angleDeg . "\n";

    //3. Rotate M2 ==> M2'
    $M2prim = Utilities::rotate_point($M2, $angleDeg);
    /*echo "M2prim : \n";
    print_r($M2prim);*/

    //4. Find distance between M1 and M2'
    $diffPt = Utilities::substract_point($M2prim, $M1);
    /*echo "diffPt : \n";
    print_r($diffPt);*/

    //5. Apply rotation and translation to all the points
    foreach ($data2Arr as $array) {
      $deltaX = $array["delta_x"];
      $deltaY = $array["delta_y"];
      $date = $array["date"];

      $ptToTransform = array($deltaX, $deltaY);
      /*echo "Pt to transform : \n";
      print_r($ptToTransform);*/
      $deltaXRotate =  Utilities::rotate_point($ptToTransform, $angleDeg);
      $deltaXTranslate =  Utilities::substract_point($deltaXRotate, $diffPt);
      $deltaXTransfo = $deltaXTranslate;

      /*echo "After transform: \n";
      print_r($deltaXTransfo);*/

      $tmpArr = array(
        "date" => $date, "delta_x" => -$deltaXTransfo[0], "delta_y" => $deltaXTransfo[1]
      );
      array_push($variationDirectionArr, $tmpArr);
    }
    $M2 = Utilities::computeMeanDirectionArr($variationDirectionArr);
    /*echo "Mean after transform: \n";
    print_r($M2);*/
    return $variationDirectionArr;
  }
}
