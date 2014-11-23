<?php
/// useful oage variables
$hits; // How many rows to display per page.
$page; // Which is the current page to display, use this to calculate the offset value
$max;  // Max pages in the table: SELECT COUNT(id) AS rows FROM VGames
$min;  // Startpage, usually 0 or 1, what you feel is convienient

class CMovies {

  public function __construct($db){
    $this->db = $db;
  }
  public function getParams() {
    $title = isset($_GET['title']) ?$_GET['title'] : null;
    $year1 = isset($_GET['year1']) && !empty($_GET['year1']) ? $_GET['year1'] : null;
    $year2 = isset($_GET['year2']) && !empty($_GET['year2']) ? $_GET['year2'] : null;
    $orderby  = isset($_GET['orderby']) ? strtolower($_GET['orderby']) : null;
    $order    = isset($_GET['order'])   ? strtolower($_GET['order'])   : 'asc';
    $hits  = isset($_GET['hits']) ? $_GET['hits'] : 20;
    $page  = isset($_GET['page']) ? $_GET['page'] : 1;
    $price1  = isset($_GET['price1']) && !empty($_GET['price1']) ? $_GET['price1'] : null;
    $price2  = isset($_GET['price2']) && !empty($_GET['price2']) ? $_GET['price2'] : null;
    $platform  = isset($_GET['platform']) && !empty($_GET['platform']) ? $_GET['platform'] : null;
    $category  = isset($_GET['category']) && !empty($_GET['category']) ? $_GET['category'] : null;
    $prevSQL  = isset($_GET['prevSQL']) ? $_GET['prevSQL'] : null;
    $max = $this->getMax($hits);
    if(isset($prevSQL)) {
      echo $prevSQL;
    }
    $parameters = array (
      'title' => $title,
      'year1' => $year1,
      'year2' => $year2,
      'orderby' => $orderby,
      'order' => $order,
      'hits' => $hits,
      'page' => $page,
      'max' => $max,
      'price1' => $price1,
      'price2' => $price2,
      'platform' => $platform,
      'category' => $category,
    );
    return $parameters;
  }
  
