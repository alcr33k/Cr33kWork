<?php

class CBlog {
  /**
  *  Initiate the db
  */
  public function __construct($db){
    $this->db = $db;
  } 
  /*
  * Get a single post as valid html
  */
  public function getPost($slug) {
    $sql = "SELECT * FROM News WHERE slug = ? AND published <= NOW();";
    $res = $this->db->ExecuteSelectQueryAndFetchAll($sql, array($slug));
    $filterClass = new CTextFilter();
    if(isset($res[0])) {
      $c = $res[0];
      // Sanitize content before using it.
      $title  = htmlentities($c->title, null, 'UTF-8');
      $data   = $filterClass->doFilter(htmlentities($c->data, null, 'UTF-8'), $c->filter);
      $content = "
      <header>
      <h1>{$title}</h1>
      </header>
      {$data}
      ";
      return $content;
    }
    else {
			return $slug;
      /// die('Misslyckades: det finns inget innehåll.');
    }
  }
  /**
  / Get a list of all post returned as valid html
  */
  public function getAllPosts($catgry = null) {
    if($catgry != null) {
      $sql = "SELECT * FROM News WHERE category = ? AND published <= NOW() ORDER BY updated DESC;";
      $params[] = $catgry;
      $res = $this->db->ExecuteSelectQueryAndFetchAll($sql, $params);
      $filterClass = new CTextFilter();
      $content = "";
      if(isset($res[0])) {
        foreach ($res as $c) {
          // Sanitize content before using it.
          $title  = htmlentities($c->title, null, 'UTF-8');
          $data   = $filterClass->doFilter(htmlentities($c->data, null, 'UTF-8'), $c->filter);
          $snippet = strip_tags(substr($data,0,370)); /// no tags, first 400 signs 
          $content .= "
          <div class='snippet'> 
          <h2><a href ='news.php?slug={$c->slug}'>{$title}</a></h2>
          <p>Publicerad: {$c->published}</p>
          <p>{$snippet} <a href='news.php?slug={$c->slug}'>[..] Läs mer »</a></p> 
          <p>Kategori: <a href='?category={$c->category}'>{$c->category}</a></p>
          </div>";
        }
        return $content;
      }
    }
    else {
      $sql = "SELECT * FROM News WHERE published <= NOW() ORDER BY updated DESC;";
      $res = $this->db->ExecuteSelectQueryAndFetchAll($sql);
      $filterClass = new CTextFilter();
      $content = "";
      if(isset($res[0])) {
        foreach ($res as $c) {
          // Sanitize content before using it.
          $title  = htmlentities($c->title, null, 'UTF-8');
          $data   = $filterClass->doFilter(htmlentities($c->data, null, 'UTF-8'), $c->filter);
          $snippet = strip_tags(substr($data,0,370)); /// no tags, first 400 signs 
          $content .= "
          <div class='snippet'> 
          <h2><a href ='news.php?slug={$c->slug}'>{$title}</a></h2>
          <p>Publicerad: {$c->published}</p>
          <p>{$snippet} <a href='news.php?slug={$c->slug}'>[..] Läs mer »</a></p> 
          <p>Kategori: <a href='?category={$c->category}'>{$c->category}</a></p>
          </div>";
        }
        return $content;
      }
    }
  }
  /*
  * Get the title by the url
  */
  public function getTitle($slug) {
    $sql = "SELECT * FROM News WHERE slug = ? AND published <= NOW();";
    $res = $this->db->ExecuteSelectQueryAndFetchAll($sql, array($slug));
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