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

        $this->convertInclinometerDataToAngle();
    }

    /**
     * convert the raw data inclinometer received by the sensor to angle in degree
     *
     * @param float $nx inclination x
     * @param float $ny inclination y
     * @param float $nz inclination z
     * @return void  assign object value of angles
     */
    private function convertInclinometerDataToAngle()
    {
        $xData_g = Utilities::mgToG($this->X);
        $yData_g = Utilities::mgToG($this->Y);
        $zData_g = Utilities::mgToG($this->Z);

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

    public function computeInclinationZ()
    {
        $X_g = Utilities::mgToG($this->X);
        $Y_g = Utilities::mgToG($this->Y);
        $Z_g = Utilities::mgToG($this->Z);
        $norm = sqrt(pow($X_g, 2) + pow($Y_g, 2));
        $tetaZ = atan($norm / $Z_g);
        $this->$tetaZ = $tetaZ;
        return $tetaZ;
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
