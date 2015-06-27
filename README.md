# House

A super-minimal pure PHP MVC toolkit.

* Fast. No bloat to slow 'er down.
* Modular. Everything is optional.
* Familiar. & otherwise intuitive.


## Controller

The controller layer represents the interface for your application. 
Use it to define a `Route`, accept a `Request`, and return a `Response`.

### Hello World

We'll start with a Hello world route in `app.php`.

	<?php
	
	$app = new House\Router;
	$app->get('/', function($req, $resp){
		return 'Hello World';			
	});

### Advanced route matching

The router is powerful in its simplicity.

* Exact string match
* Regular expressions
* Simplified expressions
* Array of conditions
* Arbitrary callbacks

You can even group routes based on criteria:

```php
$app->group('/api/1', function($app){
	$app->group('/user', function($app){
		$app->put(function(){
			// Create user
		})
		->get('/:id', function(){
			// Retrieve user
		})
		->post('/:id', function(){
			// Update user
		})
		->delete('/:id', function(){
			// Delete user
		});
	});
});
```

You can attach middleware to routes with `->before()` and `->after()`.

```php
$app->before('*', function($req, $resp){
	House\Log::info('Request: ' . $req);
	$resp->code(200);
});
```

You can optionally catch errors per route as well.

```php
$app->error('*', function($req, $resp){
	House\Log::error($req->exception->getMessage());
	return 500;
});
```

Notice that the return value is intuitively written to the response.

* Strings write to the repsonse body
* Integers set the response code
* Arrays for Rack-style response

See `example.php` for more examples of exactly how badass `Router` is.

## Model

The model layer is very basic. There is no ORM. If you need a more robust
model layer, please see Symfony or php-activerecord. Otherwise, check this:

```php
class User extends House\Model {}

// throws House\NotFound
User::find(['id' => 1]);

// returns array() 
$users = User::where(['status' => ['active', 'inactive']])->limit(5)->all();
```

### Supported databases

* MySQL
* More to come..


## View

The view layer is almost vanishingly small. It's so simple, it doesn't even need to exist.
Returning a string from a controller method automatically calls `$response->write($string)`.

So, view helpers are any method that return a string. We've bundled a few to get you started,
but House could potentially support any template engine, flat file, etc. Since House is not a
framework, there is no special View registry or folder, so you can easily install alternative
template engines with composer and use them as-is.

```php
$app->get('test.json', function($req, $resp){
	return json(['hello' => 'world']);
});

$app->get('/', function(){
	return haml('index');
});
```
