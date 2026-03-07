<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\Folder;
use App\Models\ShoppingItem;
use App\Models\ShoppingList;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ShoppingItemApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Folder $folder;

    private ShoppingList $list;

    private ShoppingItem $item;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->folder = Folder::factory()->for($this->user)->create();
        $this->list = ShoppingList::factory()->for($this->user)->for($this->folder)->create();
        $this->item = ShoppingItem::factory()->for($this->user)->for($this->list)->create(['name' => 'Молоко']);
    }

    public function test_unauthenticated_user_cannot_access_items(): void
    {
        $response = $this->getJson('/api/v1/items');

        $response->assertUnauthorized();
    }

    public function test_index_returns_all_items(): void
    {
        Sanctum::actingAs($this->user);

        ShoppingItem::factory()->for($this->user)->for($this->list)->create();

        $response = $this->getJson('/api/v1/items');

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/vnd.api+json')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'type',
                        'id',
                        'attributes' => ['name', 'quantity', 'is_done', 'is_starred'],
                    ],
                ],
            ]);

        $response->assertJsonPath('data.0.type', 'items');
        $response->assertJsonCount(2, 'data');
    }

    public function test_index_does_not_return_other_users_items(): void
    {
        Sanctum::actingAs($this->user);

        $otherUser = User::factory()->create();
        $otherFolder = Folder::factory()->for($otherUser)->create();
        $otherList = ShoppingList::factory()->for($otherUser)->for($otherFolder)->create();
        ShoppingItem::factory()->for($otherUser)->for($otherList)->create();

        $response = $this->getJson('/api/v1/items');

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_store_creates_item(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/items', [
            'data' => [
                'type' => 'items',
                'attributes' => [
                    'shopping_list_id' => $this->list->id,
                    'name' => 'Хлеб',
                    'quantity' => 2,
                    'quantity_type' => 'шт',
                    'price' => 50,
                ],
            ],
        ]);

        $response->assertCreated()
            ->assertHeader('Content-Type', 'application/vnd.api+json')
            ->assertJsonPath('data.type', 'items')
            ->assertJsonPath('data.attributes.name', 'Хлеб')
            ->assertJsonPath('data.attributes.quantity', 2)
            ->assertJsonPath('data.attributes.price', 50);

        $this->assertDatabaseHas('shopping_items', [
            'user_id' => $this->user->id,
            'shopping_list_id' => $this->list->id,
            'name' => 'Хлеб',
        ]);
    }

    public function test_store_validates_required_fields(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/items', [
            'data' => [
                'type' => 'items',
                'attributes' => [],
            ],
        ]);

        $response->assertUnprocessable();
    }

    public function test_store_rejects_other_users_list(): void
    {
        Sanctum::actingAs($this->user);

        $otherUser = User::factory()->create();
        $otherFolder = Folder::factory()->for($otherUser)->create();
        $otherList = ShoppingList::factory()->for($otherUser)->for($otherFolder)->create();

        $response = $this->postJson('/api/v1/items', [
            'data' => [
                'type' => 'items',
                'attributes' => [
                    'shopping_list_id' => $otherList->id,
                    'name' => 'Хак',
                ],
            ],
        ]);

        $response->assertForbidden();
    }

    public function test_show_returns_single_item(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson("/api/v1/items/{$this->item->id}");

        $response->assertOk()
            ->assertJsonPath('data.type', 'items')
            ->assertJsonPath('data.id', (string) $this->item->id)
            ->assertJsonPath('data.attributes.name', 'Молоко');
    }

    public function test_show_forbidden_for_other_user(): void
    {
        Sanctum::actingAs($this->user);

        $otherUser = User::factory()->create();
        $otherFolder = Folder::factory()->for($otherUser)->create();
        $otherList = ShoppingList::factory()->for($otherUser)->for($otherFolder)->create();
        $otherItem = ShoppingItem::factory()->for($otherUser)->for($otherList)->create();

        $response = $this->getJson("/api/v1/items/{$otherItem->id}");

        $response->assertForbidden();
    }

    public function test_show_with_include_list(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson("/api/v1/items/{$this->item->id}?include=list");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'relationships' => ['list'],
                ],
                'included' => [
                    '*' => ['type', 'id', 'attributes'],
                ],
            ]);

        $response->assertJsonPath('included.0.type', 'lists');
    }

    public function test_update_modifies_item(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->patchJson("/api/v1/items/{$this->item->id}", [
            'data' => [
                'id' => (string) $this->item->id,
                'type' => 'items',
                'attributes' => [
                    'name' => 'Кефир',
                    'quantity' => 3,
                ],
            ],
        ]);

        $response->assertOk()
            ->assertJsonPath('data.attributes.name', 'Кефир')
            ->assertJsonPath('data.attributes.quantity', 3);
    }

    public function test_update_marks_item_as_done(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->patchJson("/api/v1/items/{$this->item->id}", [
            'data' => [
                'id' => (string) $this->item->id,
                'type' => 'items',
                'attributes' => [
                    'is_done' => true,
                ],
            ],
        ]);

        $response->assertOk()
            ->assertJsonPath('data.attributes.is_done', true);
    }

    public function test_update_forbidden_for_other_user(): void
    {
        Sanctum::actingAs($this->user);

        $otherUser = User::factory()->create();
        $otherFolder = Folder::factory()->for($otherUser)->create();
        $otherList = ShoppingList::factory()->for($otherUser)->for($otherFolder)->create();
        $otherItem = ShoppingItem::factory()->for($otherUser)->for($otherList)->create();

        $response = $this->patchJson("/api/v1/items/{$otherItem->id}", [
            'data' => [
                'id' => (string) $otherItem->id,
                'type' => 'items',
                'attributes' => ['name' => 'Хак'],
            ],
        ]);

        $response->assertForbidden();
    }

    public function test_destroy_deletes_item(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->deleteJson("/api/v1/items/{$this->item->id}");

        $response->assertNoContent();
        $this->assertDatabaseMissing('shopping_items', ['id' => $this->item->id]);
    }

    public function test_destroy_forbidden_for_other_user(): void
    {
        Sanctum::actingAs($this->user);

        $otherUser = User::factory()->create();
        $otherFolder = Folder::factory()->for($otherUser)->create();
        $otherList = ShoppingList::factory()->for($otherUser)->for($otherFolder)->create();
        $otherItem = ShoppingItem::factory()->for($otherUser)->for($otherList)->create();

        $response = $this->deleteJson("/api/v1/items/{$otherItem->id}");

        $response->assertForbidden();
    }

    public function test_from_list_returns_items_in_list(): void
    {
        Sanctum::actingAs($this->user);

        $list2 = ShoppingList::factory()->for($this->user)->for($this->folder)->create();
        ShoppingItem::factory()->for($this->user)->for($list2)->create();

        $response = $this->getJson("/api/v1/lists/{$this->list->id}/items");

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.attributes.shopping_list_id', $this->list->id);
    }

    public function test_from_list_forbidden_for_other_user(): void
    {
        Sanctum::actingAs($this->user);

        $otherUser = User::factory()->create();
        $otherFolder = Folder::factory()->for($otherUser)->create();
        $otherList = ShoppingList::factory()->for($otherUser)->for($otherFolder)->create();

        $response = $this->getJson("/api/v1/lists/{$otherList->id}/items");

        $response->assertForbidden();
    }

    public function test_uncross_all_resets_done_status(): void
    {
        Sanctum::actingAs($this->user);

        ShoppingItem::factory()->for($this->user)->for($this->list)->done()->count(3)->create();

        $response = $this->patchJson("/api/v1/lists/{$this->list->id}/items/undone");

        $response->assertNoContent();

        $doneCount = $this->list->items()->where('is_done', true)->count();
        $this->assertEquals(0, $doneCount);
    }

    public function test_uncross_all_forbidden_for_other_user(): void
    {
        Sanctum::actingAs($this->user);

        $otherUser = User::factory()->create();
        $otherFolder = Folder::factory()->for($otherUser)->create();
        $otherList = ShoppingList::factory()->for($otherUser)->for($otherFolder)->create();

        $response = $this->patchJson("/api/v1/lists/{$otherList->id}/items/undone");

        $response->assertForbidden();
    }

    public function test_destroy_all_deletes_all_items_in_list(): void
    {
        Sanctum::actingAs($this->user);

        ShoppingItem::factory()->for($this->user)->for($this->list)->count(3)->create();

        $response = $this->deleteJson("/api/v1/lists/{$this->list->id}/items");

        $response->assertNoContent();

        $this->assertEquals(0, $this->list->items()->count());
    }

    public function test_destroy_all_does_not_affect_other_lists(): void
    {
        Sanctum::actingAs($this->user);

        ShoppingItem::factory()->for($this->user)->for($this->list)->count(2)->create();

        $list2 = ShoppingList::factory()->for($this->user)->for($this->folder)->create();
        ShoppingItem::factory()->for($this->user)->for($list2)->count(2)->create();

        $response = $this->deleteJson("/api/v1/lists/{$this->list->id}/items");

        $response->assertNoContent();

        $this->assertEquals(0, $this->list->items()->count());
        $this->assertEquals(2, $list2->items()->count());
    }
}
