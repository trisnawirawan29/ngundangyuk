<?php

namespace Database\Factories;

use App\Models\Invitation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Invitation>
 */
class InvitationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => 'Undangan Pernikahan',
            'slug' => Str::random(10),
            'template' => 'wedding-premium',
            'content' => ['groom_name' => 'Adit', 'bride_name' => 'Ayu', 'date' => '2026-12-12', 'time' => '10:00 WIB', 'venue' => 'Gedung Serbaguna', 'address' => 'Jl. Kebahagiaan No. 1', 'hero_message' => 'Dengan penuh kebahagiaan, kami mengundang Anda.', 'story' => 'Kisah kami dimulai dari sebuah pertemuan sederhana.', 'rsvp_url' => ''],
            'sections' => ['header', 'hero', 'story', 'couple', 'countdown', 'event', 'gallery', 'rsvp', 'footer'],
            'is_published' => false,
        ];
    }
}
