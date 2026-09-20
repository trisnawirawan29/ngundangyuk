<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('invitation_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 80)->unique();
            $table->string('description', 255)->nullable();
            $table->string('accent_color', 20)->default('#9a6a48');
            $table->string('paper_color', 20)->default('#fffaf5');
            $table->string('design_class', 80)->default('invitation-template-classic');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        DB::table('invitation_templates')->insert([
            ['name' => 'Classic Elegance', 'slug' => 'classic', 'description' => 'Nuansa hangat dan elegan untuk momen spesial.', 'accent_color' => '#9a6a48', 'paper_color' => '#fffaf5', 'design_class' => 'invitation-template-classic', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Modern Minimal', 'slug' => 'modern', 'description' => 'Tampilan bersih dengan tipografi modern.', 'accent_color' => '#456b67', 'paper_color' => '#f5faf8', 'design_class' => 'invitation-template-modern', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Floral Garden', 'slug' => 'floral', 'description' => 'Lembut, romantis, dan penuh warna bunga.', 'accent_color' => '#a75570', 'paper_color' => '#fff8fa', 'design_class' => 'invitation-template-floral', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invitation_templates');
    }
};
