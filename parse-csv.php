<?php

  require 'lib/mastodon.php';
  require 'lib/csv.php';
  require 'lib/opml.php';

  if (empty($argv[1]) || !file_exists($argv[1])) {
    die("You need to specify a valid input file.\n");
  }

  $usernames = csv_to_array($argv[1], function($line) {
    return $line['Account address'];
  });

  $blogs = get_blogs_from_usernames($usernames);
  
  array_to_csv('output.csv', $blogs, ['URL', 'RSS']);
  file_put_contents('output.opml', blogs_to_opml($blogs));
