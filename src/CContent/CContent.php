<?php
/**
 * Function to initiate tables X
 * Function to add / update / remove
 * Samt edit.php, remove.php och add.php
 */
class CContent {
  /**
  *  Initiate the post-tables
  */
  public function __construct($db){
    $this->db = $db;
	$this->initiate_content();
  } 
  public function initiate_content () {
    $query = "
    CREATE TABLE IF NOT EXISTS Content(
      id INT AUTO_INCREMENT PRIMARY KEY NOT NULL,
        slug CHAR(80) UNIQUE,
      url CHAR(80) UNIQUE,
   
      TYPE CHAR(80),
        title VARCHAR(80),
        DATA TEXT,
        FILTER CHAR(80),
   
        published DATETIME,
        created DATETIME,
        updated DATETIME,
        deleted DATETIME
      user VARCHAR(80)
    ";
	$this->db->ExecuteQuery($query); 
  }
  /**
  * Create a slug of a string, to be used as url.
  *
  * @param string $str the string to format as slug.
  * @returns str the formatted slug. 
  */
  function slugify($str) {
    $str = mb_strtolower(trim($str));
    $str = str_replace(array('å','ä','ö'), array('a','a','o'), $str);
    $str = preg_replace('/[^a-z0-9-]/', '-', $str);
    $str = trim(preg_replace('/-+/', '-', $str), '-');
    return $str;
   }
  /**
  * Create a link to the content, based on its type.
  *
  * @param object $content to link to. 
  * @return string with url to display content.
  */
  private function getUrlToContent($content) {
      return "news.php?slug={$content->slug}";
  }
  /**
  * Get content as list
  */
  public function getContentList($db) {
    $sql = 'SELECT *, (published <= NOW()) AS available FROM News;';
    $res = $db->ExecuteSelectQueryAndFetchAll($sql);
    $items = "<ul>";
    foreach($res AS $key => $val) {
      $items .= "<li>(" . (!$val->available ? 'inte ' : null) . "publicerad): " . htmlentities($val->title, ENT_QUOTES, 'UTF-8') . " (<a href='edit_post.php?id={$val->id}'>editera</a> <a href='" . $this->getUrlToContent($val) . "'>visa</a> <a href='delete_post.php?id=" . $val->id . "'>ta bort</a>)</li>\n";
    }
    $items .= "</ul>";
    return $items;
  }
  public function getMovieList($db) {
    $sql = 'SELECT *, (added <= NOW()) AS available FROM Games;';
    $res = $db->ExecuteSelectQueryAndFetchAll($sql);
    $items = "<ul>";
    foreach($res AS $key => $val) {
      $items .= "<li>(" . (!$val->available ? 'inte ' : null) . "tilllagd): " . htmlentities($val->title, ENT_QUOTES, 'UTF-8') . " (<a href='edit_movie.php?id={$val->id}'>editera</a> <a href='rental.php?title=". $val->title ."'>visa</a> <a href='delete_movie.php?id=" . $val->id . "'>ta bort</a>)</li>\n";
    }
    $items .= "</ul>";
    return $items;
  }
  /**
  * Get all info about on specific post by id
  */
  public function getbyID($id, $db) {
    $sql = 'SELECT * FROM News WHERE id = ?';
    $res = $db->ExecuteSelectQueryAndFetchAll($sql, array($id));
    if(isset($res[0])) { /// Make sure it gives a value
      $content = $res[0];
    }
    else {
      die('Failed: No content connected to that id exists. <a href="view_posts.php">Click here</a> to go back to content_view.php');
    }
    return $content;
  }
  /**
  * Or game
  */
  public function getGamebyID($id, $db) {
    $sql = 'SELECT * FROM Games WHERE id = ?';
    $res = $db->ExecuteSelectQueryAndFetchAll($sql, array($id));
    if(isset($res[0])) { /// Make sure it gives a value
      $content = $res[0];
    }
    else {
      die('Failed: No content connected to that id exists. <a href="admin.php">Click here</a> to go back to admin.php');
    }
    return $content;
  }
  /**
  * Function to update a post 
  */
  public function updateContent($title,$slug,$data,$filter,$published,$id,$db) {
    $sql = '
    UPDATE News SET
      title   = ?,
      slug    = ?,
      data    = ?,
      filter  = ?,
      published = ?,
      updated = NOW()
    WHERE 
      id = ?;
      SET NAMES utf8;
    ';
    $params = array($title, $slug, $data, $filter, $published, $id);
	$res = $this->db->ExecuteQuery($sql, $params);
    if($res) {
      return "Posten har uppdaterats";
    }
    else {
      return "Posten har ej uppdaterats";
    }
  }
  /**
  * and to update movie
  */ 
  public function updateMovie($title,$year,$decr,$platform,$category,$price,$trailer,$id) {
    $sql = '
    UPDATE Games SET
      title   = ?,
      year    = ?,
      decr    = ?,
      platform  = ?,
      category = ?,
      price = ?,
      trailer = ?
    WHERE 
      id = ?;
      SET NAMES utf8;
    ';
    $params = array($title,$year,$decr,$platform,$category,$price,$trailer,$id);
    $res = $this->db->ExecuteQuery($sql, $params);
    if($res) {
      return "Spelet har uppdaterats";
    }
    else {
      return "Spelet har ej uppdaterats";
    }
  }
  /**
  * Function to add a post
  */
  public function createNewPost ($title,$data,$filter,$category,$user) {
    if(!$this->mempty($title,$data,$filter)) { /// Make sure that all parameters are set
      $id = $this->getGreatestID();
      $thisID = $id + 1;
      $slug = "news-{$thisID}";
      $sql = '
      INSERT INTO News 
      (title, slug, data, filter, category, published) 
      VALUES (?, ?, ?, ?, ?, NOW())';
      $params = array($title, $slug, $data, $filter, $category);
      $res = $this->db->ExecuteQuery($sql, $params);
      if($res) {
        return "Posten har skapats";
      }
      else {
        return "Posten kunde ej skapas";
      }
    }
    else {
      return "Error, not all parameters set.";
    }
  }
  public function createNewMovie ($title,$year,$decr,$platform,$category,$price,$trailer) {
    if($this->mempty($title,$year,$decr,$platform,$category,$price) == true) {     /// Make sure that all parameters are set
      return "Error, not all parameters set.";
    }
    else if((!is_numeric($year)) || (!is_numeric($price))) { /// make sure that they are numeric
      return "Error, price or year is not numeric";
    }
    else {
      $sql = '
      INSERT INTO Games 
      (title, year, decr, category, platform, price, trailer, Added) 
      VALUES (?, ?, ?, ?, ?, ?, ?, NOW())';
      $params = array($title,$year,$decr,$category,$platform,$price,$trailer);
      $res = $this->db->ExecuteQuery($sql, $params);
      if($res) {
        return "Spelet har lagts till";
      }
      else {
        return "Spelet kunde ej läggas till";
      }
    }
  }
  public function getGreatestID() {
    $sql = "SELECT max(id) AS lastID FROM News;";
    $res = $this->db->ExecuteSelectQueryAndFetchAll($sql);
    return $res[0]->lastID;
  }
  /**
  * Function to delete post
  */
  public function deletePost($id, $title) {
    $sql = "DELETE FROM News WHERE id = ? LIMIT 1";
    $res = $this->db->ExecuteQuery($sql, array($id)); 
    if($res) {
        return '<p>"'.$title.'" har tagits bort.</p>';
    }
    else {
        return "Sidan kan inte tas bort.";
    } 
  }
  /*
  * Delet movie
  */
  public function deleteMovie($id, $title) {
    $sql = "DELETE FROM Games WHERE id = ? LIMIT 1";
    $res = $this->db->ExecuteQuery($sql, array($id)); 
    if($res) {
        return '<p>"'.$title.'" har tagits bort.</p>';
    }
    else {
        return "Filmen kan inte tas bort.";
    } 
  }
  function mempty() /// helper function for multiple empty, credit: http://stackoverflow.com/questions/4993104/using-ifempty-with-multiple-variables-php
  {
    foreach(func_get_args() as $arg)
      if(empty($arg))
        continue;
      else
        return false;
    return true;
  }
}