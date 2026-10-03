<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Migration helper
 */

function execute_script($path)
{
	$CI =& get_instance();

	$version = preg_replace("/(.*_)?(.*).sql/", "$2", $path);
	error_log("Migrating to $version (file: $path)");

	$sql = file_get_contents($path);

	if ($CI->db->dbdriver === 'postgre')
	{
		$sql = _convert_mysql_to_pgsql($sql);
	}

	/*
	CI migration only allows you to run one statement at a time.
	This small script splits the statements allowing you to run them all in one go.
	*/

	$sqls = explode(';', $sql);
	array_pop($sqls);

	foreach ($sqls as $statement)
	{
		$statement = trim($statement . ';');

		if ($statement === ';' || $statement === '')
		{
			continue;
		}

		if (!$CI->db->simple_query($statement))
		{
			foreach ($CI->db->error() as $error)
			{
				error_log('error: ' . $error);
			}
		}
	}

	error_log("Migrated to $version");
}

/**
 * Convert MySQL-specific SQL syntax to PostgreSQL-compatible syntax.
 * This allows the same SQL migration scripts to work on both databases.
 */
function _convert_mysql_to_pgsql($sql)
{
	// Remove all backtick quoting — PostgreSQL uses bare identifiers
	$sql = str_replace('`', '', $sql);

	// Convert MySQL types to PostgreSQL equivalents
	$sql = preg_replace('/\bTINYINT\s*\(\d+\)/i', 'SMALLINT', $sql);
	$sql = preg_replace('/\bTINYINT\b/i', 'SMALLINT', $sql);
	$sql = preg_replace('/\bINT\s*\(\d+\)/i', 'INT', $sql);
	$sql = preg_replace('/\bINTEGER\s*\(\d+\)/i', 'INTEGER', $sql);
	// Keep DECIMAL(n,m) precision for PostgreSQL — do NOT strip it
	// Only convert DOUBLE to DOUBLE PRECISION if needed
	// (double numeric with precision already handled below)

	// Remove AUTO_INCREMENT keywords
	$sql = preg_replace('/\bAUTO_INCREMENT\b/i', '', $sql);
	$sql = preg_replace('/\bAUTO_INCREMENT\s*=\s*\d+\b/i', '', $sql);

	// Convert MySQL DROP FOREIGN KEY to PostgreSQL DROP CONSTRAINT
	$sql = preg_replace('/\bDROP\s+FOREIGN\s+KEY\b/i', 'DROP CONSTRAINT', $sql);

	// Convert MySQL DROP INDEX to PostgreSQL DROP INDEX
	// MySQL: DROP INDEX index_name ON table  →  PostgreSQL: DROP INDEX IF EXISTS index_name
	$sql = preg_replace('/DROP\s+INDEX\s+(\S+)\s+ON\s+(\S+)/i', 'DROP INDEX IF EXISTS $1', $sql);
	// MySQL: ALTER TABLE x DROP INDEX index_name  →  PostgreSQL: ALTER TABLE x DROP INDEX IF EXISTS index_name
	$sql = preg_replace('/ALTER\s+TABLE\s+(\S+)\s+DROP\s+INDEX\s+(\S+)/i', 'ALTER TABLE $1 DROP INDEX IF EXISTS $2', $sql);

	// Remove MySQL ADD KEY clauses (indexes already created in schema)
	// MySQL: ADD KEY index_name (col)  →  remove entirely
	$sql = preg_replace('/\bADD\s+KEY\s+\S+\s*\([^)]+\)/i', '', $sql);

	// Remove MySQL ADD UNIQUE KEY (use ADD UNIQUE instead)
	$sql = preg_replace('/\bADD\s+UNIQUE\s+KEY\b/i', 'ADD UNIQUE', $sql);

	// Convert MySQL MODIFY COLUMN / MODIFY to PostgreSQL ALTER COLUMN TYPE
	// Capture only column name and type; discard NOT NULL, DEFAULT, etc.
	// since those are separate clauses in PostgreSQL and columns already exist
	$sql = preg_replace('/MODIFY\s+(?:COLUMN\s+)?(\w+)\s+(\w+)([^,;]*)/i', 'ALTER COLUMN $1 TYPE $2', $sql);

	// Convert MySQL CHANGE old_col new_col type → use new_col
	$sql = preg_replace('/CHANGE\s+\w+\s+(\w+)\s+(\w+)([^,;]*)/i', 'ALTER COLUMN $1 TYPE $2', $sql);

	// Remove MySQL AFTER column specification
	$sql = preg_replace('/\bAFTER\s+\w+\b/i', '', $sql);

	// Convert INSERT IGNORE to regular INSERT
	$sql = preg_replace('/\bINSERT\s+IGNORE\b/i', 'INSERT', $sql);

	// Remove MySQL CONVERT TO CHARACTER SET clauses
	$sql = preg_replace('/\bCONVERT\s+TO\s+CHARACTER\s+SET\s+\w+(?:\s+COLLATE\s+\w+)?/i', '', $sql);

	// Remove MySQL ENGINE and CHARSET clauses
	$sql = preg_replace('/\bENGINE\s*=\s*\w+/i', '', $sql);
	$sql = preg_replace('/\bDEFAULT\s+CHARSET\s*=\s*\w+/i', '', $sql);
	$sql = preg_replace('/\bDEFAULT\s+COLLATE\s*=\s*\w+/i', '', $sql);
	$sql = preg_replace('/\bCHARSET\s*=\s*\w+/i', '', $sql);

	// Remove UNSIGNED keyword
	$sql = preg_replace('/\bUNSIGNED\b/i', '', $sql);

	// Remove inline INDEX(col) inside CREATE TABLE statements
	// PostgreSQL doesn't support inline index definitions in CREATE TABLE
	// Matches patterns like: (INDEX(col_name) ...) or (INDEX(col_name), ...)
	$sql = preg_replace('/\)\s*INDEX\s*\(\s*\w+\s*\)/', ')', $sql);
	// Also handle INDEX(col_name) followed by comma
	$sql = preg_replace('/\(\s*INDEX\s*\(\s*\w+\s*\)\s*,/', '(', $sql);

	// Convert MySQL IFNULL() to PostgreSQL COALESCE()
	$sql = preg_replace('/IFNULL\s*\(/i', 'COALESCE(', $sql);

	// Split multi-table DROP TABLE into individual DROP TABLE IF EXISTS statements
	$sql = preg_replace_callback(
		'/DROP\s+TABLE\s+([^;]+);/i',
		function ($matches) {
			$tables_str = trim($matches[1]);
			$tables_str = preg_replace('/IF EXISTS/i', '', $tables_str);
			$tables = array_map('trim', explode(',', $tables_str));
			$result = '';
			foreach ($tables as $table) {
				$result .= 'DROP TABLE IF EXISTS ' . $table . '; ';
			}
			return rtrim($result);
		},
		$sql
	);

	// Remove trailing/leading commas left by removed clauses
	$sql = preg_replace('/,\s*,/', ',', $sql);
	$sql = preg_replace('/,\s*\)/', ')', $sql);
	$sql = preg_replace('/\s+,/', ',', $sql);

	// Remove empty ALTER TABLE statements (e.g., after CONVERT TO CHARACTER SET is removed)
	$sql = preg_replace('/ALTER\s+TABLE\s+\S+\s*;/i', '/* empty alter table */;', $sql);

	return $sql;
}
