<?php 

namespace PP\SMW\Redirects;

use Wikimedia\Rdbms\IDatabase;
use WikiPage;
use PP\SMW\Helpers\DatabaseHelper;

class RecordHandler {

	public function __construct( IDatabase $dbw ) {
 		$this->dbw = $dbw;

	}

	public function delete( WikiPage $page ) {

		$this->dbw->delete(
			'pp_smw_redirects',
			array('rid' => $page->getId() )
		);

	}

	public function record( WikiPage $page, string $displayPage, string $proxyObject ) {

		// Replace is like insert, but replaces any
		// existing row with the same id.
		$this->dbw->replace(
			'pp_smw_redirects', 
			'rid',
			array(
				'rid' => $page->getId(),
				'displayed_in' => $displayPage,
				'object_declaration' => $proxyObject,
			)
		);
	}

	public static function getInstance() {
		$handler = new RecordHandler( DatabaseHelper::getConnection( 'read-write' ) );
		return $handler;
	}

	public static function isRecorded( WikiPage $page ) {
		$res = DatabaseHelper::getConnection('read-only')->select(
			'pp_smw_redirects',
			'rid',
			array(
				'rid' => $page->getId(),
			)
		);
		$row = $res->next();
		return isset($row->rid);
	}

}