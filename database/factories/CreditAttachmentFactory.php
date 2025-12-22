<?php

namespace Database\Factories;

use App\Models\CreditAttachment;
use App\Models\BuyerCreditHistory;
use Illuminate\Database\Eloquent\Factories\Factory;

class CreditAttachmentFactory extends Factory
{
    protected $model = CreditAttachment::class;

    public function definition(): array
    {
        return [
            'credit_history_id' => BuyerCreditHistory::factory(),
            'file_path' => 'attachments/' . $this->faker->uuid() . '.pdf',
            'original_name' => $this->faker->word() . '.pdf',
        ];
    }
}

