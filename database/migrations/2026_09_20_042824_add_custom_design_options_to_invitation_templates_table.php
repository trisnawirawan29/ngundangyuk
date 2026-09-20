<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('invitation_templates', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->string('header_style', 40)->default('classic')->after('design_class');
            $table->string('hero_style', 40)->default('centered')->after('header_style');
            $table->string('footer_style', 40)->default('simple')->after('hero_style');
            $table->string('opening_style', 40)->default('envelope')->after('footer_style');
            $table->string('image_url', 500)->nullable()->after('opening_style');

            $table->index(['user_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invitation_templates', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropIndex(['user_id', 'is_active']);
            $table->dropColumn(['user_id', 'header_style', 'hero_style', 'footer_style', 'opening_style', 'image_url']);
        });
    }
};
