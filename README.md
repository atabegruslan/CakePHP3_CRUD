# CakePHP 3.8

## Tutorials

- Official Documentation: https://book.cakephp.org/3/en/index.html 
- http://www.w3programmers.com/category/cakephp/ (Old, but good reference)
- https://www.youtube.com/playlist?list=PLnBvgoOXZNCMdw9s1E6StlXaYZz0xti4n (v3.4)
- https://www.youtube.com/playlist?list=PLmrTMUhqzS3gJzugIEBKwHyG1SJIiN8nU (v3.6)
- https://www.youtube.com/playlist?list=PLrOp29ATDP7ZhqPSsZNCFlBJpnv38vuZg 
- https://www.youtube.com/playlist?list=PLmrTMUhqzS3jZEhNrrysfYAJUNFjjDqmB
- https://www.youtube.com/playlist?list=PLsoBxH455yoZExk1_J3QxbW5F1F0MJJez
- https://www.youtube.com/playlist?list=PLWov0H-mAq9inDqfhh3hFbKYpv3P6p1_d
- https://www.sitepoint.com/application-development-cakephp/ (Old, but good reference)
- https://medium.com/@Phillaf/cakephp-crud-walk-through-501310a43462
- https://book.cakephp.org/3/en/controllers/components/authentication.html
- https://book.cakephp.org/2/en/models/associations-linking-models-together.html
- https://www.startutorial.com/articles/view/how-to-build-facebook-login-using-cakephp-facebook-login-plugin
- https://book.cakephp.org/3/en/tutorials-and-examples/blog-auth-example/auth.html
- https://www.youtube.com/playlist?list=PLy6f6YeaisJLnQpKpeeJX_ooJg_IH1Oib (Old, but good reference)
- https://www.youtube.com/playlist?list=PLillGF-RfqbbObywQ2KOugFDL9F_k4yLr
- https://www.youtube.com/playlist?list=PLWov0H-mAq9inDqfhh3hFbKYpv3P6p1_d

## Preliminaries

Include php intl extension in `php.ini`

## Install

### via CLI:

`composer create-project --prefer-dist cakephp/app tourist_blog`

### Or via NetBeans: 

http://www.imi21.com/cakephp-3-addressbook-1/

1. download 'org-netbeans-modules-php-cake3-0.4.0.nbm' from https://github.com/junichi11/cakephp3-netbeans/releases (https://github.com/junichi11/cakephp3-netbeans)

2. Tools > Plugins > Downloaded > Add Plugins > the .nbm

3. New PHP project, cakephp 3 framework project

4. right click project > composer > update(dev)

## Checks

- `config/app.php`
	- Security salt
	- DB Credentials

```php
'Datasources' => [
    'default' => [
    	'host' => '***',
        'username' => '***',
        'password' => '***',
        'database' => '***',
```

## Database

Create DB `tourist_blog.sql`

### Note: Naming conventions:

|     | Cake 3 | Cake 2 |
| --- | --- | --- |
| Database Table | entries | entries |
| X-Ref Table | entries_somethings | entries_somethings |
| Model class | src/Model/Entry.php | Model/Entry.php |
| Controller class | src/Controller/EntryController.php | Controller/EntriesController.php |
| View template | src/Template/Entries/(CRUD).ctp | View/Entries/(CRUD).ctp |
| URL | {domain-name}/tourist_blog/entries/ | {domain-name}/tourist_blog/entries/ |

## Scaffold

`bin/cake bake all entries` (use plural "entries")

Now the entire basics of the Entry CRUD is done. See `{domain-name}/tourist_blog/entries/`

## Login

`bin/cake bake all users`

`src/Controller/AppController.php`
```php
$this->loadComponent('Auth', [
    'authenticate' => [
        'Form' => [
            'fields' => [
                'username' => 'email',
                'password' => 'password'
            ]
        ]
    ],
    'loginAction' => [
        'controller' => 'Users',
        'action' => 'login'
    ]
]);
```

`src/Model/Entity/User.php`
```php
protected function _setPassword($value) {
    return (new DefaultPasswordHasher)->hash($value);
}
```

`src/Controller/UsersController`
```php
public function login()
{
    if ($this->request->is('post')) {
        $user = $this->Auth->identify();
        if ($user) {
            $this->Auth->setUser($user);
            return $this->redirect($this->Auth->redirectUrl());
        }

        $this->Flash->error('Your username or password is incorrect');
    }
}

public function logout()
{
    $this->Flash->success('Logged out');
    return $this->redirect($this->Auth->logout());
}
```

