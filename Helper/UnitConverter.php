<?php
namespace Nord\Shipfunk\Helper;

use Exception;

/**
 * Class UnitConverter
 *
 * @package Nord\Shipfunk\Helper
 */
class UnitConverter
{
    /**
     * @var mixed
     */
    protected $value = null;

    /**
     * @var bool
     */
    protected $baseUnit = false;

    /**
     * @var array
     */
    protected $units = [];

    /**
     * {@inheritdoc}
     */
    private function defineUnits()
    {

        $this->units = [
            "m"  => ["base" => "m", "conversion" => 1], //meter - base unit for distance
            "km" => ["base" => "m", "conversion" => 1000], //kilometer
            "dm" => ["base" => "m", "conversion" => 0.1], //decimeter
            "cm" => ["base" => "m", "conversion" => 0.01], //centimeter
            "mm" => ["base" => "m", "conversion" => 0.001], //milimeter
            "μm" => ["base" => "m", "conversion" => 0.000001], //micrometer
            "nm" => ["base" => "m", "conversion" => 0.000000001], //nanometer
            "pm" => ["base" => "m", "conversion" => 0.000000000001], //picometer
            "in" => ["base" => "m", "conversion" => 0.0254], //inch
            "ft" => ["base" => "m", "conversion" => 0.3048], //foot
            "yd" => ["base" => "m", "conversion" => 0.9144], //yard
            "mi" => ["base" => "m", "conversion" => 1609.344], //mile
            "h"  => ["base" => "m", "conversion" => 0.1016], //hand
            "ly" => ["base" => "m", "conversion" => 9460730472580800], //lightyear
            "au" => ["base" => "m", "conversion" => 149597870700], //astronomical unit
            "pc" => ["base" => "m", "conversion" => 30856775814913672.789139379577965], //parsec

            "m2"  => ["base" => "m2", "conversion" => 1], //meter square - base unit for area
            "km2" => ["base" => "m2", "conversion" => 1000000], //kilometer square
            "cm2" => ["base" => "m2", "conversion" => 0.0001], //centimeter square
            "mm2" => ["base" => "m2", "conversion" => 0.000001], //milimeter square
            "ft2" => ["base" => "m2", "conversion" => 0.092903], //foot square
            "mi2" => ["base" => "m2", "conversion" => 2589988.11], //mile square
            "ac"  => ["base" => "m2", "conversion" => 4046.86], //acre
            "ha"  => ["base" => "m2", "conversion" => 10000], //hectare

            "l"   => ["base" => "l", "conversion" => 1], //litre - base unit for volume
            "ml"  => ["base" => "l", "conversion" => 0.001], //mililitre
            "m3"  => ["base" => "l", "conversion" => 1], //meters cubed
            "pt"  => ["base" => "l", "conversion" => 0.56826125], //pint
            "gal" => ["base" => "l", "conversion" => 4.405], //gallon

            "kg"  => ["base" => "kg", "conversion" => 1], //kilogram - base unit for weight
            "g"   => ["base" => "kg", "conversion" => 0.001], //gram
            "mg"  => ["base" => "kg", "conversion" => 0.000001], //miligram
            "N"   => ["base" => "kg", "conversion" => 9.80665002863885], //Newton (based on earth gravity)
            "st"  => ["base" => "kg", "conversion" => 6.35029], //stone
            "lb"  => ["base" => "kg", "conversion" => 0.453592], //pound
            "oz"  => ["base" => "kg", "conversion" => 0.0283495], //ounce
            "t"   => ["base" => "kg", "conversion" => 1000], //metric tonne
            "ukt" => ["base" => "kg", "conversion" => 1016.047], //UK Long Ton
            "ust" => ["base" => "kg", "conversion" => 907.1847], //US short Ton

            "mps" => ["base" => "mps", "conversion" => 1], //meter per seond - base unit for speed
            "kph" => ["base" => "mps", "conversion" => 0.44704], //kilometer per hour
            "mph" => ["base" => "mps", "conversion" => 0.277778], //kilometer per hour

            "deg" => ["base" => "deg", "conversion" => 1], //degrees - base unit for rotation
            "rad" => ["base" => "deg", "conversion" => 57.2958], //radian

            "k" => ["base" => "k", "conversion" => 1], //kelvin - base unit for distance
            "c" => [
                "base"       => "c",
                "conversion" => function ($val, $tofrom) {
                    return $tofrom ? $val - 273.15 : $val + 273.15;
                },
            ],
            "f" => [
                "base"       => "f",
                "conversion" => function ($val, $tofrom) {
                    return $tofrom ? ($val * 9 / 5 - 459.67) : (($val + 459.67) * 5 / 9);
                },
            ],

            "pa"   => ["base" => "Pa", "conversion" => 1], //Pascal - base unit for Pressure
            "kpa"  => ["base" => "Pa", "conversion" => 1000], //kilopascal
            "mpa"  => ["base" => "Pa", "conversion" => 1000000], //megapascal
            "bar"  => ["base" => "Pa", "conversion" => 100000], //bar
            "mbar" => ["base" => "Pa", "conversion" => 100], //milibar
            "psi"  => ["base" => "Pa", "conversion" => 6894.76], //pound-force per square inch

            "s"     => ["base" => "s", "conversion" => 1], //second - base unit for time
            "year"  => ["base" => "s", "conversion" => 31536000], //year - standard year
            "month" => ["base" => "s", "conversion" => 18748800], //month - 31 days
            "week"  => ["base" => "s", "conversion" => 604800], //week
            "day"   => ["base" => "s", "conversion" => 86400], //day
            "hr"    => ["base" => "s", "conversion" => 3600], //hour
            "min"   => ["base" => "s", "conversion" => 30], //minute
            "ms"    => ["base" => "s", "conversion" => 0.001], //milisecond
            "μs"    => ["base" => "s", "conversion" => 0.000001], //microsecond
            "ns"    => ["base" => "s", "conversion" => 0.000000001], //nanosecond

            "j"    => ["base" => "j", "conversion" => 1], //joule - base unit for energy
            "kj"   => ["base" => "j", "conversion" => 1000], //kilojoule
            "mj"   => ["base" => "j", "conversion" => 1000000], //megajoule
            "cal"  => ["base" => "j", "conversion" => 4184], //calorie
            "Nm"   => ["base" => "j", "conversion" => 1], //newton meter
            "ftlb" => ["base" => "j", "conversion" => 1.35582], //foot pound
            "whr"  => ["base" => "j", "conversion" => 3600], //watt hour
            "kwhr" => ["base" => "j", "conversion" => 3600000], //kilowatt hour
            "mwhr" => ["base" => "j", "conversion" => 3600000000], //megawatt hour
            "mev"  => ["base" => "j", "conversion" => 0.00000000000000016], //mega electron volt
        ];
    }

