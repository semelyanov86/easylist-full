<?php

declare(strict_types=1);

namespace Tests\Feature\Shopping;

use App\Models\Folder;
use App\Models\ShoppingList;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShoppingListCrudTest extends TestCase
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

    public function test_can_create_list(): void
    {
        $response = $this->actingAs($this->user)->post(route('shopping.lists.store'), [
            'name' => 'Еженедельный',
            'folder_id' => $this->folder->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('shopping_lists', [
            'user_id' => $this->user->id,
            'folder_id' => $this->folder->id,
            'name' => 'Еженедельный',
        ]);
    }

    public function test_name_is_required(): void
    {
        $response = $this->actingAs($this->user)->post(route('shopping.lists.store'), [
            'folder_id' => $this->folder->id,
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_can_create_list_without_folder(): void
    {
        $response = $this->actingAs($this->user)->post(route('shopping.lists.store'), [
            'name' => 'Без папки',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('shopping_lists', [
            'user_id' => $this->user->id,
            'folder_id' => null,
            'name' => 'Без папки',
        ]);
    }

    public function test_folder_id_must_exist(): void
    {
        $response = $this->actingAs($this->user)->post(route('shopping.lists.store'), [
            'name' => 'Список',
            'folder_id' => 99999,
        ]);

        $response->assertSessionHasErrors('folder_id');
    }

    public function test_can_update_list(): void
    {
        $list = ShoppingList::factory()->for($this->user)->create([
            'folder_id' => $this->folder->id,
            'name' => 'Старое название',
        ]);

        $response = $this->actingAs($this->user)->patch(route('shopping.lists.update', $list), [
            'name' => 'Новое название',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('shopping_lists', [
            'id' => $list->id,
            'name' => 'Новое название',
        ]);
    }

    public function test_cannot_update_other_users_list(): void
    {
        $otherUser = User::factory()->create();
        $otherFolder = Folder::factory()->for($otherUser)->create();
        $otherList = ShoppingList::factory()->for($otherUser)->create([
            'folder_id' => $otherFolder->id,
        ]);

        $response = $this->actingAs($this->user)->patch(route('shopping.lists.update', $otherList), [
            'name' => 'Хакнутый',
        ]);

        $response->assertForbidden();
    }

    public function test_can_delete_list(): void
    {
        $list = ShoppingList::factory()->for($this->user)->create([
            'folder_id' => $this->folder->id,
        ]);

        $response = $this->actingAs($this->user)->delete(route('shopping.lists.destroy', $list));

        $response->assertRedirect();
        $this->assertDatabaseMissing('shopping_lists', ['id' => $list->id]);
    }

    public function test_cannot_delete_other_users_list(): void
    {
        $otherUser = User::factory()->create();
        $otherFolder = Folder::factory()->for($otherUser)->create();
        $otherList = ShoppingList::factory()->for($otherUser)->create([
            'folder_id' => $otherFolder->id,
        ]);

        $response = $this->actingAs($this->user)->delete(route('shopping.lists.destroy', $otherList));

        $response->assertForbidden();
    }

    public function test_can_reorder_lists(): void
    {
        $list1 = ShoppingList::factory()->for($this->user)->create([
            'folder_id' => $this->folder->id,
        ]);

        $list2 = ShoppingList::factory()->for($this->user)->create([
            'folder_id' => $this->folder->id,
        ]);

        $response = $this->actingAs($this->user)->post(route('shopping.lists.reorder'), [
            'ids' => [$list2->id, $list1->id],
        ]);

        $response->assertRedirect();

        $list1->refresh();
        $list2->refresh();

        $this->assertTrue($list2->order_column < $list1->order_column);
    }
}
