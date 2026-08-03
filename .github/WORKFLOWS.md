# CI/CD Workflows

This repository uses four GitHub Actions workflows. The plugin is versioned by
[release-please](https://github.com/googleapis/release-please) based on
[conventional commits](https://www.conventionalcommits.org/):
`fix:` → patch, `feat:` → minor, `feat!:` / `BREAKING CHANGE:` → major.

Tag format: `v*` (e.g. `v1.1.2`).

---

## Overview

```
Push to main
    │
    ├──▶ [release-please.yml]
    │        Creates / updates the release PR
    │
    │    On release PR (opened / synchronize)
    ├──▶ [update-plugin-version.yml]
    │        Syncs public/plugin.php Version + readme.txt Stable tag & changelog
    │
    │    On PR to main
    └──▶ [pr.yml]
             php -l on 7.4 / 8.2 / 8.3


Merge release PR  →  release-please pushes tag v1.1.2 + creates GitHub Release
    │
    └── v*  ──▶ [wordpress-svn-release.yml]
                    Version check → pack → upload zip to the Release
                    → deploy to WordPress.org SVN (trunk + tags/1.1.2)
```

---

## Workflows

### `pr.yml` — PR Checks

**Trigger:** Any pull request targeting `main`

Runs `php -l` over every `*.php` file on a PHP matrix (7.4, 8.2, 8.3). The
plugin has no build step, so there is nothing else to check here.

---

### `release-please.yml` — Release PR Management

**Trigger:** Push to `main`
**Token:** installation token of the org-owned *Palasthotel Release Bot* GitHub
App, minted per run by `actions/create-github-app-token`. Required because
`GITHUB_TOKEN` pushes do not trigger downstream workflows — the tag would never
start `wordpress-svn-release.yml`.

```
Push to main
      │
      ▼
  release-please
      │
      └──▶ opens / updates PR  "chore(main): release 1.1.2"
                bumps version.txt
                bumps .release-please-manifest.json
                updates CHANGELOG.md

  PR merged
      └──▶ pushes tag v1.1.2
           creates GitHub Release
```

> release-please overwrites the release PR branch on every run — it does not
> rebase. If main gets another commit, the branch is re-created and the commit
> from `update-plugin-version.yml` is re-applied by that workflow.

---

### `update-plugin-version.yml` — Plugin Version Files

**Trigger:** `pull_request` on `main` — types: `opened`, `synchronize`
**Condition:** Only runs for release-please PRs (`release-please--*`) whose head
branch lives in this repository
**Token:** app installation token — pushing with it re-runs the PR checks on the
new head commit, and `bin/update-plugin-version.sh` is idempotent, so the
resulting `synchronize` event is a no-op instead of a loop

Keeps the files WordPress actually reads in sync with `version.txt` *before* the
PR is merged and the tag is created.

```
Release PR opened / updated
              │
              ▼
    bash bin/update-plugin-version.sh
              │
              ├── reads version from version.txt
              ├── updates "Version:" header in public/plugin.php
              ├── updates "Stable tag:" in public/readme.txt
              └── prepends new "= x.y.z =" section to the readme.txt changelog
              │
              ▼
    git commit + push → back onto the release PR branch
```

---

### `wordpress-svn-release.yml` — Deploy to WordPress.org

**Trigger:** Push of a `v*` tag, or `workflow_dispatch` with a version input

```
Tag: v1.1.2
      │
      ├── strip prefix → VERSION=1.1.2
      │
      ├── bin/version-checker.sh
      │       version.txt == readme.txt Stable tag == plugin header Version == tag
      │       mismatch → job fails before anything is published
      │
      ├── bin/pack.sh
      │       copies public/ → build/environment-info/
      │       zip → environment-info.zip
      │
      ├──▶ Upload environment-info.zip to the GitHub Release
      │       (softprops/action-gh-release, continue-on-error)
      │
      ├── svn checkout  $SVN_REPO_URL  →  ./svn/
      │
      └── SVN commit
              rm trunk/*  +  rm tags/$VERSION
              rsync -rL public/ → trunk/  →  tags/$VERSION/
              rsync --delete assets/ → assets/   (plugin page media)
              svn add --force .
              svn rm deleted files
              svn commit "Release version $VERSION"
```

`assets/` sits next to `trunk/` in the SVN repository and is served on the plugin
page only — it is not part of what users download. The repository mirrors it with
`--delete`, so it is the source of truth. When you adopt this workflow in a repo
whose SVN `assets/` already holds files, copy those into the repository first,
otherwise the next release deletes them.

---

### Re-running a failed deploy

A tag ruleset prevents `v*` tags from being moved, and re-running a tag event
always replays the workflow file as it existed at that tag — so a fix to the
workflow cannot be picked up by re-running the failed job. Use **Run workflow**
instead, select the branch carrying the fix and enter the version without the
leading `v`. A dispatch run deploys the content of the ref you select;
`bin/version-checker.sh` fails the job before anything is published if the version
does not match.

`rsync -rL` rather than `cp -r` because `cp` is platform-dependent — GNU `cp`
keeps symlinks while descending a directory, BSD `cp` resolves them — and SVN
refuses a commit that puts a symlink where it versions a regular file.

---

## Required secrets / variables

| Name | Type | Level | Value |
|---|---|---|---|
| `RELEASE_BOT_APP_ID` | variable | org | App ID of the *Palasthotel Release Bot* GitHub App |
| `RELEASE_BOT_PRIVATE_KEY` | secret | org | that app's private key (full `.pem`, incl. BEGIN/END lines) |
| `SVN_USERNAME` | secret | org | WordPress.org committer |
| `SVN_PASSWORD` | secret | org | WordPress.org password |
| `SVN_REPO_URL` | variable | repo | `https://plugins.svn.wordpress.org/environment-info` |

The GitHub App is installed on this repository with `Contents: read & write` and
`Pull requests: read & write`. `SVN_REPO_URL` is repo-level because the slug
differs per plugin; everything else is shared across all plugin repos.

release-please never pushes to `main` — it opens a pull request — so a branch
ruleset on `main` needs no exception for the app. Add the app as a bypass actor
only if one of these applies: a **tag** ruleset restricts creating `v*` tags, a
ruleset also covers the `release-please--*` branches and forbids direct pushes,
or signed commits are required. In those cases the bot cannot tag the release or
update its own release PR.

---

## Files the release touches

| File | Updated by | Purpose |
|---|---|---|
| `version.txt` | release-please | machine-readable version, source for the sync script |
| `.release-please-manifest.json` | release-please | last released version |
| `CHANGELOG.md` | release-please | GitHub-facing changelog |
| `public/plugin.php` | `bin/update-plugin-version.sh` | `Version:` plugin header |
| `public/readme.txt` | `bin/update-plugin-version.sh` | `Stable tag:` + `== Changelog ==` |
