# Contact Form

A contact form with a shortcode, email notification and database storage of submissions.

A plugin for [Modulo CMS](https://github.com/PhantomPixelDev/modulo-cms).

## Install

From the admin (**Plugins → Browse**) or the command line:

```bash
php artisan plugin:install contact-form
php artisan plugin:activate contact-form
```

Installs come from the [Modulo plugin registry](https://github.com/PhantomPixelDev/modulo-registry),
which records the SHA-256 of every release package; the download is verified against it
before anything is unpacked.

## Use

Put `[contact_form]` in any page. Set the recipient address, subject and success
message under the plugin's settings.

## Releasing

1. Bump `version` in `plugin.json`.
2. Tag it: `git tag vX.Y.Z && git push --tags`. The release workflow refuses a tag that
   does not match `plugin.json`, then publishes `contact-form-X.Y.Z.zip` and its `.sha256`.
3. Update this plugin's entry in the registry with the new version, asset URL and checksum.

## License

MIT
