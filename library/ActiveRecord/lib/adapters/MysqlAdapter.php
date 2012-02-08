<?php
/**
 * @package ActiveRecord
 */
namespace ActiveRecord;

/**
 * Adapter for MySQL.
 *
 * @package ActiveRecord
 */
class MysqlAdapter extends Connection
{
	static $DEFAULT_PORT = 3306;

	public function limit($sql, $offset, $limit)
	{
		$offset = intval($offset);
		$limit = intval($limit);
		return "$sql LIMIT $offset,$limit";
	}

	public function query_column_info($table)
	{
		return $this->query("SHOW COLUMNS FROM $table");
	}

	public function query_for_tables()
	{
		return $this->query('SHOW TABLES');
	}

	public function create_column(&$column)
	{
		$c = new Column();
		$c->inflected_name	= Inflector::instance()->variablize($column['Field']);
		$c->name			= $column['Field'];
		$c->nullable		= ($column['Null'] === 'YES' ? true : false);
		$c->pk				= ($column['Key'] === 'PRI' ? true : false);
		$c->auto_increment	= ($column['Extra'] === 'auto_increment' ? true : false);

		if ($column['Type'] == 'timestamp' || $column['Type'] == 'datetime')
		{
			$c->raw_type = 'datetime';
			$c->length = 19;
		}
		elseif ($column['Type'] == 'date')
		{
			$c->raw_type = 'date';
			$c->length = 10;
		}
		elseif ($column['Type'] == 'time')
		{
			$c->raw_type = 'time';
			$c->length = 8;
		}
		else
		{
			preg_match('/^([A-Za-z0-9_]+)(\(([0-9]+(,[0-9]+)?)\))?/',$column['Type'],$matches);

			$c->raw_type = (count($matches) > 0 ? $matches[1] : $column['Type']);

			if (count($matches) >= 4)
				$c->length = intval($matches[3]);
		}

		$c->map_raw_type();
		$c->default = $c->cast($column['Default'],$this);

		return $c;
	}
}
?>