    /**
     * UnitConverter constructor.
     */
    public function __construct()
    {
        $this->defineUnits();
    }

    /**
     * @param string $value
     * @param string $unit
     *
     * @throws Exception
     *
     * @return $this
     */
    public function from($value, $unit)
    {

        if (null === $value) {
            throw new Exception("Value Not Set");
        }

        if ($unit) {

            if (array_key_exists($unit, $this->units)) {
                $unitLookup = $this->units[$unit];

                $this->baseUnit = $unitLookup["base"];
                $this->value    = $this->convertToBase($value, $unitLookup);
            } else {
                throw new Exception("Unit Does Not Exist");
            }
        } else {
            $this->value = $value;
        }

        return $this;
    }

    /**
     * @param string $unit
     * @param null   $decimals
     * @param bool   $round
     *
     * @return array|float|int
     * @throws Exception
     */
    public function to($unit, $decimals = null, $round = true)
    {

        if (null === $this->value) {
            throw new Exception("From Value Not Set");
        }

        if (!$unit) {
            throw new Exception("Unit Not Set");
        }

        if (is_array($unit)) {
            return $this->toMany($unit, $decimals, $round);
        } else {
            if (array_key_exists($unit, $this->units)) {
                $unitLookup = $this->units[$unit];

                if ($this->baseUnit) {
                    if ($unitLookup["base"] != $this->baseUnit) {
                        throw new Exception("Cannot Convert Between Units of Different Types");
                    }
                } else {
                    $this->baseUnit = $unitLookup["base"];
                }

                if (is_callable($unitLookup["conversion"])) {
                    $result = $unitLookup["conversion"]($this->value, true);
                } else {
                    $result = $this->value / $unitLookup["conversion"];
                }

                if (null !== $decimals) {
                    if ($round) {
                        $result = round($result, $decimals);
                    } else {
                        $shifter = $decimals ? pow(10, $decimals) : 1;
                        $result  = floor($result * $shifter) / $shifter;
                    }
                }

                return $result;
            } else {
                throw new Exception("Unit Does Not Exist");
            }
        }
    }

