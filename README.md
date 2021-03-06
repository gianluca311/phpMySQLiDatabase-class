phpMySQLiDatabase-class
=======================
This class is an enhanced MySQLi class for PHP.

Examples
--------
config.php
```php
<?php
$dbc = array("host" => "localhost", "username" => "dbuser", "password" => "1234", "dbname" => "exampledb");
?>
```

main.php
```php
<?php
include_once('database.class.php');
$DB = Database::getInst();

$result = $DB->sendQuery("SELECT firstname FROM example");
while($row = $DB->fetchArray($result)) {
	//some code here
}

echo $DB->countRows($result);

//in case for debugging
$DB->sendQuery("SELECT 1+2;", true);

?>
```

License
-------

This library is free software; you can redistribute it and/or
modify it under the terms of the GNU Lesser General Public License
as published by the Free Software Foundation; either version 2.1
of the License, or (at your option) any later version.

This library is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
Lesser General Public License for more details.

You should have received a copy of the GNU Lesser General Public
License along with this library; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
