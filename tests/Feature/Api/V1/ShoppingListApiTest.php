<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\Folder;
use App\Models\ShoppingItem;
use App\Models\ShoppingList;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ShoppingListApiTest extends TestCase
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
        $this->list = ShoppingList::factory()->for($this->user)->for($this->folder)->create(['name' => 'Еженедельный']);
    }

    public function test_unauthenticated_user_cannot_access_lists(): void
    {
        $response = $this->getJson('/api/v1/lists');

        $response->assertUnauthorized();
    }

    public function test_index_returns_all_lists(): void
    {
        Sanctum::actingAs($this->user);

        ShoppingList::factory()->for($this->user)->for($this->folder)->create();

        $response = $this->getJson('/api/v1/lists');

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/vnd.api+json')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'type',
                        'id',
                        'attributes' => ['name', 'icon', 'folder_id', 'is_public', 'order_column'],
                    ],
                ],
            ]);

        $response->assertJsonPath('data.0.type', 'lists');
        $response->assertJsonCount(2, 'data');
    }

    public function test_index_does_not_return_other_users_lists(): void
    {
        Sanctum::actingAs($this->user);

        $otherUser = User::factory()->create();
        $otherFolder = Folder::factory()->for($otherUser)->create();
        ShoppingList::factory()->for($otherUser)->for($otherFolder)->create();

        $response = $this->getJson('/api/v1/lists');

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_store_creates_list(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/lists', [
            'data' => [
                'type' => 'lists',
                'attributes' => [
                    'folder_id' => $this->folder->id,
                    'name' => 'Новый список',
                ],
            ],
        ]);

        $response->assertCreated()
            ->assertHeader('Content-Type', 'application/vnd.api+json')
            ->assertJsonPath('data.type', 'lists')
            ->assertJsonPath('data.attributes.name', 'Новый список');

        $this->assertDatabaseHas('shopping_lists', [
            'user_id' => $this->user->id,
            'folder_id' => $this->folder->id,
            'name' => 'Новый список',
        ]);
    }

    public function test_store_validates_required_fields(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/lists', [
            'data' => [
                'type' => 'lists',
                'attributes' => [],
            ],
        ]);

        $response->assertUnprocessable();
    }

    public function test_store_rejects_other_users_folder(): void
    {
        Sanctum::actingAs($this->user);

        $otherUser = User::factory()->create();
        $otherFolder = Folder::factory()->for($otherUser)->create();

        $response = $this->postJson('/api/v1/lists', [
            'data' => [
                'type' => 'lists',
                'attributes' => [
                    'folder_id' => $otherFolder->id,
                    'name' => 'Хак',
                ],
            ],
        ]);

        $response->assertForbidden();
    }

    public function test_show_returns_single_list(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson("/api/v1/lists/{$this->list->id}");

        $response->assertOk()
            ->assertJsonPath('data.type', 'lists')
            ->assertJsonPath('data.id', (string) $this->list->id)
            ->assertJsonPath('data.attributes.name', 'Еженедельный');
    }

    public function test_show_forbidden_for_other_user(): void
    {
        Sanctum::actingAs($this->user);

        $otherUser = User::factory()->create();
        $otherFolder = Folder::factory()->for($otherUser)->create();
        $otherList = ShoppingList::factory()->for($otherUser)->for($otherFolder)->create();

        $response = $this->getJson("/api/v1/lists/{$otherList->id}");

        $response->assertForbidden();
    }

    public function test_show_with_include_items(): void
    {
        Sanctum::actingAs($this->user);

        ShoppingItem::factory()->for($this->user)->for($this->list)->create();

        $response = $this->getJson("/api/v1/lists/{$this->list->id}?include=items");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'relationships' => ['items'],
                ],
                'included' => [
                    '*' => ['type', 'id', 'attributes'],
                ],
            ]);

        $response->assertJsonPath('included.0.type', 'items');
    }

    public function test_update_modifies_list(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->patchJson("/api/v1/lists/{$this->list->id}", [
            'data' => [
                'id' => (string) $this->list->id,
                'type' => 'lists',
                'attributes' => [
                    'name' => 'Обновлённый список',
                ],
            ],
        ]);

        $response->assertOk()
            ->assertJsonPath('data.attributes.name', 'Обновлённый список');
    }

    public function test_update_sets_link_when_making_public(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->patchJson("/api/v1/lists/{$this->list->id}", [
            'data' => [
                'id' => (string) $this->list->id,
                'type' => 'lists',
                'attributes' => [
                    'is_public' => true,
                ],
            ],
        ]);

        $response->assertOk()
            ->assertJsonPath('data.attributes.is_public', true);

        $this->list->refresh();
        $this->assertNotNull($this->list->link);
    }

    public function test_update_clears_link_when_making_private(): void
    {
        Sanctum::actingAs($this->user);

        $this->list->update(['is_public' => true, 'link' => Str::uuid()->toString()]);

        $response = $this->patchJson("/api/v1/lists/{$this->list->id}", [
            'data' => [
                'id' => (string) $this->list->id,
                'type' => 'lists',
                'attributes' => [
                    'is_public' => false,
                ],
            ],
        ]);

        $response->assertOk()
            ->assertJsonPath('data.attributes.is_public', false)
            ->assertJsonPath('data.attributes.link', null);
    }

    public function test_destroy_deletes_list(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->deleteJson("/api/v1/lists/{$this->list->id}");

        $response->assertNoContent();
        $this->assertDatabaseMissing('shopping_lists', ['id' => $this->list->id]);
    }

    public function test_destroy_cascades_to_items(): void
    {
        Sanctum::actingAs($this->user);

        ShoppingItem::factory()->for($this->user)->for($this->list)->count(3)->create();

        $response = $this->deleteJson("/api/v1/lists/{$this->list->id}");

        $response->assertNoContent();
        $this->assertDatabaseCount('shopping_items', 0);
    }

    public function test_from_folder_returns_lists_in_folder(): void
    {
        Sanctum::actingAs($this->user);

        $folder2 = Folder::factory()->for($this->user)->create();
        ShoppingList::factory()->for($this->user)->for($folder2)->create();

        $response = $this->getJson("/api/v1/folders/{$this->folder->id}/lists");

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.attributes.folder_id', $this->folder->id);
    }

    public function test_from_folder_forbidden_for_other_user(): void
    {
        Sanctum::actingAs($this->user);

        $otherUser = User::factory()->create();
        $otherFolder = Folder::factory()->for($otherUser)->create();

        $response = $this->getJson("/api/v1/folders/{$otherFolder->id}/lists");

        $response->assertForbidden();
    }

    public function test_public_show_returns_shared_list(): void
    {
        $uuid = Str::uuid()->toString();
        $this->list->update(['is_public' => true, 'link' => $uuid]);
        ShoppingItem::factory()->for($this->user)->for($this->list)->create();

        $response = $this->getJson("/api/v1/links/{$uuid}");

        $response->assertOk()
            ->assertJsonPath('data.type', 'lists')
            ->assertJsonStructure([
                'included' => [
                    '*' => ['type', 'id', 'attributes'],
                ],
            ]);
    }

    public function test_public_show_returns_404_for_private_list(): void
    {
        $response = $this->getJson('/api/v1/links/' . Str::uuid()->toString());

        $response->assertNotFound();
    }

    public function test_send_email_sends_list(): void
    {
        Sanctum::actingAs($this->user);

        ShoppingItem::factory()->for($this->user)->for($this->list)->create(['name' => 'Молоко']);

        /** @var array<int, array{to: string, subject: string}> $sentEmails */
        $sentEmails = [];

        Mail::shouldReceive('raw')
            ->once()
            ->andReturnUsing(function (string $text, callable $callback) use (&$sentEmails): void {
                $message = new \Illuminate\Mail\Message(new \Symfony\Component\Mime\Email());
                $callback($message);
                $sentEmails[] = ['text' => $text];
            });

        $response = $this->postJson("/api/v1/lists/{$this->list->id}/email", [
            'data' => [
                'type' => 'emails',
                'attributes' => [
                    'email' => 'test@example.com',
                ],
            ],
        ]);

        $response->assertNoContent();
        $this->assertNotEmpty($sentEmails);
    }

    public function test_send_email_validates_email(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson("/api/v1/lists/{$this->list->id}/email", [
            'data' => [
                'type' => 'emails',
                'attributes' => [
                    'email' => 'not-an-email',
                ],
            ],
        ]);

        $response->assertUnprocessable();
    }
}
