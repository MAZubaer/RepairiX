<?php

namespace App\Models;

use App\Services\Images\ShopImageStorage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopImage extends Model
{
    use HasFactory;

    protected $primaryKey = 'image_id';

    protected $fillable = [
        'shop_id',
        'image_path',
        'disk',
        'type',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class, 'shop_id', 'shop_id');
    }

    /** Delivery URL for a size variant: thumb, full, logo or cover. */
    public function url(string $variant = 'full'): string
    {
        return app(ShopImageStorage::class)->url($this, $variant);
    }

    /** The shape every page receives for a gallery photo. */
    public function toGalleryArray(): array
    {
        return [
            'image_id' => $this->image_id,
            'thumb_url' => $this->url('thumb'),
            'url' => $this->url('full'),
        ];
    }
}
