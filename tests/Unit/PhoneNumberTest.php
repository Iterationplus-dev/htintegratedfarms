<?php

namespace Tests\Unit;

use App\Rules\PhoneNumber;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PhoneNumberTest extends TestCase
{
    /**
     * @return array<string, array{string, bool}>
     */
    public static function numbers(): array
    {
        return [
            'local mobile' => ['08107807012', true],
            'local with spaces' => ['0810 780 7012', true],
            'international NG' => ['+234 810 780 7012', true],
            'NG 00 prefix' => ['002348107807012', true],
            'NG with dashes' => ['+234-805-046-0470', true],
            'UK number' => ['+44 7760 892302', true],
            'NG too short' => ['0810780701', false],
            'NG bad network digit' => ['06107807012', false],
            'no country code' => ['7760892302', false],
            'letters' => ['call me', false],
            'too long' => ['+1234567890123456', false],
        ];
    }

    #[DataProvider('numbers')]
    public function test_validates_phone_numbers(string $number, bool $valid): void
    {
        $this->assertSame($valid, Validator::make(['phone' => $number], ['phone' => [new PhoneNumber]])->passes());
    }

    public function test_normalizes_formatting(): void
    {
        $this->assertSame('+2348107807012', PhoneNumber::normalize(' +234 (810) 780-7012 '));
        $this->assertSame('+447760892302', PhoneNumber::normalize('0044 7760 892302'));
    }
}
