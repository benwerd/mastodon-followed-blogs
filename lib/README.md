# Mastodon follows to RSS / OPML

Takes your exported CSV of accounts you follow on Mastodon and returns both a CSV and an OPML file of their blog RSS feeds.

## Installation

Run `composer install`

## Use

1. Download your CSV of followed accounts from Mastodon. (Settings -> Import and Export)
2. Run `php parse-csv.php {input filename}`
3. Wait a long time
4. Use `output.csv` or `output.opml` to import feeds into your favorite software