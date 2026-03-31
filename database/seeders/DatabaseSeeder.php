<?php

namespace Database\Seeders;

use Database\Seeders\test_information\AttributesSeeder;
use Database\Seeders\test_information\CategorySeeder;
use Database\Seeders\test_information\CountriesSeeder;
use Database\Seeders\test_information\GlobalSettingsSeeder;
use Database\Seeders\test_information\ProductAttributeSeeder;
use Database\Seeders\test_information\ProductSeeder;
use Database\Seeders\test_information\TestUsersSeeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    private array $fakeProducts = [
        'en' => [
            ['description' => 'Fresh and crisp apples from sustainable orchards. Perfect for healthy snacking.'],
            ['description' => 'Sweet and ripe bananas. Rich in potassium and great for smoothies.'],
            ['description' => 'Juicy oranges bursting with vitamin C. Ideal for fresh juice.'],
            ['description' => 'Sweet and tender pears. Perfect for fresh eating or baking.'],
            ['description' => 'Ripe mangoes rich in vitamins. Great for desserts and smoothies.'],
            ['description' => 'Sweet seedless grapes. Perfect for snacking and wine making.'],
            ['description' => 'Tree-ripened peaches. Excellent for pies and preserves.'],
            ['description' => 'Fresh plums with sweet-tart flavor. Great for jams.'],
            ['description' => 'Succulent strawberries. Perfect for fresh eating and desserts.'],
            ['description' => 'Tender raspberries. Ideal for jams and baking.'],
            ['description' => 'Fresh blueberries rich in antioxidants. Great for breakfast.'],
            ['description' => 'Sweet cherries. Perfect for pies and snacking.'],
            ['description' => 'Juicy watermelon. Refreshing summer treat.'],
            ['description' => 'Sweet honeydew melon. Great for fruit salads.'],
            ['description' => 'Fresh pineapple. Perfect for grilling and desserts.'],
            ['description' => 'Ripe kiwi fruit. Rich in vitamin C.'],
            ['description' => 'Fresh pomegranate. Full of antioxidants.'],
            ['description' => 'Zesty lemons. Perfect for cooking and drinks.'],
            ['description' => 'Fresh limes. Great for cocktails and marinades.'],
            ['description' => 'Young coconuts. Perfect for hydration.'],

            ['description' => 'Fresh carrots from local farms. Great for salads and cooking.'],
            ['description' => 'Crisp lettuce. Perfect for sandwiches and salads.'],
            ['description' => 'Fresh tomatoes. Ideal for salads and sauces.'],
            ['description' => 'Sweet bell peppers. Great for stuffing and stir-fries.'],
            ['description' => 'Fresh cucumbers. Perfect for salads and pickling.'],
            ['description' => 'Tender broccoli. Rich in nutrients and versatile.'],
            ['description' => 'Fresh cauliflower. Great for roasting and soups.'],
            ['description' => 'Sweet corn. Perfect for grilling and side dishes.'],
            ['description' => 'Fresh spinach. Ideal for salads and smoothies.'],
            ['description' => 'Tender green beans. Great for side dishes.'],
            ['description' => 'Fresh asparagus. Perfect for grilling and roasting.'],
            ['description' => 'Sweet peas. Ideal for soups and side dishes.'],
            ['description' => 'Fresh zucchini. Great for grilling and baking.'],
            ['description' => 'Tender eggplant. Perfect for Mediterranean dishes.'],
            ['description' => 'Fresh Brussels sprouts. Great roasted or sautéed.'],
            ['description' => 'Sweet potatoes. Perfect for baking and fries.'],
            ['description' => 'Fresh beets. Great for salads and juicing.'],
            ['description' => 'Crisp radishes. Perfect for salads and garnishes.'],
            ['description' => 'Fresh kale. Ideal for smoothies and chips.'],
            ['description' => 'Tender artichokes. Great steamed or grilled.'],

            ['description' => 'Fresh button mushrooms. Perfect for salads and cooking.'],
            ['description' => 'Wild porcini mushrooms. Great for pasta and risotto.'],
            ['description' => 'Fresh shiitake mushrooms. Ideal for Asian cuisine.'],
            ['description' => 'Tender oyster mushrooms. Perfect for stir-fries.'],
            ['description' => 'Fresh chanterelles. Great for sauces and soups.'],
            ['description' => 'Wild morel mushrooms. Perfect for gourmet dishes.'],
            ['description' => 'Fresh crimini mushrooms. Ideal for stuffing.'],
            ['description' => 'Tender portobello mushrooms. Great for grilling.'],
            ['description' => 'Fresh enoki mushrooms. Perfect for soups and salads.'],
            ['description' => 'Wild black trumpet mushrooms. Ideal for sauces.'],

            ['description' => 'Fresh whole milk from local farms. Perfect for drinking and cooking.'],
            ['description' => 'Creamy yogurt. Great for breakfast and snacks.'],
            ['description' => 'Fresh cottage cheese. Ideal for healthy snacking.'],
            ['description' => 'Aged cheddar cheese. Perfect for sandwiches.'],
            ['description' => 'Fresh mozzarella. Great for pizzas and salads.'],
            ['description' => 'Creamy butter. Perfect for baking and cooking.'],
            ['description' => 'Fresh cream. Ideal for coffee and desserts.'],
            ['description' => 'Aged parmesan cheese. Perfect for pasta dishes.'],
            ['description' => 'Fresh ricotta cheese. Great for lasagna and desserts.'],
            ['description' => 'Creamy sour cream. Perfect for garnishing.'],

            ['description' => 'Fresh blackberries. Perfect for jams and desserts.'],
            ['description' => 'Wild cranberries. Great for sauces and baking.'],
            ['description' => 'Fresh gooseberries. Ideal for pies and preserves.'],
            ['description' => 'Tender currants. Perfect for jams and jellies.'],
            ['description' => 'Fresh elderberries. Great for syrups and wines.'],
            ['description' => 'Wild huckleberries. Perfect for pies and muffins.'],
            ['description' => 'Fresh boysenberries. Ideal for desserts and jams.'],
            ['description' => 'Tender mulberries. Great for fresh eating.'],
            ['description' => 'Fresh loganberries. Perfect for preserves.'],
            ['description' => 'Wild cloudberries. Ideal for special desserts.'],
        ],
        'lt' => [
            ['description' => 'Švieži ir traškūs obuoliai iš tvarių sodų. Puikiai tinka užkandžiams.'],
            ['description' => 'Saldūs ir prinokę bananai. Turtingi kalio ir puikiai tinka kokteiliams.'],
            ['description' => 'Sultingi apelsinai, kupini vitamino C. Idealūs šviežioms sultims.'],
            ['description' => 'Saldžios ir švelnios kriaušės. Puikiai tinka valgyti šviežias ar kepti.'],
            ['description' => 'Prinokę mangai, turtingi vitaminų. Puikiai tinka desertams ir kokteiliams.'],
            ['description' => 'Saldžios besėklės vynuogės. Puikiai tinka užkandžiams ir vynui gaminti.'],
            ['description' => 'Ant medžio prinokę persikai. Puikiai tinka pyragams ir uogienėms.'],
            ['description' => 'Šviežios slyvos su saldžiai rūgščiu skoniu. Puikiai tinka džemams.'],
            ['description' => 'Sultingos braškės. Puikiai tinka valgyti šviežias ir desertams.'],
            ['description' => 'Švelnios avietės. Idealios džemams ir kepiniams.'],
            ['description' => 'Šviežios mėlynės, turtingos antioksidantų. Puikiai tinka pusryčiams.'],
            ['description' => 'Saldžios vyšnios. Puikiai tinka pyragams ir užkandžiams.'],
            ['description' => 'Sultingas arbūzas. Gaivus vasaros desertas.'],
            ['description' => 'Saldus melionas. Puikiai tinka vaisių salotoms.'],
            ['description' => 'Šviežias ananasas. Puikiai tinka kepti ir desertams.'],
            ['description' => 'Prinokęs kivis. Turtingas vitamino C.'],
            ['description' => 'Šviežias granatas. Pilnas antioksidantų.'],
            ['description' => 'Citrinos su žievelėmis. Puikiai tinka maistui ir gėrimams.'],
            ['description' => 'Švieži laimai. Puikiai tinka kokteiliams ir marinatams.'],
            ['description' => 'Jauni kokosai. Puikiai tinka hidratacijai.'],

            ['description' => 'Šviežios morkos iš vietinių ūkių. Puikiai tinka salotoms ir maistui.'],
            ['description' => 'Traški salota. Puikiai tinka sumuštiniams ir salotoms.'],
            ['description' => 'Švieži pomidorai. Idealūs salotoms ir padažams.'],
            ['description' => 'Saldžios paprikos. Puikiai tinka įdarymui ir kepimui.'],
            ['description' => 'Švieži agurkai. Puikiai tinka salotoms ir marinuoti.'],
            ['description' => 'Švelnus brokolius. Turtingas maistinėmis medžiagomis ir universalus.'],
            ['description' => 'Šviežias žiedininis kopūstas. Puikiai tinka kepti ir sriuboms.'],
            ['description' => 'Saldūs kukurūzai. Puikiai tinka kepti ir garnyrams.'],
            ['description' => 'Šviežias špinatas. Idealus salotoms ir kokteiliams.'],
            ['description' => 'Švelnios žaliosios pupelės. Puikiai tinka garnyrams.'],

            ['description' => 'Švieži pievagrybiai. Puikiai tinka salotoms ir maistui.'],
            ['description' => 'Laukiniai baravykai. Puikiai tinka makaronams ir rizotui.'],
            ['description' => 'Švieži šitakė grybai. Idealūs azijietiškiems patiekalams.'],
            ['description' => 'Švelnūs gluosniniai grybai. Puikiai tinka kepimui.'],
            ['description' => 'Švieži voveraitės. Puikiai tinka padažams ir sriuboms.'],
            ['description' => 'Laukiniai briedžiukai. Puikiai tinka gurmanų patiekalams.'],
            ['description' => 'Švieži rudieji pievagrybiai. Idealūs įdarymui.'],
            ['description' => 'Švelnūs portobello grybai. Puikiai tinka kepti.'],
            ['description' => 'Švieži enoki grybai. Puikiai tinka sriuboms ir salotoms.'],
            ['description' => 'Laukiniai juodieji trimitai. Idealūs padažams.'],

            ['description' => 'Šviežias pilno riebumo pienas iš vietinių ūkių. Puikiai tinka gerti ir gaminti.'],
            ['description' => 'Kreminis jogurtas. Puikiai tinka pusryčiams ir užkandžiams.'],
            ['description' => 'Šviežias varškės sūris. Idealus sveikiems užkandžiams.'],
            ['description' => 'Brandintas čederio sūris. Puikiai tinka sumuštiniams.'],
            ['description' => 'Šviežias mocarelos sūris. Puikiai tinka picoms ir salotoms.'],
            ['description' => 'Kreminis sviestas. Puikiai tinka kepimui ir maistui.'],
            ['description' => 'Šviežia grietinėlė. Ideali kavai ir desertams.'],
            ['description' => 'Brandintas parmezano sūris. Puikiai tinka makaronų patiekalams.'],
            ['description' => 'Šviežias rikotos sūris. Puikiai tinka lazanijai ir desertams.'],
            ['description' => 'Kreminis grietinė. Puikiai tinka garnyrui.'],

            // Additional categories continue with similar detailed descriptions...
        ],
    ];

    public function run(): void
    {
        DB::disableQueryLog();
        $isSqlite = DB::connection()->getDriverName() === 'sqlite';

        if ($isSqlite) {
            DB::statement('PRAGMA journal_mode = MEMORY');
            DB::statement('PRAGMA synchronous = OFF');
            DB::statement('PRAGMA temp_store = MEMORY');
            DB::statement('PRAGMA cache_size = -200000');
        }

        Model::unguarded(function (): void {
            $this->call([
                CountriesSeeder::class,
                CategorySeeder::class,
                GlobalSettingsSeeder::class,
                TestUsersSeeder::class,
                ProductSeeder::class,
                AttributesSeeder::class,
                ProductAttributeSeeder::class,
                AdminSeeder::class,
            ]);
        });
    }
}
