# DBChief

DBChief is a simple class for connecting to and interacting with MySQL databases in PHP. The class makes it easy to:

* Establish a connection to your database
* Insert new rows into tables
* Update existing data in tables
* Select data from tables
* Delete data from tables

## Using DBChief

To use DBChief, simply include the 'database.php' class in your project:

```php
require_once("database.php");
```

Be sure to specify in the class constants at the top of 'database.php' the correct paths to: 
* Your database configuration file (.ini, for example) 
* A file for DBChief to log any errors

```php
const DB_CONFIG_FILE = "/path/to/db_config.ini";
const DB_ERROR_LOG = "/path/to/db_error.log";
```

Create a new instance of the Database class, and you're ready to start:

```php
$db = new Database();
```

DBChief currently has functions to support INSERT, UPDATE, SELECT and DELETE operations. Below are examples of how to call each function, along with the equivalent query that the call will construct and execute.

### Insert

To insert a new row into a table:

```php
$insert = $db->insert("album", ["artist", "title", "released"], ["'Manic Street Preachers'", "'Everything Must Go'", 1996]);
```

Equivalent to the query:

```sql
INSERT INTO album (artist, title, released) VALUES ('Manic Street Preachers', 'Everything Must Go', 1996); 
```

### Update

To update existing data in a table:

```php
$update = $db->update("user", ["first_name", "surname"], ["'Steve'", "'Neale'"], [["id" "=", 6]]);
```

Equivalent to the query:

```sql
UPDATE user SET first_name='Steve', surname='Neale' WHERE id=6; 
```

### Select

To select data from a table:

```php
$db->select(["id", "email"], "user", [["gender", "=", 2], ["age", ">=", 30]], ["age", "DESC"], 25);
```

Equivalent to the query:

```sql
SELECT id, email FROM user WHERE gender=2 AND age>=30 ORDER BY age DESC LIMIT 25; 
```

### Delete

To delete data from a table:

```php
$db->delete("album", [["genre", "=", "grunge"], ["released", ">", 1996]]);
```

Equivalent to the query:

```sql
DELETE FROM album WHERE genre='grunge' AND released>1996; 
```