    /**
     * @param array $unitList
     * @param null  $decimals
     * @param bool  $round
     *
     * @return array
     */
    private function toMany($unitList = [], $decimals = null, $round = true)
    {

        $resultList = [];

        foreach ($unitList as $key) {
            $resultList[$key] = $this->to($key, $decimals, $round);
        }

        return $resultList;
    }

    /**
     * @param null $decimals
     * @param bool $round
     *
     * @return array
     * @throws Exception
     */
    public function toAll($decimals = null, $round = true)
    {

        if (null === $this->value) {
            throw new Exception("From Value Not Set");
        }

        if ($this->baseUnit) {

            $unitList = [];
            foreach ($this->units as $key => $values) {
                if ($values["base"] == $this->baseUnit) {
                    array_push($unitList, $key);
                }
            }

            return $this->toMany($unitList, $decimals, $round);

        } else {
            throw new Exception("No From Unit Set");
        }

    }

    /**
     * @param string $unit
     * @param string $base
     * @param string $conversion
     *
     * @return bool
     * @throws Exception
     */
    public function addUnit($unit, $base, $conversion)
    {

        if (array_key_exists($unit, $this->units)) {
            throw new Exception("Unit Already Exists");
        } else {
            if (!array_key_exists($base, $this->units) && $base != $unit) {
                throw new Exception("Base Unit Does Not Exist");
            } else {
                $this->units[$unit] = ["base" => $base, "conversion" => $conversion];

                return true;
            }
        }

    }

    /**
     * @param string $unit
     *
     * @return bool
     * @throws Exception
     */
    public function removeUnit($unit)
    {
        //check unit exists
        if (array_key_exists($unit, $this->units)) {

            //if unit is base unit remove all dependant units
            if ($this->units[$unit]["base"] == $unit) {
                foreach ($this->units as $key => $values) {
                    if ($values["base"] == $unit) {
                        //remove unit
                        unset($this->units[$key]);
                    }
                }

            } else {
                //remove unit
                unset($this->units[$unit]);
            }

            return true;

        } else {
            throw new Exception("Unit Does Not Exist");
        }
    }

    /**
     * @param string $unit
     *
     * @return array
     * @throws Exception
     */
    public function getUnits($unit)
    {
        if (array_key_exists($unit, $this->units)) {
            $baseUnit = $this->units[$unit]["base"];

            $unitList = [];
            foreach ($this->units as $key => $values) {
                if ($values["base"] == $baseUnit) {
                    array_push($unitList, $key);
                }
            }

            return $unitList;
        } else {
            throw new Exception("Unit Does Not Exist");
        }
    }

    /**
     * @param string $value
     * @param array  $unitArray
     *
     * @return mixed
     */
    private function convertToBase($value, $unitArray)
    {

        if (is_callable($unitArray["conversion"])) {
            return $unitArray["conversion"]($value, false);
        } else {
            return $value * $unitArray["conversion"];
        }
    }
}