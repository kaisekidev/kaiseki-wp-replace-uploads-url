<?php

declare(strict_types=1);

namespace Kaiseki\WordPress\ReplaceUploadsUrl;

use Kaiseki\WordPress\Environment\EnvironmentInterface;
use Kaiseki\WordPress\Hook\HookProviderInterface;

use function __;
use function add_action;
use function sprintf;
use function trim;

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

    public function replaceInAttachmentImageSrc(array|false $image): array|false
    {
        if (isset($image[0])) {
            $image[0] = $this->findAndReplaceUrls($image[0]);
        }

        return $image;
    }

    public function replaceInAttachmentImageAttributes(array $attr): array
    {

        if (isset($attr['srcset'])) {
            $attr['srcset'] = $this->findAndReplaceUrls($attr['srcset']);
        }

        return $attr;
    }

    /**
     * Modify Image for Javascript
     * Primarily used for media library
     *
     * @since 1.3.0
     *
     * @param array      $response   Array of prepared attachment data
     * @param int|object $attachment Attachment ID or object
     * @param array      $meta       Array of attachment metadata
     *
     * @return array $response   Modified attachment data
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

    /**
     * Modify Images in Content
     *
     * @since 1.2.0
     *
     * @param string $content
     *
     * @return string $content
     */
    public function replaceInContent(string $content)
    {
        return $this->findAndReplaceUrls($content);
    }

    /**
     * Update Image URL
     *
     * @param mixed  $content
     * @param string $imageUrl
     *
     * @return string $image_url
     *
     *@since 1.0.0
     */
    public function findAndReplaceUrls($content)
    {
        if (!$content) {
            return $content;
        }

        return str_replace($this->localUrl, $this->remoteUrl, $content);
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
