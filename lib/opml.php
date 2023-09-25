<?php

  function blogs_to_opml($blogs) {

    $feeds = '';

    foreach($blogs as $blog) {
      if (!empty($blog['feed'])) {
        $feeds .= "            <outline text=\"{$blog['feed']}\" title=\"{$blog['url']}\" type=\"rss\" xmlUrl=\"{$blog['feed']}\" htmlUrl=\"{$blog['url']}\" />\n";
      }
    }

    return <<< END
    <?xml version="1.0" encoding="UTF-8"?>
    <opml version="2.0">
        <head>
            <title>OPML Feeds</title>
        </head>
        <body>
{$feeds}
        </body>
    </opml>
END;

  }
