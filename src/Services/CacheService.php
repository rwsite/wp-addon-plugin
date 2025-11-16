<?php

namespace WpAddon\Services;

use WpAddon\Interfaces\CacheInterface;

class CacheService implements CacheInterface
{
    private string $cacheDir;
    private int $ttl;

    public function __construct(string $cacheDir = '', int $ttl = 3600)
    {
        $this->cacheDir = $cacheDir ?: WP_CONTENT_DIR . '/cache/pages/';
        $this->ttl = $ttl;
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }

    public function generateCacheKey(string $url): string
    {
        return md5($url);
    }

    public function getCachedContent(string $key): ?string
    {
        $file = $this->cacheDir . $key . '.gz';
        if (!file_exists($file) || (time() - filemtime($file)) > $this->ttl) {
            return null;
        }
        $content = gzuncompress(file_get_contents($file));
        return $content ?: null;
    }

    public function saveCachedContent(string $key, string $content): void
    {
        $file = $this->cacheDir . $key . '.gz';
        file_put_contents($file, gzcompress($content, 6));
    }

    public function clearCache(): void
    {
        $files = glob($this->cacheDir . '*.gz');
        foreach ($files as $file) {
            unlink($file);
        }
    }
}
