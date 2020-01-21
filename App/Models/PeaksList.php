<?php

namespace App\Models;

use App\Config;

/**
* Peak object
*
* PHP version 7.0
*/
class PeaksList
{
  public $arrayPeaks = array(1 => 2);
  public $ListLength;
  
  public function __construct()
  {
    $this->removePeak(0);
    $this->ListLength = 0;
  }

    public function setNewPeak($lX, $lY)
    {
        $this->ListLength++;
        $this->arrayPeaks[$lX] = $lY;
        print_r($this->arrayPeaks);
    }

    public function getNumber()
    {
        return $this->ListLength;
    }

    public function getLargestPeakAmplitude()
    {
        $PeaksNoIdx =  array_values($this->arrayPeaks);
        if (!empty($PeaksNoIdx)) {
            $LfltLargestY = $PeaksNoIdx[0];

            for ($i = 1; $i < $this->ListLength; $i++) {
                if ($PeaksNoIdx[$i] > $LfltLargestY)
                    $LfltLargestY = $PeaksNoIdx[$i];
            }
            return $LfltLargestY;
        }
      
    }

    public function getSmallestPeakIndex()
    {
        $PeaksNoIdx =  array_values($this->arrayPeaks);
        if (!empty($PeaksNoIdx)) {
            $LfltSmallestY = $PeaksNoIdx[0];
            $lReturnIdx = 0;
            for ($i = 1; $i < $this->ListLength; $i++) {
                if ($PeaksNoIdx[$i] < $LfltSmallestY) {
                    $LfltSmallestY < $PeaksNoIdx[$i];
                    $lReturnIdx = $i;
                }
            }
            return $lReturnIdx;
        }
       
    }

    function array_splice_preserve_keys(&$input, $offset, $length = null, $replacement = array())
    {
        if (empty($replacement)) {
            return array_splice($input, $offset, $length);
        }

        $part_before  = array_slice($input, 0, $offset, $preserve_keys = true);
        $part_removed = array_slice($input, $offset, $length, $preserve_keys = true);
        $part_after   = array_slice($input, $offset + $length, null, $preserve_keys = true);

        $input = $part_before + $replacement + $part_after;

        return $part_removed;
    }

    function array_splice_assoc(&$input, $offset, $length, $replacement = array())
    {
        $replacement = (array) $replacement;
        $key_indices = array_flip(array_keys($input));
        if (isset($input[$offset]) && is_string($offset)) {
            $offset = $key_indices[$offset];
        }
        if (isset($input[$length]) && is_string($length)) {
            $length = $key_indices[$length] - $offset;
        }

        $input = array_slice($input, 0, $offset, TRUE)
            + $replacement
            + array_slice($input, $offset + $length, NULL, TRUE);
    }

    public function removePeak($index)
    {
        echo "BEFORE REMOVE with index : ". $index."\n";
        print_r($this->arrayPeaks);
        $this->array_splice_assoc($this->arrayPeaks, $index, 1);
        //array_splice($this->arrayPeaks, $index, 1);
        
        $this->ListLength--;
        echo "AFTER REMOVE : \n";
        print_r($this->arrayPeaks);


    }

    public function getPeakSize($index)
    {
        $PeaksNoIdx =  array_values($this->arrayPeaks);
        /*echo "Index : ". $index;
        echo $PeaksNoIdx[$index];
        print_r($PeaksNoIdx);*/
        return $PeaksNoIdx[$index];
    }

    public function getArray()
    {
        return $this->arrayPeaks;
    }
}