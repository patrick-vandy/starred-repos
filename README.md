# starred-repos

Retrieve and display the most starred repos on GitHub. There is an import utility that retrieves the most starred repos
from the GitHub API and stores them in a MySQL table. Then there is a web interface built on bootstrap to view the imported
repos.

This is built on a simple custom framework that provides basic routing and an MVC architecture.


## Dependencies

- Apache v2.x with mod rewrite enabled
- PHP v5.4 or greater
- MySQL v5.2 or greater
- The following php extensions are required:
  - php5-cli
  - php5-common
  - php5-curl
  - php5-json
  - php5-mcrypt
  - php5-mysql  


## Install

##### Installation steps:

- Clone this repository
  * `git clone https://github.com/patrick-vandy/starred-repos.git`
- The `public` directory of this repository should be the document root of a virtual host
  * This keeps the `framework`, `_src` and `.git` directories outside the public root for security
- Create a MySQL database
- Load `_src/sql/github.sql` into the database
  * `mysql -u root -p [DB_NAME] < _src/sql/github.sql`
- Create a user with the following priviliges on the database: `SELECT, INSERT, UPDATE, DELETE, EXECUTE, TRIGGER`
- Update the following values in `framework/config.php` to match your database and user:
  * `Config::set('db_host', '[DB_HOST]');`
  * `Config::set('db_name', '[DB_NAME]');`
  * `Config::set('db_user', '[DB_USER]');`
  * `Config::set('db_pass', '[DB_PASSWORD]');`
- Update the following config value to match the server_name directive of the virtual host
  * `Config::set('site_url', '[SITE_URL]');`
- Update the following config values (optional)
  * `//Config::set('github_token', '[TOKEN]'); // uncomment to authenticate to GitHub API using access token`
  * `Config::set('max_repos', 200); // set the number of repos to import from the GitHub API`
- Run the import utility to save the repos to the database from the GitHub API
  * `cd /path/to/repo/public`
  * `php index.php -c util -m import`
- Alternatively, you can override the max_repos setting to import more or less repos
  * `php index.php -c util -m import -i 150`


##### Installation Notes:

The framework relies on mod_rewrite. Ideally these directives are placed directly in the virtual host config file. However,
for easy installation they are contained in a .htacess file in the public directory. `AllowOverride All` has to be set for
the virtual host to recognize the .htaccess file.

If an access_token **is not set** in the config file and the import utility fails with a 403 response from the GitHub API, try
creating an access_token on GitHub and setting it in the config file. The 403 response typically happens due to the maximum
number of allowed requests to the API being exceeded. Authenticated requests using access_token are allowed more requests.

If an access_token **is set** and the import utility fails with a 401, the access_token is not valid. Try commenting out the
line in the config file that sets the token and run the import again.


## Update

This will update repos already in the database and insert any missing ones. If there are existing repos in the database
that are not included in the import, they are deleted.

- Update the imported repos from the GitHub API by running the import utility
  * `php index.php -c util -m import`


## Framework Overview

A custom framework is used for this project. The framework is built on an MVC architecture and provides some
basic implementations of routing, input processing and database interaction.

Essentially, the point to this framework is to allow code to be written following the MVC pattern and utilize pretty URLs
to route http requests to an appropriate controller and method. There is also the ability to create CLI controllers and
invoke them from the command line through the main entry file, `public/index.php`.

Online documentation of all classes and methods is available [here](http://ec2-52-33-6-212.us-west-2.compute.amazonaws.com:8080).


##### Namespace / Naming Conventions

PSR-4 conventions are followed for namespaces and file names. The namespace relative to the root namespace should match
the directory structure relative to the root directory for all classes and files. Files should be named the same as the
class name `i.e. \framework\controller\User is defined in framework/controller/User.php`

Composer's autoloader is used, so there is no need to use require statements.


##### Web Routing / Controllers

The default routing schema is `BASE_URL/controller/method/[arg1/[arg2...]]`. For example, the URL www.mysite.com/user/view/10
would result in `\framework\controller\User::view($id)` being called with a value of 10 for `$id`.

Let's say we want to create the page /home/index. The following controller class would be created in `framework/controllers/Home.php`

```
<?php

namespace framework\controller;

use framework\core\Controller;

class Home extends Controller
{
  
  public function __construct()
  {
    parent::__construct();
  }
  
  
  public function index()
  {
    echo 'Hello World!';
  }
  
}
```

*Notice the controller extends `\framework\core\Controller` and calls the parent constructor*


##### CLI Routing / Controllers

It is also possible to create CLI controllers. These can only be invoked from the command line. If you try to access them
via an HTTP request you will get a 404 response. CLI controllers are invoked by running

`php index.php -c CONTROLLER -m METHOD [-i ARG [-i ARG2...]]`

CLI controllers are created using the same technique as web controllers except they must be in the namespace
`\framework\controllers\cli`. Let's create an example import util in `framework/controllers/cli/Util.php` that
we could call using `php index.php -c util -m import -i 10`

```
<?php

namespace framework\controller\cli;

use framework\core\Controller;
use framework\model\Util AS UtilModel;

class Util extends Controller
{

  private $model;
  
  public function __construct()
  {
    parent::__construct();
    $this->model = new UtilModel();
  }
  
  
  public function import($limit)
  {
    for ($i = 1; $i <= $limit; $i++)
    {
      $this->model->import($i);
    }
  }
  
}
```


##### Models / Database

In the previous example of a CLI controller we used a model named Util. Models live in `\framework\model`and should
extend `\framework\core\Model`. Let's create the Util model from the above example and interact with the database.

```
<?php

namespace framework\model;

use framework\core\Model;
use framework\core\DB;

class Util extends Model
{

  private $db;

  public function __construct()
  {
    parent::__construct();
    $this->db = DB::connect(); // returns a PDO instance using connection info from config file
  }
  
  public function import($number)
  {
    $sql = 'INSERT INTO t1 (col1) VALUES (?)';
    $stmt = $this->db->prepare($sql);
    $stmt->execute([$number]);
  }

}
```


##### Views

Views are simply static html files with placeholders denoted by `%%VAR_NAME%%`.

`\framework\core\Controller::load_view($name, $values = [], $ext = '.html')` is a protected method that can be
called within any controller class to load a view file and replace the placeholders with values. `$values`
is an associative array with keys corresponding to the placeholders in the view. For example, given a view
file in `view/piece/linked-list.html` containing:

```
<li><a href="%%LINK%%">%%TEXT%%</a></li>
```

The following code can be used inside a controller class to load the piece with values and store it in a variable:

```
$values = ['link' => 'http://google.com', 'text' => 'Google'];
$html = $this->load_view('piece/linked-list', $values);
```

Views can be anything from a small piece like the example above to a full html document or template. The point to
the views is to store **ALL** html in views and prevent the need to build ugly html strings in php classes.


##### Core Classes

PDO is extended in `\framework\core\PDO` to customize it for the framework. See the file for more details.

All of the classes are well documented, so for specifics of each class simply look at the file.
