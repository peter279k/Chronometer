<?php

namespace PHLAK\Chronometer\Tests;

use PHLAK\Chronometer\Timer;
use PHLAK\Chronometer\Lap;
use PHLAK\Chronometer\Exceptions\TimerException;
use PHPUnit\Framework\TestCase;

class TimerTest extends TestCase
{
    /** @var string Microtime regular expression */
    protected $microtimeRegex = '/[0-9]{10}(\.[0-9]+)?/';

    public function tearDown()
    {
        Timer::reset();
    }

    public function test_it_can_start_a_timer()
    {
        $start = Timer::start();
        $lastLap = Timer::lastLap();

        $this->assertRegExp($this->microtimeRegex, (string) $start);
        $this->assertEquals(new Lap($start, 0), $lastLap);
    }

    public function test_it_can_end_a_timer()
    {
        $start = Timer::start();
        $end = Timer::stop();
        $lastLap = Timer::lastLap();

        $this->assertRegExp($this->microtimeRegex, (string) $end);
        $this->assertEquals(new Lap($end, $end - $start), $lastLap);
    }

    public function test_it_can_get_the_time_elapsed()
    {
        Timer::start();
        usleep(5000);

        $elapsed = Timer::elapsed();

        $this->assertRegExp('/0\.005[0-9]+/', (string) $elapsed);
    }

    public function test_it_can_get_the_total_time_elapsed()
    {
        Timer::start();
        usleep(10000);
        Timer::stop();

        $elapsed = Timer::elapsed();

        $this->assertRegExp('/0\.01[0-9]+/', (string) $elapsed);
    }

    public function test_it_can_add_a_lap()
    {
        $start = Timer::start();
        usleep(2000);
        $lap = Timer::addLap();
        usleep(10000);
        $end = Timer::stop();

        $laps = Timer::laps();

        $this->assertInstanceOf(Lap::class, $lap);
        $this->assertRegExp($this->microtimeRegex, (string) $lap->time);
        $this->assertRegExp('/0\.[0-9]+/', (string) $lap->duration);
        $this->assertNull($lap->description);

        $this->assertEquals([new Lap($start, 0), $lap, new Lap($end, $end - $lap->time)], $laps);
    }

    public function test_it_can_not_be_started_twice()
    {
        $this->expectException(TimerException::class);
        $this->expectExceptionMessage('Timer already running, must reset timer before starting again');

        Timer::start();
        Timer::start();
    }

    public function test_it_can_be_started_twice_with_a_parameter()
    {
        Timer::start();
        $start = Timer::start($reset = true);
        $lastLap = Timer::lastLap();

        $this->assertRegExp($this->microtimeRegex, (string) $start);
        $this->assertEquals(new Lap($start, 0), $lastLap);
    }

    public function test_it_cannot_be_stopped_without_being_started()
    {
        $this->expectException(TimerException::class);
        $this->expectExceptionMessage('Timer must be started before stopping');

        Timer::stop();
    }

    public function test_it_can_get_the_start_time()
    {
        $start = Timer::start();
        usleep(1000);
        $started = Timer::started();

        $this->assertEquals($start, $started);
    }

    public function test_it_can_get_the_stop_time()
    {
        Timer::start();
        usleep(1000);
        $stop = Timer::stop();

        $stopped = Timer::stopped();

        $this->assertEquals($stop, $stopped);
    }

    public function test_it_can_not_get_an_elapsed_time_without_being_started()
    {
        $this->expectException(TimerException::class);
        $this->expectExceptionMessage('Timer must be started first');

        Timer::elapsed();
    }

    public function test_it_can_not_get_the_start_time_without_being_started()
    {
        $this->expectException(TimerException::class);
        $this->expectExceptionMessage('Timer must be started first');

        Timer::started();
    }

    public function test_it_can_not_get_the_stopped_time_without_being_started()
    {
        $this->expectException(TimerException::class);
        $this->expectExceptionMessage('Timer must be started and stopped first');

        Timer::stopped();
    }

    public function test_it_can_not_get_the_stopped_time_without_being_stopped()
    {
        Timer::start();

        $this->expectException(TimerException::class);
        $this->expectExceptionMessage('Timer must be started and stopped first');

        Timer::stopped();
    }

    public function test_it_can_not_add_a_lap_without_being_started()
    {
        $this->expectException(TimerException::class);
        $this->expectExceptionMessage('Timer must be started first');

        Timer::addLap();
    }

    public function test_it_cannot_add_a_lap_after_being_stopped()
    {
        Timer::start();
        Timer::stop();

        $this->expectException(TimerException::class);
        $this->expectExceptionMessage('Cannot add a lap after timer has been stopped');

        Timer::addLap();
    }

    public function test_it_can_not_get_the_last_lap_without_being_started()
    {
        $this->expectException(TimerException::class);
        $this->expectExceptionMessage('Timer must be started first');

        Timer::lastLap();
    }

    public function test_it_can_not_get_an_array_of_laps_without_being_started()
    {
        $this->expectException(TimerException::class);
        $this->expectExceptionMessage('Timer must be started first');

        Timer::laps();
    }

    public function test_it_can_have_named_laps()
    {
        $start = Timer::start();
        usleep(1000);
        $lap = Timer::addLap('The first lap.');

        $this->assertEquals('The first lap.', $lap->description);
    }
}
