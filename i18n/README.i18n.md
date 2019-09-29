Technically, we don't require internationalization, but:

* for some things, MediaWiki REQUIRES the existence of basic i18n support.
* some of the Semantic Mediawiki classes we are using expect string labels 
  instead of plain strings in some places (e.g., "messages" for query parameters).