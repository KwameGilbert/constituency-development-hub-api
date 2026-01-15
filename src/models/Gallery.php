<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

/**
 * Gallery Model
 * 
 * Represents a photo album in the gallery.
 *
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property string|null $description
 * @property string $category
 * @property \Illuminate\Support\Carbon $date
 * @property string $location
 * @property string $cover_image
 * @property array|null $images
 * @property string $status
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Gallery extends Model
{
    protected $table = 'galleries';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = true;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'category',
        'date',
        'location',
        'cover_image',
        'images',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'date' => 'date',
        'images' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /* -----------------------------------------------------------------
     |  Static Methods
     | -----------------------------------------------------------------
     */

    public static function findBySlug(string $slug): ?Gallery
    {
        return static::where('slug', $slug)->first();
    }

    /* -----------------------------------------------------------------
     |  Helper Methods
     | -----------------------------------------------------------------
     */

    public function toPublicArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'category' => $this->category,
            'date' => $this->date?->format('F d, Y'),
            'location' => $this->location,
            'cover_image' => $this->cover_image,
            'images' => $this->images ?? [],
            'status' => $this->status,
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
