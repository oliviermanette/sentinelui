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

    /** 
     * constructor
     *
     * @return void
     * 
     */
  public function __construct()
  {
    $this->removePeak(0);
    $this->ListLength = 0;
  }

    /** 
     * set a new peak in the list
     *
     * @param int $lX index (frequency)
     * @param int $lY Y axis index (amplitude) 
     * @return void
     * 
     */
    public function setNewPeak($lX, $lY)
    {
        $this->ListLength++;
        $this->arrayPeaks[$lX] = $lY;
    }

    /** 
     * Get the size of the list of peak
     *
     * @return int size of the list
     * 
     */
    public function getNumber()
    {
        return $this->ListLength;
    }

    /** 
     * Get the largest peak amplitude of the list
     *
     * @return int amplitude of the largest peak
     * 
     */
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

    /** 
     * Get the index of the smallest peak inside the list
     *
     * @return int index of the smallest peak in the list
     * 
     */
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

    /** 
     * remove a peak from the list given an index
     *
     * @param int $index 
     * @return void
     * 
     */
    public function removePeak($index)
    {
        $this->array_splice_assoc($this->arrayPeaks, $index, 1);
        //array_splice($this->arrayPeaks, $index, 1);
        $this->ListLength--;
    }

    /** Get the size of the peak
     *
     * @param int $index 
     * @return int amplitude of the peak
     * 
     */
    public function getPeakSize($index)
    {
        $PeaksNoIdx =  array_values($this->arrayPeaks);
        return $PeaksNoIdx[$index];
    }

    /** Get the list of peaks
     *
     * @return array list of peaks
     * 
     */
    public function getArray()
    {
        return $this->arrayPeaks;
    }
}