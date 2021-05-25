<?php

namespace Pharaonic\Hijri;

trait HijriCarbon
{
    /**
     * Adjustment days of Hijri Date
     *
     * @var integer
     */
    protected static $HIJRI_ADJUSTMENT = -1;

    /**
     * Setting adjustment days for Hijri
     *
     * @param integer $days
     * @return void
     */
    public function setHijriAdjustment(int $days)
    {
        self::$HIJRI_ADJUSTMENT = $days;
    }

    /**
     * Getting adjustment days for Hijri
     *
     * @return integer
     */
    public function getHijriAdjustment()
    {
        return self::$HIJRI_ADJUSTMENT;
    }

    /**
     * Move to Hijri class
     *
     * @return Hijri
     */
    public function toHijri()
    {
        return Hijri::getInstance()->parse($this);
    }
}
