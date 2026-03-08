<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ShoppingItem;
use App\Models\ShoppingList;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Импорт данных из старого приложения (списки покупок и элементы).
 */
class ShoppingListLegacySeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrFail();

        ShoppingList::withoutEvents(function () use ($user): void {
            foreach ($this->lists() as $listData) {
                $items = $listData['items'];
                unset($listData['items']);

                $list = ShoppingList::query()->create([
                    'user_id' => $user->id,
                    'folder_id' => null,
                    ...$listData,
                ]);

                ShoppingItem::withoutEvents(function () use ($user, $list, $items): void {
                    foreach ($items as $order => $item) {
                        ShoppingItem::query()->create([
                            'user_id' => $user->id,
                            'shopping_list_id' => $list->id,
                            'order_column' => $order + 1,
                            ...$item,
                        ]);
                    }
                });
            }
        });
    }

    /**
     * @return list<array{name: string, icon: string|null, order_column: int, created_at: string, updated_at: string, items: list<array{name: string, description: string, quantity: int, quantity_type: string, price: int, is_starred: bool, is_done: bool}>}>
     */
    private function lists(): array
    {
        return [
            [
                'name' => 'Суп куриный', 'icon' => '🥕', 'order_column' => 1,
                'created_at' => '2023-03-19 16:45:28', 'updated_at' => '2023-03-20 08:17:41',
                'items' => [
                    ['name' => 'Капуста', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Картофель', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Куриный бульон', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Лук', 'description' => '', 'quantity' => 2, 'quantity_type' => 'шт', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Морковь', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Сельдерей', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Соевый соус', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Чеснок', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                ],
            ],
            [
                'name' => 'Покупка на 7 ноября', 'icon' => null, 'order_column' => 2,
                'created_at' => '2023-03-19 16:47:28', 'updated_at' => '2023-03-19 16:54:33',
                'items' => [
                    ['name' => 'Йогурт', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Кассеты для бритвы', 'description' => 'Gilette Fusion', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Мыло', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Печенье', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Стиральный порошок', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Тряпки', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                ],
            ],
            [
                'name' => 'Курица в соусе', 'icon' => null, 'order_column' => 3,
                'created_at' => '2023-03-20 08:17:18', 'updated_at' => '2023-03-20 08:17:18',
                'items' => [
                    ['name' => 'Зелень', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Кари', 'description' => '', 'quantity' => 1, 'quantity_type' => 'ч/л', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Кориандр', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Курица бёдра', 'description' => '', 'quantity' => 1, 'quantity_type' => 'кг', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Лук', 'description' => '', 'quantity' => 2, 'quantity_type' => 'шт', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Перец', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Помидоры', 'description' => '', 'quantity' => 5, 'quantity_type' => 'шт', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Яйца', 'description' => '', 'quantity' => 5, 'quantity_type' => 'шт', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                ],
            ],
            [
                'name' => 'Макароны по флотски с овощами', 'icon' => null, 'order_column' => 4,
                'created_at' => '2023-03-20 08:19:43', 'updated_at' => '2023-03-20 08:19:43',
                'items' => [
                    ['name' => 'Замороженные овощи', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Лук', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Макароны', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Перец', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Сушёный чеснок', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Томатная паста', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Фарш', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                ],
            ],
            [
                'name' => 'Мясная подлива', 'icon' => null, 'order_column' => 5,
                'created_at' => '2023-03-20 08:21:09', 'updated_at' => '2023-03-20 08:21:09',
                'items' => [
                    ['name' => 'Лук', 'description' => '', 'quantity' => 1, 'quantity_type' => 'шт', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Мука', 'description' => '', 'quantity' => 1, 'quantity_type' => 'ст л', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Мясо', 'description' => '', 'quantity' => 800, 'quantity_type' => 'гр', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Растительное масло', 'description' => 'Oliveoil', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Сливочное масло', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Томатная паста', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Чеснок', 'description' => 'Knoblauch', 'quantity' => 4, 'quantity_type' => 'зубчика', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                ],
            ],
            [
                'name' => 'Жульен', 'icon' => null, 'order_column' => 6,
                'created_at' => '2023-03-20 08:23:10', 'updated_at' => '2023-03-20 08:23:10',
                'items' => [
                    ['name' => 'Куриная грудка', 'description' => '', 'quantity' => 1, 'quantity_type' => 'шт', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Грибы', 'description' => '', 'quantity' => 450, 'quantity_type' => 'гр', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Лук', 'description' => '', 'quantity' => 2, 'quantity_type' => 'шт', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Мука', 'description' => '', 'quantity' => 1, 'quantity_type' => 'ст л', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Мускатный орех', 'description' => 'die Muskatnuss', 'quantity' => 1, 'quantity_type' => 'ч л', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Петрушка', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Подсолнечное масло', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Сливки 33%', 'description' => '', 'quantity' => 250, 'quantity_type' => 'мл', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Сливочное масло', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Сметана', 'description' => '', 'quantity' => 1, 'quantity_type' => 'ст л', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Сушёный чеснок', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Сыр', 'description' => '', 'quantity' => 150, 'quantity_type' => 'гр', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Тимьян', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                ],
            ],
            [
                'name' => 'First', 'icon' => null, 'order_column' => 7,
                'created_at' => '2023-03-20 14:14:13', 'updated_at' => '2023-03-20 14:35:21',
                'items' => [
                    ['name' => 'Sbsbb', 'description' => 'Sbba', 'quantity' => 10, 'quantity_type' => 'Dhhd', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                ],
            ],
            [
                'name' => 'вауц', 'icon' => null, 'order_column' => 8,
                'created_at' => '2023-03-20 15:11:54', 'updated_at' => '2023-03-20 15:11:54',
                'items' => [],
            ],
            [
                'name' => 'уац', 'icon' => null, 'order_column' => 9,
                'created_at' => '2023-03-20 15:47:53', 'updated_at' => '2023-03-20 15:47:53',
                'items' => [],
            ],
            [
                'name' => '444', 'icon' => null, 'order_column' => 10,
                'created_at' => '2023-03-20 15:48:23', 'updated_at' => '2023-03-20 15:48:23',
                'items' => [],
            ],
            [
                'name' => 'Котлеты с картошкой', 'icon' => null, 'order_column' => 11,
                'created_at' => '2023-03-20 17:24:40', 'updated_at' => '2023-03-20 17:24:40',
                'items' => [
                    ['name' => 'Картофель', 'description' => '', 'quantity' => 7, 'quantity_type' => 'шт', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Морковь', 'description' => '', 'quantity' => 2, 'quantity_type' => 'шт', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Паприка', 'description' => '', 'quantity' => 2, 'quantity_type' => 'ч л', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Перец болгарский', 'description' => '', 'quantity' => 1, 'quantity_type' => 'шт', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Помидоры', 'description' => '', 'quantity' => 2, 'quantity_type' => 'шт', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Томатная паста', 'description' => '', 'quantity' => 1, 'quantity_type' => 'ст л', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Фарш', 'description' => '', 'quantity' => 600, 'quantity_type' => 'гр', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Хмели сунели', 'description' => '', 'quantity' => 1, 'quantity_type' => 'ч л', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Чёрный перец', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Чеснок', 'description' => '', 'quantity' => 2, 'quantity_type' => 'зуб', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Яйцо', 'description' => '', 'quantity' => 1, 'quantity_type' => 'шт', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                ],
            ],
            [
                'name' => 'Суп с говядиной', 'icon' => '☕', 'order_column' => 12,
                'created_at' => '2023-03-20 17:27:44', 'updated_at' => '2023-03-20 17:27:44',
                'items' => [
                    ['name' => 'Вермишель', 'description' => '', 'quantity' => 70, 'quantity_type' => 'гр', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Винный красный уксус', 'description' => '', 'quantity' => 1, 'quantity_type' => 'ст л', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Говядина', 'description' => '', 'quantity' => 350, 'quantity_type' => 'гр', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Лавровый лист', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Лук', 'description' => '', 'quantity' => 170, 'quantity_type' => 'гр', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Морковь', 'description' => '', 'quantity' => 170, 'quantity_type' => 'гр', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Растительное масло', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Розмарин', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Свежезамороженный горошек', 'description' => '', 'quantity' => 1, 'quantity_type' => 'ст', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Сельдерей', 'description' => '', 'quantity' => 170, 'quantity_type' => 'гр', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Соль', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Тимьян', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Томатная паста', 'description' => '', 'quantity' => 2, 'quantity_type' => 'ст л', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Чёрный перец', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Чеснок', 'description' => '', 'quantity' => 2, 'quantity_type' => 'зуб', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Шалфей', 'description' => 'Salbei', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                ],
            ],
            [
                'name' => 'Курица простая в сливочном соусе', 'icon' => null, 'order_column' => 13,
                'created_at' => '2023-03-20 17:31:16', 'updated_at' => '2023-03-20 17:31:16',
                'items' => [
                    ['name' => 'Куриное филе', 'description' => '', 'quantity' => 2, 'quantity_type' => 'шт', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Лук', 'description' => '', 'quantity' => 2, 'quantity_type' => 'шт', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Морковь', 'description' => '', 'quantity' => 1, 'quantity_type' => 'шт', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Растительное масло', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Сливки', 'description' => '33%', 'quantity' => 200, 'quantity_type' => 'мл', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                ],
            ],
            [
                'name' => 'Простой говяжий суп', 'icon' => '🥩', 'order_column' => 14,
                'created_at' => '2023-03-20 17:32:45', 'updated_at' => '2023-03-20 17:32:45',
                'items' => [
                    ['name' => 'Болгарский перец', 'description' => '', 'quantity' => 1, 'quantity_type' => 'шт', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Вода', 'description' => '', 'quantity' => 2, 'quantity_type' => 'л', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Говядина', 'description' => '', 'quantity' => 650, 'quantity_type' => 'гр', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Картофель', 'description' => '', 'quantity' => 3, 'quantity_type' => 'шт', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Лук', 'description' => '', 'quantity' => 2, 'quantity_type' => 'шт', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Морковь', 'description' => '', 'quantity' => 1, 'quantity_type' => 'шт', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Томатная паста', 'description' => '', 'quantity' => 2, 'quantity_type' => 'ст л', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                ],
            ],
            [
                'name' => 'Большой гуляш из говядины', 'icon' => '🍗', 'order_column' => 15,
                'created_at' => '2023-03-20 17:34:22', 'updated_at' => '2023-03-20 17:34:22',
                'items' => [
                    ['name' => 'Говядина', 'description' => '', 'quantity' => 2, 'quantity_type' => 'кг', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Жидкий бульон', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Имбирь', 'description' => '', 'quantity' => 70, 'quantity_type' => 'гр', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Лук', 'description' => '', 'quantity' => 500, 'quantity_type' => 'гр', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Морковь', 'description' => '', 'quantity' => 400, 'quantity_type' => 'гр', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Помидоры', 'description' => '', 'quantity' => 500, 'quantity_type' => 'гр', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Сельдерей', 'description' => '', 'quantity' => 200, 'quantity_type' => 'гр', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Сладкий перец', 'description' => '', 'quantity' => 3, 'quantity_type' => 'шт', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Томатная паста', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Чеснок', 'description' => '', 'quantity' => 3, 'quantity_type' => 'шт', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                ],
            ],
            [
                'name' => 'Мягкое мясо', 'icon' => null, 'order_column' => 16,
                'created_at' => '2023-03-20 18:23:11', 'updated_at' => '2023-03-20 18:23:11',
                'items' => [
                    ['name' => 'Белый перец', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Говядина мякоть', 'description' => '', 'quantity' => 350, 'quantity_type' => 'гр', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Зерна кунжута', 'description' => 'Sesam Korn', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Имбирь', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Лук', 'description' => '', 'quantity' => 2, 'quantity_type' => 'шт', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Перец болгарский', 'description' => '', 'quantity' => 1, 'quantity_type' => 'шт', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Чеснок', 'description' => '', 'quantity' => 1, 'quantity_type' => 'шт', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Яйцо', 'description' => '', 'quantity' => 1, 'quantity_type' => 'шт', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                ],
            ],
            [
                'name' => 'Макароны по-флотски', 'icon' => null, 'order_column' => 17,
                'created_at' => '2023-03-20 18:24:54', 'updated_at' => '2023-03-20 18:24:54',
                'items' => [
                    ['name' => 'Красный перец', 'description' => '', 'quantity' => 1, 'quantity_type' => 'шт', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Лук', 'description' => '', 'quantity' => 1, 'quantity_type' => 'шт', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Макароны', 'description' => '', 'quantity' => 400, 'quantity_type' => 'гр', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Орегано', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Сушёная петрушка', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Сушёный чеснок', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Томатная паста', 'description' => '', 'quantity' => 1, 'quantity_type' => 'ст л', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Фарш говяжий', 'description' => '', 'quantity' => 400, 'quantity_type' => 'гр', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                ],
            ],
            [
                'name' => 'Куриные ножки KFC', 'icon' => null, 'order_column' => 18,
                'created_at' => '2023-03-21 17:33:57', 'updated_at' => '2023-03-21 17:33:57',
                'items' => [
                    ['name' => 'Вода', 'description' => '', 'quantity' => 200, 'quantity_type' => 'мл', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Куриные ножки', 'description' => '', 'quantity' => 8, 'quantity_type' => 'шт', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Масло для жарки', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Молоко', 'description' => '', 'quantity' => 200, 'quantity_type' => 'мл', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Мука', 'description' => '', 'quantity' => 100, 'quantity_type' => 'гр', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Панировочные сухари', 'description' => 'das Paniermehl', 'quantity' => 200, 'quantity_type' => 'гр', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Соль', 'description' => '', 'quantity' => 1, 'quantity_type' => 'ч л', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Сухой укроп', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Чёрный перец', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Чеснок', 'description' => '', 'quantity' => 1, 'quantity_type' => 'зуб', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Яйцо', 'description' => '', 'quantity' => 2, 'quantity_type' => 'шт', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                ],
            ],
            [
                'name' => 'Суп с фрикадельками', 'icon' => null, 'order_column' => 19,
                'created_at' => '2023-03-21 17:36:36', 'updated_at' => '2023-03-21 17:36:36',
                'items' => [
                    ['name' => 'Кабачок или цукини', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Картошка', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Листовая капуста', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Лук', 'description' => '', 'quantity' => 2, 'quantity_type' => 'шт', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Морковь', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Сок лимона', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Фарш', 'description' => '', 'quantity' => 500, 'quantity_type' => 'гр', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Чеснок', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                ],
            ],
            [
                'name' => 'Жаркое из курицы', 'icon' => null, 'order_column' => 20,
                'created_at' => '2023-03-21 17:37:53', 'updated_at' => '2023-03-21 17:37:53',
                'items' => [
                    ['name' => 'Баззилик', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Кабачок или цукини', 'description' => 'die Zucchini', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Картошка', 'description' => '', 'quantity' => 1, 'quantity_type' => 'кг', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Кукурузный крахмал', 'description' => '', 'quantity' => 1, 'quantity_type' => 'ст л', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Куриные ножки', 'description' => '', 'quantity' => 7, 'quantity_type' => 'шт', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Лук', 'description' => '', 'quantity' => 2, 'quantity_type' => 'шт', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Морковь', 'description' => '', 'quantity' => 1, 'quantity_type' => 'шт', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Орегано', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Соевый соус', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Тимьян', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Томатная паста', 'description' => '', 'quantity' => 2, 'quantity_type' => 'ст л', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Чеснок', 'description' => '', 'quantity' => 3, 'quantity_type' => 'зуб', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                ],
            ],
            [
                'name' => 'Суп куриный сливочный', 'icon' => '💧', 'order_column' => 21,
                'created_at' => '2023-03-22 08:05:09', 'updated_at' => '2023-03-22 08:05:09',
                'items' => [
                    ['name' => 'Имбирь', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Кориандр', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Курица', 'description' => '', 'quantity' => 500, 'quantity_type' => 'гр', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Лаврушка', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Лук', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Макароны', 'description' => '', 'quantity' => 1, 'quantity_type' => 'стакан', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Масло подсолнечное', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Морковь', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Мука', 'description' => '', 'quantity' => 3, 'quantity_type' => 'ст л', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Петрушка', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Сельдерей', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Сливки', 'description' => '33%', 'quantity' => 1, 'quantity_type' => 'стакан', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Тимьян', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Фасоль консервированная', 'description' => 'Bohnen', 'quantity' => 200, 'quantity_type' => 'мл', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Чеснок', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                ],
            ],
            [
                'name' => 'Свинина в соусе', 'icon' => '🍖', 'order_column' => 22,
                'created_at' => '2023-03-22 08:07:46', 'updated_at' => '2023-03-22 08:07:46',
                'items' => [
                    ['name' => 'Горчица дижонская', 'description' => 'der Senf', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Лук', 'description' => '', 'quantity' => 1, 'quantity_type' => 'шт', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Свиная корейка', 'description' => 'die Rippenstuck', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Сливки', 'description' => '33 %', 'quantity' => 125, 'quantity_type' => 'мл', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Сливочное масло', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Чеснок', 'description' => '', 'quantity' => 3, 'quantity_type' => 'зуб', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Шалфей', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                ],
            ],
            [
                'name' => 'Фарш с макаронами', 'icon' => null, 'order_column' => 23,
                'created_at' => '2023-03-22 08:09:30', 'updated_at' => '2023-03-22 08:09:30',
                'items' => [
                    ['name' => 'Вода', 'description' => '', 'quantity' => 1, 'quantity_type' => 'л', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Говяжий фарш', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Горчица', 'description' => '', 'quantity' => 1, 'quantity_type' => 'ст л', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Замороженный горошек', 'description' => '', 'quantity' => 1, 'quantity_type' => 'ст', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Лук', 'description' => '', 'quantity' => 1, 'quantity_type' => 'шт', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Морковь', 'description' => '', 'quantity' => 2, 'quantity_type' => 'шт', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Перец', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Петрушка', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Подсолнечное масло', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Сливки', 'description' => '1/4 стакана', 'quantity' => 0, 'quantity_type' => 'стакана', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Соевый соус', 'description' => '', 'quantity' => 1, 'quantity_type' => 'ст л', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Соль', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Тимьян', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Чеснок', 'description' => '', 'quantity' => 2, 'quantity_type' => 'зуб', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Шампиньоны', 'description' => '', 'quantity' => 200, 'quantity_type' => 'гр', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Яичная лапша', 'description' => '', 'quantity' => 100, 'quantity_type' => 'гр', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                ],
            ],
            [
                'name' => 'Рассольник с перловкой и солёными огурцами', 'icon' => null, 'order_column' => 24,
                'created_at' => '2023-03-22 08:27:23', 'updated_at' => '2023-03-22 08:27:23',
                'items' => [
                    ['name' => 'Говядина', 'description' => 'С косточками', 'quantity' => 600, 'quantity_type' => 'гр', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Картофель', 'description' => '', 'quantity' => 4, 'quantity_type' => 'шт', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Лавровый лист', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Лук', 'description' => '', 'quantity' => 2, 'quantity_type' => 'шт', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Маринованные огурцы', 'description' => 'die Essiggurke, die Gewurzgurke', 'quantity' => 5, 'quantity_type' => 'шт', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Морковь', 'description' => '', 'quantity' => 2, 'quantity_type' => 'шт', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Перловая крупа', 'description' => 'die Perlgraupen', 'quantity' => 100, 'quantity_type' => 'гр', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Растительное масло', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Сельдерей', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Соль', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Сушёный чеснок', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Укроп', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Хмели-сунели', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                ],
            ],
            [
                'name' => 'Бифстроганов', 'icon' => null, 'order_column' => 25,
                'created_at' => '2023-03-22 18:53:46', 'updated_at' => '2023-03-22 18:53:46',
                'items' => [
                    ['name' => 'Говядина', 'description' => '', 'quantity' => 600, 'quantity_type' => 'гр', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Лук', 'description' => '', 'quantity' => 2, 'quantity_type' => 'шт', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Масло', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Мука', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Паприка', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Перец', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Сметана', 'description' => '', 'quantity' => 3, 'quantity_type' => 'ст л', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Соль', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Томатная паста', 'description' => '', 'quantity' => 2, 'quantity_type' => 'ст л', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Чеснок', 'description' => '', 'quantity' => 3, 'quantity_type' => 'зуб', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                ],
            ],
            [
                'name' => 'Глинтвейн', 'icon' => '🍷', 'order_column' => 26,
                'created_at' => '2023-03-24 19:30:54', 'updated_at' => '2023-03-24 19:34:06',
                'items' => [
                    ['name' => 'Апельсин', 'description' => '', 'quantity' => 2, 'quantity_type' => 'шт', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Бадьян', 'description' => 'Sternanis', 'quantity' => 2, 'quantity_type' => 'цветка', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Вино красное полусухое', 'description' => '', 'quantity' => 400, 'quantity_type' => 'мл', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Гвоздика', 'description' => 'die Nelke, die Gewurznelke', 'quantity' => 4, 'quantity_type' => 'бутона', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Имбирь', 'description' => 'der Ingwer', 'quantity' => 1, 'quantity_type' => 'ч/л', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Корица', 'description' => 'die Zimststange', 'quantity' => 1, 'quantity_type' => 'палка', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Мускатный орех', 'description' => '', 'quantity' => 1, 'quantity_type' => 'ч/л', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                ],
            ],
            [
                'name' => 'Суп из чечевицы', 'icon' => '🍴', 'order_column' => 27,
                'created_at' => '2023-03-24 19:33:52', 'updated_at' => '2023-03-24 19:33:52',
                'items' => [
                    ['name' => 'Зира', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Имбирь', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Картофель', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Куриный бульон', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Луковица', 'description' => '', 'quantity' => 1, 'quantity_type' => 'шт', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Молотый кориандр', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Морковь', 'description' => '', 'quantity' => 3, 'quantity_type' => 'шт', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Помидоры в собственном соку', 'description' => '', 'quantity' => 800, 'quantity_type' => 'мл', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Приправа кари', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Сельдерей', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Чёрный перец', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Чеснок', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Чечевица', 'description' => '', 'quantity' => 1, 'quantity_type' => 'ст', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Курица', 'description' => '', 'quantity' => 500, 'quantity_type' => 'гр', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                ],
            ],
            [
                'name' => 'Бутерброды с мясом', 'icon' => null, 'order_column' => 28,
                'created_at' => '2023-03-24 19:36:16', 'updated_at' => '2023-03-24 19:36:16',
                'items' => [
                    ['name' => 'Аджика', 'description' => '', 'quantity' => 1, 'quantity_type' => 'ч/л', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Батон', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Говяжий фарш', 'description' => '', 'quantity' => 500, 'quantity_type' => 'гр', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Дижонская горчица', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Лук', 'description' => '', 'quantity' => 1, 'quantity_type' => 'шт', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Маринованные огурцы', 'description' => '', 'quantity' => 3, 'quantity_type' => 'шт', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Паприка', 'description' => '', 'quantity' => 1, 'quantity_type' => 'ч/л', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Соевый соус', 'description' => '', 'quantity' => 1, 'quantity_type' => 'ст л', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Томатная паста', 'description' => '', 'quantity' => 3, 'quantity_type' => 'ст л', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Чеснок', 'description' => '', 'quantity' => 1, 'quantity_type' => 'зуб', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                ],
            ],
            [
                'name' => 'Суп из говядины и чечевицы', 'icon' => null, 'order_column' => 29,
                'created_at' => '2023-03-24 19:38:48', 'updated_at' => '2023-03-24 19:38:48',
                'items' => [
                    ['name' => 'Говядина', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Кабачок', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Картофель', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Лавровый лист', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Лук', 'description' => '', 'quantity' => 1, 'quantity_type' => 'шт', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Морковь', 'description' => '', 'quantity' => 1, 'quantity_type' => 'шт', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Паприка', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Сельдерей', 'description' => '', 'quantity' => 1, 'quantity_type' => 'шт', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Томатная паста', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Хмели-сунели', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Чеснок', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Чечевица', 'description' => '', 'quantity' => 1, 'quantity_type' => 'ст', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                ],
            ],
            [
                'name' => 'Завтрак с картошкой', 'icon' => null, 'order_column' => 30,
                'created_at' => '2023-03-24 19:40:11', 'updated_at' => '2023-03-24 19:40:11',
                'items' => [
                    ['name' => 'Болгарский перец', 'description' => '', 'quantity' => 1, 'quantity_type' => 'шт', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Зелёный лук', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Картофель', 'description' => '0,5 кг', 'quantity' => 0, 'quantity_type' => 'кг', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Колбаса', 'description' => '', 'quantity' => 150, 'quantity_type' => 'гр', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Луковица', 'description' => '', 'quantity' => 1, 'quantity_type' => 'шт', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Яйца', 'description' => '', 'quantity' => 4, 'quantity_type' => 'шт', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                ],
            ],
            [
                'name' => 'Суп сырный', 'icon' => null, 'order_column' => 31,
                'created_at' => '2023-03-24 19:41:54', 'updated_at' => '2023-03-24 19:41:54',
                'items' => [
                    ['name' => 'Батон', 'description' => '', 'quantity' => 3, 'quantity_type' => 'куска', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Вода', 'description' => '1.5 литра', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Картофель', 'description' => '', 'quantity' => 3, 'quantity_type' => 'шт', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Куриное филе', 'description' => '', 'quantity' => 1, 'quantity_type' => 'шт', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Лавровый лист', 'description' => '', 'quantity' => 2, 'quantity_type' => 'шт', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Лук', 'description' => '', 'quantity' => 1, 'quantity_type' => 'шт', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Морковь', 'description' => '', 'quantity' => 1, 'quantity_type' => 'шт', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Плавленный сыр', 'description' => 'der Schmelzkäse', 'quantity' => 2, 'quantity_type' => 'шт', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Сливочное масло', 'description' => '', 'quantity' => 20, 'quantity_type' => 'гр', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Чёрный перец', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Чеснок', 'description' => '', 'quantity' => 3, 'quantity_type' => 'зуб', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Шампиньоны', 'description' => '', 'quantity' => 300, 'quantity_type' => 'гр', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                ],
            ],
            [
                'name' => 'Грибной суп-пюре', 'icon' => null, 'order_column' => 32,
                'created_at' => '2024-03-02 08:12:37', 'updated_at' => '2024-03-02 08:12:37',
                'items' => [
                    ['name' => 'Грибы', 'description' => '', 'quantity' => 250, 'quantity_type' => 'гр', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Куриное филе', 'description' => '', 'quantity' => 2, 'quantity_type' => 'шт', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Молоко', 'description' => '', 'quantity' => 450, 'quantity_type' => 'мл', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Сливки', 'description' => '', 'quantity' => 150, 'quantity_type' => 'мл', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Чеснок', 'description' => '', 'quantity' => 4, 'quantity_type' => 'зуб', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Мука', 'description' => '', 'quantity' => 3, 'quantity_type' => 'ст л', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Манная крупа', 'description' => 'der Grieß', 'quantity' => 2, 'quantity_type' => 'ст л', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Мускатный орех', 'description' => '', 'quantity' => 1, 'quantity_type' => 'ч л', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Перец', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                ],
            ],
            [
                'name' => 'Бараньи рёбра', 'icon' => null, 'order_column' => 33,
                'created_at' => '2024-03-07 17:30:13', 'updated_at' => '2024-03-07 17:30:13',
                'items' => [
                    ['name' => 'Бараньи рёбрышки', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Лук', 'description' => '', 'quantity' => 1, 'quantity_type' => 'кг', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Чёрный перец', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Паприка', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                ],
            ],
            [
                'name' => 'Паста со сливочным соусом', 'icon' => null, 'order_column' => 34,
                'created_at' => '2024-03-09 11:00:38', 'updated_at' => '2024-03-09 11:00:38',
                'items' => [
                    ['name' => 'Спагетти', 'description' => '', 'quantity' => 250, 'quantity_type' => 'гр', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Сливочное масло', 'description' => '', 'quantity' => 30, 'quantity_type' => 'гр', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Чеснок', 'description' => '', 'quantity' => 4, 'quantity_type' => 'зуб', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Мука', 'description' => '', 'quantity' => 20, 'quantity_type' => 'гр', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Молоко', 'description' => '', 'quantity' => 500, 'quantity_type' => 'гр', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Сыр', 'description' => '', 'quantity' => 60, 'quantity_type' => 'гр', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Итальянские травы', 'description' => '', 'quantity' => 1, 'quantity_type' => 'ч л', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                ],
            ],
            [
                'name' => 'Нежная куриная грудка', 'icon' => null, 'order_column' => 35,
                'created_at' => '2024-03-09 11:04:27', 'updated_at' => '2024-03-09 11:04:27',
                'items' => [
                    ['name' => 'Куриная грудка', 'description' => '', 'quantity' => 3, 'quantity_type' => 'шт', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Сливочное масло', 'description' => '', 'quantity' => 80, 'quantity_type' => 'гр', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Мука', 'description' => '', 'quantity' => 60, 'quantity_type' => 'гр', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Чеснок', 'description' => '', 'quantity' => 3, 'quantity_type' => 'зуб', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Мёд', 'description' => '', 'quantity' => 3, 'quantity_type' => 'ст л', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Соевый соус', 'description' => 'die Sojasoße', 'quantity' => 4, 'quantity_type' => 'ст л', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                ],
            ],
            [
                'name' => 'Покупки на 16 марта 2024 г.', 'icon' => null, 'order_column' => 36,
                'created_at' => '2024-03-16 14:21:00', 'updated_at' => '2024-03-16 14:21:00',
                'items' => [
                    ['name' => 'Тряпки для пола', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Картошка', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Куриные ножки', 'description' => '', 'quantity' => 7, 'quantity_type' => 'шт', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Чеснок', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Куриное филе', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Плавленный сыр', 'description' => 'der Schmelzkäse', 'quantity' => 2, 'quantity_type' => 'шт', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Шампиньоны', 'description' => '', 'quantity' => 300, 'quantity_type' => 'гр', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Супный набор', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Куриный бульон', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Бананы по скидке', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Сыр по скидке', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Йогурт Müller по скидке', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Хлеб', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                ],
            ],
            [
                'name' => 'Покупки от 23 марта 2024 г.', 'icon' => null, 'order_column' => 37,
                'created_at' => '2024-03-22 14:21:14', 'updated_at' => '2024-03-22 14:21:14',
                'items' => [
                    ['name' => 'Средство для мытья посуды', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Сливочное масло', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Имбирь', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Курица', 'description' => '', 'quantity' => 500, 'quantity_type' => 'гр', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Грибы', 'description' => '', 'quantity' => 700, 'quantity_type' => 'гр', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Морковь', 'description' => '', 'quantity' => 3, 'quantity_type' => 'шт', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Лук репчатый', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Картофель', 'description' => '', 'quantity' => 1, 'quantity_type' => 'мешок', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                    ['name' => 'Хлеб', 'description' => '', 'quantity' => 0, 'quantity_type' => '', 'price' => 0, 'is_starred' => false, 'is_done' => false],
                ],
            ],
        ];
    }
}
