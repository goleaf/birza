<?php

namespace App\Actions\Promotions;

use App\Actions\Cart\CalculateCartTotalsAction;
use App\Models\Cart;
use App\Models\Users\Buyer;

class ApplyPromoCodeAction
{
    public function __construct(
        private readonly CalculateCartTotalsAction $calculateCartTotalsAction,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function handle(Cart $cart, ?Buyer $buyer, string $code): array
    {
        return $this->calculateCartTotalsAction->handle($cart, $buyer, $code);
    }
}
