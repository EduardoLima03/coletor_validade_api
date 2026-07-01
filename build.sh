#!/bin/bash
set -e

ENV_FILE=".env"
COMPOSE_FILE="docker-compose.prod.yml"
OUTPUT_TAR="../datacheck-image.tar"

# Read current version
APP_VERSION=$(grep -oP '^APP_VERSION=\K.*' "$ENV_FILE" 2>/dev/null || echo "1.0.0")

# Increment patch version
IFS='.' read -r major minor patch <<< "$APP_VERSION"
patch=$((patch + 1))
NEW_VERSION="$major.$minor.$patch"

echo "=== datacheck build ==="
echo "  Versao anterior: $APP_VERSION"
echo "  Nova versao:     $NEW_VERSION"

# Update .env
sed -i "s/^APP_VERSION=.*/APP_VERSION=$NEW_VERSION/" "$ENV_FILE"
export APP_VERSION="$NEW_VERSION"

# Build
docker compose -f "$COMPOSE_FILE" build --no-cache 2>&1

# Save tar
echo "--- Salvando imagem ---"
docker save "datacheck:$NEW_VERSION" -o "$OUTPUT_TAR"
ls -lh "$OUTPUT_TAR"

echo "=== Pronto! datacheck:$NEW_VERSION ==="
