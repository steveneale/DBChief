<?php
/*
 * 'database.php'
 * 
 * Simple class for connecting to a MySQL database, and inserting, updating, selecting and deleting records.
 *
 * This file is part of the 'DBChief' package (https://github.com/steveneale/DBChief).
 * 
 * 2018 Steve Neale <steveneale3000@gmail.com>
 */

class Database
{
	const DB_CONFIG_FILE = "db_config.ini";
	const DB_ERROR_LOG = "db_error.log";

	private $connection = NULL;

	#########################################################################
	# Get information about the database connection and interaction with it #
	#########################################################################

	/* Return the current connection */
	public function get_connection()
	{
		return $this->connection;
	}

	/* Return the ID of the last inserted item */
	public function last_id()
	{
		return mysqli_insert_id($this->connection);
	}

	/* Close the connection */
	public function close()
	{
		mysqli_close($this->connection);
	}

	#################################################################
	# Perform INSERT, UPDATE, SELECT, and DELETE operations on data #
	#################################################################

	/* Insert data into a given table */
	public function insert($table, $fields, $values)
	{
		if ((is_array($fields) && is_array($values)) && (count($fields) == count($values)))
		{
			$query = "INSERT INTO $table (".implode(", ", $fields).") VALUES (".implode(", ", $values).")";

			$query_result = mysqli_query($this->connection, $query);
		
			if($query_result == 0)
			{
				error_log("[".date('Y-m-d h:i:s', time())."] database.php: insert()\n--- table: $table\n--- fields: [".implode(", ", $fields)."]\n--- values: [".implode(", ", $values)."]\n--- mysqli_error: \"".mysqli_error($this->connection)."\"\n\n", 3, self::DB_ERROR_LOG);
			}
			return $query_result;	
		}
		else
		{
			error_log("[".date('Y-m-d h:i:s', time())."] database.php: insert()\n--- table: $table\n--- fields: [".implode(", ", $fields)."]\n--- values: [".implode(", ", $values)."]\n--- Field and value numbers/types do not appear to match\n\n", 3, self::DB_ERROR_LOG);
			return 0;
		}
	}

	/* Update data in a given table */
	public function update($table, $fields, $values, $where)
	{
		if ((is_array($fields) && is_array($values)) && (count($fields) == count($values)))
		{
			$query = "UPDATE $table SET ";
			$updates = array();
			for($i = 0; $i < count($fields); $i++)
			{
				array_push($updates, "$fields[$i]=$values[$i]");
			}
			$query .= implode(", ", $updates);
		}
		else
		{
			error_log("[".date('Y-m-d h:i:s', time())."] database.php: update()\n--- table: $table\n--- fields: [".implode(", ", $fields)."]\n--- values: [".implode(", ", $values)."]\n--- Field and value numbers/types do not appear to match\n\n", 3, self::DB_ERROR_LOG);
			return 0;
		}

		if ($where != NULL && is_array($where))
		{
			if (is_array($where))
			{
				$conditions = array();
				for($i=0; $i < count($where); $i++)
				{
					$field = $where[$i][0];
					$operator = $where[$i][1];
					$value = $where[$i][2];
					array_push($conditions, "$field$operator$value");
				}
				$query .= " WHERE ".implode(" AND ", $conditions); 
			}
			else
			{
				error_log("[".date('Y-m-d h:i:s', time())."] database.php: update()\n--- table: $table\n--- Error: The variable passed to the 'where' parameter was either NULL or was not an array.\n\n", 3, self::DB_ERROR_LOG);
				return 0;
			}
		}

		$query_result = mysqli_query($this->connection, $query);
		
		if($query_result == 0)
		{
			error_log("[".date('Y-m-d h:i:s', time())."] database.php: update()\n--- table: $table\n--- fields: [".implode(", ", $fields)."]\n--- values: [".implode(", ", $values)."]\n--- where: ".json_encode($where)."\n--- mysqli_error: \"".mysqli_error($this->connection)."\"\n\n", 3, self::DB_ERROR_LOG);
		}
		return $query_result;
	}

