# OctaORM
A is an advance PHP-PDO ORM database class used by OctaPHP framework. This database class is inspired by codeigniter active record.

[![Build Status](http://img.shields.io/travis/badges/badgerbadgerbadger.svg?style=flat-square)](https://travis-ci.org/badges/badgerbadgerbadger) 
[![Coverage Status](http://img.shields.io/coveralls/badges/badgerbadgerbadger.svg?style=flat-square)](https://coveralls.io/r/badges/badgerbadgerbadger) 
[![License](http://img.shields.io/:license-mit-blue.svg?style=flat-square)](http://badges.mit-license.org) 
[![Badges](http://img.shields.io/:badges-8/10-ff6799.svg?style=flat-square)](https://github.com/badges/badgerbadgerbadger)

## Powered By
<img src="vendor/docs-img/redbeanphp.png" width="100" height="100">

# Author
[Melquecedec Catang-catang](https://www.linkedin.com/in/melquecedec-catang-catang)

# Getting Started

installing OctaORM does not require composer, clone the project and put it in the directory where your site locally allocated.

### Prerequisites

```
-Php 5.4+
-mysql (any version that supports php 5.4+)
```

### How To Use
Open ```database_config.php``` file and modify this variables below
```
$database = array(
    'hostname' => 'localhost',
    'username' => 'root',
    'password' => '',
    'database' => 'your_database_name_here'
);
```

Require the ```database_config.php``` file in your project.
```
require_once('database_config.php');

//some code here
```

# Documentation

**$db->get(); And $db->result();** <br />
Runs the selection query and returns an array of result.

```
$db->get('mytable');
$query = $db->result();

// Produces: SELECT * FROM mytable
```

The second and third parameters of ```$db->get()``` enable you to set a limit and offset clause:
```
$db->get('mytable', 10, 20); <br />
$query = $db->result();
// Produces: SELECT * FROM mytable LIMIT 20, 10 (in MySQL. Other databases have slightly different syntax)
```

You'll notice that the above function is assigned to a variable named $query, which can be used to show the results:
```
$db->get('mytable');
$query = $db->result();

foreach ($query as $row)
{
    echo $row->title;
}
```
<br />

**$db->get(); And $db->row();** <br />
Runs the selection query and returns a row of data.

```
$db->get('mytable');
$query = $db->row();

// Produces: SELECT * FROM mytable LIMIT 1
```

You'll notice that the above function is assigned to a variable named $query, which can be used to fetch the row:
```
$db->get('mytable');
$query = $db->row();
echo $query['title']
```
<br />

**$db->get(); And $db->num_rows();** <br />
Permits you to determine the number of rows in a particular table. 

```
$db->get('mytable');
$query = $db->num_rows();

// Produces an integer, like 25
```

You'll notice that the above function is assigned to a variable named $query, which can be used to fetch the row:
```
$db->get('mytable');
$query = $db->row();
echo $query; //return number of rows
```
<br />

**$db->last_query(); (for sql debugging)** <br />
Permits you to determine the last query which was run

```
$db->get('mytable');
$query = $db->result();
$last_query = $db->last_query();
echo $last_query;

// will return sql code: "SELECT * FROM mytable"
```
<br />

**$db->select();** <br />
Permits you to write the SELECT portion of your query:

```
$db->select('title, content, date');

$query = $db->get('mytable');

// Produces: SELECT title, content, date FROM mytable
```
$db->select() accepts an optional second parameter. If you set it to FALSE, OctaPHP will not try to protect your field or table names with backticks. This is useful if you need a compound select statement.
```
$db->select('(SELECT SUM(payments.amount) FROM payments WHERE payments.invoice_id=4') AS amount_paid', FALSE);
$query = $db->get('mytable');
```
<br />

**$db->join();** <br />
Permits you to write the JOIN portion of your query:

```
$db->select('*');
$db->join('comments', 'comments.id = blogs.id');
$db->get('blogs');
$query = $db->result();

// Produces:
// SELECT * FROM blogs
// INNER JOIN comments ON comments.id = blogs.id
```

Multiple function calls can be made if you need several joins in one query. <br />
If you need a specific type of JOIN you can specify it via the third parameter of the function. Options are: left, right, outer, inner, left outer, and right outer.

```
 $db->join('comments', 'comments.id = blogs.id', 'LEFT');

// Produces: LEFT JOIN comments ON comments.id = blogs.id
```
<br />

**$db->where();** <br />
This function enables you to set WHERE clauses using one of four methods:

* Simple key/value method: <br />
Notice that the equal sign is added for you.
If you use multiple function calls they will be chained together with AND between them:
	```
	$db->where('name', $name);
	$db->where('title', $title);
	$db->where('status', $status);

	// WHERE name = 'Joe' AND title = 'boss' AND status = 'active' 
	```
* Custom key/value method: <br />
You can include an operator in the first parameter in order to control the comparison:
```
$db->where('name !=', $name);
$db->where('id <', $id);

// Produces: WHERE name != 'Joe' AND id < 45 
```
* Associative array method: <br />
You can include your own operators using this method as well:
```
$array = array('name' => $name, 'title' => $title, 'status' => $status);

$db->where($array);

// Produces: WHERE name = 'Joe' AND title = 'boss' AND status = 'active' 
```

* Custom string: <br />
You can write your own clauses manually:
```
$where = "name='Joe' AND status='boss' OR status='active'";

$db->where($where);
```
**$db->where()** <br />
accepts an optional third parameter. If you set it to FALSE, OctaPHP will not try to protect your field or table names with backticks.
<br />

**$db->or_where();** <br />
This function is identical to the one above, except that multiple instances are joined by OR:
```
 $db->where('name !=', $name);
$db->or_where('id >', $id);

// Produces: WHERE name != 'Joe' OR id > 50
```
<br />

**$db->where_in();** <br />
Generates a WHERE field IN ('item', 'item') SQL query joined with AND if appropriate
```
$names = array('Frank', 'Todd', 'James');
$db->where_in('username', $names);
// Produces: WHERE username IN ('Frank', 'Todd', 'James')
```
<br />

**$db->or_where_in();** <br />
Generates a WHERE field IN ('item', 'item') SQL query joined with OR if appropriate
```
$names = array('Frank', 'Todd', 'James');
$db->or_where_in('username', $names);
// Produces: OR username IN ('Frank', 'Todd', 'James')
```
<br />

**$db->where_not_in();** <br />
Generates a WHERE field NOT IN ('item', 'item') SQL query joined with AND if appropriate
```
 $names = array('Frank', 'Todd', 'James');
$db->where_not_in('username', $names);
// Produces: WHERE username NOT IN ('Frank', 'Todd', 'James')
```
<br />

**$db->or_where_not_in();** <br />
Generates a WHERE field NOT IN ('item', 'item') SQL query joined with OR if appropriate
```
$names = array('Frank', 'Todd', 'James');
$db->or_where_not_in('username', $names);
// Produces: OR username NOT IN ('Frank', 'Todd', 'James')
```
<br />

**$db->like();** <br />
This function enables you to generate LIKE clauses, useful for doing searches.

* Simple key/value method: <br />
```
$db->like('title', 'match');

// Produces: WHERE title LIKE '%match%' 
```
If you use multiple function calls they will be chained together with AND between them:
```
$db->like('title', 'match');
$db->like('body', 'match');

// WHERE title LIKE '%match%' AND body LIKE '%match%
```
If you want to control where the wildcard (%) is placed, you can use an optional third argument. Your options are 'before', 'after' and 'both' (which is the default). 
```
$db->like('title', 'match', 'before');
// Produces: WHERE title LIKE '%match'

$db->like('title', 'match', 'after');
// Produces: WHERE title LIKE 'match%'

$db->like('title', 'match', 'both');
// Produces: WHERE title LIKE '%match%' 
```
If you do not want to use the wildcard (%) you can pass to the optional third argument the option 'none'. 
```
$db->like('title', 'match', 'none');
// Produces: WHERE title LIKE 'match' 
```
* Associative array method: <br />
```
$array = array('title' => $match, 'page1' => $match, 'page2' => $match);

$db->like($array);

// WHERE title LIKE '%match%' AND page1 LIKE '%match%' AND page2 LIKE '%match%'
```
<br />

**$db->or_like();** <br />
This function is identical to the one above, except that multiple instances are joined by OR:
```
$db->like('title', 'match');
$db->or_like('body', $match);

// WHERE title LIKE '%match%' OR body LIKE '%match%'
```

**$db->not_like();** <br />
This function is identical to like(), except that it generates NOT LIKE statements:
```
$db->not_like('title', 'match');

// WHERE title NOT LIKE '%match%
```
<br />

**$db->or_not_like();** <br />
This function is identical to not_like(), except that multiple instances are joined by OR:
```
$db->like('title', 'match');
$db->or_not_like('body', 'match');

// WHERE title LIKE '%match% OR body NOT LIKE '%match%'
```
<br />

**$db->group_by();** <br />
Permits you to write the GROUP BY portion of your query:
```
$db->group_by("title");

// Produces: GROUP BY title 
```
You can also pass an array of multiple values as well:
```
$db->group_by(array("title", "date"));

// Produces: GROUP BY title, date
```
<br />

**$db->order_by();** <br />
Lets you set an ORDER BY clause. The first parameter contains the name of the column you would like to order by. The second parameter lets you set the direction of the result. Options are asc or desc, or random. 
```
$db->order_by("title", "desc");

// Produces: ORDER BY title DESC 
```
You can also pass your own string in the first parameter:
```
$db->order_by('title desc, name asc');

// Produces: ORDER BY title DESC, name ASC 
```
Or multiple function calls can be made if you need multiple fields.
```
$db->order_by("title", "desc");
$db->order_by("name", "asc");

// Produces: ORDER BY title DESC, name ASC 
```
<br />

**$db->insert();** <br />
Generates an insert string based on the data you supply, and runs the query. You can either pass an array or an object to the function. Here is an example using an array:
```
$data = array(
   'title' => 'My title' ,
   'name' => 'My Name' ,
   'date' => 'My date'
);

$db->insert('mytable', $data);

// Produces: INSERT INTO mytable (title, name, date) VALUES ('My title', 'My name', 'My date')
```
The first parameter will contain the table name, the second is an associative array of values.
Here is an example using an object:
```
/*
    class Myclass {
        var $title = 'My Title';
        var $content = 'My Content';
        var $date = 'My Date';
    }
*/

$object = new Myclass;

$db->insert('mytable', $object);
Generates an insert string based on the data you supply, and runs the query. You can either pass an array or an object to the function. Here is an example using an array:

// Produces: INSERT INTO mytable (title, content, date) VALUES ('My Title', 'My Content', 'My Date')
```
The first parameter will contain the table name, the second is an object.
> __Note:__ All values are escaped automatically producing safer queries.
<br />

**$db->insert_batch();** <br />
Generates an insert string based on the data you supply, and runs the query. You can either pass an array or an object to the function. Here is an example using an array:
```
$data = array(
    [
        'title' => 'My title' ,
        'name' => 'My Name' ,
        'date' => 'My date'
    ],
    [
        'title' => 'Another title' ,
        'name' => 'Another Name' ,
        'date' => 'Another date'
    ]
);
$db->insert_batch($data, 'mytable');

// Produces: INSERT INTO mytable (title, name, date) VALUES ('My title', 'My name', 'My date'), ('Another title', 'Another name', 'Another date')
```
The first parameter is an associative array of values, the second will contain the table name.
> __Note:__ All values are escaped automatically producing safer queries.
<br />

**$db->update();** <br />
Generates an update string and runs the query based on the data you supply. You can pass an array or an object to the function. Here is an example using an array:
```
$data = array(
    'id' => $id,
    'title' => $title,
    'name' => $name,
    'date' => $date
);
$db->update('mytable', $data, 'id');

// Produces:
// UPDATE mytable
// SET title = '{$title}', name = '{$name}', date = '{$date}'
// WHERE id = $id
```
<br />

**$db->update_batch();** <br />
Generates an update string based on the data you supply, and runs the query. You can either pass an array or an object to the function. Here is an example using an array:
```
$data = array(
    [
        'id' => '1' ,
        'title' => 'My title' ,
        'name' => 'My Name 2' ,
        'date' => 'My date 2'
    ],
    [
        'id' => '2' ,
        'title' => 'Another title' ,
        'name' => 'Another Name 2' ,
        'date' => 'Another date 2'
    ]
);

$db->update_batch('mytable', $data, 'id'); 

// Produces:
// UPDATE mytable
// SET title = 'My title', name = 'My Name 2', date = 'My date 2'
// WHERE id = $id
// ELSE END
// SET title = 'Another title', name = 'Another Name 2', date = 'Another date 2'
// WHERE id = $id
```

The first parameter will contain the table name, the second is an associative array of values, the third parameter is the where key.
> __Note:__ All values are escaped automatically producing safer queries.
<br />

**$db->delete();** <br />
Generates a delete SQL string and runs the query.
```
$db->delete('mytable', array('id' => $id));

// Produces:
// DELETE FROM mytable
// WHERE id = $id
```
The first parameter is the table name, the second is the where clause.
If you want to delete multiple data from a table, the second parameter must be an array of ID's
```
$db->delete($table,[1,2,3]);

// Produces:
// DELETE FROM mytable
// WHERE (id) IN (1,2,3)
```

**$db->delete_all();** <br />
This will wipe all the data of a table
```
$db->delete_all('mytable');

// Produces:
// TRUNCATE TABLE mytable
```
<br />

**Example Of Queries Using OctaPHP Active Record** <br />
```
$select = array(
   "*",
   "u.first_name",
   "u.last_name",
   "u.email",
   "ut.type"
);

$db->select($select);
$db->order_by("u.userID","DESC");
$db->group_by("u.user_type");
$db->join("user_role AS ur","ur.user_role_id=ut.user_role","LEFT");
$db->join("user_type AS ut","ut.user_type_id=u.user_type","LEFT");
$db->get('users AS u');
$result_data = $db->result(); //this will return an array of result
```
<br />

**LIST OF PRE-DEFINED CLASS** <br />
```
--$db->insert_id() //return the last inserted id
--$db->select()
--$db->where()
--$db->or_where()
--$db->where_in()
--$db->or_where_in()
--$db->where_not_in()
--$db->or_where_not_in()
--$db->order_by()
--$db->group_by()
--$db->join()
--$db->get()
--$db->row()
--$db->num_rows()
--$db->result()
--$db->like()
--$db->or_like()
--$db->not_like()
--$db->or_not_like()
```

# License
[![License](http://img.shields.io/:license-mit-blue.svg?style=flat-square)](http://badges.mit-license.org)
- [MIT](LICENSE.md)
- Copyright 2019 © OctaORM.

# Acknowledgments
* RedBeanPHP

# Support
Reach me out on this social media site. <br />
[Linkedin.com](https://www.linkedin.com/in/melquecedec-catang-catang)