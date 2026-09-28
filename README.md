# .well-known/feeds

This plugin started as a WordPress implementation of [Dan Q's `.well-known/feeds` proposal](https://github.com/Dan-Q/well-known-feeds). The idea: `<link rel="alternate" type="application/rss+xml" …>` is fine, but it only describes a single page, so a *site* needs a standard place to share the list of feeds that belong to it. Later I added [Mark Nottingham's feed menu draft](https://www.ietf.org/archive/id/draft-nottingham-feed-menu-00.html), which does the same in JSON.

Since then the plugin grew a bit. It now also helps to find the feeds of a site in other places:

- the well-known endpoints, for feed readers and other tools
- more `<link rel="alternate">` in the HTML head, for the feeds that belong to a page
- two blocks, to list the feeds on a "follow me" page

So the name does not cover everything anymore. I think it still makes sense though. `.well-known` is about finding something at a place you know, without guessing. That is what all parts of the plugin do: the endpoints for machines, the head links for browsers and feed readers, the blocks for people. And all of them use the same list of feeds that the well-known endpoints are built on.

## The well-known endpoints

The plugin adds two well-known endpoints:

- **`/.well-known/feeds`**: an [OPML](http://opml.org/) document with all feeds of the site, following [Dan Q's `.well-known/feeds` spec](https://github.com/Dan-Q/well-known-feeds). It ships with an XSL stylesheet, so it is also readable in a browser.
- **`/.well-known/feed-menu.json`**: a JSON version, following [draft-nottingham-feed-menu-00](https://www.ietf.org/archive/id/draft-nottingham-feed-menu-00.html).

## What is in the list

Both endpoints list, for every feed type registered in WordPress:

- all posts
- all comments
- every post format that has content (Aside, Gallery, …)

"Feed type" means whatever is registered, not just RSS and Atom. On a plain WordPress that is RSS, RSS 2.0, RDF and Atom. If other plugins add feeds (for example JSON Feed, or the ActivityStreams feeds from ActivityPub), those show up too.

The variants of one source are grouped together. In the JSON menu they become the members of one feed object, in OPML they are nested under one outline.

## Feed discovery in the HTML head

A `<link rel="alternate">` still makes sense for feeds that belong to a page, so the plugin adds a few more to the ones WordPress already prints:

- on a single post: the feeds of its categories and tags, its author and its post format
- on the homepage: the feeds of all post formats that have content
- on the "standard" post-format archive: its own feed

Pages don't get a post-format feed, because pages have no post formats.

"Standard" is not a real post format in WordPress, so the plugin also makes `/type/standard/` (and its feeds) work. It lists all posts that have none of the post formats the theme supports.

## Feed Types block

The "Feed Types" block lists all registered feed types with their URLs, for example for a "follow me" page:

- as1: https://example.com/feed/as1/
- as2: https://example.com/feed/as2/
- json: https://example.com/feed/json/

The list is generated, so it updates itself when you add or remove a feed plugin. In the block settings you can pick which types to show. If you pick none (or all), the block shows all of them.

The label is the feed type slug, the same as the end of the URL. A plugin can give its feed a nicer name with the `well_known_feed_type_label` filter.

## Feed Form block

The "Feed Form" block is a small form to subscribe to the feed of a category, a tag or a post format. You pick one of them in the block settings. Readers choose the category (or tag, or post format) and the feed type, and the plugin sends them to the real feed URL, for example `/category/indieweb/feed/atom/`.

- Categories and post formats are dropdowns. Only post formats with content are listed.
- Tags are a dropdown too, but only with the most used tags (50 by default, you can change it in the block settings), because a site can have a lot of tags. They are sorted by name.
- The form works without JavaScript.
- The form uses the markup of the core Search block, so it looks like the search form of your theme.

## A few things to know

- The feed-menu draft only defines `rss` and `atom`. The extra types (`json`, `as1`, `as2`, …) are added as extra members. The draft says clients should ignore members they do not know, so this stays compatible.
- The JSON points at a schema (`feed-menu-schema.json`) based on Appendix A of the draft. I had to relax it a bit, so the extra members and the `$schema` field validate.
- Post formats without any posts are left out. The archive feed would be empty, so I don't advertise it.
- This is still early. The draft is a draft, and the OPML side is beta. Feedback and issues are welcome: https://github.com/pfefferle/wordpress-well-known-feeds/issues

## Filters

- `well_known_feed_types`: the list of feed types to expose.
- `well_known_feed_menu`: the whole JSON menu before it is served.
- `well_known_feed_type_label`: the label of a feed type in the Feed Types block (default: the slug).
- `well_known_feeds_discovery_feeds`: the extra feeds printed as `<link rel="alternate">` in the HTML head.
