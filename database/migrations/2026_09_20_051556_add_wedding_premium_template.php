<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! DB::table('invitation_templates')->where('slug', 'wedding-premium')->exists()) {
            DB::table('invitation_templates')->insert([
                'name' => 'Wedding Premium',
                'slug' => 'wedding-premium',
                'description' => 'Gaya premium romantis dengan header floral dan hero berbingkai.',
                'accent_color' => '#8e5d55',
                'paper_color' => '#fffaf5',
                'design_class' => 'invitation-template-floral',
                'header_style' => 'floral',
                'hero_style' => 'frame',
                'footer_style' => 'signature',
                'opening_style' => 'envelope',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('invitation_templates')->where('slug', 'wedding-premium')->delete();
    }
};
