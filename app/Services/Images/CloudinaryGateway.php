<?php

namespace App\Services\Images;

use Cloudinary\Cloudinary;
use Cloudinary\Configuration\Configuration;
use RuntimeException;

/**
 * Thin wrapper around the Cloudinary SDK: the only class that talks to Cloudinary.
 * Keeping it small makes it easy to swap for a fake in tests.
 */
class CloudinaryGateway
{
    private ?Cloudinary $client = null;

    public function __construct(private readonly ?string $cloudinaryUrl)
    {
    }

    public function isConfigured(): bool
    {
        return filled($this->cloudinaryUrl);
    }

    /**
     * Upload a local file into $folder and return its public_id and size.
     *
     * @return array{public_id: string, width: int|null, height: int|null}
     */
    public function upload(string $localPath, string $folder): array
    {
        $result = $this->client()->uploadApi()->upload($localPath, [
            'folder' => $folder,
            'resource_type' => 'image',
            'unique_filename' => true,
            'overwrite' => false,
        ]);

        return [
            'public_id' => (string) $result['public_id'],
            'width' => isset($result['width']) ? (int) $result['width'] : null,
            'height' => isset($result['height']) ? (int) $result['height'] : null,
        ];
    }

    public function destroy(string $publicId): void
    {
        $this->client()->uploadApi()->destroy($publicId, ['resource_type' => 'image', 'invalidate' => true]);
    }

    /** Build a CDN delivery URL. Pure string work: no network call. */
    public function url(string $publicId, string $transformation): string
    {
        return (string) $this->client()->image($publicId)->addTransformation($transformation)->toUrl();
    }

    private function client(): Cloudinary
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('Cloudinary is not configured. Set CLOUDINARY_URL in your .env file.');
        }

        if (! $this->client) {
            $config = new Configuration($this->cloudinaryUrl);
            $config->url->secure(true);
            $config->url->analytics(false);
            $this->client = new Cloudinary($config);
        }

        return $this->client;
    }
}
