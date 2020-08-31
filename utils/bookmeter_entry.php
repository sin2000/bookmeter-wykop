<?php

require_once 'app_auth.php';
require_once 'site_globals.php';

class bookmeter_entry
{
  private $title;
  private $author;
  private $genre;
  private $isbn;
  private $descr;
  private $img_file;
  private $img_file_type;
  private $img_url;
  private $rate;
  private $use_star_rating = false;
  private $add_ad = false;

  public function compose_msg($counter)
  {
    $prev_counter = $counter - 1;

    $isbn_row = '';
    if(empty($this->isbn) == false)
      $isbn_row = "**ISBN:** " . $this->isbn . "\n";

    $rate_out = "";
    if($this->use_star_rating == false)
      $rate_out = $this->rate . "/10";
    else
    {
      $rate_out = str_repeat('★', $this->rate);
      $rate_out .= str_repeat('☆', 10 - $this->rate);
    }

    $app = new app_auth;
    $ad = "Wpis dodano za pomocą strony: [" . $app->get_current_base_url() . "](" . $app->get_current_base_url() . ")";

    $body = $prev_counter . " + 1 = " . $counter . "\n\n"
    . "**Tytuł:** " . $this->title . "\n"
    . "**Autor:** " . $this->author . "\n"
    . "**Gatunek:** " . $this->genre . "\n"
    . $isbn_row
    . "**Ocena:** " . $rate_out . "\n\n"
    . $this->descr . "\n\n"
    . ($this->add_ad == true ? ($ad . "\n\n") : "")
    . "#" . site_globals::$tag_name;

    return $body;
  }

  public function validate()
  {
    $err_msg = '';
    $err_msg = $this->error_if_empty($this->title, 'tytuł jest wymagany');
    if($err_msg != '')
      return $err_msg;

    $err_msg = $this->error_if_empty($this->author, 'autor jest wymagany');
    if($err_msg != '')
      return $err_msg;

    $err_msg = $this->error_if_empty($this->genre, 'gatunek jest wymagany');
    if($err_msg != '')
      return $err_msg;

    $err_msg = $this->error_if_nonum($this->rate, 1, 10, 'ocena jest wymagana w zakresie od 1 do 10');
    if($err_msg != '')
      return $err_msg;

    $err_msg = $this->validate_img_file();
    if($err_msg != '')
      return $err_msg;

    return '';
  }

  public function get_img()
  {
    if($this->img_file_type != false)
      return new CURLFile($this->img_file['tmp_name'], $this->img_file_type, $this->img_file['name']);

    return empty($this->img_url) ? null : $this->img_url;
  }

  public function set_actual_counter($counter)
  {
    $this->actual_counter = $counter;
  }

  public function set_title($title)
  {
    $this->title = trim($title);
  }
  
  public function set_author($author)
  {
    $this->author = trim($author);
  }

  public function set_genre($genre)
  {
    $this->genre = trim($genre);
  }

  public function set_isbn($isbn)
  {
    $this->isbn = trim($isbn);
  }

  public function set_description($description)
  {
    $this->descr = trim($description);
  }

  public function set_img_file($img_file)
  {
    $this->img_file = $img_file;
    $this->img_file_type = empty($this->img_file['tmp_name']) ? false : mime_content_type($this->img_file['tmp_name']);
  }

  public function set_img_url($img_url)
  {
    $this->img_url = trim($img_url);
  }

  public function set_rate($rate)
  {
    $this->rate = $rate;
  }

  public function set_use_star_rating($use_star_rating)
  {
    $this->use_star_rating = $use_star_rating == "on" ? true : false;
  }

  public function set_add_ad($add_ad)
  {
    $this->add_ad = $add_ad == "on" ? true : false;
  }

  private function validate_img_file()
  {
    $file_up_errors = array(
      0 => 'There is no error, the file uploaded with success',
      1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
      2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
      3 => 'The uploaded file was only partially uploaded',
      4 => 'No file was uploaded',
      6 => 'Missing a temporary folder',
      7 => 'Failed to write file to disk.',
      8 => 'A PHP extension stopped the file upload.',
    );

    if(isset($this->img_file))
    {
      if($this->img_file['error'] == UPLOAD_ERR_NO_FILE)
        return '';
      
      if($this->img_file['error'] != UPLOAD_ERR_OK)
        return $file_up_errors[$this->img_file['error']];

      $file_size = $this->img_file['size'];
      if($file_size > (3 * 1024 * 1024))
        return 'maksymalna wielkość pliku to 3 MB';

      if($this->img_file_type != 'image/jpeg' && $this->img_file_type != 'image/png')
        return 'dozwolone typy plików to JPG i PNG';
    }

    return '';
  }

  private function error_if_empty($field, $errmsg)
  {
    if(isset($field) && $field != '')
      return '';

    return $errmsg;
  }

  private function error_if_nonum($field, $int_min, $int_max, $errmsg)
  {
    if($field == '' || is_numeric($field) == false || $field < $int_min || $field > $int_max)
      return $errmsg;

    return '';
  }
}

?>