<?php

  require dirname(__FILE__) . '/../vendor/autoload.php';

  define('NON_BLOGS', [
    'github.com',
    'github.io',
    'twitter.com',
    'facebook.com',
    'fb.me',
    '/t.co',
    '/t.me',
    'threads.net',
    'bsky.app',
    'post.news',
    't2.social',
    'instagram.com',
    'pebble.is',
    'linkedin.com',
    'pixelfed',
    'keyoxide.org',
    'etsy.com',
    'w3.org',
    'linktr.ee',
    'bio.link',
    'twitch.tv',
    'codeberg.org',
    'opencollective.com',
    'patreon.com',
    'youtube.com',
    'apple.com',
    'soundcloud.com',
    'flickr.com',
    'smugmug.com',
    'etsy.com',
    'orcid.org',
    'polywork.com',
    'liberapay.com',
    'venmo.com',
    'paypal.com',
    'paypal.me',
    'cash.app',
    'are.na',
    '/x.com',
    'bandcamp.com',
    'gofund.me',
    'gofundme.com',
    'google.com',
    'calendly.com',
    'ko-fi.com',
    'wattpad.com',
    'read.cv',
    '.amazon.',
    '/amazon.',
    'amzn.to',
    'kobo.com',
    'goodreads.com',
    'letterboxd.com',
    'gitlab.com',
    'matrix.to',
    'imdb.com',
    'wx.watch',
    'fedified.com',
    'authory.com',
    'confirmsubscription.com',
    'muckrack.com',
    'presscheck.org',
    'about.me',
    'campsite.bio',
    'bit.ly',
    'keybase.io',
    'zazzle.com',
    'bookshop.org',
    'espn.com',
    'spotify.com',
    'gravatar.com',
    'wikipedia.org',
    'audius.co',
    'firefish',
    'moodle',
    'slides.com',
    'twittodon.com',
    'threema.id',
    'pinterest.com',
    'pronouns.org',
    'zencaster.com',
    'codepen.io',
    'blubrry.com',
    'tumblr.com',
    'api.',
    'shop.',
    '/videos',
    '/signup',
    '/author',
    '/user',
    '/profile',
    '/staff',
    '/podcast',
    '/autor',
    '/people',
    '/article',
    '/person',
    '/files',
    '/show',
    '/view',
    '/product',
    '/book',
    '/series',
    '/gallery',
    '/newsletter',
    'newsletter.',
    '/@', // Not a silo but useful to remove other Mastodon profiles
    '?', // We want blogs, not database results
  ]);

  function convert_username_to_url($username) {
    $parts = explode('@', $username);
    return 'https://' . $parts[1] . '/@' . $parts[0];
  }

  function is_silo($url) {
    foreach(NON_BLOGS as $silo) {
      if (substr_count($url, $silo)) {
        return true;
      }
    }
    return false;
  }

  function get_blogs_or_websites_from_username($username, $instance) {
    $links = [];

    $query_url = 'https://' . $instance . '/api/v1/accounts/lookup?acct=';

    if (!filter_var($query_url, FILTER_VALIDATE_URL)) die ('Instance domain does not appear to be valid.');

    try {
      $contents = @file_get_contents($query_url . urlencode($username));
      if (!empty($contents)) {
        $data = json_decode($contents);
        if ($data) {
          if (!empty($data->fields)) {
            foreach($data->fields as $field) {
              if (substr_count($field->value, '<a')) {
                $a = @(new SimpleXMLElement($field->value));
                if (!empty($a['href'])) {
                  $a['href'] = trim(strtolower($a['href']));
                  if (!is_silo($a['href']) && !in_array($a['href'], $links)) {
                    $links[] = $a['href'];
                    echo "{$a['href']}\n";
                  }
                }
              }
            }
          }
        }
      }
    } catch(Exception $e) {
      return [];
    }
    return $links;
  }

  function get_websites_from_usernames($usernames, $instance) {
    $websites = [];
    foreach($usernames as $username) {
      $urls = get_blogs_or_websites_from_username($username, $instance);
      foreach($urls as $url) {
        if (filter_var($url, FILTER_VALIDATE_URL)) {
          $url = trim(strtolower($url));
          if (substr_count($url, '/') < 6 && strlen($url) <= 80) { // Over 6 slashes or 80 chars and we usually have ourselves an article, not a blog
            $websites[] = $url;
          }
        }
      }
    }
    return $websites;
  }

  function get_blogs_from_websites($websites) {
    $blogs = [];
    $unique_feeds = [];
    foreach($websites as $website) {
      try {
        $contents = @file_get_contents($website);
        if (!empty($contents)) {
          $parser = new Mf2\Parser($contents, $website);
          $mf = $parser->parse();
          $feeds = @($mf['rels']['alternate'] ?: array());
          if (!empty($feeds)) {
            $feed_to_use = false;
            foreach($feeds as $feed) {
              $feed = strtolower($feed);
              if ($feed_to_use) break;
              if (!substr_count($feed, 'http')) continue;
              if (substr_count($feed, 'android-app://')) continue;
              if (substr_count($feed, '/comments')) continue;
              if (substr_count($feed, 'wp-json')) continue;
              if (in_array($feed, $unique_feeds)) continue;
              $feed_to_use = $feed;
            }
            if (!empty($feed_to_use)) {
              $unique_feeds[] = $feed_to_use;
              $blogs[] = ['url' => $website, 'feed' => $feed_to_use];
            }
          }
        }
      } catch (Exception $e) {
        continue;
      }
    }
    return $blogs;
  }

  function get_blogs_from_usernames($usernames, $instance) {
    return get_blogs_from_websites(get_websites_from_usernames($usernames, $instance));
  }
