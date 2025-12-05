#!/bin/bash
# Fix all hardcoded /admin/ links in admin files

echo "Checking for hardcoded /admin/ URLs..."

grep -rn 'href="/admin/' admin/ --include="*.php" | wc -l

echo "Total hardcoded admin links found"

echo ""
echo "Checking for hardcoded /customer/ URLs..."

grep -rn 'href="/customer/' customer/ --include="*.php" | wc -l

echo "Total hardcoded customer links found"

echo ""
echo "Checking for hardcoded / URLs in main files..."

grep -rn 'href="/' *.php | grep -v 'href="<?php' | grep -v 'href="http' | wc -l

echo "Total hardcoded root links found"
