<?php

declare(strict_types=1);

namespace Tests\Feature\Shopping;

use App\Models\Folder;
use App\Models\ShoppingList;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FolderCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    public function test_can_create_folder(): void
    {
        $response = $this->actingAs($this->user)->post(route('shopping.folders.store'), [
            'name' => 'Продукты',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('folders', [
            'user_id' => $this->user->id,
            'name' => 'Продукты',
        ]);
    }

    public function test_can_create_folder_with_icon(): void
    {
        $response = $this->actingAs($this->user)->post(route('shopping.folders.store'), [
            'name' => 'Продукты',
            'icon' => '🛒',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('folders', [
            'user_id' => $this->user->id,
            'name' => 'Продукты',
            'icon' => '🛒',
        ]);
    }

    public function test_name_is_required(): void
    {
        $response = $this->actingAs($this->user)->post(route('shopping.folders.store'), [
            'icon' => '🛒',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_can_update_folder(): void
    {
        $folder = Folder::factory()->for($this->user)->create(['name' => 'Старое']);

        $response = $this->actingAs($this->user)->patch(route('shopping.folders.update', $folder), [
            'name' => 'Новое',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('folders', [
            'id' => $folder->id,
            'name' => 'Новое',
        ]);
    }

    public function test_cannot_update_other_users_folder(): void
    {
        $otherUser = User::factory()->create();
        $otherFolder = Folder::factory()->for($otherUser)->create();

        $response = $this->actingAs($this->user)->patch(route('shopping.folders.update', $otherFolder), [
            'name' => 'Хакнутая',
        ]);

        $response->assertForbidden();
    }

    public function test_can_delete_folder(): void
    {
        $folder = Folder::factory()->for($this->user)->create();

        $response = $this->actingAs($this->user)->delete(route('shopping.folders.destroy', $folder));

        $response->assertRedirect();
        $this->assertDatabaseMissing('folders', ['id' => $folder->id]);
    }

    public function test_cannot_delete_other_users_folder(): void
    {
        $otherUser = User::factory()->create();
        $otherFolder = Folder::factory()->for($otherUser)->create();

        $response = $this->actingAs($this->user)->delete(route('shopping.folders.destroy', $otherFolder));

        $response->assertForbidden();
    }

    public function test_deleting_folder_nullifies_lists_folder_id(): void
    {
        $folder = Folder::factory()->for($this->user)->create();
        $list = ShoppingList::factory()->for($this->user)->create([
            'folder_id' => $folder->id,
        ]);

        $response = $this->actingAs($this->user)->delete(route('shopping.folders.destroy', $folder));

        $response->assertRedirect();
        $this->assertDatabaseMissing('folders', ['id' => $folder->id]);
        $this->assertDatabaseHas('shopping_lists', [
            'id' => $list->id,
            'folder_id' => null,
        ]);
    }

    public function test_can_reorder_folders(): void
    {
        $folder1 = Folder::factory()->for($this->user)->create();
        $folder2 = Folder::factory()->for($this->user)->create();

        $response = $this->actingAs($this->user)->post(route('shopping.folders.reorder'), [
            'ids' => [$folder2->id, $folder1->id],
        ]);

        $response->assertRedirect();

        $folder1->refresh();
        $folder2->refresh();

        $this->assertTrue($folder2->order_column < $folder1->order_column);
    }
}
