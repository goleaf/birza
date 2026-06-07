<?php

namespace App\Livewire\Backend\ProductQuestions;

use App\Actions\ProductQuestions\ApproveProductQuestionAction;
use App\Actions\ProductQuestions\HideProductQuestionAction;
use App\Actions\ProductQuestions\RejectProductQuestionAction;
use App\Enums\ProductQuestionStatus;
use App\Models\ProductQuestion;
use App\Models\Users\Admin;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.backend.app')]
class Index extends Component
{
    use WithPagination;

    public string $status = 'pending';

    /**
     * @var array<int, string>
     */
    public array $reasons = [];

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function approve(int $questionId, ApproveProductQuestionAction $action): void
    {
        $action->handle(
            productQuestion: $this->question($questionId),
            admin: $this->admin(),
            reason: $this->reasons[$questionId] ?? null,
        );

        session()->flash('success', __('products.questions.approved'));
    }

    public function reject(int $questionId, RejectProductQuestionAction $action): void
    {
        $this->validateReason($questionId);

        $action->handle(
            productQuestion: $this->question($questionId),
            admin: $this->admin(),
            reason: $this->reasons[$questionId] ?? null,
        );

        session()->flash('success', __('products.questions.rejected'));
    }

    public function hide(int $questionId, HideProductQuestionAction $action): void
    {
        $this->validateReason($questionId);

        $action->handle(
            productQuestion: $this->question($questionId),
            actor: $this->admin(),
            reason: $this->reasons[$questionId] ?? null,
            source: 'admin_product_questions',
        );

        session()->flash('success', __('products.questions.hidden'));
    }

    public function render(): View
    {
        $status = ProductQuestionStatus::tryFrom($this->status);

        return view('livewire.backend.product-questions.index', [
            'questions' => ProductQuestion::query()
                ->visibleToAdmin()
                ->select([
                    'id',
                    'product_id',
                    'seller_id',
                    'buyer_id',
                    'answered_by_seller_id',
                    'moderated_by_admin_id',
                    'question',
                    'answer',
                    'answered_at',
                    'status',
                    'is_public',
                    'guest_name',
                    'moderated_at',
                    'moderation_reason',
                    'created_at',
                ])
                ->with([
                    'product:id,name,seller_id,is_active,deleted_at',
                    'seller:id,name,email,company_name',
                    'buyer:id,name,email,company_name',
                    'answeredBySeller:id,name,company_name',
                    'moderatedByAdmin:id,name,email',
                ])
                ->when($status !== null, fn ($query) => $query->where('status', $status->value))
                ->latest()
                ->paginate(20)
                ->withQueryString(),
            'statusOptions' => ProductQuestionStatus::options(),
        ]);
    }

    private function admin(): Admin
    {
        $admin = Auth::guard('admin')->user();

        abort_unless($admin instanceof Admin, 403);

        return $admin;
    }

    private function question(int $questionId): ProductQuestion
    {
        return ProductQuestion::query()
            ->visibleToAdmin()
            ->findOrFail($questionId);
    }

    private function validateReason(int $questionId): void
    {
        $this->validate([
            "reasons.{$questionId}" => ['nullable', 'string', 'max:255'],
        ], [], [
            "reasons.{$questionId}" => __('products.questions.moderation_reason'),
        ]);
    }
}
