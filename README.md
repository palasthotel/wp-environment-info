# Environment Info (WordPress-Plugin)

Shows which environment a WordPress install is running on, as a coloured label in
the admin bar. Never again delete production content because you thought you were
on staging.

- **WordPress.org:** https://wordpress.org/plugins/environment-info/
- **User documentation:** [public/readme.txt](public/readme.txt) (the text shown on WordPress.org)
- **Changelog:** [CHANGELOG.md](CHANGELOG.md) — release-please owns that file, so do
  not add notes to it by hand. Entries up to 1.1.1 are in the `== Changelog ==`
  section of [public/readme.txt](public/readme.txt).

## Setup

Activate the plugin and add the `ENVIRONMENT_INFO_SETTINGS` constant to
`wp-config.php`:

```php
define( 'ENVIRONMENT_INFO_SETTINGS', [
	[
		"path"  => "butler",
		"title" => "🤖 Local Butler DEV",
	],
	[
		"path"       => "s1234",
		"title"      => "🤖 Freistil Site s1234",
		"background" => "#238422",
		"color"      => "white",
	],
	[
		"hostname"   => "host1",
		"title"      => "🤖 FlyingCircus Host1",
		"background" => "#238422",
		"color"      => "white",
	],
] );
```

Each entry needs a `title` and is matched by either `hostname`, compared against
`gethostname()`, or `path`, which matches when it appears anywhere in the plugin's
own directory path. **Exactly one entry has to match**: with none, the admin bar
reads *🤖 Unknown Server*, and with more than one the plugin shows nothing and
writes the ambiguity to the error log.

`background` and `color` are written into a `style` attribute, so any CSS colour
value works.

Because the values come from a constant in `wp-config.php`, they are set by
whoever administers the server — the plugin has no settings screen and reads
nothing from requests.

### Identifying the environment yourself

The `environment_info_identify_site` filter runs before the built-in matching and
short-circuits it when it returns an array:

```php
add_filter( 'environment_info_identify_site', function ( $site ) {
	if ( $site !== null ) {
		return $site;
	}

	return [
		"title"      => "🤖 " . wp_get_environment_type(),
		"background" => "#1d4ed8",
		"color"      => "white",
	];
} );
```

## Where the label appears

The admin bar item shows for every logged-in user who sees the bar, and links to
**Tools → Environment Info**. That page lists every configured environment, marks
the active one, and prints the current hostname and path so a non-matching
configuration can be diagnosed. It requires `manage_options`.

## Repository layout

`public/` is exactly what ships to WordPress.org. Everything outside it is
repository-only.

| Path | Description |
|---|---|
| `public/plugin.php` | the plugin |
| `public/readme.txt` | WordPress.org plugin page |
| `public/LICENSE` | GPL-3.0 text, shipped with the plugin |
| `assets/` | media for the WordPress.org plugin page — not part of the download |
| `plugin.php` | DEV wrapper, loads `public/plugin.php` when the repository is checked out into `wp-content/plugins/` |
| `LICENSE` | copy of the licence text so GitHub detects it |
| `bin/` | release helper scripts |
| `.github/workflows/` | CI/CD — see [.github/WORKFLOWS.md](.github/WORKFLOWS.md) |

## Releasing

Releases are automated with [release-please](https://github.com/googleapis/release-please)
and deployed to the WordPress.org SVN repository. There is nothing to bump by
hand — commit with [conventional commits](https://www.conventionalcommits.org/)
and merge the release PR:

```
fix: …   → patch    feat: …  → minor    feat!: … → major
```

The full pipeline is documented in [.github/WORKFLOWS.md](.github/WORKFLOWS.md),
the commit conventions in [CONTRIBUTING.md](CONTRIBUTING.md).

## Building locally

```sh
bash bin/pack.sh    # → environment-info.zip
```

## License

GNU General Public License v3.0 or later — see [LICENSE](LICENSE).
