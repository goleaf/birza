<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Category;
use App\Models\Country;
use App\Models\GlobalSettings;
use App\Models\Product;
use App\Models\Users\Admin;
use App\Models\Users\Buyer;
use App\Models\Users\Seller;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Symfony\Component\Console\Helper\ProgressBar;

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
            ['description' => 'Wild cloudberries. Ideal for special desserts.']
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
            ['description' => 'Kreminis grietinė. Puikiai tinka garnyrui.']

            // Additional categories continue with similar detailed descriptions...
        ]
    ];
    public function run(): void
    {
        $this->resetTables();

        $this->seedAdmin();
        $this->seedGlobalSettings();
        $this->seedCountries();
        $this->seedCategories();
        $this->seedTestUsers();
        $this->seedProducts();
        $this->seedAttributes();
    }

    private function resetTables(): void
    {
        $tables = [
            'product_attribute_value',
            'attribute_product',
            'product_attribute',
            'category_attribute',
            'seller_categories',
            'attribute_values',
            'attributes',
            'products',
            'categories',
            'countries',
            'users_sellers',
            'users_buyers',
            'users_admins',
            'global_settings',
            'orders',
            'order_items',
            'carts',
            'activities',
            'buyer_credit_history',
            'credit_attachments',
            'seller_transactions',
        ];

        Schema::disableForeignKeyConstraints();

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->truncate();
            }
        }

        Schema::enableForeignKeyConstraints();
    }

    private function seedAdmin(): void
    {
        Admin::create([
            'name' => 'Admin',
            'email' => 'admin@admin.com',
            'password' => Hash::make('password'),
        ]);
    }

    private function seedGlobalSettings(): void
    {
        GlobalSettings::create([
            'portal_additional_price' => rand(1, 10),
        ]);
    }

    private function seedCountries(): void
    {
        $countriesJsonPath = base_path('database/seeders/data/countries.json');
        $countriesData = json_decode(File::get($countriesJsonPath), true);

        $countries = $countriesData['countries'] ?? [];
        $translations = $countriesData['translations'] ?? [];

        $translationMap = $this->buildTranslationMap($translations);

        $this->withProgressBar(count($countries), 'Countries', function (?ProgressBar $bar) use ($countries, $translationMap) {
            foreach ($countries as $country) {
                $alpha2 = Str::lower($country['alpha2'] ?? '');
                if ($alpha2 == '') {
                    if ($bar) {
                        $bar->advance();
                    }
                    continue;
                }

                if (empty($country['region'])) {
                    if ($bar) {
                        $bar->advance();
                    }
                    continue;
                }

                $translatedNames = [
                    'lt' => $translationMap['lt'][$alpha2] ?? Str::upper($alpha2),
                    'en' => $translationMap['en'][$alpha2] ?? Str::upper($alpha2),
                ];

                Country::create([
                    'alpha2' => $alpha2,
                    'region' => $country['region'],
                    'country_name' => $translatedNames,
                ]);

                if ($bar) {
                    $bar->advance();
                }
            }
        });
    }

    private function buildTranslationMap(array $translations): array
    {
        $map = ['en' => [], 'lt' => []];

        foreach ($translations as $locale => $entries) {
            foreach ($entries as $entry) {
                $alpha2 = Str::lower($entry['alpha2'] ?? '');
                if ($alpha2 == '') {
                    continue;
                }

                $map[$locale][$alpha2] = $entry['country_name'] ?? null;
            }
        }

        return $map;
    }

    private function seedCategories(): void
    {
        $categories = [
                    [
                        "main_category" => [
                            "lt" => ["name" => "Šviežia mėsa"],
                            "en" => ["name" => "Fresh meat"]
                        ],
                        "subcategories" => [
                            ["lt" => ["name" => "Kiauliena"], "en" => ["name" => "Pork"]],
                            ["lt" => ["name" => "Jautiena"], "en" => ["name" => "Beef"]],
                            ["lt" => ["name" => "Kalakutiena"], "en" => ["name" => "Turkey"]],
                            ["lt" => ["name" => "Vištiena"], "en" => ["name" => "Chicken"]],
                            ["lt" => ["name" => "Kiti produktai"], "en" => ["name" => "Other products"]]
                        ]
                    ],
                    [
                        "main_category" => [
                            "lt" => ["name" => "Malta mėsa"],
                            "en" => ["name" => "Miced meat"]
                        ],
                        "subcategories" => [
                            ["lt" => ["name" => "Kiauliena"], "en" => ["name" => "Pork"]],
                            ["lt" => ["name" => "Jautiena"], "en" => ["name" => "Beef"]],
                            ["lt" => ["name" => "Kalakutiena"], "en" => ["name" => "Turkey"]],
                            ["lt" => ["name" => "Vištiena"], "en" => ["name" => "Chicken"]],
                            ["lt" => ["name" => "Kiti produktai"], "en" => ["name" => "Other products"]]
                        ]
                    ],
                    [
                        "main_category" => [
                            "lt" => ["name" => "Marinuoti mėsa ir paukštiena"],
                            "en" => ["name" => "Marinated meat and poultry"]
                        ],
                        "subcategories" => [
                            ["lt" => ["name" => "Marinuota mėsa"], "en" => ["name" => "Marinated meat"]],
                            ["lt" => ["name" => "Marinuota vištiena"], "en" => ["name" => "Marinated poultry"]],
                            ["lt" => ["name" => "Kiti produktai"], "en" => ["name" => "Other products"]]
                        ]
                    ],
                    [
                        "main_category" => [
                            "lt" => ["name" => "Šviežios dešrelės"],
                            "en" => ["name" => "Fresh sausages"]
                        ],
                        "subcategories" => [
                            ["lt" => ["name" => "Šviežios dešrelės"], "en" => ["name" => "Fresh sausages"]],
                            ["lt" => ["name" => "Kiti produktai"], "en" => ["name" => "Other products"]]
                        ]
                    ],
                    [
                        "main_category" => [
                            "lt" => ["name" => "Mėsos ir paukštienos gaminiai"],
                            "en" => ["name" => "Meat and poultry products"]
                        ],
                        "subcategories" => [
                            ["lt" => ["name" => "Virtos dešros ir dešrelės"], "en" => ["name" => "Cooked sausages"]],
                            ["lt" => ["name" => "Paštetas"], "en" => ["name" => "Pates"]],
                            ["lt" => ["name" => "Parūkytos dešros ir dešrelės"], "en" => ["name" => "Smoked sausages"]],
                            ["lt" => ["name" => "Vytintos dešros ir dešrelės"], "en" => ["name" => "Dried sausages"]],
                            ["lt" => ["name" => "Karštai rūkyti gaminiai"], "en" => ["name" => "Smoked products"]],
                            ["lt" => ["name" => "Vytinti gaminiai"], "en" => ["name" => "Cured products"]],
                            ["lt" => ["name" => "Mėsos ir paukštienos konservai"], "en" => ["name" => "Meat and poultry canned food"]],
                            ["lt" => ["name" => "Dešrelės kepimui"], "en" => ["name" => "Grill sausages"]],
                            ["lt" => ["name" => "Kiti produktai"], "en" => ["name" => "Other products"]]
                        ]
                    ],
                    [
                        "main_category" => [
                            "lt" => ["name" => "Kulinarija"],
                            "en" => ["name" => "Cooking"]
                        ],
                        "subcategories" => [
                            ["lt" => ["name" => "Užkandžiai"], "en" => ["name" => "Snacks"]],
                            ["lt" => ["name" => "Sumuštiniai"], "en" => ["name" => "Sandwiches"]],
                            ["lt" => ["name" => "Kiti produktai"], "en" => ["name" => "Other products"]]
                        ]
                    ],
                    [
                        "main_category" => [
                            "lt" => ["name" => "Žuvis, žuvų gaminiai"],
                            "en" => ["name" => "Fish products"]
                        ],
                        "subcategories" => [
                            ["lt" => ["name" => "Silkė"], "en" => ["name" => "Herring"]],
                            ["lt" => ["name" => "Lašiša"], "en" => ["name" => "Salmon"]],
                            ["lt" => ["name" => "Ikrai"], "en" => ["name" => "Caviar"]],
                            ["lt" => ["name" => "Užtepelės ir paštetai"], "en" => ["name" => "Smears and pates"]],
                            ["lt" => ["name" => "Kiti produktai"], "en" => ["name" => "Other products"]]
                        ]
                    ],
                    [
                        "main_category" => [
                            "lt" => ["name" => "Daržovės ir vaisiai"],
                            "en" => ["name" => "Vegetables and fruits"]
                        ],
                        "subcategories" => [
                            ["lt" => ["name" => "Citrusiniai vaisiai"], "en" => ["name" => "Citrus fruits"]],
                            ["lt" => ["name" => "Vynuogės ir uogos"], "en" => ["name" => "Grapes and berries"]],
                            ["lt" => ["name" => "Obuoliai ir kriaušės"], "en" => ["name" => "Apples and pears"]],
                            ["lt" => ["name" => "Arbūzai"], "en" => ["name" => "Watermelons"]],
                            ["lt" => ["name" => "Slyvos"], "en" => ["name" => "Plums"]],
                            ["lt" => ["name" => "Pomidorai ir agurkai"], "en" => ["name" => "Tomatoes and cucumbers"]],
                            ["lt" => ["name" => "Svogūnai ir česnakai"], "en" => ["name" => "Onions and garlic"]],
                            ["lt" => ["name" => "Burokėliai"], "en" => ["name" => "Beets"]],
                            ["lt" => ["name" => "Salotos"], "en" => ["name" => "Lettuce"]],
                            ["lt" => ["name" => "Moliūgai"], "en" => ["name" => "Pumpkins"]],
                            ["lt" => ["name" => "Bulvės ir morkos"], "en" => ["name" => "Potatoes and carrots"]],
                            ["lt" => ["name" => "Kopūstai"], "en" => ["name" => "Cabbage"]],
                            ["lt" => ["name" => "Marinuotos, raugintos ir sūdytos daržovės"], "en" => ["name" => "Marinated, fermented, and salted vegetables"]],
                            ["lt" => ["name" => "Kiti produktai"], "en" => ["name" => "Other products"]]
                        ]
                    ],
                    [
                        "main_category" => [
                            "lt" => ["name" => "Pieno gaminiai"],
                            "en" => ["name" => "Milk products"]
                        ],
                        "subcategories" => [
                            ["lt" => ["name" => "Ilgo galiojimo pienas"], "en" => ["name" => "Long shelf milk"]],
                            ["lt" => ["name" => "Pienas"], "en" => ["name" => "Milk"]],
                            ["lt" => ["name" => "Varškės sūris"], "en" => ["name" => "Cottage cheese"]],
                            ["lt" => ["name" => "Fermentinis sūris"], "en" => ["name" => "Fermented cheese"]],
                            ["lt" => ["name" => "Tepamieji sūriai"], "en" => ["name" => "Spreadable cheese"]],
                            ["lt" => ["name" => "Kiti pieno produktai"], "en" => ["name" => "Other milk products"]]
                        ]
                    ],
                    [
                        "main_category" => [
                            "lt" => ["name" => "Majonezas"],
                            "en" => ["name" => "Mayonnaise"]
                        ],
                        "subcategories" => [
                            ["lt" => ["name" => "Majonezas"], "en" => ["name" => "Mayonnaise"]],
                            ["lt" => ["name" => "Kiti produktai"], "en" => ["name" => "Other products"]]
                        ]
                    ],
                    [
                        "main_category" => [
                            "lt" => ["name" => "Kiaušiniai"],
                            "en" => ["name" => "Eggs"]
                        ],
                        "subcategories" => [
                            ["lt" => ["name" => "Kiaušiniai"], "en" => ["name" => "Eggs"]],
                            ["lt" => ["name" => "Kiti produktai"], "en" => ["name" => "Other products"]]
                        ]
                    ],
                    [
                        "main_category" => [
                            "lt" => ["name" => "Duonos gaminiai ir konditerija"],
                            "en" => ["name" => "Bread products and confectionery"]
                        ],
                        "subcategories" => [
                            ["lt" => ["name" => "Šviesi duona"], "en" => ["name" => "Light bread"]],
                            ["lt" => ["name" => "Tamsi duona"], "en" => ["name" => "Dark bread"]],
                            ["lt" => ["name" => "Batonas"], "en" => ["name" => "White bread"]],
                            ["lt" => ["name" => "Sumuštinių duona"], "en" => ["name" => "Sandwich bread"]],
                            ["lt" => ["name" => "Kiti duonos gaminiai"], "en" => ["name" => "Other bread products"]],
                            ["lt" => ["name" => "Šakotis"], "en" => ["name" => "Sakotis"]],
                            ["lt" => ["name" => "Skrusdėlynas"], "en" => ["name" => "Baumkuchen"]],
                            ["lt" => ["name" => "Pyragaičiai ir desertai"], "en" => ["name" => "Cakes and desserts"]],
                            ["lt" => ["name" => "Nesaldžios bandelės"], "en" => ["name" => "Not sweet buns"]],
                            ["lt" => ["name" => "Saldžios bandelės"], "en" => ["name" => "Sweet buns"]],
                            ["lt" => ["name" => "Spurgos"], "en" => ["name" => "Donuts"]],
                            ["lt" => ["name" => "Kiti produktai"], "en" => ["name" => "Other products"]]
                        ]
                    ],
                    [
                        "main_category" => [
                            "lt" => ["name" => "Maistas sportui"],
                            "en" => ["name" => "Food for sports"]
                        ],
                        "subcategories" => [
                            ["lt" => ["name" => "Vitaminai"], "en" => ["name" => "Vitamins"]],
                            ["lt" => ["name" => "Batonėliai"], "en" => ["name" => "Bars"]],
                            ["lt" => ["name" => "Kiti produktai"], "en" => ["name" => "Other products"]]
                        ]
                    ],
                    [
                        "main_category" => [
                            "lt" => ["name" => "Medus"],
                            "en" => ["name" => "Honey"]
                        ],
                        "subcategories" => [
                            ["lt" => ["name" => "Medus"], "en" => ["name" => "Honey"]],
                            ["lt" => ["name" => "Kiti produktai"], "en" => ["name" => "Other products"]]
                        ]
                    ],
                    [
                        "main_category" => [
                            "lt" => ["name" => "Bakalėja"],
                            "en" => ["name" => "Groceries"]
                        ],
                        "subcategories" => [
                            ["lt" => ["name" => "Malta kava"], "en" => ["name" => "Coffee"]],
                            ["lt" => ["name" => "Kavos pupelės"], "en" => ["name" => "Coffee beans"]],
                            ["lt" => ["name" => "Kavos kapsulės"], "en" => ["name" => "Coffee capsules"]],
                            ["lt" => ["name" => "Tirpi kava"], "en" => ["name" => "Instant coffee"]],
                            ["lt" => ["name" => "Kakava"], "en" => ["name" => "Cocoa"]],
                            ["lt" => ["name" => "Žalioji arbata"], "en" => ["name" => "Green tea"]],
                            ["lt" => ["name" => "Baltoji arbata"], "en" => ["name" => "White tea"]],
                            ["lt" => ["name" => "Vaisinė arbata"], "en" => ["name" => "Fruit tea"]],
                            ["lt" => ["name" => "Juoda arbata"], "en" => ["name" => "Black tea"]],
                            ["lt" => ["name" => "Šokoladas"], "en" => ["name" => "Chocolate"]],
                            ["lt" => ["name" => "Sausainiai"], "en" => ["name" => "Cookies"]],
                            ["lt" => ["name" => "Zefirai"], "en" => ["name" => "Marshmallows"]],
                            ["lt" => ["name" => "Vafliai"], "en" => ["name" => "Waffles"]],
                            ["lt" => ["name" => "Saldainiai"], "en" => ["name" => "Sweets"]],
                            ["lt" => ["name" => "Kiti produktai"], "en" => ["name" => "Other products"]]
                        ]
                    ],
                    [
                        "main_category" => [
                            "lt" => ["name" => "Konservuoti gaminiai"],
                            "en" => ["name" => "Pickled, canned food"]
                        ],
                        "subcategories" => [
                            ["lt" => ["name" => "Konservuoti agurkai"], "en" => ["name" => "Canned cucumbers"]],
                            ["lt" => ["name" => "Konservuoti pomidorai"], "en" => ["name" => "Canned tomatoes"]],
                            ["lt" => ["name" => "Konservuoti žirneliai"], "en" => ["name" => "Canned peas"]],
                            ["lt" => ["name" => "Konservuotos sriubos"], "en" => ["name" => "Canned soups"]],
                            ["lt" => ["name" => "Uogienė ir džemai"], "en" => ["name" => "Jams and jams"]],
                            ["lt" => ["name" => "Konservuoti vaisiai ir uogos"], "en" => ["name" => "Canned fruits and berries"]],
                            ["lt" => ["name" => "Konservuoti burokėliai"], "en" => ["name" => "Canned beets"]],
                            ["lt" => ["name" => "Kiti produktai"], "en" => ["name" => "Other products"]]
                        ]
                    ],
                    [
                        "main_category" => [
                            "lt" => ["name" => "Padažai"],
                            "en" => ["name" => "Sauces"]
                        ],
                        "subcategories" => [
                            ["lt" => ["name" => "Kečiupas"], "en" => ["name" => "Ketchup"]],
                            ["lt" => ["name" => "Pomidorų padažas"], "en" => ["name" => "Tomato sauces"]],
                            ["lt" => ["name" => "Krienai"], "en" => ["name" => "Horseradish"]],
                            ["lt" => ["name" => "Saldžiarūgštys padažas"], "en" => ["name" => "Sweet and sour sauce"]],
                            ["lt" => ["name" => "Garstyčios"], "en" => ["name" => "Mustard"]],
                            ["lt" => ["name" => "Kiti padažai"], "en" => ["name" => "Other sauces"]]
                        ]
                    ],
                    [
                        "main_category" => [
                            "lt" => ["name" => "Prieskoniai"],
                            "en" => ["name" => "Spices"]
                        ],
                        "subcategories" => [
                            ["lt" => ["name" => "Druska"], "en" => ["name" => "Salt"]],
                            ["lt" => ["name" => "Pipirai"], "en" => ["name" => "Pepper"]],
                            ["lt" => ["name" => "Universlūs prieskoniai"], "en" => ["name" => "Universal spices"]],
                            ["lt" => ["name" => "Liofilizuoti prieskoniai"], "en" => ["name" => "Freeze-dried spices"]],
                            ["lt" => ["name" => "Grynieji prieskoniai ir žolelės"], "en" => ["name" => "Pure herbs and spices"]],
                            ["lt" => ["name" => "Prieskonių, žolelių mišiniai"], "en" => ["name" => "Spice and herb mixes"]],
                            ["lt" => ["name" => "Bulvių prieskoniai"], "en" => ["name" => "Potato spices"]],
                            ["lt" => ["name" => "Kiti produktai"], "en" => ["name" => "Other products"]]
                        ]
                    ],
                    [
                        "main_category" => [
                            "lt" => ["name" => "Gėrimai"],
                            "en" => ["name" => "Drinks"]
                        ],
                        "subcategories" => [
                            ["lt" => ["name" => "Gazuotas vanduo"], "en" => ["name" => "Carbonated water"]],
                            ["lt" => ["name" => "Negazuotas vanduo"], "en" => ["name" => "Still water"]],
                            ["lt" => ["name" => "Sultys"], "en" => ["name" => "Juice"]],
                            ["lt" => ["name" => "Gazuoti vaisvandeniai"], "en" => ["name" => "Fizzy soft drinks"]],
                            ["lt" => ["name" => "Negazuoti vaisvandeniai"], "en" => ["name" => "Still flavored drinks"]],
                            ["lt" => ["name" => "Gira"], "en" => ["name" => "Kvass"]],
                            ["lt" => ["name" => "Sporto gerimai"], "en" => ["name" => "Sports drinks"]],
                            ["lt" => ["name" => "Imuniteto gėrimai"], "en" => ["name" => "Immunity drinks"]],
                            ["lt" => ["name" => "Sirupai"], "en" => ["name" => "Syrups"]],
                            ["lt" => ["name" => "Kiti gėrimo produktai"], "en" => ["name" => "Other drink products"]]
                        ]
                    ],

                    [
                        "main_category" => [
                            "lt" => ["name" => "Alkoholiniai gėrimai"],
                            "en" => ["name" => "Alcoholic drinks"]
                        ],
                        "subcategories" => [
                            ["lt" => ["name" => "Šviesus alus"], "en" => ["name" => "Light beer"]],
                            ["lt" => ["name" => "Tamsus alus"], "en" => ["name" => "Dark beer"]],
                            ["lt" => ["name" => "Mažujų daryklų alus"], "en" => ["name" => "Craft beer"]],
                            ["lt" => ["name" => "Alaus kokteiliai"], "en" => ["name" => "Beer cocktails"]],
                            ["lt" => ["name" => "Raudonas vynas"], "en" => ["name" => "Red wine"]],
                            ["lt" => ["name" => "Baltas vynas"], "en" => ["name" => "White wine"]],
                            ["lt" => ["name" => "Rausvasis vynas"], "en" => ["name" => "Rose wine"]],
                            ["lt" => ["name" => "Putojantis vynas"], "en" => ["name" => "Sparkling wine"]],
                            ["lt" => ["name" => "Sidras"], "en" => ["name" => "Cider"]],
                            ["lt" => ["name" => "Kiti produktai"], "en" => ["name" => "Other products"]]
                        ]
                    ],
                    [
                        "main_category" => [
                            "lt" => ["name" => "Stiprieji alkoholiniai gėrimai"],
                            "en" => ["name" => "Spirits"]
                        ],
                        "subcategories" => [
                            ["lt" => ["name" => "Degtinė"], "en" => ["name" => "Vodka"]],
                            ["lt" => ["name" => "Brendis"], "en" => ["name" => "Brandy"]],
                            ["lt" => ["name" => "Trauktinė"], "en" => ["name" => "Bitter"]],
                            ["lt" => ["name" => "Likeris"], "en" => ["name" => "Liqueur"]],
                            ["lt" => ["name" => "Kiti produktai"], "en" => ["name" => "Other products"]]
                        ]
                    ],
                    [
                        "main_category" => [
                            "lt" => ["name" => "Gyvūnų prekės"],
                            "en" => ["name" => "Pets"]
                        ],
                        "subcategories" => [
                            ["lt" => ["name" => "Konservuotas šunų ėdalas"], "en" => ["name" => "Dry dog food"]],
                            ["lt" => ["name" => "Sausas šunų ėdalas"], "en" => ["name" => "Canned dog food"]],
                            ["lt" => ["name" => "Šunų skanėstai"], "en" => ["name" => "Dog snacks"]],
                            ["lt" => ["name" => "Sausas kačių ėdalas"], "en" => ["name" => "Canned cat food"]],
                            ["lt" => ["name" => "Konservuotas kačių ėdalas"], "en" => ["name" => "Dry cat food"]],
                            ["lt" => ["name" => "Kačių skanėstai"], "en" => ["name" => "Cat snacks"]],
                            ["lt" => ["name" => "Gyvūnų aksesuarai"], "en" => ["name" => "Pet accessories"]],
                            ["lt" => ["name" => "Gyvūnų higienos reikmenys"], "en" => ["name" => "Pet hygiene"]],
                            ["lt" => ["name" => "Kiti produktai"], "en" => ["name" => "Other products"]]
                        ]
                    ],
                    [
                        "main_category" => [
                            "lt" => ["name" => "Buitinės chemijos prekės"],
                            "en" => ["name" => "Household chemicals"]
                        ],
                        "subcategories" => [
                            ["lt" => ["name" => "Namų kvapai"], "en" => ["name" => "Home fragrance"]],
                            ["lt" => ["name" => "Virtuvės ir vonios kambario valykliai"], "en" => ["name" => "Kitchen and bathroom cleaners"]],
                            ["lt" => ["name" => "Langų valykliai"], "en" => ["name" => "Window cleaners"]],
                            ["lt" => ["name" => "Universalūs valykliai"], "en" => ["name" => "All-purpose cleaners"]],
                            ["lt" => ["name" => "Skalbamieji milteliai"], "en" => ["name" => "Washing powder"]],
                            ["lt" => ["name" => "Skalbinių minkštikliai"], "en" => ["name" => "Fabric softeners"]],
                            ["lt" => ["name" => "Skystos skalbimo priemonės"], "en" => ["name" => "Liquid laundry detergents"]],
                            ["lt" => ["name" => "Dėmių valykliai ir balinimo priemonės"], "en" => ["name" => "Stain removers and whiteners"]]
                        ]
                    ],
                    [
                        "main_category" => [
                            "lt" => ["name" => "Probiotikai"],
                            "en" => ["name" => "Probiotics"]
                        ],
                        "subcategories" => [
                            ["lt" => ["name" => "Probiotikai"], "en" => ["name" => "Probiotics"]],
                            ["lt" => ["name" => "Žarnyno sveikata"], "en" => ["name" => "Gut health"]],
                            ["lt" => ["name" => "Grožis ir odos sveikata"], "en" => ["name" => "Beauty and skin health"]],
                            ["lt" => ["name" => "Sveikatingumas"], "en" => ["name" => "Health"]],
                            ["lt" => ["name" => "Streso ir nuotaikos pusiausvyra"], "en" => ["name" => "Stress and mood balance"]]
                        ]
                    ],
                    [
                        "main_category" => [
                            "lt" => ["name" => "Sėklos"],
                            "en" => ["name" => "Seeds"]
                        ],
                        "subcategories" => [
                            ["lt" => ["name" => "Agurkai"], "en" => ["name" => "Cucumber"]],
                            ["lt" => ["name" => "Pomidorai"], "en" => ["name" => "Tomatoes"]],
                            ["lt" => ["name" => "Krapai"], "en" => ["name" => "Dill"]],
                            ["lt" => ["name" => "Žirniai"], "en" => ["name" => "Peas"]],
                            ["lt" => ["name" => "Morkos"], "en" => ["name" => "Carrots"]],
                            ["lt" => ["name" => "Salotos"], "en" => ["name" => "Lettuce"]],
                            ["lt" => ["name" => "Svogūnai"], "en" => ["name" => "Onions"]],
                            ["lt" => ["name" => "Žemuogių, braškių sėklos"], "en" => ["name" => "Berry seeds"]],
                            ["lt" => ["name" => "Burokėliai"], "en" => ["name" => "Beets"]],
                            ["lt" => ["name" => "Kopūstai"], "en" => ["name" => "Cabbage"]],
                            ["lt" => ["name" => "Gėlių sėklos"], "en" => ["name" => "Flower seeds"]],
                            ["lt" => ["name" => "Smidrai"], "en" => ["name" => "Spinach"]],
                            ["lt" => ["name" => "Moliūgai"], "en" => ["name" => "Pumpkin"]],
                            ["lt" => ["name" => "Arbuzas"], "en" => ["name" => "Watermelon"]]
                        ]
                    ],
                    [
                        "main_category" => [
                            "lt" => ["name" => "Gėlės"],
                            "en" => ["name" => "Flowers"]
                        ],
                        "subcategories" => [
                            ["lt" => ["name" => "Tulpės"], "en" => ["name" => "Tulips"]],
                            ["lt" => ["name" => "Rožės"], "en" => ["name" => "Roses"]],
                            ["lt" => ["name" => "Bijūnai"], "en" => ["name" => "Peonies"]],
                            ["lt" => ["name" => "Kardeliai"], "en" => ["name" => "Gladioli"]]
                        ]
                    ]
                ];
        $totalCategories = 0;
        foreach ($categories as $categoryData) {
            $totalCategories += 1;
            $totalCategories += count($categoryData['subcategories']);
        }

        $this->withProgressBar($totalCategories, 'Categories', function (?ProgressBar $bar) use ($categories) {
            DB::transaction(function () use ($categories, $bar) {
                foreach ($categories as $categoryData) {
                    $category = new Category();
                    $category->setTranslations('category_name', [
                        'lt' => $categoryData['main_category']['lt']['name'],
                        'en' => $categoryData['main_category']['en']['name'],
                    ]);
                    $category->parent_category_id = null;
                    $category->save();

                    if ($bar) {
                        $bar->advance();
                    }

                    foreach ($categoryData['subcategories'] as $subcategoryData) {
                        $subcategory = new Category();
                        $subcategory->setTranslations('category_name', [
                            'lt' => $subcategoryData['lt']['name'],
                            'en' => $subcategoryData['en']['name'],
                        ]);
                        $subcategory->parent_category_id = $category->id;
                        $subcategory->save();

                        if ($bar) {
                            $bar->advance();
                        }
                    }
                }
            });
        });
    }

    private function seedTestUsers(): void
    {
        $faker = Faker::create();

        $lithuanianStreets = [
                    'Gedimino pr.',
                    'Pilies g.',
                    'Vilniaus g.',
                    'Kauno g.',
                    'Laisvės al.',
                    'Vytauto pr.',
                    'Mindaugo g.',
                    'Basanavičiaus g.',
                    'Savanorių pr.',
                    'Žirmūnų g.'
                ];

        $lithuanianCities = [
                    'Vilnius',
                    'Kaunas',
                    'Klaipėda',
                    'Šiauliai',
                    'Panevėžys'
                ];

        $businessCenters = [
                    'Verslo centras "Europa"',
                    'Verslo centras "Green Hall"',
                    'Verslo centras "3 Burės"',
                    'Verslo centras "Nova"',
                    'Verslo centras "Quadrum"'
                ];

        $users = [
                    Buyer::class => [
                        ['Test buyer 1', 'buyer1'],
                        ['Test buyer 2', 'buyer2'],
                        ['Test buyer 3', 'buyer3'],
                        ['Test buyer 4', 'buyer4'],
                        ['Test buyer 5', 'buyer5'],
                        ['Test buyer 6', 'buyer6'],
                        ['Test buyer 7', 'buyer7'],
                        ['Test buyer 8', 'buyer8'],
                        ['Test buyer 9', 'buyer9'],
                        ['Test buyer 10', 'buyer10']
                    ],
                    Seller::class => [
                        ['Test seller 1', 'seller1'],
                        ['Test seller 2', 'seller2'],
                        ['Test seller 3', 'seller3'],
                        ['Test seller 4', 'seller4'],
                        ['Test seller 5', 'seller5'],
                        ['Test seller 6', 'seller6'],
                        ['Test seller 7', 'seller7'],
                        ['Test seller 8', 'seller8'],
                        ['Test seller 9', 'seller9'],
                        ['Test seller 10', 'seller10']
                    ]
                ];

        $totalUsers = 0;
        foreach ($users as $userList) {
            $totalUsers += count($userList);
        }

        $this->withProgressBar($totalUsers, 'Users', function (?ProgressBar $bar) use ($users, $faker, $lithuanianStreets, $lithuanianCities, $businessCenters) {
            foreach ($users as $model => $userList) {
                foreach ($userList as [$name, $type]) {
                    $address = $faker->randomElement($businessCenters) . ', ' .
                        $faker->randomElement($lithuanianStreets) . ' ' .
                        $faker->buildingNumber . ', ' .
                        ($faker->boolean(70) ? $faker->numerify('LT-#####') . ', ' : '') .
                        $faker->randomElement($lithuanianCities) . ', Lithuania';

                    $userData = [
                        'name' => $name,
                        'email' => $type . '@birza.lt',
                        'password' => Hash::make('password123'),
                        'vat_code' => 'LT' . $faker->numerify('#########'),
                        'address' => $address,
                        'phone' => '+370' . $faker->numerify('########'),
                        'is_verified' => true,
                        'company_code' => 'LT' . $faker->numerify('#########'),
                        'company_name' => ucfirst($type) . ' test company',
                    ];

                    $user = $model::create($userData);

                    if ($model === Seller::class) {
                        $subcategories = Category::whereNotNull('parent_category_id')
                            ->inRandomOrder()
                            ->take(4)
                            ->pluck('id');

                        $user->categories()->attach($subcategories);
                    }

                    if ($bar) {
                        $bar->advance();
                    }
                }
            }
        });
    }

    private function seedProducts(): void
    {
        $faker = Faker::create();
        $units = Product::UNITS;

        $sellers = Seller::all();
        $categories = Category::all();
        $countries = Country::where('region', 'Europe')->get();

        if ($sellers->isEmpty() || $categories->isEmpty() || $countries->isEmpty()) {
            throw new \RuntimeException('Sellers, Categories, and Countries must exist before seeding products.');
        }

        $productDirectory = 'public/products';
        if (Storage::exists($productDirectory)) {
            Storage::deleteDirectory($productDirectory);
        }
        Storage::makeDirectory($productDirectory);

        $subcategoriesCount = Category::whereNotNull('parent_category_id')->count();
        $totalProducts = $subcategoriesCount * 10;

        $this->withProgressBar($totalProducts, 'Products', function (?ProgressBar $bar) use ($categories, $sellers, $countries, $productDirectory, $faker, $units) {
            foreach ($categories as $category) {
                foreach ($category->subcategories as $subcategory) {
                    $this->createProductsForCategory($subcategory, $sellers, $countries, $productDirectory, $faker, $units, $bar);
                }
            }
        });
    }

    private function createProductsForCategory(Category $category, $sellers, $countries, string $productDirectory, $faker, array $units, ?ProgressBar $bar): int
    {
        $productsCreated = 0;

        for ($i = 0; $i < 10; $i++) {
            $isOrganic = $faker->boolean(30);
            $filename = $this->generateImage($productDirectory, $faker);
            $stock = $faker->numberBetween(10, 100);
            $seller = $sellers->random();
            $country = $countries->random();
            $price = $faker->randomFloat(2, 1, 100);
            $unit = $faker->randomElement($units);

            $product = new Product();
            $product->category_id = $category->id;
            $product->seller_id = $seller->id;
            $product->price = $price;
            $product->unit = $unit;
            $product->is_organic = $isOrganic;
            $product->country_of_origin = $country->id;
            $product->product_image = $filename;
            $product->is_active = $faker->boolean(90);
            $product->stock = $stock;
            $product->min_order_count = round($stock / 4);
            $product->created_at = $faker->dateTimeBetween('-6 months', 'now');
            $product->updated_at = now();

            $product->setTranslations('description', [
                'en' => $this->fakeProducts['en'][$i % count($this->fakeProducts['en'])]['description'],
                'lt' => $this->fakeProducts['lt'][$i % count($this->fakeProducts['lt'])]['description'],
            ]);

            $product->save();
            $productsCreated++;

            if ($bar) {
                $bar->advance();
            }
        }

        return $productsCreated;
    }

    private function generateImage(string $productDirectory, $faker): string
    {
        $color = $faker->hexColor();
        $img = Image::canvas(500, 500, $color)->encode('webp', 80);
        $filename = 'product_' . Str::random(10) . '.webp';
        Storage::put($productDirectory . '/' . $filename, $img);

        return $filename;
    }

    private function seedAttributes(): void
    {
        $attributeData = [
                    [
                        'en' => 'Nutritional Value',
                        'lt' => 'Maistinė vertė',
                        'values' => [
                            ['en' => 'High Protein', 'lt' => 'Didelis baltymiškumas'],
                            ['en' => 'Low Fat', 'lt' => 'Mažai riebalų'],
                            ['en' => 'Rich in Vitamins', 'lt' => 'Turtingas vitaminais']
                        ],
                        'is_filterable' => true,
                        'is_required' => false
                    ],
                    [
                        'en' => 'Dietary Type',
                        'lt' => 'Mitybos tipas',
                        'values' => [
                            ['en' => 'Vegan', 'lt' => 'Veganiška'],
                            ['en' => 'Vegetarian', 'lt' => 'Vegetariška'],
                            ['en' => 'Gluten-Free', 'lt' => 'Be gliuteno'],
                            ['en' => 'Organic', 'lt' => 'Ekologiška']
                        ],
                        'is_filterable' => true,
                        'is_required' => false
                    ],
                    [
                        'en' => 'Origin',
                        'lt' => 'Kilmė',
                        'values' => [
                            ['en' => 'Local', 'lt' => 'Vietinė'],
                            ['en' => 'Imported', 'lt' => 'Importinė'],
                            ['en' => 'Artisan', 'lt' => 'Amatininkų']
                        ],
                        'is_filterable' => true,
                        'is_required' => false
                    ],
                    [
                        'en' => 'Preservation Method',
                        'lt' => 'Išsaugojimo metodas',
                        'values' => [
                            ['en' => 'Fresh', 'lt' => 'Šviežia'],
                            ['en' => 'Frozen', 'lt' => 'Užšaldyta'],
                            ['en' => 'Canned', 'lt' => 'Konservuota']
                        ],
                        'is_filterable' => true,
                        'is_required' => false
                    ],
                    [
                        'en' => 'Spiciness',
                        'lt' => 'Aštrumas',
                        'values' => [
                            ['en' => 'Mild', 'lt' => 'Švelnus'],
                            ['en' => 'Medium', 'lt' => 'Vidutinis'],
                            ['en' => 'Spicy', 'lt' => 'Aštrus']
                        ],
                        'is_filterable' => true,
                        'is_required' => false
                    ],
                    [
                        'en' => 'Packaging',
                        'lt' => 'Pakavimas',
                        'values' => [
                            ['en' => 'Plastic', 'lt' => 'Plastikas'],
                            ['en' => 'Glass', 'lt' => 'Stiklas'],
                            ['en' => 'Cardboard', 'lt' => 'Kartonas'],
                            ['en' => 'Eco-Friendly', 'lt' => 'Aplinkai draugiškas']
                        ],
                        'is_filterable' => true,
                        'is_required' => false
                    ],
                    [
                        'en' => 'Temperature Range',
                        'lt' => 'Temperatūros diapazonas',
                        'values' => [
                            ['en' => 'Room Temperature', 'lt' => 'Kambario temperatūra'],
                            ['en' => 'Refrigerated', 'lt' => 'Šaldytuvas'],
                            ['en' => 'Frozen', 'lt' => 'Užšaldyta']
                        ],
                        'is_filterable' => true,
                        'is_required' => false
                    ],
                    [
                        'en' => 'Allergen Information',
                        'lt' => 'Alergenų informacija',
                        'values' => [
                            ['en' => 'Nuts', 'lt' => 'Riešutai'],
                            ['en' => 'Dairy', 'lt' => 'Pieno produktai'],
                            ['en' => 'Eggs', 'lt' => 'Kiaušiniai'],
                            ['en' => 'Soy', 'lt' => 'Soja'],
                            ['en' => 'Wheat', 'lt' => 'Kviečiai']
                        ],
                        'is_filterable' => true,
                        'is_required' => false
                    ],
                    [
                        'en' => 'Weight',
                        'lt' => 'Svoris',
                        'values' => [
                            ['en' => 'Light', 'lt' => 'Lengvas'],
                            ['en' => 'Medium', 'lt' => 'Vidutinis'],
                            ['en' => 'Heavy', 'lt' => 'Sunkus']
                        ],
                        'is_filterable' => true,
                        'is_required' => false
                    ],
                    [
                        'en' => 'Cooking Method',
                        'lt' => 'Gaminimo metodas',
                        'values' => [
                            ['en' => 'Boiled', 'lt' => 'Virtas'],
                            ['en' => 'Grilled', 'lt' => 'Keptas ant grotelių'],
                            ['en' => 'Baked', 'lt' => 'Keptas orkaitėje'],
                            ['en' => 'Raw', 'lt' => 'Žalias']
                        ],
                        'is_filterable' => true,
                        'is_required' => false
                    ]
                ];
        $this->withProgressBar(count($attributeData), 'Attributes', function (?ProgressBar $bar) use ($attributeData) {
            foreach ($attributeData as $attributeInfo) {
                $attribute = Attribute::create([
                    'name' => [
                        'en' => $attributeInfo['en'],
                        'lt' => $attributeInfo['lt'],
                    ],
                    'type' => 'select',
                    'is_active' => true,
                    'is_filterable' => $attributeInfo['is_filterable'],
                    'is_required' => $attributeInfo['is_required'],
                ]);

                foreach ($attributeInfo['values'] as $valueInfo) {
                    AttributeValue::create([
                        'attribute_id' => $attribute->id,
                        'value' => [
                            'en' => $valueInfo['en'],
                            'lt' => $valueInfo['lt'],
                        ],
                        'is_active' => true,
                    ]);
                }

                if ($bar) {
                    $bar->advance();
                }
            }
        });

        $products = Product::all();
        if ($products->isEmpty()) {
            return;
        }

        $allAttributeValues = AttributeValue::all();
        $totalAssignments = $products->count() * $allAttributeValues->count();

        $this->withProgressBar($totalAssignments, 'Assigning Attributes', function (?ProgressBar $bar) use ($products, $allAttributeValues) {
            foreach ($products as $product) {
                foreach ($allAttributeValues as $attributeValue) {
                    $product->attributeValues()->attach($attributeValue->id, [
                        'attribute_id' => $attributeValue->attribute_id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    if ($bar) {
                        $bar->advance();
                    }
                }
            }
        });
    }

    private function withProgressBar(int $total, string $label, callable $callback): void
    {
        if ($total <= 0) {
            $callback(null);

            return;
        }

        if (!$this->command) {
            $callback(null);

            return;
        }

        $output = $this->command->getOutput();
        $output->writeln($label);

        $progressBar = $output->createProgressBar($total);
        $progressBar->start();

        $callback($progressBar);

        $progressBar->finish();
        $output->writeln('');
    }
}
