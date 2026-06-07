<?php

namespace App\Livewire\Frontend\Seller\ProductQuestions;

use App\Actions\ProductQuestions\AnswerProductQuestionAction;
use App\Actions\ProductQuestions\HideProductQuestionAction;
use App\Enums\ProductQuestionStatus;
use App\Models\ProductQuestion;
use App\Models\Users\Seller;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.frontend.app')]
class Index extends Component
{
    use WithPagination;

    public string $status = 'pending';

    /**
     * @var array<int, string>
     */
    public array $answers = [];

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function answer(int $questionId, AnswerProductQuestionAction $action): void
    {
        $this->validate([
            "answers.{$questionId}" => ['required', 'string', 'min:5', 'max:2000'],
        ], [], [
            "answers.{$questionId}" => __('products.questions.answer'),
        ]);

        $question = $this->sellerQuestion($questionId);
        $action->handle($question, $this->seller(), $this->answers[$questionId]);

        unset($this->answers[$questionId]);

        session()->flash('success', __('products.questions.answered_successfully'));
    }

    public function hide(int $questionId, HideProductQuestionAction $action): void
    {
        $action->handle(
            productQuestion: $this->sellerQuestion($questionId),
            actor: $this->seller(),
            reason: __('products.questions.hidden_by_seller'),
            source: 'seller_product_questions',
        );

        session()->flash('success', __('products.questions.hidden'));
    }

    public function render(): View
    {
        $status = ProductQuestionStatus::tryFrom($this->status);

        return view('livewire.frontend.seller.product-questions.index', [
            'questions' => ProductQuestion::query()
                ->forSeller($this->seller())
                ->select([
                    'id',
                    'product_id',
                    'seller_id',
                    'buyer_id',
                    'answered_by_seller_id',
                    'question',
                    'answer',
                    'answered_at',
                    'status',
                    'is_public',
                    'guest_name',
                    'created_at',
                ])
                ->with([
                    'product:id,name,seller_id,is_active,deleted_at',
                    'buyer:id,name,email,company_name',
                    'answeredBySeller:id,name,company_name',
                ])
                ->when($status !== null, fn ($query) => $query->where('status', $status->value))
                ->latest()
                ->paginate(15)
                ->withQueryString(),
            'statusOptions' => ProductQuestionStatus::options(),
        ]);
    }

    private function seller(): Seller
    {
        $seller = Auth::guard('seller')->user();

        abort_unless($seller instanceof Seller, 403);

        return $seller;
    }

    private function sellerQuestion(int $questionId): ProductQuestion
    {
        return ProductQuestion::query()
            ->forSeller($this->seller())
            ->findOrFail($questionId);
    }
}
