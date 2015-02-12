<?php

/**
 * @testCase
 * @dataProvider? ../../databases.ini
 */

namespace NextrasTests\Dbal;

use Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';


class TransactionsTest extends IntegrationTestCase
{

	public function testRollback()
	{
		$this->connection->transactionBegin();

		$this->connection->query('INSERT INTO tags %values', [
			'name' => '_TRANS_ROLLBACK_'
		]);

		Assert::same(1, $this->connection->query('SELECT COUNT(*) FROM tags WHERE name = %s', '_TRANS_ROLLBACK_')->fetchField());

		$this->connection->transactionRollback();

		Assert::same(0, $this->connection->query('SELECT COUNT(*) FROM tags WHERE name = %s', '_TRANS_ROLLBACK_')->fetchField());
	}


	public function testCommit()
	{
		$this->connection->transactionBegin();

		$this->connection->query('INSERT INTO tags %values', [
			'name' => '_TRANS_COMMIT_'
		]);

		Assert::same(1, $this->connection->query('SELECT COUNT(*) FROM tags WHERE name = %s', '_TRANS_COMMIT_')->fetchField());

		$this->connection->transactionCommit();

		Assert::same(1, $this->connection->query('SELECT COUNT(*) FROM tags WHERE name = %s', '_TRANS_COMMIT_')->fetchField());
	}

}


$test = new TransactionsTest();
$test->run();