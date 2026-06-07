<?php

namespace App\Livewire\Frontend\ProductQuestions;

use App\Actions\ProductQuestions\CreateProductQuestionAction;
use App\Models\Product;
use App\Models\ProductQuestion;
use App\Models\Users\Buyer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Panel extends Component
{
    public Product $product;

    public string $question = '';

    public string $guestName = '';

    public string $guestEmail = '';

    public ?string $successMessage = null;

    public function mount(Product $product): void
    {
        $this->product = $product;
    }

    public function submit(CreateProductQuestionAction $action): void
    {
        $validated = $this->validate($this->rules(), [], $this->validationAttributes());

        try {
            $action->handle(
                product: $this->product,
                buyer: $this->buyer(),
                question: $validated['question'],
                guestName: $validated['guestName'] ?? null,
                guestEmail: $validated['guestEmail'] ?? null,
            );
        } catch (ValidationException $exception) {
            throw $exception;
        }

        $this->reset(['question', 'guestName', 'guestEmail']);
        $this->successMessage = __('products.questions.pending_moderation');
    }

    public function render(): View
    {
        return view('livewire.frontend.product-questions.panel', [
            'questions' => ProductQuestion::query()
                ->publicAnswered()
                ->select([
                    'id',
                    'product_id',
                    'buyer_id',
                    'answered_by_seller_id',
                    'question',
                    'answer',
                    'answered_at',
                    'guest_name',
                    'created_at',
                ])
                ->where('product_id', $this->product->id)
                ->with([
                    'buyer:id,name,company_name',
                    'answeredBySeller:id,name,company_name',
                ])
                ->latest('answered_at')
                ->limit(10)
                ->get(),
            'buyer' => $this->buyer(),
        ]);
    }

    /**
     * @return array<string, list<string>>
     */
    protected function rules(): array
    {
        $rules = [
            'question' => ['required', 'string', 'min:10', 'max:1000'],
        ];

        if ($this->buyer() === null) {
            $rules['guestName'] = ['required', 'string', 'min:2', 'max:120'];
            $rules['guestEmail'] = ['nullable', 'email:rfc', 'max:255'];
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return [
            'question' => __('products.questions.question'),
            'guestName' => __('products.questions.guest_name'),
            'guestEmail' => __('products.questions.guest_email'),
        ];
    }

    private function buyer(): ?Buyer
    {
        $buyer = Auth::guard('buyer')->user();

        return $buyer instanceof Buyer ? $buyer : null;
    }
}
