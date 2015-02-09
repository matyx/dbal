<?php

/**
 * This file is part of the Nextras\Dbal library.
 * @license    MIT
 * @link       https://github.com/nextras/dbal
 */

namespace Nextras\Dbal;

use Nextras\Dbal\Drivers\IDriver;
use Nextras\Dbal\Drivers\DriverException;
use Nextras\Dbal\Exceptions\DbalException;
use Nextras\Dbal\Exceptions\NotImplementedException;
use Nextras\Dbal\Result\Result;


class Connection
{
	/** @var array of callbacks: function(Connection $connection) */
	public $onConnect = [];

	/** @var array of callbacks: function(Connection $connection) */
	public $onDisconnect = [];

	/** @var array of callbacks: function(Connection $connection, string $query) */
	public $onBeforeQuery = [];

	/** @var array of callbacks: function(Connection $connection, string $query, Result $rowset) */
	public $onAfterQuery = [];

	/** @var array */
	private $config;

	/** @var IDriver */
	private $driver;

	/** @var SqlProcessor */
	private $sqlPreprocessor;


	public function __construct(array $config)
	{
		$this->config = $config;

		$driver = $config['driver'];
		if (is_object($driver)) {
			$this->driver = $driver;

		} else {
			$driver = ucfirst($driver);
			$driver = "Nextras\\Dbal\\Drivers\\{$driver}\\{$driver}Driver";
			$this->driver = new $driver;
		}

		$this->sqlPreprocessor = new SqlProcessor($this->driver);
	}


	public function connect()
	{
		if ($this->driver->isConnected()) {
			return;
		}

		try {
			$this->driver->connect($this->config);
		} catch (DriverException $e) {
			throw $this->driver->convertException($e);
		}

		$this->fireEvent('onConnect', [$this]);
	}


	public function disconnect()
	{
		$this->driver->disconnect();
		$this->fireEvent('onDisconnect', [$this]);
	}


	public function reconnect()
	{
		$this->disconnect();
		$this->connect();
	}


	public function getDriver()
	{
		return $this->driver;
	}


	/**
	 * @param  string $query
	 * @return Result|NULL
	 * @throws DbalException
	 */
	public function query($query)
	{
		$this->connect();
		$sql = $this->sqlPreprocessor->process(func_get_args());
		$this->fireEvent('onBeforeQuery', [$this, $sql]);

		try {
			$result = $this->driver->nativeQuery($sql);

		} catch (DriverException $e) {
			throw $this->driver->convertException($e);
		}

		$this->fireEvent('onAfterQuery', [$this, $sql, $result]);
		return $result;
	}


	public function queryArgs($query, array $args)
	{
		array_unshift($args, $query);
		return call_user_func_array([$this, 'query'], $args);
	}


	public function getLastInsertedId($sequenceName = NULL)
	{
		$this->connect();
		return $this->driver->getLastInsertedId($sequenceName);
	}


	public function transactionBegin()
	{
		throw new NotImplementedException();
	}


	public function transactionCommit()
	{
		throw new NotImplementedException();
	}


	public function transactionRollback()
	{
		throw new NotImplementedException();
	}


	public function ping()
	{
		$this->connect();
		try {
			return $this->driver->ping();
		} catch (DriverException $e) {
			return FALSE;
		}
	}


	private function fireEvent($event, $args)
	{
		foreach ($this->$event as $callback) {
			call_user_func_array($callback, $args);
		}
	}

}
