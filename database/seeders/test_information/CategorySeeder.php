<?php

namespace Database\Seeders\test_information;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'main_category' => [
                    'lt' => ['name' => 'Šviežia mėsa'],
                    'en' => ['name' => 'Fresh meat'],
                ],
                'subcategories' => [
                    ['lt' => ['name' => 'Kiauliena'], 'en' => ['name' => 'Pork']],
                    ['lt' => ['name' => 'Jautiena'], 'en' => ['name' => 'Beef']],
                    ['lt' => ['name' => 'Kalakutiena'], 'en' => ['name' => 'Turkey']],
                    ['lt' => ['name' => 'Vištiena'], 'en' => ['name' => 'Chicken']],
                    ['lt' => ['name' => 'Kiti produktai'], 'en' => ['name' => 'Other products']],
                ],
            ],
            [
                'main_category' => [
                    'lt' => ['name' => 'Malta mėsa'],
                    'en' => ['name' => 'Miced meat'],
                ],
                'subcategories' => [
                    ['lt' => ['name' => 'Kiauliena'], 'en' => ['name' => 'Pork']],
                    ['lt' => ['name' => 'Jautiena'], 'en' => ['name' => 'Beef']],
                    ['lt' => ['name' => 'Kalakutiena'], 'en' => ['name' => 'Turkey']],
                    ['lt' => ['name' => 'Vištiena'], 'en' => ['name' => 'Chicken']],
                    ['lt' => ['name' => 'Kiti produktai'], 'en' => ['name' => 'Other products']],
                ],
            ],
            [
                'main_category' => [
                    'lt' => ['name' => 'Marinuoti mėsa ir paukštiena'],
                    'en' => ['name' => 'Marinated meat and poultry'],
                ],
                'subcategories' => [
                    ['lt' => ['name' => 'Marinuota mėsa'], 'en' => ['name' => 'Marinated meat']],
                    ['lt' => ['name' => 'Marinuota vištiena'], 'en' => ['name' => 'Marinated poultry']],
                    ['lt' => ['name' => 'Kiti produktai'], 'en' => ['name' => 'Other products']],
                ],
            ],
            [
                'main_category' => [
                    'lt' => ['name' => 'Šviežios dešrelės'],
                    'en' => ['name' => 'Fresh sausages'],
                ],
                'subcategories' => [
                    ['lt' => ['name' => 'Šviežios dešrelės'], 'en' => ['name' => 'Fresh sausages']],
                    ['lt' => ['name' => 'Kiti produktai'], 'en' => ['name' => 'Other products']],
                ],
            ],
            [
                'main_category' => [
                    'lt' => ['name' => 'Mėsos ir paukštienos gaminiai'],
                    'en' => ['name' => 'Meat and poultry products'],
                ],
                'subcategories' => [
                    ['lt' => ['name' => 'Virtos dešros ir dešrelės'], 'en' => ['name' => 'Cooked sausages']],
                    ['lt' => ['name' => 'Paštetas'], 'en' => ['name' => 'Pates']],
                    ['lt' => ['name' => 'Parūkytos dešros ir dešrelės'], 'en' => ['name' => 'Smoked sausages']],
                    ['lt' => ['name' => 'Vytintos dešros ir dešrelės'], 'en' => ['name' => 'Dried sausages']],
                    ['lt' => ['name' => 'Karštai rūkyti gaminiai'], 'en' => ['name' => 'Smoked products']],
                    ['lt' => ['name' => 'Vytinti gaminiai'], 'en' => ['name' => 'Cured products']],
                    ['lt' => ['name' => 'Mėsos ir paukštienos konservai'], 'en' => ['name' => 'Meat and poultry canned food']],
                    ['lt' => ['name' => 'Dešrelės kepimui'], 'en' => ['name' => 'Grill sausages']],
                    ['lt' => ['name' => 'Kiti produktai'], 'en' => ['name' => 'Other products']],
                ],
            ],
            [
                'main_category' => [
                    'lt' => ['name' => 'Kulinarija'],
                    'en' => ['name' => 'Cooking'],
                ],
                'subcategories' => [
                    ['lt' => ['name' => 'Užkandžiai'], 'en' => ['name' => 'Snacks']],
                    ['lt' => ['name' => 'Sumuštiniai'], 'en' => ['name' => 'Sandwiches']],
                    ['lt' => ['name' => 'Kiti produktai'], 'en' => ['name' => 'Other products']],
                ],
            ],
            [
                'main_category' => [
                    'lt' => ['name' => 'Žuvis, žuvų gaminiai'],
                    'en' => ['name' => 'Fish products'],
                ],
                'subcategories' => [
                    ['lt' => ['name' => 'Silkė'], 'en' => ['name' => 'Herring']],
                    ['lt' => ['name' => 'Lašiša'], 'en' => ['name' => 'Salmon']],
                    ['lt' => ['name' => 'Ikrai'], 'en' => ['name' => 'Caviar']],
                    ['lt' => ['name' => 'Užtepelės ir paštetai'], 'en' => ['name' => 'Smears and pates']],
                    ['lt' => ['name' => 'Kiti produktai'], 'en' => ['name' => 'Other products']],
                ],
            ],
            [
                'main_category' => [
                    'lt' => ['name' => 'Daržovės ir vaisiai'],
                    'en' => ['name' => 'Vegetables and fruits'],
                ],
                'subcategories' => [
                    ['lt' => ['name' => 'Citrusiniai vaisiai'], 'en' => ['name' => 'Citrus fruits']],
                    ['lt' => ['name' => 'Vynuogės ir uogos'], 'en' => ['name' => 'Grapes and berries']],
                    ['lt' => ['name' => 'Obuoliai ir kriaušės'], 'en' => ['name' => 'Apples and pears']],
                    ['lt' => ['name' => 'Arbūzai'], 'en' => ['name' => 'Watermelons']],
                    ['lt' => ['name' => 'Slyvos'], 'en' => ['name' => 'Plums']],
                    ['lt' => ['name' => 'Pomidorai ir agurkai'], 'en' => ['name' => 'Tomatoes and cucumbers']],
                    ['lt' => ['name' => 'Svogūnai ir česnakai'], 'en' => ['name' => 'Onions and garlic']],
                    ['lt' => ['name' => 'Burokėliai'], 'en' => ['name' => 'Beets']],
                    ['lt' => ['name' => 'Salotos'], 'en' => ['name' => 'Lettuce']],
                    ['lt' => ['name' => 'Moliūgai'], 'en' => ['name' => 'Pumpkins']],
                    ['lt' => ['name' => 'Bulvės ir morkos'], 'en' => ['name' => 'Potatoes and carrots']],
                    ['lt' => ['name' => 'Kopūstai'], 'en' => ['name' => 'Cabbage']],
                    ['lt' => ['name' => 'Marinuotos, raugintos ir sūdytos daržovės'], 'en' => ['name' => 'Marinated, fermented, and salted vegetables']],
                    ['lt' => ['name' => 'Kiti produktai'], 'en' => ['name' => 'Other products']],
                ],
            ],
            [
                'main_category' => [
                    'lt' => ['name' => 'Pieno gaminiai'],
                    'en' => ['name' => 'Milk products'],
                ],
                'subcategories' => [
                    ['lt' => ['name' => 'Ilgo galiojimo pienas'], 'en' => ['name' => 'Long shelf milk']],
                    ['lt' => ['name' => 'Pienas'], 'en' => ['name' => 'Milk']],
                    ['lt' => ['name' => 'Varškės sūris'], 'en' => ['name' => 'Cottage cheese']],
                    ['lt' => ['name' => 'Fermentinis sūris'], 'en' => ['name' => 'Fermented cheese']],
                    ['lt' => ['name' => 'Tepamieji sūriai'], 'en' => ['name' => 'Spreadable cheese']],
                    ['lt' => ['name' => 'Kiti pieno produktai'], 'en' => ['name' => 'Other milk products']],
                ],
            ],
            [
                'main_category' => [
                    'lt' => ['name' => 'Majonezas'],
                    'en' => ['name' => 'Mayonnaise'],
                ],
                'subcategories' => [
                    ['lt' => ['name' => 'Majonezas'], 'en' => ['name' => 'Mayonnaise']],
                    ['lt' => ['name' => 'Kiti produktai'], 'en' => ['name' => 'Other products']],
                ],
            ],
            [
                'main_category' => [
                    'lt' => ['name' => 'Kiaušiniai'],
                    'en' => ['name' => 'Eggs'],
                ],
                'subcategories' => [
                    ['lt' => ['name' => 'Kiaušiniai'], 'en' => ['name' => 'Eggs']],
                    ['lt' => ['name' => 'Kiti produktai'], 'en' => ['name' => 'Other products']],
                ],
            ],
            [
                'main_category' => [
                    'lt' => ['name' => 'Duonos gaminiai ir konditerija'],
                    'en' => ['name' => 'Bread products and confectionery'],
                ],
                'subcategories' => [
                    ['lt' => ['name' => 'Šviesi duona'], 'en' => ['name' => 'Light bread']],
                    ['lt' => ['name' => 'Tamsi duona'], 'en' => ['name' => 'Dark bread']],
                    ['lt' => ['name' => 'Batonas'], 'en' => ['name' => 'White bread']],
                    ['lt' => ['name' => 'Sumuštinių duona'], 'en' => ['name' => 'Sandwich bread']],
                    ['lt' => ['name' => 'Kiti duonos gaminiai'], 'en' => ['name' => 'Other bread products']],
                    ['lt' => ['name' => 'Šakotis'], 'en' => ['name' => 'Sakotis']],
                    ['lt' => ['name' => 'Skrusdėlynas'], 'en' => ['name' => 'Baumkuchen']],
                    ['lt' => ['name' => 'Pyragaičiai ir desertai'], 'en' => ['name' => 'Cakes and desserts']],
                    ['lt' => ['name' => 'Nesaldžios bandelės'], 'en' => ['name' => 'Not sweet buns']],
                    ['lt' => ['name' => 'Saldžios bandelės'], 'en' => ['name' => 'Sweet buns']],
                    ['lt' => ['name' => 'Spurgos'], 'en' => ['name' => 'Donuts']],
                    ['lt' => ['name' => 'Kiti produktai'], 'en' => ['name' => 'Other products']],
                ],
            ],
            [
                'main_category' => [
                    'lt' => ['name' => 'Maistas sportui'],
                    'en' => ['name' => 'Food for sports'],
                ],
                'subcategories' => [
                    ['lt' => ['name' => 'Vitaminai'], 'en' => ['name' => 'Vitamins']],
                    ['lt' => ['name' => 'Batonėliai'], 'en' => ['name' => 'Bars']],
                    ['lt' => ['name' => 'Kiti produktai'], 'en' => ['name' => 'Other products']],
                ],
            ],
            [
                'main_category' => [
                    'lt' => ['name' => 'Medus'],
                    'en' => ['name' => 'Honey'],
                ],
                'subcategories' => [
                    ['lt' => ['name' => 'Medus'], 'en' => ['name' => 'Honey']],
                    ['lt' => ['name' => 'Kiti produktai'], 'en' => ['name' => 'Other products']],
                ],
            ],
            [
                'main_category' => [
                    'lt' => ['name' => 'Bakalėja'],
                    'en' => ['name' => 'Groceries'],
                ],
                'subcategories' => [
                    ['lt' => ['name' => 'Malta kava'], 'en' => ['name' => 'Coffee']],
                    ['lt' => ['name' => 'Kavos pupelės'], 'en' => ['name' => 'Coffee beans']],
                    ['lt' => ['name' => 'Kavos kapsulės'], 'en' => ['name' => 'Coffee capsules']],
                    ['lt' => ['name' => 'Tirpi kava'], 'en' => ['name' => 'Instant coffee']],
                    ['lt' => ['name' => 'Kakava'], 'en' => ['name' => 'Cocoa']],
                    ['lt' => ['name' => 'Žalioji arbata'], 'en' => ['name' => 'Green tea']],
                    ['lt' => ['name' => 'Baltoji arbata'], 'en' => ['name' => 'White tea']],
                    ['lt' => ['name' => 'Vaisinė arbata'], 'en' => ['name' => 'Fruit tea']],
                    ['lt' => ['name' => 'Juoda arbata'], 'en' => ['name' => 'Black tea']],
                    ['lt' => ['name' => 'Šokoladas'], 'en' => ['name' => 'Chocolate']],
                    ['lt' => ['name' => 'Sausainiai'], 'en' => ['name' => 'Cookies']],
                    ['lt' => ['name' => 'Zefirai'], 'en' => ['name' => 'Marshmallows']],
                    ['lt' => ['name' => 'Vafliai'], 'en' => ['name' => 'Waffles']],
                    ['lt' => ['name' => 'Saldainiai'], 'en' => ['name' => 'Sweets']],
                    ['lt' => ['name' => 'Kiti produktai'], 'en' => ['name' => 'Other products']],
                ],
            ],
            [
                'main_category' => [
                    'lt' => ['name' => 'Konservuoti gaminiai'],
                    'en' => ['name' => 'Pickled, canned food'],
                ],
                'subcategories' => [
                    ['lt' => ['name' => 'Konservuoti agurkai'], 'en' => ['name' => 'Canned cucumbers']],
                    ['lt' => ['name' => 'Konservuoti pomidorai'], 'en' => ['name' => 'Canned tomatoes']],
                    ['lt' => ['name' => 'Konservuoti žirneliai'], 'en' => ['name' => 'Canned peas']],
                    ['lt' => ['name' => 'Konservuotos sriubos'], 'en' => ['name' => 'Canned soups']],
                    ['lt' => ['name' => 'Uogienė ir džemai'], 'en' => ['name' => 'Jams and jams']],
                    ['lt' => ['name' => 'Konservuoti vaisiai ir uogos'], 'en' => ['name' => 'Canned fruits and berries']],
                    ['lt' => ['name' => 'Konservuoti burokėliai'], 'en' => ['name' => 'Canned beets']],
                    ['lt' => ['name' => 'Kiti produktai'], 'en' => ['name' => 'Other products']],
                ],
            ],
            [
                'main_category' => [
                    'lt' => ['name' => 'Padažai'],
                    'en' => ['name' => 'Sauces'],
                ],
                'subcategories' => [
                    ['lt' => ['name' => 'Kečiupas'], 'en' => ['name' => 'Ketchup']],
                    ['lt' => ['name' => 'Pomidorų padažas'], 'en' => ['name' => 'Tomato sauces']],
                    ['lt' => ['name' => 'Krienai'], 'en' => ['name' => 'Horseradish']],
                    ['lt' => ['name' => 'Saldžiarūgštys padažas'], 'en' => ['name' => 'Sweet and sour sauce']],
                    ['lt' => ['name' => 'Garstyčios'], 'en' => ['name' => 'Mustard']],
                    ['lt' => ['name' => 'Kiti padažai'], 'en' => ['name' => 'Other sauces']],
                ],
            ],
            [
                'main_category' => [
                    'lt' => ['name' => 'Prieskoniai'],
                    'en' => ['name' => 'Spices'],
                ],
                'subcategories' => [
                    ['lt' => ['name' => 'Druska'], 'en' => ['name' => 'Salt']],
                    ['lt' => ['name' => 'Pipirai'], 'en' => ['name' => 'Pepper']],
                    ['lt' => ['name' => 'Universlūs prieskoniai'], 'en' => ['name' => 'Universal spices']],
                    ['lt' => ['name' => 'Liofilizuoti prieskoniai'], 'en' => ['name' => 'Freeze-dried spices']],
                    ['lt' => ['name' => 'Grynieji prieskoniai ir žolelės'], 'en' => ['name' => 'Pure herbs and spices']],
                    ['lt' => ['name' => 'Prieskonių, žolelių mišiniai'], 'en' => ['name' => 'Spice and herb mixes']],
                    ['lt' => ['name' => 'Bulvių prieskoniai'], 'en' => ['name' => 'Potato spices']],
                    ['lt' => ['name' => 'Kiti produktai'], 'en' => ['name' => 'Other products']],
                ],
            ],
            [
                'main_category' => [
                    'lt' => ['name' => 'Gėrimai'],
                    'en' => ['name' => 'Drinks'],
                ],
                'subcategories' => [
                    ['lt' => ['name' => 'Gazuotas vanduo'], 'en' => ['name' => 'Carbonated water']],
                    ['lt' => ['name' => 'Negazuotas vanduo'], 'en' => ['name' => 'Still water']],
                    ['lt' => ['name' => 'Sultys'], 'en' => ['name' => 'Juice']],
                    ['lt' => ['name' => 'Gazuoti vaisvandeniai'], 'en' => ['name' => 'Fizzy soft drinks']],
                    ['lt' => ['name' => 'Negazuoti vaisvandeniai'], 'en' => ['name' => 'Still flavored drinks']],
                    ['lt' => ['name' => 'Gira'], 'en' => ['name' => 'Kvass']],
                    ['lt' => ['name' => 'Sporto gerimai'], 'en' => ['name' => 'Sports drinks']],
                    ['lt' => ['name' => 'Imuniteto gėrimai'], 'en' => ['name' => 'Immunity drinks']],
                    ['lt' => ['name' => 'Sirupai'], 'en' => ['name' => 'Syrups']],
                    ['lt' => ['name' => 'Kiti gėrimo produktai'], 'en' => ['name' => 'Other drink products']],
                ],
            ],

            [
                'main_category' => [
                    'lt' => ['name' => 'Alkoholiniai gėrimai'],
                    'en' => ['name' => 'Alcoholic drinks'],
                ],
                'subcategories' => [
                    ['lt' => ['name' => 'Šviesus alus'], 'en' => ['name' => 'Light beer']],
                    ['lt' => ['name' => 'Tamsus alus'], 'en' => ['name' => 'Dark beer']],
                    ['lt' => ['name' => 'Mažujų daryklų alus'], 'en' => ['name' => 'Craft beer']],
                    ['lt' => ['name' => 'Alaus kokteiliai'], 'en' => ['name' => 'Beer cocktails']],
                    ['lt' => ['name' => 'Raudonas vynas'], 'en' => ['name' => 'Red wine']],
                    ['lt' => ['name' => 'Baltas vynas'], 'en' => ['name' => 'White wine']],
                    ['lt' => ['name' => 'Rausvasis vynas'], 'en' => ['name' => 'Rose wine']],
                    ['lt' => ['name' => 'Putojantis vynas'], 'en' => ['name' => 'Sparkling wine']],
                    ['lt' => ['name' => 'Sidras'], 'en' => ['name' => 'Cider']],
                    ['lt' => ['name' => 'Kiti produktai'], 'en' => ['name' => 'Other products']],
                ],
            ],
            [
                'main_category' => [
                    'lt' => ['name' => 'Stiprieji alkoholiniai gėrimai'],
                    'en' => ['name' => 'Spirits'],
                ],
                'subcategories' => [
                    ['lt' => ['name' => 'Degtinė'], 'en' => ['name' => 'Vodka']],
                    ['lt' => ['name' => 'Brendis'], 'en' => ['name' => 'Brandy']],
                    ['lt' => ['name' => 'Trauktinė'], 'en' => ['name' => 'Bitter']],
                    ['lt' => ['name' => 'Likeris'], 'en' => ['name' => 'Liqueur']],
                    ['lt' => ['name' => 'Kiti produktai'], 'en' => ['name' => 'Other products']],
                ],
            ],
            [
                'main_category' => [
                    'lt' => ['name' => 'Gyvūnų prekės'],
                    'en' => ['name' => 'Pets'],
                ],
                'subcategories' => [
                    ['lt' => ['name' => 'Konservuotas šunų ėdalas'], 'en' => ['name' => 'Dry dog food']],
                    ['lt' => ['name' => 'Sausas šunų ėdalas'], 'en' => ['name' => 'Canned dog food']],
                    ['lt' => ['name' => 'Šunų skanėstai'], 'en' => ['name' => 'Dog snacks']],
                    ['lt' => ['name' => 'Sausas kačių ėdalas'], 'en' => ['name' => 'Canned cat food']],
                    ['lt' => ['name' => 'Konservuotas kačių ėdalas'], 'en' => ['name' => 'Dry cat food']],
                    ['lt' => ['name' => 'Kačių skanėstai'], 'en' => ['name' => 'Cat snacks']],
                    ['lt' => ['name' => 'Gyvūnų aksesuarai'], 'en' => ['name' => 'Pet accessories']],
                    ['lt' => ['name' => 'Gyvūnų higienos reikmenys'], 'en' => ['name' => 'Pet hygiene']],
                    ['lt' => ['name' => 'Kiti produktai'], 'en' => ['name' => 'Other products']],
                ],
            ],
            [
                'main_category' => [
                    'lt' => ['name' => 'Buitinės chemijos prekės'],
                    'en' => ['name' => 'Household chemicals'],
                ],
                'subcategories' => [
                    ['lt' => ['name' => 'Namų kvapai'], 'en' => ['name' => 'Home fragrance']],
                    ['lt' => ['name' => 'Virtuvės ir vonios kambario valykliai'], 'en' => ['name' => 'Kitchen and bathroom cleaners']],
                    ['lt' => ['name' => 'Langų valykliai'], 'en' => ['name' => 'Window cleaners']],
                    ['lt' => ['name' => 'Universalūs valykliai'], 'en' => ['name' => 'All-purpose cleaners']],
                    ['lt' => ['name' => 'Skalbamieji milteliai'], 'en' => ['name' => 'Washing powder']],
                    ['lt' => ['name' => 'Skalbinių minkštikliai'], 'en' => ['name' => 'Fabric softeners']],
                    ['lt' => ['name' => 'Skystos skalbimo priemonės'], 'en' => ['name' => 'Liquid laundry detergents']],
                    ['lt' => ['name' => 'Dėmių valykliai ir balinimo priemonės'], 'en' => ['name' => 'Stain removers and whiteners']],
                ],
            ],
            [
                'main_category' => [
                    'lt' => ['name' => 'Probiotikai'],
                    'en' => ['name' => 'Probiotics'],
                ],
                'subcategories' => [
                    ['lt' => ['name' => 'Probiotikai'], 'en' => ['name' => 'Probiotics']],
                    ['lt' => ['name' => 'Žarnyno sveikata'], 'en' => ['name' => 'Gut health']],
                    ['lt' => ['name' => 'Grožis ir odos sveikata'], 'en' => ['name' => 'Beauty and skin health']],
                    ['lt' => ['name' => 'Sveikatingumas'], 'en' => ['name' => 'Health']],
                    ['lt' => ['name' => 'Streso ir nuotaikos pusiausvyra'], 'en' => ['name' => 'Stress and mood balance']],
                ],
            ],
            [
                'main_category' => [
                    'lt' => ['name' => 'Sėklos'],
                    'en' => ['name' => 'Seeds'],
                ],
                'subcategories' => [
                    ['lt' => ['name' => 'Agurkai'], 'en' => ['name' => 'Cucumber']],
                    ['lt' => ['name' => 'Pomidorai'], 'en' => ['name' => 'Tomatoes']],
                    ['lt' => ['name' => 'Krapai'], 'en' => ['name' => 'Dill']],
                    ['lt' => ['name' => 'Žirniai'], 'en' => ['name' => 'Peas']],
                    ['lt' => ['name' => 'Morkos'], 'en' => ['name' => 'Carrots']],
                    ['lt' => ['name' => 'Salotos'], 'en' => ['name' => 'Lettuce']],
                    ['lt' => ['name' => 'Svogūnai'], 'en' => ['name' => 'Onions']],
                    ['lt' => ['name' => 'Žemuogių, braškių sėklos'], 'en' => ['name' => 'Berry seeds']],
                    ['lt' => ['name' => 'Burokėliai'], 'en' => ['name' => 'Beets']],
                    ['lt' => ['name' => 'Kopūstai'], 'en' => ['name' => 'Cabbage']],
                    ['lt' => ['name' => 'Gėlių sėklos'], 'en' => ['name' => 'Flower seeds']],
                    ['lt' => ['name' => 'Smidrai'], 'en' => ['name' => 'Spinach']],
                    ['lt' => ['name' => 'Moliūgai'], 'en' => ['name' => 'Pumpkin']],
                    ['lt' => ['name' => 'Arbuzas'], 'en' => ['name' => 'Watermelon']],
                ],
            ],
            [
                'main_category' => [
                    'lt' => ['name' => 'Gėlės'],
                    'en' => ['name' => 'Flowers'],
                ],
                'subcategories' => [
                    ['lt' => ['name' => 'Tulpės'], 'en' => ['name' => 'Tulips']],
                    ['lt' => ['name' => 'Rožės'], 'en' => ['name' => 'Roses']],
                    ['lt' => ['name' => 'Bijūnai'], 'en' => ['name' => 'Peonies']],
                    ['lt' => ['name' => 'Kardeliai'], 'en' => ['name' => 'Gladioli']],
                ],
            ],
        ];

        /** @var Collection<int, Category> $existingCategories */
        $existingCategories = Category::query()
            ->select(['id', 'parent_category_id', 'category_name', 'slug', 'order'])
            ->get();

        foreach ($categories as $mainIndex => $categoryData) {
            $mainCategory = $this->upsertCategory(
                $existingCategories,
                $this->translationsFromNode($categoryData['main_category']),
                null,
                ($mainIndex + 1) * 100
            );

            foreach ($categoryData['subcategories'] as $subIndex => $subcategoryData) {
                $this->upsertCategory(
                    $existingCategories,
                    $this->translationsFromNode($subcategoryData),
                    $mainCategory->id,
                    $mainCategory->order + $subIndex + 1
                );
            }
        }
    }

    /**
     * @param  Collection<int, Category>  $existingCategories
     * @param  array{lt: string, en: string}  $translations
     */
    private function upsertCategory(
        Collection $existingCategories,
        array $translations,
        ?int $parentCategoryId,
        int $order
    ): Category {
        $category = $existingCategories->first(
            fn (Category $existingCategory): bool => $this->matchesCategory(
                $existingCategory,
                $translations,
                $parentCategoryId
            )
        );

        if ($category === null) {
            $category = new Category;
            $existingCategories->push($category);
        }

        $category->parent_category_id = $parentCategoryId;
        $category->order = $order;
        $category->is_active = true;
        $category->setTranslations('category_name', $translations);
        $category->setTranslations('slug', $this->slugsFromTranslations($translations));
        $category->save();

        return $category;
    }

    /**
     * @param  array{lt: array{name: string}, en: array{name: string}}  $node
     * @return array{lt: string, en: string}
     */
    private function translationsFromNode(array $node): array
    {
        return [
            'lt' => $node['lt']['name'],
            'en' => $node['en']['name'],
        ];
    }

    /**
     * @param  array{lt: string, en: string}  $translations
     */
    private function slugsFromTranslations(array $translations): array
    {
        return [
            'lt' => Str::slug($translations['lt']),
            'en' => Str::slug($translations['en']),
        ];
    }

    /**
     * @param  array{lt: string, en: string}  $translations
     */
    private function matchesCategory(Category $category, array $translations, ?int $parentCategoryId): bool
    {
        return $category->parent_category_id === $parentCategoryId
            && $category->getTranslation('category_name', 'lt') === $translations['lt']
            && $category->getTranslation('category_name', 'en') === $translations['en'];
    }
}
