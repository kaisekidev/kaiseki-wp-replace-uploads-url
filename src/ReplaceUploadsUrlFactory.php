<?php

declare(strict_types=1);

namespace Kaiseki\WordPress\ReplaceUploadsUrl;

use Kaiseki\Config\Config;
use Kaiseki\WordPress\Environment\EnvironmentInterface;
use Psr\Container\ContainerInterface;

final class ReplaceUploadsUrlFactory
{
    public function __invoke(ContainerInterface $container): ReplaceUploadsUrl
    {
        $config = Config::fromContainer($container);

        return new ReplaceUploadsUrl(
            $container->get(EnvironmentInterface::class),
            $config->string('replace_uploads_url', '')
        );
    }
}
