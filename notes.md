# Notes

## Queries

### Don' use raw

Don't do this:

```php
$active_movie_types = $this->Movie->query('SELECT id FROM '.Environment::read('database.prefix').'movie_types WHERE enabled = 1 AND `name` IN (\''.implode("','",$movie_type_names).'\')');
```

Do this:

```php
$objMovieType = ClassRegistry::init('Movie.MovieType');
$option = array(
	'fields' => array('id'),
	'conditions' => array(
		'enabled' => 1,
		'name IN' => $movie_type_names
	)
);
$active_movie_types = $objMovieType->find('all', $option);
```

Because the `model->find` is database agnotic, so when we decide to change database, we dont need to change the query again

and `model->find` is also have security feature to prevent SQL Injection/

### Example of Join (1-many) vs Contain (many-many):

```php
$conditions = $this->Common->get_filter_conditions($data_search, $model, $languages_model, $this->filter);

$contain = array(
    'MovieType' => array( 'fields' => array('name') ),
);

$this->Paginator->settings = array(
	'contain' => $contain,
	'fields' => array($model.".*", $languages_model.".name"),
	'conditions' => array($conditions),
    'limit' => Environment::read('web.limit_record'),
	'order' => array($model . '.created' => 'DESC'),
	'joins' => array(
		array(
			'alias' => $languages_model,
			'table' => Environment::read('table_prefix') . 'movie_languages',
			'type' => 'left',
			'conditions' => array(
				$model.'.id = '.$languages_model.'.movie_id',
				$languages_model.'.language' => $this->lang18
			),
		),
	),
);

$this->paginate();
```

### XRef Tables

Use `saveAll` using array like this
```php
$data_update['Student']['id'] = $student_id
$data_update['Subject'] = null
```

Delete directly from `students_subjects` using `delete(integer $id = null)` or `deleteAll(mixed $conditions, $cascade = true)`. In the first option, when you run `saveAll($data_update)`, CakePHP will automatically delete the records in xRef table.

## Config files

```php
bootstrap => /**
 * This file is loaded automatically by the app/webroot/index.php file after core.php
 *
 * This file should load/create any application wide configuration settings, such as
 * Caching, Logging, loading additional configuration files.
 *
 * You should also use this file to include any files that provide global functions/constants
 * that your application uses.
```

```php
core =>
* This is core configuration file.
 *
 * Use it to configure core behavior of Cake.en
```

```php
env =>
for user adjustable configuration like db, email setting
Which runs first, which runs 2nd, which runs 3rd? => i think it would be core, bootstrap, and then env
In cake2, where's the best place to set eg root domain like http://localhost/tourist/ => env
```

In cake3, `app.config` vs `.env`: https://book.cakephp.org/3/en/development/configuration.html

## Make APIs

https://github.com/atabegruslan/Tourist-Blog-Cake3/tree/master/Illustrations/api/

## Reverse routing explained

http://www.sethcardoza.com/2009/08/cakephp-reverse-routing/
