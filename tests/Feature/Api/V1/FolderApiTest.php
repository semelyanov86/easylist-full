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

class FolderApiTest extends TestCase
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

    public function test_unauthenticated_user_cannot_access_folders(): void
    {
        $response = $this->getJson('/api/v1/folders');

        $response->assertUnauthorized();
    }

    public function test_index_returns_all_folders(): void
    {
        Sanctum::actingAs($this->user);

        Folder::factory()->for($this->user)->create();

        $response = $this->getJson('/api/v1/folders');

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/vnd.api+json')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'type',
                        'id',
                        'attributes' => ['name', 'icon', 'order_column'],
                    ],
                ],
            ]);

        $response->assertJsonPath('data.0.type', 'folders');
        $response->assertJsonCount(2, 'data');
    }

    public function test_index_does_not_return_other_users_folders(): void
    {
        Sanctum::actingAs($this->user);

        $otherUser = User::factory()->create();
        Folder::factory()->for($otherUser)->create();

        $response = $this->getJson('/api/v1/folders');

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_index_with_include_lists(): void
    {
        Sanctum::actingAs($this->user);

        ShoppingList::factory()->for($this->user)->for($this->folder)->create();

        $response = $this->getJson('/api/v1/folders?include=lists');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'type',
                        'id',
                        'attributes',
                        'relationships' => ['lists'],
                    ],
                ],
                'included' => [
                    '*' => ['type', 'id', 'attributes'],
                ],
            ]);

        $response->assertJsonPath('included.0.type', 'lists');
    }

    public function test_store_creates_folder(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/folders', [
            'data' => [
                'type' => 'folders',
                'attributes' => [
                    'name' => 'Бытовая химия',
                    'icon' => '🧹',
                ],
            ],
        ]);

        $response->assertCreated()
            ->assertHeader('Content-Type', 'application/vnd.api+json')
            ->assertJsonPath('data.type', 'folders')
            ->assertJsonPath('data.attributes.name', 'Бытовая химия')
            ->assertJsonPath('data.attributes.icon', '🧹');

        $this->assertDatabaseHas('folders', [
            'user_id' => $this->user->id,
            'name' => 'Бытовая химия',
        ]);
    }

    public function test_store_validates_required_name(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/folders', [
            'data' => [
                'type' => 'folders',
                'attributes' => [],
            ],
        ]);

        $response->assertUnprocessable();
    }

    public function test_show_returns_single_folder(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson("/api/v1/folders/{$this->folder->id}");

        $response->assertOk()
            ->assertJsonPath('data.type', 'folders')
            ->assertJsonPath('data.id', (string) $this->folder->id)
            ->assertJsonPath('data.attributes.name', 'Продукты');
    }

    public function test_show_forbidden_for_other_user(): void
    {
        Sanctum::actingAs($this->user);

        $otherUser = User::factory()->create();
        $otherFolder = Folder::factory()->for($otherUser)->create();

        $response = $this->getJson("/api/v1/folders/{$otherFolder->id}");

        $response->assertForbidden();
    }

    public function test_update_modifies_folder(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->patchJson("/api/v1/folders/{$this->folder->id}", [
            'data' => [
                'id' => (string) $this->folder->id,
                'type' => 'folders',
                'attributes' => [
                    'name' => 'Обновлённая папка',
                ],
            ],
        ]);

        $response->assertOk()
            ->assertJsonPath('data.attributes.name', 'Обновлённая папка');

        $this->assertDatabaseHas('folders', [
            'id' => $this->folder->id,
            'name' => 'Обновлённая папка',
        ]);
    }

    public function test_update_forbidden_for_other_user(): void
    {
        Sanctum::actingAs($this->user);

        $otherUser = User::factory()->create();
        $otherFolder = Folder::factory()->for($otherUser)->create();

        $response = $this->patchJson("/api/v1/folders/{$otherFolder->id}", [
            'data' => [
                'id' => (string) $otherFolder->id,
                'type' => 'folders',
                'attributes' => ['name' => 'Хак'],
            ],
        ]);

        $response->assertForbidden();
    }

    public function test_destroy_deletes_folder(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->deleteJson("/api/v1/folders/{$this->folder->id}");

        $response->assertNoContent();
        $this->assertDatabaseMissing('folders', ['id' => $this->folder->id]);
    }

    public function test_destroy_forbidden_for_other_user(): void
    {
        Sanctum::actingAs($this->user);

        $otherUser = User::factory()->create();
        $otherFolder = Folder::factory()->for($otherUser)->create();

        $response = $this->deleteJson("/api/v1/folders/{$otherFolder->id}");

        $response->assertForbidden();
    }

    public function test_destroy_nullifies_lists_folder_id(): void
    {
        Sanctum::actingAs($this->user);

        $list = ShoppingList::factory()->for($this->user)->for($this->folder)->create();
        $item = ShoppingItem::factory()->for($this->user)->for($list)->create();

        $response = $this->deleteJson("/api/v1/folders/{$this->folder->id}");

        $response->assertNoContent();
        $this->assertDatabaseMissing('folders', ['id' => $this->folder->id]);
        $this->assertDatabaseHas('shopping_lists', [
            'id' => $list->id,
            'folder_id' => null,
        ]);
        $this->assertDatabaseHas('shopping_items', ['id' => $item->id]);
    }

    public function test_folders_are_ordered_by_order_column(): void
    {
        Sanctum::actingAs($this->user);

        $folder2 = Folder::factory()->for($this->user)->create();

        $response = $this->getJson('/api/v1/folders');

        $response->assertOk();

        /** @var list<array{id: string}> $data */
        $data = $response->json('data');
        $ids = array_map(fn (array $item): string => $item['id'], $data);
        $this->assertEquals([(string) $this->folder->id, (string) $folder2->id], $ids);
    }

    public function test_index_rejects_invalid_include(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/v1/folders?include=invalid');

        $response->assertStatus(400);
    }
}
