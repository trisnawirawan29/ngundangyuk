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
        DB::table('invitations')->whereIn('template', ['classic', 'modern', 'floral'])->update(['template' => 'wedding-premium']);
        DB::table('invitation_templates')->whereIn('slug', ['classic', 'modern', 'floral'])->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Legacy templates are intentionally not recreated after removal.
    }
};
