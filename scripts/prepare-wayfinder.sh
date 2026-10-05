#!/bin/sh
# Give a checkout the gitignored things `tsc` and the app need:
#   - Laravel runtime dirs (a fresh git worktree has none, and artisan refuses
#     to boot without them);
#   - Wayfinder output (resources/js/{actions,routes}), regenerated from the
#     PHP source whenever it is missing.
#
# Called from the lefthook post-checkout / post-merge hooks, and safe to run by
# hand: `sh scripts/prepare-wayfinder.sh`. Exits 0 silently when dependencies
# are not installed yet — run `composer run setup` first.
set -eu

cd "$(dirname "$0")/.."

[ -f vendor/autoload.php ] || exit 0

mkdir -p storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/app/public \
    storage/app/private \
    bootstrap/cache

if [ -d resources/js/actions ] && [ -d resources/js/routes ]; then
    exit 0
fi

php artisan wayfinder:generate --with-form
