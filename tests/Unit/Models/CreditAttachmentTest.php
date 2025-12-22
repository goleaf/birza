<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\CreditAttachment;
use App\Models\BuyerCreditHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CreditAttachmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_attachment_belongs_to_credit_history(): void
    {
        $history = BuyerCreditHistory::factory()->create();
        $attachment = CreditAttachment::factory()->create(['credit_history_id' => $history->id]);

        $this->assertInstanceOf(BuyerCreditHistory::class, $attachment->creditHistory);
        $this->assertEquals($history->id, $attachment->creditHistory->id);
    }

    public function test_attachment_fillable(): void
    {
        $attachment = new CreditAttachment();
        $fillable = $attachment->getFillable();

        $this->assertContains('credit_history_id', $fillable);
        $this->assertContains('file_path', $fillable);
        $this->assertContains('original_name', $fillable);
    }
}

