# kaiseki/wp-replace-uploads-url

Rewrites local uploads URLs to a remote production URL on non-production WordPress environments.

On local and staging environments, media in the database often points at uploads that only exist on
production. This serves those files from the production URL instead — rewriting attachment `src`,
`srcset`, the media-library JS payload and post content — so you don't have to sync the uploads
directory. It is a no-op on production and whenever no remote URL is configured. Wired as a
`kaiseki/wp-hook` `HookProviderInterface` through `ConfigProvider`.

## Installation

```bash
composer require kaiseki/wp-replace-uploads-url
```

Requires PHP 8.2 or newer.

## Usage

Register `ConfigProvider` with your laminas-style config aggregator and set the production uploads
URL via the `replace_uploads_url` config key:

```php
use Kaiseki\WordPress\ReplaceUploadsUrl\ReplaceUploadsUrl;

return [
    'replace_uploads_url' => 'https://www.production-site.com/wp-content/uploads',
    'hook' => [
        'provider' => [
            ReplaceUploadsUrl::class,
        ],
    ],
];
```

The remote URL can also be supplied through the `REPLACE_UPLOADS_URL` constant (e.g. defined in
`wp-config.php`), which takes precedence over the config value:

```php
define('REPLACE_UPLOADS_URL', 'https://www.production-site.com/wp-content/uploads');
```

Production is detected through `kaiseki/wp-env`'s `EnvironmentInterface`: when
`isProduction()` is true the filters are never registered, so production output is left untouched.

## Development

```bash
composer install
composer check   # check-deps, cs-check, phpstan
```

## License

MIT — see [LICENSE](LICENSE.md).
