<?php 

namespace PP\SMW\Redirects;

use Wikimedia\Rdbms\IDatabase;
use WikiPage;
use PP\SMW\Helpers\DatabaseHelper;

class RecordHandler {

	public function __construct( IDatabase $dbw ) {
 		$this->dbw = $dbw;

	}

	public function delete( int $pageId ) {

		$this->dbw->delete(
			'pp_smw_redirects',
			array('rid' => $pageId )
		);

	}

	public function record( int $pageId, string $displayPage, string $proxyObject ) {

		// Replace is like insert, but replaces any
		// existing row with the same id.
		$this->dbw->replace(
			'pp_smw_redirects', 
			'rid',
			array(
				'rid' => $pageId,
				'displayed_in' => $displayPage,
				'object_declaration' => $proxyObject,
			)
		);
	}

	public static function getInstance() {
		$handler = new RecordHandler( DatabaseHelper::getConnection( 'read-write' ) );
		return $handler;
	}

	public static function isRecorded( int $pageId ) {
		$res = DatabaseHelper::getConnection('read-only')->select(
			'pp_smw_redirects',
			'rid',
			array(
				'rid' => $pageId,
			)
		);
		$row = $res->next();
		return isset($row->rid);
	}

}