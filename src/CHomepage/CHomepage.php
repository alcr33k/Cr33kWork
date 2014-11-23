<?php
  
class CHomepage {  
  public function __construct($db){
    $this->db = $db;
  }
  public function getNewestGames() { /// 3 newest games, need to make added to db
    $sql = 'SELECT * FROM Games ORDER BY Added DESC LIMIT 3;';
    $res = $this->db->ExecuteSelectQueryAndFetchAll($sql);
    $code = '<h2>Senaste spelen</h2>';
    foreach ($res as $val) {
      $code.= "
        <h3><a href='rental.php?title={$val->title}'>{$val->title}</a></h3>
        <img src='img.php?src={$val->image}&width=108&height=128&crop-to-fit' alt='{$val->title}' />
      ";
    }
    return $code;
  }
  public function getNewestPosts() {
    $sql = 'SELECT * FROM News ORDER BY published DESC LIMIT 3;';
    $res = $this->db->ExecuteSelectQueryAndFetchAll($sql);
    $code = '<h2>Senaste nyheterna</h2>';
    foreach ($res as $val) {
      $code.= "
        <h3><a href='news.php?slug=news-{$val->id}'>{$val->title}</a></h3>
      ";
    }
    return $code;
  }
  public function getPopularCategories() {
    $code = '
      <h2>Populära nyhets-kategorier</h2>
      <ul class="categoryList">
        <li><a href="news.php?category=PC">PC</a></li>
        <li><a href="news.php?category=XBox">Xbox</a></li>
        <li><a href="news.php?category=PlayStation"></a>PlayStation</li>
      </ul>
    ';
    return $code;
  }
  public function getPopularGameCategories() {
    $code = '
      <h2>Populära spel-kategorier</h2>
      <ul class="categoryList">
        <li><a href="rental.php?&category=action">Action</a></li>
        <li><a href="rental.php?&category=sport">Sport</a></li>
        <li><a href="rental.php?&category=survival"></a>Survival</li>
      </ul>
    ';
    return $code;
  }
}