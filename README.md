# .well-known/feeds

`<link rel="alternate" type="application/rss+xml" …>` is fine, but it only describes a single page. I feel like there should be a standard way for a *site* to share the list of feeds that belong to it.

This plugin adds two well-known endpoints for that:

- **`/.well-known/feeds`**: an [OPML](http://opml.org/) document with all feeds of the site. It ships with an XSL stylesheet, so it is also readable in a browser.
- **`/.well-known/feed-menu.json`**: a JSON version, following [draft-nottingham-feed-menu-00](https://www.ietf.org/archive/id/draft-nottingham-feed-menu-00.html).

## What is in the list

Both endpoints list, for every feed type registered in WordPress:

- all posts
- all comments
- every post format that has content (Aside, Gallery, …)

"Feed type" means whatever is registered, not just RSS and Atom. On a plain WordPress that is RSS, RSS 2.0, RDF and Atom. If other plugins add feeds (for example JSON Feed, or the ActivityStreams feeds from ActivityPub), those show up too.

The variants of one source are grouped together. In the JSON menu they become the members of one feed object, in OPML they are nested under one outline.

## A few things to know

- The feed-menu draft only defines `rss` and `atom`. The extra types (`json`, `as1`, `as2`, …) are added as extra members. The draft says clients should ignore members they do not know, so this stays compatible.
- The JSON points at a schema (`feed-menu-schema.json`) based on Appendix A of the draft. I had to relax it a bit, so the extra members and the `$schema` field validate.
- Post formats without any posts are left out. The archive feed would be empty, so I don't advertise it.
- This is still early. The draft is a draft, and the OPML side is beta. Feedback and issues are welcome: https://github.com/pfefferle/wordpress-well-known-feeds/issues

## Filters

- `well_known_feed_types`: the list of feed types to expose.
- `well_known_feed_menu`: the whole JSON menu before it is served.
