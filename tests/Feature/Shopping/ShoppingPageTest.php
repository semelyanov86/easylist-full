<?php

declare(strict_types=1);

namespace Tests\Feature\Shopping;

use App\Models\Folder;
use App\Models\ShoppingItem;
use App\Models\ShoppingList;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class ShoppingPageTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Folder $folder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->folder = Folder::factory()->for($this->user)->create(['name' => 'Продукты']);
    }

    public function test_unauthenticated_user_is_redirected(): void
    {
        $response = $this->get(route('shopping.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_page_renders_with_folders_and_lists(): void
    {
        ShoppingList::factory()->for($this->user)->create([
            'folder_id' => null,
            'name' => 'Еженедельный',
        ]);

        $response = $this->actingAs($this->user)->get(route('shopping.index'));

        $response->assertOk();
        $response->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('shopping/Index')
                ->has('folders', 1)
                ->has('lists', 1)
                ->where('selectedFolderId', null)
                ->where('selectedList', null)
        );
    }

    public function test_filter_by_folder_id(): void
    {
        $otherFolder = Folder::factory()->for($this->user)->create(['name' => 'Хозтовары']);

        ShoppingList::factory()->for($this->user)->create([
            'folder_id' => $this->folder->id,
            'name' => 'Продукты на неделю',
        ]);

        ShoppingList::factory()->for($this->user)->create([
            'folder_id' => $otherFolder->id,
            'name' => 'Уборка',
        ]);

        $response = $this->actingAs($this->user)->get(route('shopping.index', [
            'folder_id' => $this->folder->id,
        ]));

        $response->assertOk();
        $response->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('shopping/Index')
                ->has('lists', 1)
                ->where('selectedFolderId', $this->folder->id)
        );
    }

    public function test_selected_list_loaded_with_items(): void
    {
        $list = ShoppingList::factory()->for($this->user)->create([
            'folder_id' => $this->folder->id,
            'name' => 'Мой список',
        ]);

        ShoppingItem::factory()->for($this->user)->create([
            'shopping_list_id' => $list->id,
            'name' => 'Молоко',
        ]);

        ShoppingItem::factory()->for($this->user)->create([
            'shopping_list_id' => $list->id,
            'name' => 'Хлеб',
        ]);

        $response = $this->actingAs($this->user)->get(route('shopping.index', [
            'list_id' => $list->id,
        ]));

        $response->assertOk();
        $response->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('shopping/Index')
                ->where('selectedList.id', $list->id)
                ->where('selectedList.name', 'Мой список')
                ->has('selectedList.items', 2)
        );
    }

    public function test_without_folder_shows_only_unfoldered_lists(): void
    {
        ShoppingList::factory()->for($this->user)->create([
            'folder_id' => $this->folder->id,
            'name' => 'В папке',
        ]);

        ShoppingList::factory()->for($this->user)->create([
            'folder_id' => null,
            'name' => 'Без папки',
        ]);

        $response = $this->actingAs($this->user)->get(route('shopping.index'));

        $response->assertOk();
        $response->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('shopping/Index')
                ->has('lists', 1)
                ->where('lists.0.name', 'Без папки')
        );
    }

    public function test_other_users_lists_not_visible(): void
    {
        $otherUser = User::factory()->create();

        ShoppingList::factory()->for($otherUser)->create([
            'folder_id' => null,
            'name' => 'Чужой список',
        ]);

        ShoppingList::factory()->for($this->user)->create([
            'folder_id' => null,
            'name' => 'Мой список',
        ]);

        $response = $this->actingAs($this->user)->get(route('shopping.index'));

        $response->assertOk();
        $response->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('shopping/Index')
                ->has('lists', 1)
        );
    }

    public function test_cannot_select_other_users_list(): void
    {
        $otherUser = User::factory()->create();
        $otherFolder = Folder::factory()->for($otherUser)->create();
        $otherList = ShoppingList::factory()->for($otherUser)->create([
            'folder_id' => $otherFolder->id,
        ]);

        $response = $this->actingAs($this->user)->get(route('shopping.index', [
            'list_id' => $otherList->id,
        ]));

        $response->assertOk();
        $response->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('shopping/Index')
                ->where('selectedList', null)
        );
    }
}
