#!/bin/bash
set -e

CONFIG_FILE="config/app.php"
COMPOSE_FILE="docker-compose.prod.yml"
OUTPUT_TAR="../datacheck-image.tar"

# Read current version from config/app.php
APP_VERSION=$(grep -oP "'version'\s*=>\s*'\K[^']+" "$CONFIG_FILE" 2>/dev/null || echo "1.0.0")

# Increment patch version
IFS='.' read -r major minor patch <<< "$APP_VERSION"
patch=$((patch + 1))
NEW_VERSION="$major.$minor.$patch"

echo "=== datacheck build ==="
echo "  Versao anterior: $APP_VERSION"
echo "  Nova versao:     $NEW_VERSION"

# Update config/app.php
sed -i "s/'version'\s*=>\s*'[^']*'/'version' => '$NEW_VERSION'/" "$CONFIG_FILE"

# Build
docker compose -f "$COMPOSE_FILE" build --no-cache 2>&1

# Save tar
echo "--- Salvando imagem ---"
docker save "datacheck:$NEW_VERSION" -o "$OUTPUT_TAR"
ls -lh "$OUTPUT_TAR"

echo "=== Pronto! datacheck:$NEW_VERSION ==="
