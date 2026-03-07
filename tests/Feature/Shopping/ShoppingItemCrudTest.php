<?php

declare(strict_types=1);

namespace Tests\Feature\Shopping;

use App\Models\Folder;
use App\Models\ShoppingItem;
use App\Models\ShoppingList;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShoppingItemCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Folder $folder;

    private ShoppingList $list;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->folder = Folder::factory()->for($this->user)->create();
        $this->list = ShoppingList::factory()->for($this->user)->create([
            'folder_id' => $this->folder->id,
        ]);
    }

    public function test_can_create_item(): void
    {
        $response = $this->actingAs($this->user)->post(route('shopping.items.store'), [
            'shopping_list_id' => $this->list->id,
            'name' => 'Молоко',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('shopping_items', [
            'user_id' => $this->user->id,
            'shopping_list_id' => $this->list->id,
            'name' => 'Молоко',
        ]);
    }

    public function test_name_is_required(): void
    {
        $response = $this->actingAs($this->user)->post(route('shopping.items.store'), [
            'shopping_list_id' => $this->list->id,
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_shopping_list_id_is_required(): void
    {
        $response = $this->actingAs($this->user)->post(route('shopping.items.store'), [
            'name' => 'Молоко',
        ]);

        $response->assertSessionHasErrors('shopping_list_id');
    }

    public function test_can_create_item_with_all_fields(): void
    {
        $response = $this->actingAs($this->user)->post(route('shopping.items.store'), [
            'shopping_list_id' => $this->list->id,
            'name' => 'Молоко',
            'description' => 'Домик в деревне',
            'quantity' => 2,
            'quantity_type' => 'л',
            'price' => 120,
            'is_starred' => true,
            'is_done' => false,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('shopping_items', [
            'name' => 'Молоко',
            'description' => 'Домик в деревне',
            'quantity' => 2,
            'quantity_type' => 'л',
            'price' => 120,
            'is_starred' => true,
            'is_done' => false,
        ]);
    }

    public function test_can_update_item(): void
    {
        $item = ShoppingItem::factory()->for($this->user)->create([
            'shopping_list_id' => $this->list->id,
            'name' => 'Молоко',
        ]);

        $response = $this->actingAs($this->user)->patch(route('shopping.items.update', $item), [
            'name' => 'Кефир',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('shopping_items', [
            'id' => $item->id,
            'name' => 'Кефир',
        ]);
    }

    public function test_cannot_update_other_users_item(): void
    {
        $otherUser = User::factory()->create();
        $otherFolder = Folder::factory()->for($otherUser)->create();
        $otherList = ShoppingList::factory()->for($otherUser)->create([
            'folder_id' => $otherFolder->id,
        ]);
        $otherItem = ShoppingItem::factory()->for($otherUser)->create([
            'shopping_list_id' => $otherList->id,
        ]);

        $response = $this->actingAs($this->user)->patch(route('shopping.items.update', $otherItem), [
            'name' => 'Хакнутый',
        ]);

        $response->assertForbidden();
    }

    public function test_toggle_done(): void
    {
        $item = ShoppingItem::factory()->for($this->user)->create([
            'shopping_list_id' => $this->list->id,
            'is_done' => false,
        ]);

        $response = $this->actingAs($this->user)->patch(route('shopping.items.toggle', $item));

        $response->assertRedirect();
        $this->assertDatabaseHas('shopping_items', [
            'id' => $item->id,
            'is_done' => true,
        ]);

        // Повторное переключение
        $response = $this->actingAs($this->user)->patch(route('shopping.items.toggle', $item));

        $response->assertRedirect();
        $this->assertDatabaseHas('shopping_items', [
            'id' => $item->id,
            'is_done' => false,
        ]);
    }

    public function test_can_delete_item(): void
    {
        $item = ShoppingItem::factory()->for($this->user)->create([
            'shopping_list_id' => $this->list->id,
        ]);

        $response = $this->actingAs($this->user)->delete(route('shopping.items.destroy', $item));

        $response->assertRedirect();
        $this->assertDatabaseMissing('shopping_items', ['id' => $item->id]);
    }

    public function test_cannot_delete_other_users_item(): void
    {
        $otherUser = User::factory()->create();
        $otherFolder = Folder::factory()->for($otherUser)->create();
        $otherList = ShoppingList::factory()->for($otherUser)->create([
            'folder_id' => $otherFolder->id,
        ]);
        $otherItem = ShoppingItem::factory()->for($otherUser)->create([
            'shopping_list_id' => $otherList->id,
        ]);

        $response = $this->actingAs($this->user)->delete(route('shopping.items.destroy', $otherItem));

        $response->assertForbidden();
    }

    public function test_can_reorder_items(): void
    {
        $item1 = ShoppingItem::factory()->for($this->user)->create([
            'shopping_list_id' => $this->list->id,
        ]);

        $item2 = ShoppingItem::factory()->for($this->user)->create([
            'shopping_list_id' => $this->list->id,
        ]);

        $response = $this->actingAs($this->user)->post(route('shopping.items.reorder'), [
            'ids' => [$item2->id, $item1->id],
        ]);

        $response->assertRedirect();

        $item1->refresh();
        $item2->refresh();

        $this->assertTrue($item2->order_column < $item1->order_column);
    }

    public function test_uncross_all(): void
    {
        ShoppingItem::factory()->for($this->user)->done()->create([
            'shopping_list_id' => $this->list->id,
        ]);

        ShoppingItem::factory()->for($this->user)->done()->create([
            'shopping_list_id' => $this->list->id,
        ]);

        $response = $this->actingAs($this->user)->patch(route('shopping.items.uncross-all', $this->list));

        $response->assertRedirect();

        $allItems = $this->list->items()->get();
        foreach ($allItems as $item) {
            $this->assertFalse($item->is_done);
        }
    }

    public function test_destroy_all(): void
    {
        ShoppingItem::factory()->for($this->user)->count(3)->create([
            'shopping_list_id' => $this->list->id,
        ]);

        $response = $this->actingAs($this->user)->delete(route('shopping.items.destroy-all', $this->list));

        $response->assertRedirect();
        $this->assertEquals(0, $this->list->items()->count());
    }

    public function test_cannot_uncross_other_users_list(): void
    {
        $otherUser = User::factory()->create();
        $otherFolder = Folder::factory()->for($otherUser)->create();
        $otherList = ShoppingList::factory()->for($otherUser)->create([
            'folder_id' => $otherFolder->id,
        ]);

        $response = $this->actingAs($this->user)->patch(route('shopping.items.uncross-all', $otherList));

        $response->assertForbidden();
    }

    public function test_cannot_destroy_all_other_users_items(): void
    {
        $otherUser = User::factory()->create();
        $otherFolder = Folder::factory()->for($otherUser)->create();
        $otherList = ShoppingList::factory()->for($otherUser)->create([
            'folder_id' => $otherFolder->id,
        ]);

        $response = $this->actingAs($this->user)->delete(route('shopping.items.destroy-all', $otherList));

        $response->assertForbidden();
    }
}
