<?php

declare(strict_types=1);

namespace Kaiseki\WordPress\ReplaceUploadsUrl;

final class ConfigProvider
{
    /**
     * @return array<mixed>
     */
    public function __invoke(): array
    {
        return [
            // example: 'https://www.production-site.com/wp-content/uploads',
            'replace_uploads_url' => '',
            'hook' => [
                'provider' => [
                    ReplaceUploadsUrl::class,
                ],
            ],
            'dependencies' => [
                'aliases' => [],
                'factories' => [
                    ReplaceUploadsUrl::class => ReplaceUploadsUrlFactory::class,
                ],
            ],
        ];
    }
}
