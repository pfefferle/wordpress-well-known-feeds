# .well-known/feeds

`<link rel="alternate" type="application/rss+xml" …>` is fine, but it only describes a single page. I feel like there should be a standard way for a *site* to share the list of feeds that belong to it.

This plugin adds two well-known endpoints for that:

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

## A few things to know

- The feed-menu draft only defines `rss` and `atom`. The extra types (`json`, `as1`, `as2`, …) are added as extra members. The draft says clients should ignore members they do not know, so this stays compatible.
- The JSON points at a schema (`feed-menu-schema.json`) based on Appendix A of the draft. I had to relax it a bit, so the extra members and the `$schema` field validate.
- Post formats without any posts are left out. The archive feed would be empty, so I don't advertise it.
- This is still early. The draft is a draft, and the OPML side is beta. Feedback and issues are welcome: https://github.com/pfefferle/wordpress-well-known-feeds/issues

## Filters

- `well_known_feed_types`: the list of feed types to expose.
- `well_known_feed_menu`: the whole JSON menu before it is served.
- `well_known_feeds_discovery_feeds`: the extra feeds printed as `<link rel="alternate">` in the HTML head.
