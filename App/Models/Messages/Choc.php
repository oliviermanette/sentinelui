<?php


namespace App\Models\Messages;

use App\Utilities;

/**
 * 
 *
 * PHP version 7.0
 */
class Choc extends Message
{

    public function __construct($data = [])
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }

        $this->amplitude1 = Utilities::mgToG(floatval($this->amplitude1));
        $this->amplitude2 = Utilities::mgToG(floatval($this->amplitude2));
        $this->time1 = Utilities::microToSecond(floatval($this->time1));
        $this->time2 = Utilities::microToSecond(floatval($this->time2));

        $this->computeChocData();
        
    }


    /**
     * Computer Power of the choc
     *
     * @param float $amplitude_1 first amplitude (in mg or g)
     * @param float $amplitude_2 second amplitude (in mg or g)
     * @param int $time_1 first time (or period) to provok the first amplitude (in micro seconde or second)
     * @param int $time_2 second time to provok the second amplitude (in micro seconde or second)
     * @return array  $totalAreaPower, $freq1, $freq2
     */
    private function computeChocData()
    {
        $pt0 = array(0, 0);
        $pt1 = array($this->time1, $this->amplitude1);
        $pt2 = array($this->time2, $this->amplitude2);
        $pt3 = array($this->time2 + ($this->time2 - $this->time1), 0);

        #1. Compute line equation (pt1, pt2)
        $res = Utilities::computeLineEquation($pt1, $pt2);
        $slope = $res[0];
        $b = $res[1];

        #2. compute (pt1) : ax + b = 0 to find the new point on the abscille (Xc, Yc)
        $Xc = Utilities::findXItersection($slope, $b, 0);
        $ptC = array($Xc, 0);

        $distanceP1P2 = Utilities::computeDistance($pt1, $pt2);

        #3 Compute distance (pt1,ptc)
        $distanceP1PC = Utilities::computeDistance($pt1, $ptC);

        #4 compute the first area of the first triangle
        $areaTriangle1 = Utilities::computeAreaTrianglePt($pt0, $pt1, $ptC);
        $areaTriangle2 = Utilities::computeAreaTrianglePt($ptC, $pt2, $pt3);

        #5 Compute power choc by combining the two triangle
        $totalAreaPower = Utilities::computePowerArea($areaTriangle1, $areaTriangle2);

        #Compute frequence of each phase of the choc
        $freq1 = 1 / $pt1[0];
        $freq2 = 1 / $pt2[0];

        $this->frequence1 = $freq1;
        $this->frequence2 = $freq2;
        $this->power = $totalAreaPower;

    }

    public function getPowerValue()
    {
        return $this->power;
    }


    public function getPowerValueChoc($precision = 2, $unite = "mg")
    {
        if ($unite == "mg") {
            return round($this->power * 100, $precision);
        } else {
            return round($this->power, $precision);
        }
    }

}