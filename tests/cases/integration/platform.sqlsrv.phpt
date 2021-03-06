<?php declare(strict_types = 1);

/**
 * @testCase
 * @dataProvider? ../../databases.ini sqlsrv
 */

namespace NextrasTests\Dbal;

use Tester\Assert;


require_once __DIR__ . '/../../bootstrap.php';


class PlatformSqlServerTest extends IntegrationTestCase
{
	public function testTables()
	{
		$tables = $this->connection->getPlatform()->getTables();

		Assert::true(isset($tables['books']));
		Assert::same([
			'name' => 'books',
			'is_view' => false,
		], $tables['books']);

		Assert::true(isset($tables['my_books']));
		Assert::same([
			'name' => 'my_books',
			'is_view' => true,
		], $tables['my_books']);
	}


	public function testColumns()
	{
		$columns = $this->connection->getPlatform()->getColumns('books');
		Assert::same([
			'id' => [
				'name' => 'id',
				'type' => 'INT',
				'size' => 10,
				'default' => null,
				'is_primary' => true,
				'is_autoincrement' => true,
				'is_unsigned' => false,
				'is_nullable' => false,
			],
			'author_id' => [
				'name' => 'author_id',
				'type' => 'INT',
				'size' => 10,
				'default' => null,
				'is_primary' => false,
				'is_autoincrement' => false,
				'is_unsigned' => false,
				'is_nullable' => false,
			],
			'translator_id' => [
				'name' => 'translator_id',
				'type' => 'INT',
				'size' => 10,
				'default' => null,
				'is_primary' => false,
				'is_autoincrement' => false,
				'is_unsigned' => false,
				'is_nullable' => true,
			],
			'title' => [
				'name' => 'title',
				'type' => 'VARCHAR',
				'size' => 50,
				'default' => null,
				'is_primary' => false,
				'is_autoincrement' => false,
				'is_unsigned' => false,
				'is_nullable' => false,
			],
			'publisher_id' => [
				'name' => 'publisher_id',
				'type' => 'INT',
				'size' => 10,
				'default' => null,
				'is_primary' => false,
				'is_autoincrement' => false,
				'is_unsigned' => false,
				'is_nullable' => false,
			],
			'ean_id' => [
				'name' => 'ean_id',
				'type' => 'INT',
				'size' => 10,
				'default' => null,
				'is_primary' => false,
				'is_autoincrement' => false,
				'is_unsigned' => false,
				'is_nullable' => true,
			],
		], $columns);

		$columns = $this->connection->getPlatform()->getColumns('tag_followers');
		Assert::same([
			'tag_id' => [
				'name' => 'tag_id',
				'type' => 'INT',
				'size' => 10,
				'default' => null,
				'is_primary' => true,
				'is_autoincrement' => false,
				'is_unsigned' => false,
				'is_nullable' => false,
			],
			'author_id' => [
				'name' => 'author_id',
				'type' => 'INT',
				'size' => 10,
				'default' => null,
				'is_primary' => true,
				'is_autoincrement' => false,
				'is_unsigned' => false,
				'is_nullable' => false,
			],
			'created_at' => [
				'name' => 'created_at',
				'type' => 'DATETIMEOFFSET',
				'size' => null,
				'default' => null,
				'is_primary' => false,
				'is_autoincrement' => false,
				'is_unsigned' => false,
				'is_nullable' => false,
			],
		], $columns);
	}


	public function testForeignKeys()
	{
		$keys = $this->connection->getPlatform()->getForeignKeys('books');
		Assert::same([
			'author_id' => [
				'name' => 'books_authors',
				'column' => 'author_id',
				'ref_table' => 'authors',
				'ref_column' => 'id',
			],
			'ean_id' => [
				'name' => 'books_ean',
				'column' => 'ean_id',
				'ref_table' => 'eans',
				'ref_column' => 'id',
			],
			'publisher_id' => [
				'name' => 'books_publisher',
				'column' => 'publisher_id',
				'ref_table' => 'publishers',
				'ref_column' => 'id',
			],
			'translator_id' => [
				'name' => 'books_translator',
				'column' => 'translator_id',
				'ref_table' => 'authors',
				'ref_column' => 'id',
			],
		], $keys);
	}


	public function testPrimarySequence()
	{
		Assert::same(null, $this->connection->getPlatform()->getPrimarySequenceName('books'));
	}


	public function testName()
	{
		Assert::same('mssql', $this->connection->getPlatform()->getName());
	}
}


$test = new PlatformSqlServerTest();
$test->run();
