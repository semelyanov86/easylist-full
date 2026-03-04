<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\Job;
use App\Models\JobCategory;
use App\Models\JobStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class ContactTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_contact_on_own_job(): void
    {
        $user = User::factory()->create();
        $job = $this->createJobForUser($user);

        $response = $this->actingAs($user)->post(route('contacts.store', $job), [
            'first_name' => 'Иван',
            'last_name' => 'Иванов',
            'position' => 'HR-менеджер',
            'email' => 'ivan@example.com',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('contacts', [
            'job_id' => $job->id,
            'user_id' => $user->id,
            'first_name' => 'Иван',
            'last_name' => 'Иванов',
            'position' => 'HR-менеджер',
            'email' => 'ivan@example.com',
        ]);
    }

    public function test_user_cannot_create_contact_on_another_users_job(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $job = $this->createJobForUser($owner);

        $response = $this->actingAs($otherUser)->post(route('contacts.store', $job), [
            'first_name' => 'Иван',
            'last_name' => 'Иванов',
        ]);

        $response->assertForbidden();
    }

    public function test_first_name_is_required(): void
    {
        $user = User::factory()->create();
        $job = $this->createJobForUser($user);

        $response = $this->actingAs($user)->post(route('contacts.store', $job), [
            'last_name' => 'Иванов',
        ]);

        $response->assertSessionHasErrors('first_name');
    }

    public function test_last_name_is_required(): void
    {
        $user = User::factory()->create();
        $job = $this->createJobForUser($user);

        $response = $this->actingAs($user)->post(route('contacts.store', $job), [
            'first_name' => 'Иван',
        ]);

        $response->assertSessionHasErrors('last_name');
    }

    public function test_email_must_be_valid(): void
    {
        $user = User::factory()->create();
        $job = $this->createJobForUser($user);

        $response = $this->actingAs($user)->post(route('contacts.store', $job), [
            'first_name' => 'Иван',
            'last_name' => 'Иванов',
            'email' => 'not-an-email',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_url_fields_must_be_valid_urls(): void
    {
        $user = User::factory()->create();
        $job = $this->createJobForUser($user);

        $response = $this->actingAs($user)->post(route('contacts.store', $job), [
            'first_name' => 'Иван',
            'last_name' => 'Иванов',
            'linkedin_url' => 'not-a-url',
            'facebook_url' => 'also-not-url',
            'whatsapp_url' => 'invalid',
        ]);

        $response->assertSessionHasErrors(['linkedin_url', 'facebook_url', 'whatsapp_url']);
    }

    public function test_user_can_update_own_contact(): void
    {
        $user = User::factory()->create();
        $job = $this->createJobForUser($user);
        $contact = Contact::factory()->create([
            'job_id' => $job->id,
            'user_id' => $user->id,
            'first_name' => 'Иван',
            'last_name' => 'Иванов',
        ]);

        $response = $this->actingAs($user)->patch(route('contacts.update', $contact), [
            'first_name' => 'Пётр',
            'last_name' => 'Петров',
            'position' => 'CTO',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'first_name' => 'Пётр',
            'last_name' => 'Петров',
            'position' => 'CTO',
        ]);
    }

    public function test_user_cannot_update_another_users_contact(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $job = $this->createJobForUser($owner);
        $contact = Contact::factory()->create([
            'job_id' => $job->id,
            'user_id' => $owner->id,
        ]);

        $response = $this->actingAs($otherUser)->patch(route('contacts.update', $contact), [
            'first_name' => 'Попытка',
            'last_name' => 'Изменить',
        ]);

        $response->assertForbidden();
    }

    public function test_user_can_delete_own_contact(): void
    {
        $user = User::factory()->create();
        $job = $this->createJobForUser($user);
        $contact = Contact::factory()->create([
            'job_id' => $job->id,
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->delete(route('contacts.destroy', $contact));

        $response->assertRedirect();
        $this->assertDatabaseMissing('contacts', ['id' => $contact->id]);
    }

    public function test_user_cannot_delete_another_users_contact(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $job = $this->createJobForUser($owner);
        $contact = Contact::factory()->create([
            'job_id' => $job->id,
            'user_id' => $owner->id,
        ]);

        $response = $this->actingAs($otherUser)->delete(route('contacts.destroy', $contact));

        $response->assertForbidden();
    }

    public function test_guest_cannot_create_contact(): void
    {
        $user = User::factory()->create();
        $job = $this->createJobForUser($user);

        $response = $this->post(route('contacts.store', $job), [
            'first_name' => 'Иван',
            'last_name' => 'Иванов',
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_contacts_cascade_deleted_when_job_force_deleted(): void
    {
        $user = User::factory()->create();
        $job = $this->createJobForUser($user);
        $contact = Contact::factory()->create([
            'job_id' => $job->id,
            'user_id' => $user->id,
        ]);

        $job->forceDelete();

        $this->assertDatabaseMissing('contacts', ['id' => $contact->id]);
    }

    public function test_activity_log_created_on_contact_added(): void
    {
        $user = User::factory()->create();
        $job = $this->createJobForUser($user);

        $this->actingAs($user)->post(route('contacts.store', $job), [
            'first_name' => 'Иван',
            'last_name' => 'Иванов',
        ]);

        $activity = Activity::query()
            ->where('log_name', 'job')
            ->where('event', 'contact_added')
            ->where('subject_id', $job->id)
            ->latest()
            ->first();

        $this->assertNotNull($activity);
        $this->assertInstanceOf(Activity::class, $activity);
        $this->assertSame('Добавлен контакт', $activity->description);
        $this->assertSame('Иван Иванов', $activity->getExtraProperty('contact_name'));
    }

    public function test_activity_log_created_on_contact_removed(): void
    {
        $user = User::factory()->create();
        $job = $this->createJobForUser($user);
        $contact = Contact::factory()->create([
            'job_id' => $job->id,
            'user_id' => $user->id,
            'first_name' => 'Мария',
            'last_name' => 'Петрова',
        ]);

        $this->actingAs($user)->delete(route('contacts.destroy', $contact));

        $activity = Activity::query()
            ->where('log_name', 'job')
            ->where('event', 'contact_removed')
            ->where('subject_id', $job->id)
            ->latest()
            ->first();

        $this->assertNotNull($activity);
        $this->assertInstanceOf(Activity::class, $activity);
        $this->assertSame('Удалён контакт', $activity->description);
        $this->assertSame('Мария Петрова', $activity->getExtraProperty('contact_name'));
    }

    public function test_optional_fields_can_be_null(): void
    {
        $user = User::factory()->create();
        $job = $this->createJobForUser($user);

        $response = $this->actingAs($user)->post(route('contacts.store', $job), [
            'first_name' => 'Иван',
            'last_name' => 'Иванов',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('contacts', [
            'job_id' => $job->id,
            'first_name' => 'Иван',
            'last_name' => 'Иванов',
            'position' => null,
            'city' => null,
            'email' => null,
            'phone' => null,
            'description' => null,
            'linkedin_url' => null,
            'facebook_url' => null,
            'whatsapp_url' => null,
        ]);
    }

    private function createJobForUser(User $user): Job
    {
        $status = JobStatus::factory()->for($user)->create();
        $category = JobCategory::factory()->for($user)->create();

        return Job::factory()->for($user)->create([
            'job_status_id' => $status->id,
            'job_category_id' => $category->id,
        ]);
    }
}