	/* Select data from a given table */
	public function select($values, $table, $where, $order=NULL, $limit=NULL)
	{
		$query = "SELECT ".implode(", ", $values)." FROM $table";
		
		if ($where != NULL && is_array($where))
		{
			if (is_array($where))
			{
				$conditions = array();
				for($i=0; $i < count($where); $i++)
				{
					$field = $where[$i][0];
					$operator = $where[$i][1];
					$value = $where[$i][2];
					array_push($conditions, "$field$operator$value");
				}
				$query .= " WHERE ".implode(" AND ", $conditions); 
			}
			else
			{
				error_log("[".date('Y-m-d h:i:s', time())."] database.php: select()\n--- table: $table\n--- Error: The variable passed to the 'where' parameter was either NULL or was not an array.\n\n", 3, self::DB_ERROR_LOG);
				return 0;
			}
		}

		if ($order != NULL && is_array($order))
		{
			$order_value = $order[0];
			$order_direction = $order[1] != NULL ? " ".$order[1] : "";
			$query .= " ORDER BY $order_value$order_direction";
		}

		if ($limit != NULL && is_int($limit))
		{
			$query .= " LIMIT $limit";
		}

		$query_result = mysqli_query($this->connection, $query);
		
		if ($query_result != 0)
		{
			$results = array();
			if (mysqli_num_rows($query_result) > 0)
			{
				$results = array();
				while ($row = mysqli_fetch_assoc($query_result))
				{
					$results[] = $row;
				}
			}
			return $results;
		}
		else
		{
			error_log("[".date('Y-m-d h:i:s', time())."] database.php: select()\n--- values: [".implode(", ", $values)."]\n--- table: $table\n--- where: ".json_encode($where)."\n--- mysqli_error: \"".mysqli_error($this->connection)."\"\n\n", 3, self::DB_ERROR_LOG);
			return 0;
		}
	}

	/* Delete data from a given table */
	public function delete($table, $where)
	{
		$query = "DELETE FROM $table";

		if ($where != NULL && is_array($where))
		{
			$conditions = array();
			for($i=0; $i < count($where); $i++)
			{
				$field = $where[$i][0];
				$operator = $where[$i][1];
				$value = $where[$i][2];
				array_push($conditions, "$field$operator$value");
			}
			$query .= " WHERE ".implode(" AND ", $conditions);

			$query_result = mysqli_query($this->connection, $query);
		
			if($query_result == 0)
			{
				error_log("[".date('Y-m-d h:i:s', time())."] database.php: delete()\n--- table: $table\n--- where: ".json_encode($where)."\n--- mysqli_error: \"".mysqli_error($this->connection)."\"\n\n", 3, self::DB_ERROR_LOG);
			}
			return $query_result;
		}
		else
		{
			error_log("[".date('Y-m-d h:i:s', time())."] database.php: delete()\n--- table: $table\n--- Error: The variable passed to the 'where' parameter was either NULL or was not an array.\n\n", 3, self::DB_ERROR_LOG);
			return 0;
		}
	}

	###############################################################
	# Initialise class and establish a connection to the database #
	###############################################################
	public function __construct($input_config=NULL)
	{
		$config = ($input_config == NULL) ? parse_ini_file(self::DB_CONFIG_FILE) : $input_config;
		$connection = mysqli_connect($config["host"], $config["user"], $config["pass"], $config["db"]);
		if($connection === false)
		{
			error_log("[".date('Y-m-d h:i:s', time())."] database.php: __construct()\n--- A connection could not be established based on the provided database configuration (.ini) file\n--- mysqli_connect_error: \"".mysqli_connect_error()."\"\n\n", 3, self::DB_ERROR_LOG);
		}
		else
		{
			$this->connection = $connection;
		}
	}
}

?>