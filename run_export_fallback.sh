#!/bin/bash
# Simple helper script to trigger the export fallback command
cd "$(dirname "$0")"
php artisan sales:export-fallback