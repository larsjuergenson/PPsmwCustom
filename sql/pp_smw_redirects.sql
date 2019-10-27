CREATE TABLE  IF NOT EXISTS /*_*/pp_smw_redirects (
   -- rid is no auto-increment, but the page id of the redirect page which is guaranteed to be unique
  rid int(10) UNSIGNED NOT NULL PRIMARY KEY,
  displayed_in varbinary(255) NOT NULL,
  object_declaration mediumblob NOT NULL
) /*$wgDBTableOptions*/;

-- for display creation, we need to search by diplay page
CREATE INDEX /*i*/display ON /*_*/pp_smw_redirects (displayed_in);
