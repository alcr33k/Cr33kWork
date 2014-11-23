<?php
/**
 * Database wrapper, provides a database API for the framework but hides details of implementation.
 *
 */
class CUser {
    public function Login($acronym, $password, $db) {
	  $sql = "SELECT acronym, name FROM USER WHERE acronym = ? AND password = md5(concat(?, salt))";
	  $params = array($acronym, $password);
	  $res = $db->ExecuteSelectQueryAndFetchAll($sql, $params);
	  if(isset($res[0])) {
		$_SESSION['user'] = $res[0];
		header("Location: admin.php");
	  }
    else {
      return '<p>Du lyckades inte logga in</p>';
    }
    }
    public function Logout() {
	   unset($_SESSION['user']);
	   header("Location: home.php");
    }
    // Check if user is authenticated.
    public function checkIfLoggedIn()
    {
	  $acronym = isset($_SESSION['user']) ? $_SESSION['user']->acronym : null;
	  if($acronym) {
	  	return array(true, "Du är inloggad som: $acronym ({$_SESSION['user']->name})");
	  }
	  else {
	    return array(false, "Du är INTE inloggad.");
	  }
    }
	public function GetAcronym() {
	  if (isset($_SESSION['user'])) {
		return $_SESSION['user']->acronym;
	  }
	  else {
		return "None (Not logged in)";
	  }
	}
	public function GetName() {
	  if (isset($_SESSION['user'])) {
		return $_SESSION['user']->name;
	  }
	  else {
		return "None (Not logged in)";
	  }
	}
   
}