`src/Template/Users/login.ctp`
```html
<h1>Login</h1>

<?= $this->Form->create(); ?>
<?= $this->Form->input('email'); ?>
<?= $this->Form->input('password'); ?>
<?= $this->Form->button('Login'); ?>
<?= $this->Form->end(); ?>
```

## Sign Up

Alterations to UserController:

```php
use Cake\Event\Event;

class UsersController extends AppController
{

    public function beforeFilter(Event $event)
    {
        // tell Auth not to check authentication when doing the 'register' action
        $this->Auth->allow('register');
    }

    public function login()
    {
        if ($this->Auth->user()) { // if already logged in
            return $this->redirect('/entries/');
        }

        if ($this->request->is('post')) {
            $user = $this->Auth->identify();
            if ($user) {
                $this->Auth->setUser($user);
                $this->Flash->success(__('Hello ' . $this->Auth->user('name')));   

                return $this->redirect($this->Auth->redirectUrl());
            }

            $this->Flash->error('Your username or password is incorrect');
        }
    }

    public function logout()
    {
        $this->Flash->success('Logged out');
        return $this->redirect($this->Auth->logout());
    }

    public function register()
    { 
        if ($this->Auth->user()) { // if already logged in
            return $this->redirect('/entries/');
        }

        if ($this->request->is('post')) {
            $user = $this->Users->newEntity();

            $userData = $this->request->getData();

            if ($userData['password'] === $userData['password_confirm']) { 
                unset($userData['password_confirm']);
                $userData['type'] = 'normal';

                $user = $this->Users->patchEntity($user, $userData);

                if ($this->Users->save($user)) { 
                    $this->Auth->setUser($user->toArray());
                    $this->Flash->success(__('Hello ' . $this->Auth->user('name')));

                    return $this->redirect($this->Auth->redirectUrl());
                }
                $this->Flash->error(__('The user has NOT been saved.')); 
            }
            $this->Flash->error(__('Password confirmation failure.')); 
        }
    }
```

`src/Template/User/register.ctp`
```html
<h1>User Registration</h1>

<?= $this->Form->create(); ?>
<?= $this->Form->control('name'); ?>
<?= $this->Form->control('email'); ?>
<?= $this->Form->control('password', array('type' => 'password')); ?>
<?= $this->Form->control('password_confirm', array('type' => 'password')); ?>
<?= $this->Form->control('Signup', ['type' => 'submit']); ?>
<?= $this->Form->end(); ?>

<?= $this->Html->link(__('Login'), ['controller' => 'Users', 'action' => 'login']) ?>
```

Add `<?= $this->Html->link(__('Register'), ['controller' => 'Users', 'action' => 'register']) ?>` to login.ctp

Add `<?= $this->Html->link(__('Logout'), ['controller' => 'Users', 'action' => 'logout']) ?>` to pages inside login

### Helpful Tutorials:

- http://alvinalexander.com/php/cakephp-user-registration-form-example-recipe

## Bootstrap plugin

### For CakePHP 2

https://slywalker.github.io/cakephp-plugin-boost_cake/

In app/composer.json

```js
"require": {
	"slywalker/boost_cake": "*"
}
```

In CLI in app folder: `composer install`

In app/Config/bootstrap.php, add: `CakePlugin::load('BoostCake');`

In AppController

```php
class AppController extends Controller {

	public $helpers = array(
		'Session',
		'Html' => array('className' => 'BoostCake.BoostCakeHtml'),
		'Form' => array('className' => 'BoostCake.BoostCakeForm'),
		'Paginator' => array('className' => 'BoostCake.BoostCakePaginator'),
	);

}
```

Helpful link: http://stackoverflow.com/questions/22213522/using-slywalkers-twitter-bootstrap-as-a-plugin-in-cakephp

In view/layouts/default.ctp, inside head tags:

```php
<title>...</title>

<?php
	echo $this->Html->meta('icon');
	echo $this->Html->css('bootstrap.min');

	echo $this->fetch('meta');
	echo $this->fetch('css');
	echo $this->fetch('script');
?>

<?php echo $this->Html->script("jquery.min") ?>
<?php echo $this->Html->script("bootstrap.min") ?>
```