  public function getTable($db, $parameters) {
	$sql = "SELECT * FROM Games";
	$where = null;
	if($parameters['title']) {
	  $where .= ' WHERE title LIKE ?';
      $params[] = $parameters['title'] . "%";
	}
	if($parameters['year1']) {
	  if($parameters['title'] != null) {
	    $where .= ' AND year >= ?';
	  }
	  else {
	    $where .= ' WHERE year >= ?';
	  }
	  $params[] = $parameters['year1'];
	} 
	if($parameters['year2']) {
	  if(($parameters['title'] != null) || ($parameters['year1'] != null)) {
	    $where .= ' AND year <= ?';
	  }
	  else {
	    $where .= ' WHERE year <= ?';
	  }
	  $params[] = $parameters['year2'];
	}
  if($parameters['price1']) {
	  if(($parameters['title'] != null) || ($parameters['year1'] != null) || ($parameters['year2'] != null)) {
	    $where .= ' AND price >= ?';
	  }
	  else {
	    $where .= ' WHERE price >= ?';
	  }
	  $params[] = $parameters['price1'];
	}
  if($parameters['price2']) {
	  if(($parameters['title'] != null) || ($parameters['year1'] != null) || ($parameters['year2'] != null) || ($parameters['price1'] != null)) {
	    $where .= ' AND price <= ?';
	  }
	  else {
	    $where .= ' WHERE price <= ?';
	  }
	  $params[] = $parameters['price2'];
	}
  if($parameters['platform']) {
	  if(($parameters['title'] != null) || ($parameters['year1'] != null) || ($parameters['year2'] != null) || ($parameters['price1'] != null) || ($parameters['price2'] != null)) {
	    $where .= ' AND platform = ?';
	  }
	  else {
	    $where .= ' WHERE platform = ?';
	  }
	  $params[] = $parameters['platform'];
	}
  if($parameters['category']) {
	  if(($parameters['title'] != null) || ($parameters['year1'] != null) || ($parameters['year2'] != null) || ($parameters['price1'] != null) || ($parameters['price2'] != null) || ($parameters['platform'] != null)) {
	    $where .= ' AND category like ?';
	  }
	  else {
	    $where .= ' WHERE category like ?';
	  }
	  $params[] = '%' . $parameters['category'] . '%';
	}
	if(($parameters['orderby']) && (in_array($parameters['orderby'], array('title', 'year', 'platform', 'price'))) && (in_array($parameters['order'], array('asc', 'desc')))) {
		$where .= ' ORDER BY ' .$parameters['orderby']. " ". $parameters['order'];
	}
	if($parameters['hits']) {
	  $where .= ' LIMIT ' .$parameters['hits'];
	}
	if($parameters['page']) {
	  $offsetval = ($parameters['page'] - 1) * $parameters['hits'];
	  $where .= ' OFFSET ' .$offsetval;
	}
	$fullSql = $sql . $where;
	if (isset($params))
	{
		$res = $db->ExecuteSelectQueryAndFetchAll($fullSql, $params);
	}
	else
	{
		$res = $db->ExecuteSelectQueryAndFetchAll($fullSql);
	}
	$tr = "<tr><th>Bild</th><th>Titel " . $this->orderby('title') . "</th><th>År " . $this->orderby('year') . "</th><th>Beskrivning</th><th>Trailer</th><th>Plattform " . $this->orderby('platform') . "</th><th>Kategori</th><th>Pris " . $this->orderby('price') . "</th>
  </tr>";
	foreach($res AS $key => $val) {
  	  $tr .= "<tr><td><img src='img.php?src={$val->image}&width=108&height=128&crop-to-fit' alt='{$val->title}' /></td><td>{$val->title}</td><td>{$val->year}</td><td>{$val->decr}</td><td><a href='{$val->trailer}'>Trailer</a></td><td>{$val->platform}</td><td>{$val->category}</td><td>{$val->price} kr för 48h</td></tr>";
    }
    return $tr; 
  }
  public function orderby($column) {
    $currentURL = $this->getCurrentURL();
    if ((strpos($currentURL,'?') !== false))
    {
      return "<span class='orderby'><a href='{$currentURL}&orderby={$column}&order=asc'>&darr;</a><a href='{$currentURL}&orderby={$column}&order=desc'>&uarr;</a></span>";
    }
    else {
      return "<span class='orderby'><a href='?&orderby={$column}&order=asc'>&darr;</a><a href='?orderby={$column}&order=desc'>&uarr;</a></span>";
    }
  }
  public function getQueryString($options, $prepend='?') {
	// parse query string into array
	$query = array();
	parse_str($_SERVER['QUERY_STRING'], $query); 
	// Modify the existing query string with new options
	$query = array_merge($query, $options); 
	// Return the modified querystring
	return $prepend . http_build_query($query);
  }
  public function getPageNavigation($hits, $page, $max, $min=1) {
	$nav  = "<a href='" . htmlspecialchars($this->getQueryString(array('page' => $min))) . "'>&lt;&lt;</a> ";
	$nav .= "<a href='" . htmlspecialchars($this->getQueryString(array('page' => ($page > $min ? $page - 1 : $min) ))) . "'>&lt;</a> ";
	for($i=$min; $i<=$max; $i++) {
	  $nav .= "<a href='" . htmlspecialchars($this->getQueryString(array('page' => $i))) . "'>$i</a> ";
	}
	$nav .= "<a href='" . htmlspecialchars($this->getQueryString(array('page' => ($page < $max ? $page + 1 : $max) ))) . "'>&gt;</a> ";
	$nav .= "<a href='" . htmlspecialchars($this->getQueryString(array('page' => $max))) . "'>&gt;&gt;</a> ";
	return $nav;
  }
  public function doAdvancedSearch() {
    $category = isset($_POST['categoryList']) && !empty($_POST['categoryList']) ? $_POST['categoryList'] : null;
    $platform = isset($_POST['platformList']) && !empty($_POST['platformList']) ? $_POST['platformList'] : null;
    $currentURL = $this->getCurrentURL();
    if(($_POST['categoryList'] != null) && ($_POST['categoryList'] != null)) {
      if ((strpos($currentURL,'?') !== false))
      {
        $url = $currentURL . "&category={$category}&platform={$platform}";
        header('Location: '.$url);
       
      }
      else {
        $url = $currentURL . "?&category={$category}&platform={$platform}";
        header('Location: '.$url);
      }
    }
    else if(($_POST['categoryList'] != null) && ($_POST['categoryList'] == null)) { /// later schold see if not default value, varna om att nolställer vanlig sökning, gör den innan advancera<d sökning
      if ((strpos($currentURL,'?') !== false))
      {
        $url = $currentURL . "&category={$category}";
        header('Location: '.$url);
       
      }
      else {
        $url = $currentURL . "?&category={$category}";
        header('Location: '.$url);
      }
    }
    else if(($_POST['categoryList'] == null) && ($_POST['categoryList'] != null)) {
      if ((strpos($currentURL,'?') !== false))
      {
        $url = $currentURL . "platform={$platform}";
        header('Location: '.$url);
       
      }
      else {
        $url = $currentURL . "?&platform={$platform}";
        header('Location: '.$url);
      }
    }
  }
  public function getCategories() {
    $sql = "select distinct category from Games";
    $res = $this->db->ExecuteSelectQueryAndFetchAll($sql);
    $categories = array();
    foreach($res AS $key => $val) {
  	  if(strpos($val->category,',') !== false) {
        $multipleCategories = explode(", ", $val->category);
        foreach($multipleCategories as $category)
        {
          if(!in_array($category, $categories))
          {
            array_push($categories, $category);
          }
        }
      }
      else /// does not contain , add
      {
        if(!in_array($val->category, $categories))
        {
          array_push($categories, $val->category);
        }
      }
    }
    return $categories;
  }
  public function getPlatforms() {
    $sql = "select distinct platform from Games";
    $res = $this->db->ExecuteSelectQueryAndFetchAll($sql);
    $platforms = array();
    foreach($res AS $val) {
      array_push($platforms, $val->platform);
    }
    return $platforms;
  }
  public function getCategoryDropdown($selected) {
    $dropdown = '<p><label>Kategori: 
    <select name="categoryList">
      <option value="">-- Select one --</option>';
    $category = $this->getCategories();
    foreach($category as $val) {
      if ($val == $selected) {
        $categoryUC = ucfirst($val);
        $dropdown .= '<option value="' .$val. '" selected>'.$categoryUC.'</option>';
      }
      else {
        $categoryUC = ucfirst($val);
        $dropdown .= '<option value="' .$val. '">'.$categoryUC.'</option>';
      }
    }
    $dropdown .= '</select></label></p>';
    return $dropdown;
  }
  public function getPlatformDropDown($selected) {
    $dropdown =   '<p><label>Platform: 
    <select name="platformList">
      <option value="">-- Select one --</option>';
    $platform = $this->getPlatforms();
    foreach($platform as $val) {
      if ($val == $selected) {
        $dropdown .= '<option value="' .$val. '" selected>'.$val.'</option>';
      }
      else {
        $dropdown .= '<option value="' .$val. '">'.$val.'</option>';
      }
    }
    $dropdown .= '</select></label>';
    return $dropdown;
  }
  public function getHitsPerPage($hits) {
    $nav = "Träffar per sida: ";
    foreach($hits AS $val) {
      $nav .= "<a href='" . htmlentities($this->getQueryString(array('hits' => $val))) . "'>$val</a> ";
    }  
    return $nav;
  }
  private function getMax($hits) {
    $res = $this->db->ExecuteSelectQueryAndFetchAll("SELECT COUNT(id) AS rows FROM Games");
	$max = ceil($res[0]->rows / $hits);
	return $max;
	
  }
  
  private function getCurrentUrl() {
    $url = "http";
    $url .= (@$_SERVER["HTTPS"] == "on") ? 's' : '';
    $url .= "://";
    $serverPort = ($_SERVER["SERVER_PORT"] == "80") ? '' :
      (($_SERVER["SERVER_PORT"] == 443 && @$_SERVER["HTTPS"] == "on") ? '' : ":{$_SERVER['SERVER_PORT']}");
    $url .= $_SERVER["SERVER_NAME"] . $serverPort . htmlspecialchars($_SERVER["REQUEST_URI"]);
    return $url;
  }
  
}