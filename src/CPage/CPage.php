<?php

class CPage {
  /**
  *  Initiate the post-tables
  */
  public function __construct($db){
    $this->db = $db;
  } 
  /*
  * Get the page as valid html
  */
  public function getPage($url) {
    $sql = "SELECT * FROM Content WHERE type = 'page' AND url = ? AND published <= NOW();";
    $res = $this->db->ExecuteSelectQueryAndFetchAll($sql, array($url));
    $filterClass = new CTextFilter();
    if(isset($res[0])) {
      $c = $res[0];
      // Sanitize content before using it.
      $title  = htmlentities($c->title, null, 'UTF-8');
      $data   = $filterClass->doFilter(htmlentities($c->DATA, null, 'UTF-8'), $c->FILTER);
      $content = "
      <header>
      <h1>{$title}</h1>
      </header>
      {$data}
      ";
      return $content;
    }
    else {
      die('Misslyckades: det finns inget innehåll.');
    }
  }
  /*
  * Get the title by the url
  */
  public function getTitle($url) {
    $sql = "SELECT * FROM Content WHERE type = 'page' AND url = ? AND published <= NOW();";
    $res = $this->db->ExecuteSelectQueryAndFetchAll($sql, array($url));
    if(isset($res[0])) {
      $c = $res[0];
      /// Sanitize title
      $title  = htmlentities($c->title, null, 'UTF-8');
      return $title;
    }
    else {
      die('Misslyckades: det finns inget innehåll.');
    }
  }
}