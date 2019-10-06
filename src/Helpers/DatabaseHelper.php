<?php

namespace PP\SMW\Helpers;

/**
  * Provides static utitlity functions for accessing the Mediawiki database.
  *
  */
class DatabaseHelper {

	public static function getConnection( string $purpose = 'read-write' ) {
		switch ($purpose) {
			case 'read-only':
				$db_id = DB_REPLICA;
				break;
			case 'read-write':
			default:       // DB_MASTER is safe for any operation, hence the default
				$db_id = DB_MASTER;
		}

		return \MediaWiki\MediaWikiServices::getInstance()
		       ->getDbLoadBalancer()
		       ->getConnection($db_id);
	}
}