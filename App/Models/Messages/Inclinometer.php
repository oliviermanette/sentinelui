<?php


namespace App\Models\Messages;

use App\Utilities;

/**
 * 
 *
 * PHP version 7.0
 */
class Inclinometer extends Message
{

    public function __construct($data = [])
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }

        $this->convertInclinometerDataToAngle($this->X, $this->Y, $this->Z);
    }


    private function convertInclinometerDataToAngle($nx, $ny, $nz)
    {
        $xData_g = Utilities::mgToG($nx);
        $yData_g = Utilities::mgToG($ny);
        $zData_g = Utilities::mgToG($nz);

        if ($zData_g < -1) {
            $zData_g = -1;
        }
        if ($zData_g > 1) {
            $zData_g = 1;
        }
        if ($yData_g < -1) {
            $yData_g = -1;
        }
        if ($yData_g > 1) {
            $yData_g = 1;
        }
        if ($xData_g < -1) {
            $xData_g = -1;
        }
        if ($xData_g > 1) {
            $xData_g = 1;
        }

        $angleX = rad2deg(asin($xData_g));
        $angleY = rad2deg(asin($yData_g));
        $angleZ = rad2deg(acos($zData_g));

        $this->angleX = $angleX;
        $this->angleY = $angleY;
        $this->angleZ = $angleZ;
    }

    public function getAngleX()
    {
        return $this->angleX;
    }
    public function getAngleY()
    {
        return $this->angleY;
    }
    public function getAngleZ()
    {
        return $this->angleZ;
    }
}