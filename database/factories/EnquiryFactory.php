<?php

namespace Database\Factories;

use App\Enums\AreaOfInterest;
use App\Enums\EnquiryStatus;
use App\Enums\EnquiryType;
use App\Models\Enquiry;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Enquiry>
 */
class EnquiryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => EnquiryType::Contact,
            'status' => EnquiryStatus::New,
            'name' => fake()->name(),
            'organisation' => fake()->optional()->company(),
            'email' => fake()->safeEmail(),
            'phone' => '+2348107807012',
            'subject' => AreaOfInterest::GeneralInquiry->value,
            'message' => fake()->paragraph(),
            'payload' => null,
            'idempotency_key' => (string) Str::uuid(),
        ];
    }

    public function quote(): static
    {
        return $this->state([
            'type' => EnquiryType::Quote,
            'subject' => null,
            'message' => null,
            'payload' => ['delivery_location' => 'Abeokuta, Ogun', 'required_by' => now()->addWeeks(2)->toDateString()],
        ]);
    }

    public function partnership(): static
    {
        return $this->state(['type' => EnquiryType::Partnership, 'subject' => 'distributor']);
    }
}
