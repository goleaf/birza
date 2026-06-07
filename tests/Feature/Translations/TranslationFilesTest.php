<?php

namespace Tests\Feature\Translations;

use Tests\TestCase;

class TranslationFilesTest extends TestCase
{
    public function test_supported_json_translation_files_have_identical_keys(): void
    {
        $translations = $this->translationsByLocale();
        $canonicalKeys = array_keys($translations[(string) config('app.fallback_locale')]);

        foreach ($translations as $locale => $lines) {
            $this->assertSame(
                $canonicalKeys,
                array_keys($lines),
                "The [{$locale}] translation file must match the fallback locale key set.",
            );
        }
    }

    public function test_canonical_dot_based_translation_keys_exist_for_core_surfaces(): void
    {
        $requiredKeys = [
            'ui.actions.save',
            'ui.actions.cancel',
            'ui.actions.delete',
            'ui.actions.edit',
            'ui.actions.view',
            'ui.actions.search',
            'ui.actions.filter',
            'ui.actions.reset',
            'ui.actions.confirm',
            'ui.status.active',
            'ui.status.inactive',
            'marketplace.products.title',
            'marketplace.products.description',
            'marketplace.products.price',
            'marketplace.products.status.active',
            'marketplace.products.status.inactive',
            'marketplace.products.status.deleted',
            'orders.status.label',
            'orders.status.pending',
            'orders.status.accepted',
            'orders.status.rejected',
            'orders.status.processing',
            'orders.status.shipped',
            'orders.status.delivered',
            'orders.status.completed',
            'orders.status.cancelled',
            'orders.status.refunded',
            'orders.status.disputed',
            'orders.status.pending.description',
            'orders.status.accepted.description',
            'orders.status.messages.cannot_be_changed',
            'orders.status.messages.direct_change_forbidden',
            'orders.status.messages.reason_required',
            'orders.status.messages.transition_not_allowed',
            'orders.status.actor.buyer',
            'orders.status.actor.seller',
            'orders.status.actor.admin',
            'orders.status.actor.system',
            'orders.payment_status.label',
            'orders.payment_status.pending',
            'orders.payment_status.paid',
            'orders.payment_status.failed',
            'orders.payment_status.cancelled',
            'orders.payment_status.refunded',
            'orders.actions.cancel',
            'orders.messages.cancelled_successfully',
            'orders.messages.cannot_cancel',
            'orders.transactions.seller_credit',
            'orders.transactions.seller_debit',
            'cart.title',
            'cart.empty',
            'cart.actions.add',
            'cart.actions.remove',
            'cart.messages.added_successfully',
            'checkout.title',
            'checkout.steps.review',
            'checkout.steps.address',
            'checkout.steps.confirmation',
            'checkout.discount',
            'checkout.total_before_discount',
            'checkout.total_after_discount',
            'checkout.messages.order_created',
            'discounts.title',
            'discounts.create',
            'discounts.edit',
            'discounts.type.percentage',
            'discounts.type.fixed_amount',
            'discounts.status.active',
            'discounts.status.inactive',
            'promo_codes.title',
            'promo_codes.code',
            'promo_codes.apply',
            'promo_codes.remove',
            'promo_codes.invalid',
            'promo_codes.expired',
            'promo_codes.not_started',
            'promo_codes.usage_limit_reached',
            'promo_codes.user_limit_reached',
            'promo_codes.minimum_order_amount',
            'promo_codes.applied_successfully',
            'compare.title',
            'compare.empty',
            'compare.actions.add',
            'compare.actions.remove',
            'compare.actions.clear',
            'compare.messages.added',
            'compare.messages.removed',
            'compare.messages.cleared',
            'compare.messages.limit_reached',
            'compare.messages.already_exists',
            'compare.messages.product_unavailable',
            'auth.login.title',
            'auth.register.title',
            'auth.fields.email',
            'auth.fields.password',
            'validation.attributes.email',
            'validation.attributes.locale',
            'validation.attributes.password',
            'validation.attributes.query',
            'notifications.actions.view',
            'notifications.actions.mark_all_read',
            'notifications.actions.mark_read',
            'notifications.empty.title',
            'notifications.empty.message',
            'notifications.fallback.title',
            'notifications.fallback.message',
            'notifications.filters.all',
            'notifications.filters.unread',
            'notifications.filters.read',
            'notifications.ui.title',
            'notifications.ui.subtitle',
            'notifications.ui.unread',
            'notifications.ui.unread_count',
            'notifications.ui.admin_alerts',
            'notifications.messages.unauthorized',
            'notifications.orders.created.title',
            'notifications.orders.created.message',
            'notifications.orders.new_for_seller.title',
            'notifications.orders.new_for_seller.message',
            'notifications.orders.status_changed.subject',
            'notifications.orders.status_changed.title',
            'notifications.orders.status_changed.message',
            'notifications.products.approved.title',
            'notifications.products.approved.message',
            'notifications.products.moderation_required.title',
            'notifications.products.moderation_required.message',
            'notifications.products.rejected.title',
            'notifications.products.rejected.message',
            'notifications.products.rejected.message_with_reason',
            'notifications.reports.product_created.title',
            'notifications.reports.product_created.message',
            'notifications.reports.product_hidden.title',
            'notifications.reports.product_hidden.message',
            'notifications.reports.product_hidden.message_with_note',
            'notifications.stock.low.title',
            'notifications.stock.low.message',
            'notifications.stock.out.title',
            'notifications.stock.out.message',
            'notifications.product_question.created.title',
            'notifications.product_question.created.message',
            'notifications.product_question.answered.title',
            'notifications.product_question.answered.message',
            'notifications.product_question.rejected.title',
            'notifications.product_question.rejected.message',
            'notifications.product_question.rejected.message_with_reason',
            'notifications.messages.new.title',
            'notifications.messages.new.message',
            'messages.title',
            'messages.inbox',
            'messages.conversation',
            'messages.no_conversations',
            'messages.no_messages_yet',
            'messages.write_message',
            'messages.send',
            'messages.sent_successfully',
            'messages.archived_successfully',
            'messages.unread',
            'messages.related_product',
            'messages.related_order',
            'messages.contact_seller',
            'messages.ask_seller',
            'messages.validation.body_required',
            'messages.validation.body_too_long',
            'messages.errors.not_allowed',
            'messages.errors.conversation_closed',
            'products.questions.title',
            'products.questions.ask',
            'products.questions.question',
            'products.questions.answer',
            'products.questions.no_questions',
            'products.questions.submit',
            'products.questions.pending_moderation',
            'products.questions.answered_successfully',
            'products.questions.rejected',
            'products.questions.hidden',
            'products.questions.validation.question_required',
            'reports.product.guest_reporter',
            'reports.product.reasons.scam',
            'reports.product.reasons.fake_product',
            'reports.product.reasons.wrong_price',
            'reports.product.reasons.misleading_description',
            'reports.product.status.pending',
            'reports.product.status.reviewing',
            'reports.product.status.resolved',
            'stock_alerts.title',
            'stock_alerts.notify_me',
            'stock_alerts.email',
            'stock_alerts.subscribe',
            'stock_alerts.cancel',
            'stock_alerts.already_subscribed',
            'stock_alerts.created_successfully',
            'stock_alerts.cancelled_successfully',
            'stock_alerts.product_available',
            'stock_alerts.empty',
            'stock_alerts.status.active',
            'stock_alerts.status.notified',
            'stock_alerts.status.cancelled',
            'notifications.stock_alert.back_in_stock.title',
            'notifications.stock_alert.back_in_stock.message',
            'emails.seller.password_reset.subject',
        ];

        foreach ($this->translationsByLocale() as $locale => $lines) {
            foreach ($requiredKeys as $key) {
                $this->assertArrayHasKey($key, $lines, "Missing [{$key}] in [{$locale}].");
                $this->assertNotSame($key, $lines[$key], "Translation [{$key}] in [{$locale}] must not use the key as visible text.");
            }
        }
    }

    /**
     * @return array<string, array<string, string>>
     */
    private function translationsByLocale(): array
    {
        $translations = [];

        foreach ((array) config('app.locales') as $locale) {
            $path = lang_path("{$locale}.json");

            $this->assertFileExists($path);

            $lines = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
            ksort($lines);

            $translations[$locale] = $lines;
        }

        return $translations;
    }
}
