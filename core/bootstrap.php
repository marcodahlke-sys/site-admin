<?php
declare(strict_types=1);

require_once BASE_PATH . '/core/helpers.php';

require_once BASE_PATH . '/core/App.php';
require_once BASE_PATH . '/core/Config.php';
require_once BASE_PATH . '/core/Database.php';
require_once BASE_PATH . '/core/Session.php';
require_once BASE_PATH . '/core/View.php';
require_once BASE_PATH . '/core/Controller.php';
require_once BASE_PATH . '/core/Router.php';
require_once BASE_PATH . '/core/Auth.php';

require_once BASE_PATH . '/Models/BaseModel.php';
require_once BASE_PATH . '/Models/AdminUser.php';
require_once BASE_PATH . '/Models/Category.php';
require_once BASE_PATH . '/Models/Counter.php';
require_once BASE_PATH . '/Models/Like.php';
require_once BASE_PATH . '/Models/Tag.php';
require_once BASE_PATH . '/Models/Description.php';
require_once BASE_PATH . '/Models/Image.php';

require_once BASE_PATH . '/Libraries/CalendarBuilder.php';

require_once BASE_PATH . '/Controllers/HomeController.php';
require_once BASE_PATH . '/Controllers/AuthController.php';
require_once BASE_PATH . '/Controllers/AdminController.php';
require_once BASE_PATH . '/Controllers/LikeController.php';

$config = new App\Core\Config(BASE_PATH . '/.env');

$app = App\Core\App::getInstance();
$app->set('config', $config);
$app->set('db', new App\Core\Database($config));
$app->set('session', new App\Core\Session($config));
$app->set('auth', new App\Core\Auth($app->db(), $config));

$app->auth()->bootRememberLogin();