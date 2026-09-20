<?php

namespace Tests\Feature;

use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvitationTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $response = $this->get(route('invitations.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_user_can_create_an_invitation_with_selected_sections(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('invitations.store'), [
            'title' => 'Pernikahan Adit dan Ayu', 'slug' => 'adit-ayu', 'template' => 'wedding-premium',
            'sections' => ['header', 'hero', 'event', 'footer'],
            'content' => ['groom_name' => 'Adit', 'bride_name' => 'Ayu', 'date' => '2026-12-12', 'time' => '10:00 WIB', 'venue' => 'Gedung Bahagia', 'address' => 'Jl. Mawar 1', 'hero_message' => 'Sampai jumpa.', 'story' => '', 'rsvp_url' => ''],
        ]);

        $invitation = Invitation::query()->firstOrFail();

        $response->assertRedirect(route('invitations.edit', $invitation));
        $this->assertDatabaseHas('invitations', ['user_id' => $user->id, 'slug' => 'adit-ayu', 'template' => 'wedding-premium', 'is_published' => false]);
        $this->assertSame(['header', 'hero', 'event', 'footer'], $invitation->sections);
    }

    public function test_user_cannot_edit_another_users_invitation(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $invitation = Invitation::factory()->for($owner)->create();

        $response = $this->actingAs($otherUser)->get(route('invitations.edit', $invitation));

        $response->assertNotFound();
    }

    public function test_only_published_invitation_is_publicly_visible(): void
    {
        $draft = Invitation::factory()->create(['slug' => 'draft-undangan']);

        $this->get(route('invitations.public', $draft->slug))->assertNotFound();

        $published = Invitation::factory()->create(['slug' => 'undangan-live', 'is_published' => true]);
        $response = $this->get(route('invitations.public', $published->slug).'?to=Sari%20Wulandari');

        $response->assertOk()->assertSee('Adit')->assertSee('Ayu')->assertSee('Sari Wulandari')->assertSee('Buka Undangan');
    }

    public function test_owner_can_preview_a_draft_but_another_user_cannot(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $invitation = Invitation::factory()->for($owner)->create(['is_published' => false]);

        $response = $this->actingAs($owner)->get(route('invitations.preview', $invitation));

        $response->assertOk()->assertSee('MODE PREVIEW')->assertSee('Adit');
        $this->actingAs($otherUser)->get(route('invitations.preview', $invitation))->assertNotFound();
    }

    public function test_owner_can_publish_an_invitation(): void
    {
        $owner = User::factory()->create();
        $invitation = Invitation::factory()->for($owner)->create(['is_published' => false]);

        $response = $this->actingAs($owner)->patch(route('invitations.publish', $invitation));

        $response->assertRedirect();
        $this->assertDatabaseHas('invitations', ['id' => $invitation->id, 'is_published' => true]);
    }

    public function test_guest_can_submit_an_rsvp_to_a_published_invitation(): void
    {
        $invitation = Invitation::factory()->create(['slug' => 'undangan-rsvp', 'is_published' => true]);

        $response = $this->post(route('invitations.rsvp.store', $invitation->slug), [
            'guest_name' => 'Sari Wulandari', 'attendance' => 'hadir', 'guest_count' => 2, 'message' => 'Sampai jumpa!',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('rsvps', ['invitation_id' => $invitation->id, 'guest_name' => 'Sari Wulandari', 'attendance' => 'hadir', 'guest_count' => 2]);
    }

    public function test_rsvp_requires_a_valid_attendance_status(): void
    {
        $invitation = Invitation::factory()->create(['slug' => 'undangan-validasi', 'is_published' => true]);

        $response = $this->from(route('invitations.public', $invitation->slug))->post(route('invitations.rsvp.store', $invitation->slug), ['guest_name' => 'Tamu', 'attendance' => 'maybe', 'guest_count' => 1]);

        $response->assertRedirect(route('invitations.public', $invitation->slug))->assertSessionHasErrors('attendance');
        $this->assertDatabaseCount('rsvps', 0);
    }

    public function test_user_can_preview_an_active_template_before_selecting_it(): void
    {
        $user = User::factory()->create();
        $template = InvitationTemplate::query()->where('slug', 'wedding-premium')->firstOrFail();

        $response = $this->actingAs($user)->get(route('invitations.templates.preview', $template->slug));

        $response->assertOk()->assertSee('MODE PREVIEW')->assertSee($template->name);
    }

    public function test_only_superadmin_can_manage_templates(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $superadmin = User::factory()->create(['role' => 'superadmin']);

        $this->actingAs($user)->get(route('admin.templates.index'))->assertForbidden();
        $this->actingAs($superadmin)->get(route('admin.templates.index'))->assertOk()->assertSee('Template Undangan');
    }
}
