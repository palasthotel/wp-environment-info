# Contributing

## Branching

`main` is the default branch and always reflects what is released (or about to
be released). Work on a feature branch and open a pull request against `main`.

## Commit messages

Releases and the changelog are generated from the commit history, so commit
messages follow [Conventional Commits](https://www.conventionalcommits.org/):

```
<type>[optional scope][!]: <description>

[optional body]

[optional footer]
```

| Type | Effect on the version | Appears in changelog |
|---|---|---|
| `fix:` | patch (1.0.3 → 1.0.4) | yes, "Bug Fixes" |
| `feat:` | minor (1.0.3 → 1.1.0) | yes, "Features" |
| `feat!:` or `BREAKING CHANGE:` footer | major (1.0.3 → 2.0.0) | yes, highlighted |
| `docs:`, `refactor:`, `chore:`, `deps:`, `style:`, `test:`, `ci:` | none | no |

Examples:

```
fix: keep pagination working for custom taxonomies
feat: allow overriding archives per language
feat!: drop support for PHP 7.4

BREAKING CHANGE: requires PHP 8.0 or newer
```

A pull request that should trigger a release needs at least one `fix:` or
`feat:` commit. When squash-merging, make sure the squash commit message itself
is a conventional commit — that is the message release-please reads.

### Which changes get `fix:` or `feat:`

Only changes that matter to someone using the plugin. `fix:` and `feat:` decide
the version *and* write the line that ends up in the changelog on the
wordpress.org plugin page, so the question to ask before committing is whether a
user of the plugin would care about that line.

Everything else takes a type that releases nothing — workflows and CI, release
tooling, repository documentation, internal refactoring, and anything touching
files that are not shipped. As a rule of thumb, a change confined to files
outside `public/` is almost never a `fix:`.

That includes hardening. Blocking direct access to a file that is not part of the
download is `chore:`, not `fix:` — nothing changes for anyone who installed the
plugin.

## Versions

Never edit version numbers by hand. `version.txt`, `CHANGELOG.md`,
`public/plugin.php` and the `Stable tag:` in `public/readme.txt` are all
maintained by the release pipeline — see
[.github/WORKFLOWS.md](.github/WORKFLOWS.md).

Content changes to `public/readme.txt` (description, FAQ, screenshots,
tested-up-to) are of course done by hand; just leave `Stable tag:` and the
`== Changelog ==` entries alone.

## Checks

Every PR runs `php -l` against PHP 7.4, 8.2, 8.3 and 8.4, and packs the plugin so
a broken `bin/pack.sh` surfaces in the pull request.

The plugin declares `Requires at least: 5.0` (WordPress) and no `Requires PHP` at
all — the code uses nothing newer than PHP 5.4, and declaring a floor would only
lock out installs that work today. If you introduce syntax or APIs that need a
newer PHP, add `Requires PHP` to `public/plugin.php` and `public/readme.txt` in
the same PR and widen the matrix accordingly.
