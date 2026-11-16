<?php

namespace Tests\Unit;

use App\Support\OutputChecker;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class OutputCheckerTest extends TestCase
{
    #[DataProvider('outputs')]
    public function test_output_modes(string $actual, string $expected, string $mode, float $tolerance, bool $matches): void
    {
        $this->assertSame($matches, OutputChecker::matches($actual, $expected, $mode, $tolerance));
    }

    public static function outputs(): array
    {
        return [
            'trimmed exact' => ["  hello\n", 'hello', 'exact', 0.000001, true],
            'exact whitespace matters' => ['1  2', "1\n2", 'exact', 0.000001, false],
            'ordered tokens' => [" 1\t2\r\n3 ", '1 2 3', 'tokens', 0.000001, true],
            'token order' => ['2 1', '1 2', 'tokens', 0.000001, false],
            'token count' => ['1 2', '1', 'tokens', 0.000001, false],
            'token strings' => ['1.0', '1', 'tokens', 0.000001, false],
            'large integers retain precision' => ['9007199254740993', '9007199254740992', 'tokens', 0.000001, false],
            'empty exact' => ['', ' ', 'exact', 0.000001, true],
            'empty tokens' => ['', ' ', 'tokens', 0.000001, true],
            'numeric notation' => ["+1e2\t.5 -0", '100 0.5 0', 'numeric', 0, true],
            'within tolerance' => ['1.0000005', '1', 'numeric', 0.000001, true],
            'absolute boundary' => ['1.125', '1', 'numeric', 0.125, true],
            'outside tolerance' => ['1.000002', '1', 'numeric', 0.000001, false],
            'absolute not relative' => ['1000001', '1000000', 'numeric', 0.000001, false],
            'numeric token count' => ['1 2', '1', 'numeric', 0.000001, false],
            'numeric text' => ['answer: 1', '1', 'numeric', 0.000001, false],
            'nonfinite actual' => ['INF', '1', 'numeric', 0.000001, false],
            'nonfinite expected' => ['1', 'NaN', 'numeric', 0.000001, false],
            'overflow' => ['1e999', '1e999', 'numeric', 0.000001, false],
            'empty numeric' => ['', ' ', 'numeric', 0.000001, false],
            'hex rejected' => ['0x10', '16', 'numeric', 0.000001, false],
            'nul rejected' => ["1\0", '1', 'numeric', 0.000001, false],
            'tokens retain nul' => ["1\0", '1', 'tokens', 0.000001, false],
            'negative tolerance' => ['1', '1', 'numeric', -1, false],
            'nonfinite tolerance' => ['1', '1', 'numeric', INF, false],
            'nan tolerance' => ['1', '1', 'numeric', NAN, false],
            'unknown mode' => ['1', '1', 'custom', 0.000001, false],
        ];
    }
}
