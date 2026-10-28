<?php

declare(strict_types=1);

namespace Privatus\FilamentAnalytics\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Privatus\FilamentAnalytics\Support\Format;
use Privatus\FilamentAnalytics\Support\SiteId;

final class SupportTest extends TestCase
{
    /** @return iterable<array{mixed, bool}> */
    public static function siteIds(): iterable
    {
        yield ['pa_7Q2K9XH3AB', true];
        yield ['pa_a1', true];
        yield ['pa_', false];
        yield ['PA_ABC', false];
        yield ['pa_ab"><script>', false];
        yield ['pa_ab/../x', false];
        yield ["pa_ab\n", false];
        yield [null, false];
        yield [123, false];
    }

    #[DataProvider('siteIds')]
    public function testSiteIdValidation(mixed $value, bool $valid): void
    {
        $this->assertSame($valid, SiteId::isValid($value));
    }

    public function testCleanTrims(): void
    {
        $this->assertSame('pa_ABC', SiteId::clean('  pa_ABC '));
        $this->assertNull(SiteId::clean('nope'));
    }

    public function testNumbers(): void
    {
        $this->assertSame('0', Format::number(null));
        $this->assertSame('950', Format::number(950));
        $this->assertSame('9,999', Format::number(9999));
        $this->assertSame('12.3k', Format::number(12345));
        $this->assertSame('2M', Format::number(2_000_000));
        $this->assertSame('2.7', Format::number(2.7));
    }

    public function testPercentAndDuration(): void
    {
        $this->assertSame('41.3%', Format::percent(41.25));
        $this->assertSame('-3%', Format::percent(-3));
        $this->assertSame('45s', Format::duration(45_000));
        $this->assertSame('1m 23s', Format::duration(83_000));
        $this->assertSame('1h 05m', Format::duration(3_900_000));
        $this->assertSame('0s', Format::duration('x'));
    }
}