In index view ctp (for example):

```php
<?php echo $this->Form->create('BoostCake', array(
	'inputDefaults' => array(
		'div' => 'form-group',
		'label' => false,
		'wrapInput' => false,
		'class' => 'form-control'
	),
	'class' => 'well form-inline'
)); ?>

	<?php echo $this->Form->input('email', array(
		'placeholder' => 'Email'
	)); ?>
	<?php echo $this->Form->input('password', array(
		'placeholder' => 'Password'
	)); ?>
	<?php echo $this->Form->input('remember', array(
		'div' => 'checkbox',
		'class' => false,
		'label' => 'Remember me'
	)); ?>
	<?php echo $this->Form->submit('Sign in', array(
		'div' => 'form-group',
		'class' => 'btn btn-default'
	)); ?>

<?php echo $this->Form->end(); ?>      
```

### For CakePHP 3

https://github.com/friendsofcake/bootstrap-ui

In CLI:

```
composer require friendsofcake/bootstrap-ui

bin/cake plugin load BootstrapUI
```

In src\View\AppView.php

```php
use BootstrapUI\View\UIView; // use UIView

class AppView extends UIView // extend UIView
{
    public function initialize()
    {
        parent::initialize(); // add this line
    }
}
```

Add jquery and bootstrap into `webroot/js/jquery/jquery.js`, `webroot/js/bootstrap/bootstrap.js` and `webroot/css/bootstrap/bootstrap.css`

In login.ctp (for example):

```php
<?= $this->Form->create(); ?>
<?= $this->Form->control('email'); ?>
<?= $this->Form->control('password'); ?>
<?= $this->Form->control('Login', ['type' => 'submit']); ?>
```

## 2 Tables

### 1) Create a `countries` table

### 2) Generate files

By either run `bin/cake bake all countries` or seperately:

| CakePHP 2 | CakePHP 3 |
|---|---|
| `console/cake bake model Country` | `bin/cake bake model countries` |
| `console/cake bake controller Countries` | `bin/cake bake controller countries` |
| `console/cake bake view Countries` | `bin/cake bake template countries` |

#### Some theory : https://book.cakephp.org/3/en/orm/associations.html

|Relationship | Association Type | Example | Complementing Model should have |
|---|---|---|---|
| 1-1 | hasOne | 1 entry is only in 1 country | belongsTo |
| 1-many | hasMany | A user can make many entries | belongsTo |
| many-1 | belongsTo | Many entries can belong to the same user | hasOne or hasMany |
| many-many | hasAndBelongsToMany (Cake 2) / belongsToMany (Cake 3) | Continents and Countries |

### 3) Update the models

<table>
<tr>
<td>
src/Model/Table/CountriesTable.php
<pre>
<code>
namespace App\Model\Table;
use Cake\ORM\Table;

