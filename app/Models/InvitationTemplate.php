<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'name', 'slug', 'description', 'accent_color', 'paper_color', 'design_class', 'header_style', 'hero_style', 'footer_style', 'opening_style', 'image_url', 'header_image_url', 'is_active'])]
class InvitationTemplate extends Model
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
