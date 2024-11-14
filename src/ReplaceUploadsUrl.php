<?php

declare(strict_types=1);

namespace Kaiseki\WordPress\ReplaceUploadsUrl;

use Kaiseki\WordPress\Environment\EnvironmentInterface;
use Kaiseki\WordPress\Hook\HookProviderInterface;

use function add_filter;
use function defined;
use function is_string;
use function str_replace;
use function trim;
use function wp_upload_dir;

/**
 * @phpstan-type ImageArray array{string, int, int, bool}
 * @phpstan-type ResponseArray array{url: string, sizes: array{url: string}[]}
 */
final readonly class ReplaceUploadsUrl implements HookProviderInterface
{
    private ?string $remoteUrl;
    private string $localUrl;

    public function __construct(
        private EnvironmentInterface $environment,
        string $remoteUrl = ''
    ) {
        $uploadsDir = wp_upload_dir();
        $this->remoteUrl = $this->getUrl() ?? trim($remoteUrl);
        $this->localUrl = $uploadsDir['baseurl'];
    }

    public function addHooks(): void
    {
        if (
            $this->remoteUrl === null
            || $this->remoteUrl === ''
            || $this->environment->isProduction()
        ) {
            return;
        }

        add_filter('wp_get_attachment_image_src', [$this, 'replaceInAttachmentImageSrc'], 999);
        add_filter('wp_get_attachment_image_attributes', [$this, 'replaceInAttachmentImageAttributes'], 999);
        add_filter('wp_prepare_attachment_for_js', [$this, 'replaceInAttachmentForJs'], 999);
        add_filter('the_content', [$this, 'replaceInContent'], 999);
    }

    /**
     * @param ImageArray|false $image
     *
     * @return ImageArray|false
     */
    public function replaceInAttachmentImageSrc(array|false $image): array|false
    {
        if (isset($image[0])) {
            $image[0] = $this->findAndReplaceUrls($image[0]);
        }

        return $image;
    }

    /**
     * @param string[] $attr
     *
     * @return string[] $attr
     */
    public function replaceInAttachmentImageAttributes(array $attr): array
    {

        if (isset($attr['srcset'])) {
            $attr['srcset'] = $this->findAndReplaceUrls($attr['srcset']);
        }

        return $attr;
    }

    /**
     * @param ResponseArray $response
     *
     * @return ResponseArray $response
     */
    public function replaceInAttachmentForJs(array $response): array
    {

        if (isset($response['url'])) {
            $response['url'] = $this->findAndReplaceUrls($response['url']);
        }

        foreach ($response['sizes'] as &$size) {
            $size['url'] = $this->findAndReplaceUrls($size['url']);
        }

        return $response;
    }

    public function replaceInContent(string $content): string
    {
        return $this->findAndReplaceUrls($content);
    }

    public function findAndReplaceUrls(string $content): string
    {
        if ($content === '') {
            return $content;
        }

        return str_replace($this->localUrl, (string)$this->remoteUrl, $content);
    }

    private function getUrl(): ?string
    {
        if (
            defined('REPLACE_UPLOADS_URL')
            && is_string(REPLACE_UPLOADS_URL)
            && REPLACE_UPLOADS_URL !== ''
        ) {
            return trim(REPLACE_UPLOADS_URL);
        }

        return null;
    }
}