class CountriesTable extends Table
{
    public function initialize(array $config)
    {
        //...
        $this->hasOne('Entries', [
            'foreignKey' => 'country_id',
        ]);
    }
</code>
</pre>
</td>
<td>
src/Model/Table/EntriesTable.php
<pre>
<code>
namespace App\Model\Table;
use Cake\ORM\Table;

class EntriesTable extends Table
{
    public function initialize(array $config)
    {
        //...
        $this->belongsTo('Countries', [
            'foreignKey' => 'country_id',
        ]);
    }
</code>
</pre>
</td>
</tr>
</table>

#### For completion, these are the remaining cases

##### The above `hasOne` example in CakePHP 2:

<table>
<tr>
<td>
Model/Country.php
<pre>
<code>
App::uses('AppModel', 'Model');

class Country extends AppModel
{
    //...
    public $hasOne = array(
        'Entry' => array(
            'foreignKey' => 'country_id'
        )
    );
}
</code>
</pre>
</td>
<td>
Model/Entry.php
<pre>
<code>
App::uses('AppModel', 'Model');

class Entry extends AppModel
{
    //...
    public $belongsTo = array(
        'Country' => array(
            'foreignKey' => 'country_id',
        ),
    );
}
</code>
</pre>
</td>
</tr>
</table>

##### `hasMany` example in CakePHP 3:

<table>
<tr>
<td>
src/Model/Table/UsersTable.php
<pre>
<code>
namespace App\Model\Table;
use Cake\ORM\Table;

class UsersTable extends Table
{
    public function initialize(array $config)
    {
        //...
        $this->hasMany('Entries', [
            'foreignKey' => 'user_id',
        ]);
    }
</code>
</pre>
</td>
<td>
src/Model/Table/EntriesTable.php
<pre>
<code>
namespace App\Model\Table;
use Cake\ORM\Table;

class EntriesTable extends Table
{
    public function initialize(array $config)
    {
        //...
        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
        ]);
    }
</code>
</pre>
</td>
</tr>
</table>

##### `hasMany` example in CakePHP 2:

<table>
<tr>
<td>
Model/User.php
<pre>
<code>
App::uses('AppModel', 'Model');

class User extends AppModel
{
    //...
    public $hasMany = array(
        'Entry' => array(
            'foreignKey' => 'user_id'
        )
    );
}
</code>
</pre>
</td>
<td>
Model/Entry.php
<pre>
<code>
App::uses('AppModel', 'Model');

class Entry extends AppModel
{
    //...
    public $belongsTo = array(
        'User' => array(
            'foreignKey' => 'user_id',
        ),
    );
}
</code>
</pre>
</td>
</tr>
</table>

##### `belongsToMany` example in CakePHP 3:

<table>
<tr>
<td>
src/Model/Table/CountriesTable.php
<pre>
<code>
namespace App\Model\Table;
use Cake\ORM\Table;

class CountriesTable extends Table
{
    public function initialize(array $config)
    {
        //...
        $this->belongsToMany('Continents', [
            'foreignKey' => 'country_id',
            'targetForeignKey' => 'continent_id',
            'joinTable' => 'continents_countries',
        ]);
    }
</code>
</pre>
</td>
<td>
src/Model/Table/ContinentsTable.php
<pre>
<code>
namespace App\Model\Table;
use Cake\ORM\Table;

class ContinentsTable extends Table
{
    public function initialize(array $config)
    {
        //...
        $this->belongsToMany('Countries', [
            'foreignKey' => 'continent_id',
            'targetForeignKey' => 'country_id',
            'joinTable' => 'continents_countries',
        ]);
    }
</code>
</pre>
</td>
</tr>
</table>

##### `hasAndBelongsToMany` example in CakePHP 2:

<table>
<tr>
<td>
Model/Country.php
<pre>
<code>
App::uses('AppModel', 'Model');

class Country extends AppModel
{
    //...
    public $hasAndBelongsToMany = array(
        'Continent' => array(
            'joinTable' => 'continents_countries',
            'foreignKey' => 'continent_id',
            'associationForeignKey' => 'country_id',
        )
    );
}
</code>
</pre>
</td>
<td>
Model/Continent.php
<pre>
<code>
App::uses('AppModel', 'Model');

class Continent extends AppModel
{
    //...
    public $hasAndBelongsToMany = array(
        'Country' => array(
            'joinTable' => 'continents_countries',
            'foreignKey' => 'continent_id',
            'associationForeignKey' => 'country_id',
        ),
    );
}
</code>
</pre>
</td>
</tr>
</table>

### 4) Adjust Controller and View

```php
class EntriesController extends AppController
{
    public function index()
    {
        $this->paginate = [
            'contain' => ['Users', 'Countries'],
        ];

    //...
```

#### Theory: Join and Contain

![](https://raw.githubusercontent.com/atabegruslan/Tourist-Blog-Cake3/master/Illustrations/CakePHP_Join_vs_Contain.png)

index.ctp
```php
<tbody>
    <?php foreach ($entries as $entry): ?>
    <tr>
        <td><?= $entry->has('country') ? $this->Html->link($entry->country->name, ['controller' => 'Countries', 'action' => 'view', $entry->country->id]) : '' ?></td>
```

## Admin Backside

I decided that only admin can manage users and assign countries to continents.

### Make Plugin

```
bin/cake bake plugin AdminPanel
bin/cake bake all continents --plugin AdminPanel
```

If you pull from repository to another computer, you might see that the newly created plugin is causing problems. In that case, run `composer dumpautoload`. 

https://stackoverflow.com/questions/45405478/cakephp-3-error-generator-plugin-cake-core-exception-missingpluginexception

### Back Making Admin Backside

1. Follow: https://book.cakephp.org/3/en/plugins.html#plugin-routes

2. Also don't forget in `composer.json`
```js
"autoload": {
    "psr-4": {
        "App\\": "src/",
        "AdminPanel\\": "plugins/AdminPanel/src/"
    }
},
```

Other ways of making Admin Backsides: https://github.com/Ruslan-Aliyev/CakePHP_Admin_BackEnds

### Refactor front app's Continents

Now that we have `Continents` in the `AdminPanel` plugin, the `Continents` MVC in the front app is no longer needed.

Before deleting front app's MVC files for `Continents`, we need to refactor the models, so that **AdminPanel's Continents is associated with front app's Countries**.

So edit: `src/Model/Table/CountriesTable.php`
```php
$this->belongsToMany('AdminPanel.Continents', [
    'foreignKey' => 'country_id',
    'targetForeignKey' => 'continent_id',
    'joinTable' => 'continents_countries',
]);
```

and edit: `plugins/AdminPanel/src/Model/Table/ContinentsTable.php`
```php
$this->belongsToMany('Countries', [
    'foreignKey' => 'continent_id',
    'targetForeignKey' => 'country_id',
    'joinTable' => 'continents_countries',
]);
```

## Refactor Auth

Now that we have the front app and the back admin-panel in a plugin, the authentication (ie: the user controller which is "stuck" in the front app) needs to be refactored, to make the login/logout work for both front and back.

1. Create a User Accounts plugin

```
bin/cake bake plugin UAC
bin/cake bake all users --plugin UAC
```

Note: Only now did I notice the message:
```
Action required!

The CakePHP plugin installer v1.3+ no longer requires the
"post-autoload-dump" hook. Please update your app's composer.json
file and remove usage of
Cake\Composer\Installer\PluginInstaller::postAutoloadDump
```

Therefore, rid this line `"post-autoload-dump": "Cake\\Composer\\Installer\\PluginInstaller::postAutoloadDump",` from the `scripts": {}` section of `composer.json`.

2. Edit `composer.json`
```js
"autoload": {
    "psr-4": {
        "App\\": "src/",
        "AdminPanel\\": "plugins/AdminPanel/src/",
        "UAC\\": "plugins/UAC/src/"
    }
},
```

`composer dumpautoload`

3. `src/Application.php`

```php
class Application extends BaseApplication
{
    public function bootstrap()
    {
        $this->addPlugin("UAC");
```

4. Move the `beforeFilter`, `login`, `logout` & `register` functions from `src/Controller/UsersController.php` to `plugins/UAC/src/Controller/UsersController.php`. We will not be using `src/Controller/UsersController.php` anymore once all this is refactored.

5. `src/Controller/AppController.php`

```php
class AppController extends Controller
{
    public function initialize()
    {
        //...
        $this->loadComponent('Auth', [
            //...
            'loginAction' => [
                'plugin' => 'UAC', // <- ADD THIS !!!
                'controller' => 'Users',
                'action' => 'login'
            ]
        ]);
```

6. Now the logout link will be `<?= $this->Html->link(__('Logout'), ['plugin' => 'UAC', 'controller' => 'Users', 'action' => 'logout']) ?>` instead of `<?= $this->Html->link(__('Logout'), ['controller' => 'Users', 'action' => 'logout']) ?>`. So refactor that for everywhere it's used.

7. Now refactor all the `src/Model/Tables` where `Users` is associated (in this case, just `src/Model/Tables/EntriesTable.php`). Make it so that the **front app's Entries is connected to UAC plugin's Users**. 

So edit: `src/Model/Tables/EntriesTable.php`
```php
$this->belongsTo('UAC.Users', [
    'foreignKey' => 'user_id',
]);
```

and: `plugins/UAC/src/Model/Table/UsersTable.php`
```php
$this->hasMany('Entries', [
    'foreignKey' => 'user_id',
]);
```

Also move `login.ctp` & `register.ctp` from `src/Template/Users/` to `plugins/UAC/src/Template/Users/`, and add `'plugin' => 'UAC'` into the reverse routes of login and register.

Only after that you can delete `User`'s MVC files from `src/`.

## Master Layouts

Recall that we used `friendsofcake\bootstrap-ui`. After that, the default master layout will be `\vendor\friendsofcake\bootstrap-ui\src\Template\Layout\default.ctp`. 

But if you want your own custom master layout:

1. Make custom layout `src\Template\Layout\tourist_blog.ctp`

2. In `src/Controller/AppController.php`, `beforeFilter` function, write: `$this->layout = "tourist_blog";`

3. If you want the admin backside to use a different layout, then create another layout `src\Template\Layout\tourist_blog_admin.ctp` and in `plugins/AdminPanel/src/Controller/AppController.php`, `beforeFilter` function, write: `$this->layout = "tourist_blog_admin";`

## Custom input fields

### Cake 2

1. Create: `View/Elements/images_upload_customize.ctp`

2. Usage: `echo $this->element('images_upload_customize' $optionsArray);`

https://book.cakephp.org/2/en/views.html#elements

### Cake 3

1. Create: `src/Template/Element/images_upload_customize.ctp`

2. Usage: `echo $this->element('images_upload_customize' $optionsArray);`

https://book.cakephp.org/3/en/views.html#elements

## Custom Components

### Cake 2

<table>
<tr>
<td>
Controller/Component/CommonComponent.php
<pre>
<code>
App::uses('Component', 'Controller');
App::uses('Folder', 'Utility');
App::uses('File', 'Utility');

class CommonComponent extends Component 
{
    // The other component your component uses
    // public $components = ['Xxx', 'Yyy'];

    // Execute any other additional setup for your component.
    // public function initialize(Controller $controller) 
    // {
    //  parent::initialize($controller);
    // 
    //  if (!class_exists('Xxx'))
    //  {
    //      // load vendor classes if does not load before
    //      App::import('Vendor', 'Xxx');
    //  }
    // }

    public function upload_images($image, $subfolder, $prefix = "")
    {
    
    }
</code>
</pre>
</td>
<td>
Controller/AppController.php
<pre>
<code>
App::uses('Controller', 'Controller');

class AppController extends Controller 
{
    public $components = ['Common'];
</code>
</pre>
</td>
</tr>
</table>

### Cake 3

<table>
<tr>
<td>
src/Controller/Component/CommonComponent.php
<pre>
<code>
namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\Filesystem\Folder;
use Cake\Filesystem\File;

class CommonComponent extends Component
{
    // The other component your component uses
    // public $components = ['Xxx', 'Yyy'];

    // Execute any other additional setup for your component.
    // public function initialize(array $config)
    // {
    //     $this->Xxx->whatever();
    // }

    public function upload_images($image, $subfolder, $prefix = "")
    {
    
    }
</code>
</pre>
</td>
<td>
src/Controller/AppController.php
<pre>
<code>
namespace App\Controller;

use Cake\Controller\Controller;

class AppController extends Controller
{
    public function initialize()
    {
        parent::initialize();

        $this->loadComponent('Common'
</code>
</pre>
</td>
</tr>
</table>

https://book.cakephp.org/3/en/controllers/components.html#using-other-components-in-your-component

#### Theory: Useful Functions

Controller:

`App\Controller\AppController::beforeFilter(\Cake\Event\Event $event)` 

`App\Controller\AppController::beforeRender(\Cake\Event\Event $event)` 

Model:

`Cake\ORM\Table::beforeSave(Event $event, EntityInterface $entity, ArrayObject $options)`

`Cake\ORM\Table::afterSave(Event $event, EntityInterface $entity, ArrayObject $options)`

https://book.cakephp.org/3/en/orm/saving-data.html#saving-entities

## Cron Job

Freestyle and Manual ways:
- https://stackoverflow.com/questions/3192070/cron-job-with-cakephp
- https://sjinnovation.com/set-cron-job-cakephp-project/

Plugin:
- https://github.com/nojimage/cakephp-cron-jobs

Documentation's way:
- https://book.cakephp.org/3/en/console-and-shells.html
    - https://book.cakephp.org/3/en/console-and-shells/commands.html
        - https://book.cakephp.org/3/en/console-and-shells/cron-jobs.html

In Cake 2:

![](https://raw.githubusercontent.com/atabegruslan/Tourist-Blog-Cake3/master/Illustrations/cron_cake_2.PNG)

## Email

https://book.cakephp.org/3/en/core-libraries/email.html

## Misc

- https://www.tutorialandexample.com/cakephp-formhelper/
- https://stackoverflow.com/questions/41148482/cakephp-3-add-specific-js-css-file-to-a-specific-view
- https://stackoverflow.com/questions/43871420/insert-an-icon-into-a-link-with-htmlhelper-in-cakephp-3

# Todo

- Locale
- APIs
- IO Excel/CSV
