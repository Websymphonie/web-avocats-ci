#!/usr/bin/env bash
set -euo pipefail

if [[ -z "${APP_STORAGE_DIR:-}" ]]; then
    echo "APP_STORAGE_DIR doit désigner une racine de stockage persistante." >&2
    exit 2
fi

project_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd -P)"
storage_dir="$(mkdir -p "$APP_STORAGE_DIR" && cd "$APP_STORAGE_DIR" && pwd -P)"
public_dir="$storage_dir/public"

mkdir -p \
    "$public_dir/content/covers" \
    "$public_dir/galleries" \
    "$public_dir/training/covers" \
    "$public_dir/images" \
    "$public_dir/institution/portraits" \
    "$public_dir/institution/lawyers" \
    "$public_dir/uploads" \
    "$storage_dir/private/documents" \
    "$storage_dir/private/learning/resources"

ensure_link() {
    local target="$1"
    local link="$2"

    if [[ -L "$link" ]]; then
        if [[ "$(cd "$(dirname "$link")" && cd "$(readlink "$link")" 2>/dev/null && pwd -P)" == "$target" ]]; then
            return
        fi
        rm "$link"
    elif [[ -e "$link" ]]; then
        echo "Refus d’écraser un chemin existant qui n’est pas un lien symbolique : $link" >&2
        exit 1
    fi

    ln -s "$target" "$link"
}

ensure_link "$public_dir/content" "$public_dir/uploads/content"
ensure_link "$public_dir/training" "$public_dir/uploads/training"
ensure_link "$public_dir/galleries" "$public_dir/uploads/galleries"
ensure_link "$public_dir/images" "$public_dir/uploads/images"
ensure_link "$public_dir/content" "$project_dir/public/uploads/content"
ensure_link "$public_dir/training" "$project_dir/public/uploads/training"
ensure_link "$public_dir/galleries" "$project_dir/public/uploads/galleries"
ensure_link "$public_dir/images" "$project_dir/public/uploads/images"
ensure_link "$public_dir/institution" "$project_dir/public/uploads/institution"

echo "Stockage public provisionné sous $storage_dir"
