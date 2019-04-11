# Contributing and Maintaining

First, thank you for taking the time to contribute!

The following is a set of guidelines for contributors as well as information and instructions around our maintenance process. The two are closely tied together in terms of how we all work together and set expectations, so while you may not need to know everything in here to submit an issue or pull request, it's best to keep them in the same document.

## Ways to contribute

Contributing isn't just writing code - it's anything that improves the project. All contributions for Restricted Site Access are managed right here on GitHub. Here are some ways you can help:

### Reporting bugs

If you're running into an issue with the plugin, please take a look through [existing issues](https://github.com/10up/restricted-site-access/issues) and [open a new one](https://github.com/10up/restricted-site-access/issues/new) if needed. If you're able, include steps to reproduce, environment information, and screenshots/screencasts as relevant.

### Suggesting enhancements

New features and enhancements are also managed via [issues](https://github.com/10up/restricted-site-access/issues).

### Pull requests

Pull requests represent a proposed solution to a specified problem. They should always reference an issue that describes the problem and contains discussion about the problem itself. Discussion on pull requests should be limited to the pull request itself, i.e. code review.

For more on how 10up writes and manages code, check out our [10up Engineering Best Practices](https://10up.github.io/Engineering-Best-Practices/).

## Workflow

The `develop` branch is the development branch which means it contains the next version to be released. `stable` contains the current latest release and `master` contains the corresponding stable development version. Always work on the `develop` branch and open up PRs against `develop`.

## Release instructions

1. Version bump: Bump the version number in `restricted_site_access.php`.
2. Changelog: Add/update the changelog in both `readme.txt` and `CHANGELOG.md`
3. Readme updates: Make any other readme changes as necessary. `CHANGELOG.md` is geared toward GitHub and `readme.txt` contains WordPress.org-specific content. The two are slightly different.
4. Merge: Make a non-fast-forward merge from `develop` to `master`.
5. SVN update: Copy files over to the `trunk` folder of an SVN checkout of the plugin. If the plugin banner, icon, or screenshots have changed, copy those to the top-level `assets` folder. Commit those changes.
6. SVN tag: Make a folder inside `tags` with the current version number, copy the contents of `trunk` into it, and commit with the message `Tagging X.Y.Z`. There is also an SVN command for tagging; however, note that it runs on the remote and requires care because the entire WordPress.org plugins repo is actually single SVN repo.
7. Check WordPress.org: Ensure that the changes are live on https://wordpress.org/plugins/restricted-site-access/. This may take a few minutes.
8. Git tag: Tag the release in Git and push the tag to GitHub. It should now appear under [releases](https://github.com/10up/restricted-site-access/releases) there as well.
