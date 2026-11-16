<?php

namespace App\Support;

class OutputChecker
{
    public static function matches(string $actual, string $expected, string $mode = 'exact', float $tolerance = 0.000001): bool
    {
        if ($mode === 'exact') {
            return trim($actual) === trim($expected);
        }

        $actualTokens = preg_split('/\s+/', $actual, -1, PREG_SPLIT_NO_EMPTY);
        $expectedTokens = preg_split('/\s+/', $expected, -1, PREG_SPLIT_NO_EMPTY);
        if ($mode === 'tokens') {
            return $actualTokens === $expectedTokens;
        }

        if ($mode !== 'numeric' || ! is_finite($tolerance) || $tolerance < 0
            || count($actualTokens) === 0 || count($actualTokens) !== count($expectedTokens)) {
            return false;
        }

        // Numeric mode is for floating point output; exact/tokens preserve large integer precision.
        foreach ($actualTokens as $index => $actualToken) {
            $expectedToken = $expectedTokens[$index];
            if (! is_numeric($actualToken) || ! is_numeric($expectedToken)
                || ! is_finite((float) $actualToken) || ! is_finite((float) $expectedToken)
                || abs((float) $actualToken - (float) $expectedToken) > $tolerance) {
                return false;
            }
        }

        return true;
    }
}
