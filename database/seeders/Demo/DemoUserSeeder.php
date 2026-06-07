<?php

namespace Database\Seeders\Demo;

use App\Models\Category;
use App\Models\User;
use App\Models\Users\Admin;
use App\Models\Users\Buyer;
use App\Models\Users\Seller;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class DemoUserSeeder extends Seeder
{
    public const PASSWORD = 'password';

    public function run(): void
    {
        $this->admin('admin@example.com', 'Demo Admin');

        $this->buyer('buyer@example.com', 'Demo Buyer');
        $this->buyer('demo-empty-buyer@example.com', 'Demo Empty Buyer');
        $this->buyer('demo-cart-buyer@example.com', 'Demo Cart Buyer');
        $this->buyer('demo-orders-buyer@example.com', 'Demo Orders Buyer');
        $this->buyer('inactive-buyer@example.com', 'Inactive Buyer', isActive: false);
        $this->buyer('blocked-buyer@example.com', 'Blocked Buyer', isActive: false);
        $this->buyer('unverified-buyer@example.com', 'Unverified Buyer', isVerified: false);

        $this->seller('seller@example.com', 'Demo Seller');
        $this->seller('demo-seller-one@example.com', 'Demo Seller One');
        $this->seller('demo-seller-two@example.com', 'Demo Seller Two');
        $this->seller('seller-empty@example.com', 'Seller With No Products');
        $this->seller('demo-blocked-seller@example.com', 'Demo Blocked Seller', isActive: false);
        $this->seller('blocked-seller@example.com', 'Blocked Seller', isActive: false);
        $this->seller('unverified-seller@example.com', 'Unverified Seller', isVerified: false);

        $sharedUser = $this->sharedUser('buyer-seller@example.com', 'Buyer Seller Demo User');

        $this->buyer('buyer-seller@example.com', 'Buyer Seller Demo User', sharedUser: $sharedUser);
        $this->seller('buyer-seller@example.com', 'Buyer Seller Demo User', sharedUser: $sharedUser);
    }

    private function admin(string $email, string $name): Admin
    {
        $admin = Admin::query()->firstOrNew(['email' => $email]);
        $admin->name = $name;
        $admin->is_active = true;

        if (! $admin->exists) {
            $admin->password = Hash::make(self::PASSWORD);
        }

        $admin->save();

        return $admin;
    }

    private function buyer(
        string $email,
        string $name,
        bool $isActive = true,
        bool $isVerified = true,
        ?User $sharedUser = null,
    ): Buyer {
        $sharedUser ??= $this->sharedUser($email, $name, $isActive, $isVerified);

        $buyer = Buyer::query()->firstOrNew(['email' => $email]);
        $buyer->forceFill([
            'name' => $name,
            'company_name' => $name.' Ltd',
            'company_code' => $this->companyCode($email),
            'vat_code' => 'LT'.$this->companyCode($email),
            'address' => $name.' Street 1, Vilnius, Lithuania',
            'phone' => '+3706'.substr($this->companyCode($email), 0, 7),
            'bank_account' => 'LT1210000111010000'.substr($this->companyCode($email), 0, 2),
            'credit_balance' => $isActive ? 150.00 : 0.00,
            'is_verified' => $isVerified,
            'is_active' => $isActive,
            'email_verified_at' => $isVerified ? now() : null,
        ]);

        $this->setProfileUser($buyer, $sharedUser);

        if (! $buyer->exists) {
            $buyer->password = Hash::make(self::PASSWORD);
        }

        $buyer->save();

        return $buyer;
    }

    private function seller(
        string $email,
        string $companyName,
        bool $isActive = true,
        bool $isVerified = true,
        ?User $sharedUser = null,
    ): Seller {
        $sharedUser ??= $this->sharedUser($email, $companyName, $isActive, $isVerified);

        $seller = Seller::query()->firstOrNew(['email' => $email]);
        $seller->forceFill([
            'name' => $companyName,
            'company_name' => $companyName,
            'company_code' => $this->companyCode($email),
            'vat_code' => 'LT'.$this->companyCode($email),
            'address' => $companyName.' Road 1, Kaunas, Lithuania',
            'phone' => '+3706'.substr($this->companyCode($email), 0, 7),
            'veterinary_certificate_number' => 'VET-'.substr($this->companyCode($email), 0, 4),
            'bank_account' => 'LT1210000111010000'.substr($this->companyCode($email), 0, 2),
            'is_verified' => $isVerified,
            'is_active' => $isActive,
            'email_verified_at' => $isVerified ? now() : null,
            'balance' => $isActive ? 250.00 : 0.00,
        ]);

        $this->setProfileUser($seller, $sharedUser);

        if (! $seller->exists) {
            $seller->password = Hash::make(self::PASSWORD);
        }

        $seller->save();
        $seller->categories()->syncWithoutDetaching($this->sellerCategoryIds());

        return $seller;
    }

    private function sharedUser(
        string $email,
        string $name,
        bool $isActive = true,
        bool $isVerified = true,
    ): ?User {
        if (! Schema::hasTable('users')) {
            return null;
        }

        $user = User::query()->firstOrNew(['email' => $email]);
        $user->forceFill([
            'name' => $name,
            'is_active' => $isActive,
            'email_verified_at' => $isVerified ? now() : null,
        ]);

        if (! $user->exists) {
            $user->password = Hash::make(self::PASSWORD);
        }

        $user->save();

        return $user;
    }

    private function setProfileUser(Buyer|Seller $profile, ?User $user): void
    {
        if ($user === null || ! Schema::hasColumn($profile->getTable(), 'user_id')) {
            return;
        }

        $profile->user_id = $user->getKey();
    }

    /**
     * @return array<int, int>
     */
    private function sellerCategoryIds(): array
    {
        /** @var Collection<int, int> $categoryIds */
        $categoryIds = Category::query()
            ->whereNotNull('parent_category_id')
            ->orderBy('id')
            ->limit(4)
            ->pluck('id');

        return $categoryIds->all();
    }

    private function companyCode(string $email): string
    {
        return substr(str_pad((string) abs(crc32($email)), 9, '0', STR_PAD_LEFT), 0, 9);
    }
}
