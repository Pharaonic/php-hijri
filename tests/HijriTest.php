<?php

namespace Pharaonic\Hijri;

use Carbon\Carbon;
use Pharaonic\Hijri\HijriCarbon;
use PHPUnit\Framework\TestCase;

class HijriTest extends TestCase
{
    /**
     * Carbon DateTime
     *
     * @var Carbon
     */
    protected $dt;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::mixin(HijriCarbon::class);
        $this->dt = Carbon::parse('01-02-1993 19:00:00');
    }

    public function testHijri()
    {
        $this->assertEquals('Monday, Sha\'aban 8, 1413 7:00 PM', $this->dt->toHijri()->isoFormat('LLLL'));
    }

    public function testLocalizaedHijri()
    {
        $this->assertEquals('الاثنين 8 شَعبان 1413 19:00', $this->dt->toHijri()->locale('ar')->isoFormat('LLLL'));
    }
}
