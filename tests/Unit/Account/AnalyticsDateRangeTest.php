<?php

use App\Http\Livewire\Account\Analytics;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Build an anonymous harness exposing protected helpers for focused unit testing.
 *
 * @return Analytics
 */
function makeAnalyticsHarness(): Analytics
{
    return new class () extends Analytics {
        /**
         * Provide a testing hook into the protected parseDate helper.
         */
        public function callParseDate(?string $value, Carbon $fallback): Carbon
        {
            return $this->parseDate($value, $fallback);
        }

        /**
         * Provide a testing hook into the protected resolveDateRange helper.
         *
         * @return array{0: Carbon, 1: Carbon}
         */
        public function callResolveDateRange(): array
        {
            return $this->resolveDateRange();
        }

        /**
         * Expose the rangeBounds helper so the unit tests can confirm formatting logic.
         *
         * @return array{0: string, 1: string}
         */
        public function callRangeBounds(Carbon $start, Carbon $end): array
        {
            return $this->rangeBounds($start, $end);
        }
    };
}

/**
 * Unit coverage for date parsing and range normalisation in the analytics component.
 */
describe('Account analytics date helpers', function () {
    it('parses valid dates and gracefully falls back for invalid input', function () {
        // Instantiate the harness with default collection state to satisfy the base component expectations.
        $component = makeAnalyticsHarness();
        $component->topPosts = new Collection();

        $fallback = Carbon::parse('2025-04-01');

        // Valid dates should echo back the provided value without mutation.
        $parsed = $component->callParseDate('2025-03-15', $fallback);
        expect($parsed->toDateString())->toBe('2025-03-15');

        // Invalid input should seamlessly return the supplied fallback Carbon instance.
        $invalid = $component->callParseDate('not-a-date', $fallback);
        expect($invalid)->toBe($fallback);
    });

    it('normalises inverted ranges and provides string database bounds', function () {
        $component = makeAnalyticsHarness();
        $component->topPosts = new Collection();

        // Start with an inverted range where the start exceeds the end to trigger auto-correction.
        $component->startDate = '2025-04-20';
        $component->endDate = '2025-04-10';

        Carbon::setTestNow(Carbon::parse('2025-04-25 12:00:00'));

        [$start, $end] = $component->callResolveDateRange();

        // The helper should reset the range to a sensible 30-day window ending at the provided end date.
        expect($start->lessThanOrEqualTo($end))->toBeTrue();
        expect($component->startDate)->toBe($start->toDateString());
        expect($component->endDate)->toBe($end->toDateString());

        // Confirm the string bounds honour full timestamp precision for database comparisons.
        $bounds = $component->callRangeBounds($start, $end);
        expect($bounds[0])->toBe($start->toDateTimeString());
        expect($bounds[1])->toBe($end->toDateTimeString());

        Carbon::setTestNow();
    });
});
