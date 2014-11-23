<?php
/**
 * Bootstrapping functions, essential and needed for Cr33kWork to work together with some common helpers. 
 *
 */
function dump($array) {
  echo "<pre>" . htmlentities(print_r($array, 1)) . "</pre>";
}
function destorySession() {
  // Unset all of the session variables.
  $_SESSION = array();

  // If it's desired to kill the session, also delete the session cookie.
  // Note: This will destroy the session, and not just the session data!
  if (ini_get("session.use_cookies")) {
      $params = session_get_cookie_params();
      setcookie(session_name(), '', time() - 42000,
          $params["path"], $params["domain"],
          $params["secure"], $params["httponly"]
      );
  }

  // Finally, destroy the session.
  session_destroy();
  echo "Sessionen raderas, <a href='?'>starta om spelet</a>";
  exit;
}
/**
 * Default exception handler.
 *
 */
function myExceptionHandler($exception) {
  echo "Cr33kWork: Uncaught exception: <p>" . $exception->getMessage() . "</p><pre>" . $exception->getTraceAsString(), "</pre>";
}
set_exception_handler('myExceptionHandler');
 
 
/**
 * Autoloader for classes.
 *
 */
function myAutoloader($class) {
  $path = CR33KWORK_INSTALL_PATH . "/src/{$class}/{$class}.php";
  if(is_file($path)) {
    include($path);
  }
  else {
    throw new Exception("Classfile '{$class}' does not exists.");
  }
}
spl_autoload_register('myAutoloader');