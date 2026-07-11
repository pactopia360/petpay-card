<?php

namespace App\Models\Comercio;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommerceBranding extends Model
{
    protected $connection = 'mysql_comercio';

    protected $table = 'commerce_brandings';

    protected $fillable = [
        'commerce_user_id',
        'store_name',
        'slogan',
        'description',
        'logo_path',
        'banner_path',

        'header_image_path',
        'header_image_status',
        'header_image_submitted_at',
        'header_image_reviewed_at',
        'header_image_rejection_reason',
        'header_image_reviewed_by',
        'header_branch_id',

        'icon_image_path',
        'icon_image_status',
        'icon_image_submitted_at',
        'icon_image_reviewed_at',
        'icon_image_rejection_reason',
        'icon_image_reviewed_by',
        'icon_branch_id',

        'listing_image_path',
        'listing_image_status',
        'listing_image_submitted_at',
        'listing_image_reviewed_at',
        'listing_image_rejection_reason',
        'listing_image_reviewed_by',
        'listing_branch_id',

        'primary_color',
        'secondary_color',
        'accent_color',
        'background_color',
        'button_text_color',
        'show_logo',
        'show_banner',
    ];

    protected function casts(): array
    {
        return [
            'show_logo' => 'boolean',
            'show_banner' => 'boolean',

            'header_image_submitted_at' => 'datetime',
            'header_image_reviewed_at' => 'datetime',
            'header_branch_id' => 'integer',

            'icon_image_submitted_at' => 'datetime',
            'icon_image_reviewed_at' => 'datetime',
            'icon_branch_id' => 'integer',

            'listing_image_submitted_at' => 'datetime',
            'listing_image_reviewed_at' => 'datetime',
            'listing_branch_id' => 'integer',
        ];
    }

    public function commerce(): BelongsTo
    {
        return $this->belongsTo(CommerceUser::class, 'commerce_user_id');
    }

    public function headerBranch(): BelongsTo
    {
        return $this->belongsTo(CommerceBranch::class, 'header_branch_id');
    }

    public function iconBranch(): BelongsTo
    {
        return $this->belongsTo(CommerceBranch::class, 'icon_branch_id');
    }

    public function listingBranch(): BelongsTo
    {
        return $this->belongsTo(CommerceBranch::class, 'listing_branch_id');
    }

    public function imageStatus(string $type): ?string
    {
        return $this->getAttribute("{$type}_image_status");
    }

    public function imagePath(string $type): ?string
    {
        return $this->getAttribute("{$type}_image_path");
    }
}
