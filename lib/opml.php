<?php

  function blogs_to_opml($blogs) {

    $feeds = '';

    foreach($blogs as $blog) {
      if (!empty($blog[1])) {
        $feeds .= "            <outline text=\"{$blog[1]}\" title=\"{$blog[0]}\" type=\"rss\" xmlUrl=\"{$blog[1]}\" htmlUrl=\"{$blog[0]}\" />\n";
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
