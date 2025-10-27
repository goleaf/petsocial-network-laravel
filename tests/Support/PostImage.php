<?php

namespace Tests\Support;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Simplified PostImage model to persist upload metadata during tests.
 */
class PostImage extends Model
{
    use HasFactory;

    /**
     * Table reference mirroring the schema seeded in Pest hooks.
     */
    protected $table = 'post_images';

    /**
     * Fields that can be mass assigned when the component records uploads.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'post_id',
        'path',
        'name',
        'size',
        'mime_type',
    ];
}
