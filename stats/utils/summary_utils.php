<?php

require_once 'bm_edition.php';
require_once 'stats_utils.php';

class summary_utils
{
  /** @var stats_utils **/
  private $statu = null;
  private $bmdb = null;
  private $curr_datetime = null;
  private $plurals_map = null;
  private $book_counter = 0;

  /** @param stats_utils $stats_utils
   *  @param bm_database $bm_db
   * **/
  public function __construct($stats_utils, $bm_db)
  {
    $this->statu = $stats_utils;
    $this->bmdb = $bm_db;
    $this->curr_datetime = new DateTime();
    $this->plurals_map = [
      'rok' => ['lata', 'lat'],
      'miesiąc' => ['miesiące', 'miesięcy'],
      'dzień' => ['dni', 'dni'],
      'godzina' => ['godziny', 'godzin'],
      'minuta' => ['minuty', 'minut'],
      'sekunda' => ['sekundy', 'sekund'],
      'książkę' => ['książki', 'książek'],
      'książka' => ['książki', 'książek'],
      'pasek' => ['paski', 'pasków'],
      'różowy' => ['różowe', 'różowych'],
      'niebieski' => ['niebieskie', 'niebieskich'],
      'nieokreślony' => ['nieokreślone', 'nieokreślonych'],
    ];

    $this->book_counter = $this->bmdb->get_book_count();
  }

  public function has_edition_ended()
  {
    return ($this->curr_datetime >= $this->statu->get_edition_end_date());
  }

  public function get_progress()
  {
    $curr_timestamp = $this->curr_datetime->getTimestamp();
    $start_timestamp = $this->statu->get_edition_start_date()->getTimestamp();
    $end_timestamp = $this->statu->get_edition_end_date()->getTimestamp();
    if($end_timestamp <= $curr_timestamp)
      return 100;

    $full = $end_timestamp - $start_timestamp;
    $frac = $curr_timestamp - $start_timestamp;

    $perc = ($frac / $full) * 100;
    $perc = floor($perc * 10) / 10;

    return $perc;
  }

  public function get_time_left_to_end()
  {
    if($this->has_edition_ended())
      return '';

    /** @var DateInterval **/
    $diff = $this->statu->get_edition_end_date()->diff($this->curr_datetime);

    $ret = '';
    if($diff->y > 0)
      $ret .= $diff->y . ' ' . $this->get_plural('rok', $diff->y);
    
    if($diff->y > 0 || $diff->m > 0)
      $ret .= ' ' . $diff->m . ' ' . $this->get_plural('miesiąc', $diff->m);

    if($diff->y > 0 || $diff->m > 0 || $diff->d > 0)
      $ret .= ' ' . $diff->d . ' ' . $this->get_plural('dzień', $diff->d);

    if($diff->y > 0 || $diff->m > 0 || $diff->d > 0 || $diff->h > 0)
      $ret .= ' ' . $diff->h . ' ' . $this->get_plural('godzina', $diff->h);

    if($diff->y > 0 || $diff->m > 0 || $diff->d > 0 || $diff->h > 0 || $diff->i > 0)
      $ret .= ' ' . $diff->i . ' ' . $this->get_plural('minuta', $diff->i) . ' i';

    if($diff->y > 0 || $diff->m > 0 || $diff->d > 0 || $diff->h > 0 || $diff->i > 0 || $diff->s > 0)
      $ret .= ' ' . $diff->s . ' ' . $this->get_plural('sekunda', $diff->s);


    return $ret;    
  }

  public function get_book_count()
  {
    return $this->book_counter . ' ' . $this->get_plural('książkę', $this->book_counter);
  }

  public function get_book_per_day()
  {
    $diff = null;
    if($this->has_edition_ended())
      $diff = $this->statu->get_edition_end_date()->diff($this->statu->get_edition_start_date());
    else
      $diff = $this->curr_datetime->diff($this->statu->get_edition_start_date());

    if($diff->days == 0)
      return $this->book_counter . ' ' . $this->get_plural('książkę', $this->book_counter);

    $bookpd = round($this->book_counter / $diff->days);
    $bookpd = $bookpd . ' ' . $this->get_plural('książkę', $bookpd);

    return $bookpd;
  }

  public function get_login_count()
  {
    $login_count = $this->bmdb->get_login_count();

    return $login_count;
  }

  public function get_book_per_user($user_count)
  {
    if($user_count == 0)
      $user_count = 1;

    $x = round($this->book_counter / $user_count);
    $x = $x . ' ' . $this->get_plural('książka', $x);

    return $x;
  }

  public function get_sex_stats()
  {
    $stats = $this->bmdb->get_count_by_sex();
    $unknown_count = $stats['unk'];
    $female_count = $stats['fem'];
    $male_count = $stats['mal'];

    $all_count = $unknown_count + $female_count + $male_count;
    if($all_count == 0)
      $all_count = 1;
    $unknown_perc = $unknown_count * 100 / $all_count;
    $female_perc = $female_count * 100 / $all_count;
    $male_perc = $male_count * 100 / $all_count;
    $perc_arr = $this->get_full_percents([$unknown_perc, $female_perc, $male_perc]);
    $unknown_perc = $perc_arr[0];
    $female_perc = $perc_arr[1];
    $male_perc = $perc_arr[2];
    
    $female_count = htmlspecialchars($female_count);
    $male_count = htmlspecialchars($male_count);
    $unknown_count = htmlspecialchars($unknown_count);

    $html = '<span style="color:deeppink">' . $female_count . ' '  . $this->get_plural('różowy', $female_count) . '</span> ' . $this->get_plural('pasek', $female_count)
      . '(' . $female_perc . '%), '
      . '<span style="color:blue">' . $male_count . ' ' . $this->get_plural('niebieski', $male_count) . '</span>(' . $male_perc . '%)'
      . ' i ' . $unknown_count . ' ' . $this->get_plural('nieokreślony', $unknown_count) . '(' . $unknown_perc . '%)';

    return $html;
  }

  public function get_book_count_by_sex()
  {
    $stats = $this->bmdb->get_book_count_by_sex();
    $unknown_count = $stats['unk'];
    $female_count = $stats['fem'];
    $male_count = $stats['mal'];

    $all_count = $unknown_count + $female_count + $male_count;
    if($all_count == 0)
      $all_count = 1;
    $unknown_perc = $unknown_count * 100 / $all_count;
    $female_perc = $female_count * 100 / $all_count;
    $male_perc = $male_count * 100 / $all_count;
    $perc_arr = $this->get_full_percents([$unknown_perc, $female_perc, $male_perc]);
    $unknown_perc = $perc_arr[0];
    $female_perc = $perc_arr[1];
    $male_perc = $perc_arr[2];
    
    $female_count = htmlspecialchars($female_count . ' ' . $this->get_plural('książkę', $female_count) . '(' . $female_perc . '%)');
    $male_count = htmlspecialchars($male_count . ' ' . $this->get_plural('książkę', $male_count) . '(' . $male_perc . '%)');
    $unknown_count = htmlspecialchars($unknown_count . ' ' . $this->get_plural('książkę', $unknown_count) . '(' . $unknown_perc . '%)');
    
    $arr = [ 'fem' => $female_count, 'mal' => $male_count, 'unk' => $unknown_count ];

    return $arr;
  }

  public function get_top_users()
  {
    $top_users = $this->bmdb->get_top_users();
    $html = '';
    $count = count($top_users);
    for($i = 0; $i < $count; ++$i)
    {
      $nr = $i + 1;
      $entry = $top_users[$i];
      $login = htmlspecialchars($entry[0]);
      $usr_book_count = htmlspecialchars($entry[1]);
      $html .=
        "<tr>
        <th scope=\"row\">$nr</th>
        <td><a href=\"https://www.wykop.pl/tag/bookmeter/autor/$login/\" target=\"_blank\">$login</a></td>
        <td>$usr_book_count</td>
        </tr>";
    }

    return $html;
  }

  public function get_top_voted_users()
  {
    $top_voted_users = $this->bmdb->get_top_voted_users();
    $html = '';
    $count = count($top_voted_users);
    for($i = 0; $i < $count; ++$i)
    {
      $nr = $i + 1;
      $entry = $top_voted_users[$i];
      $login = htmlspecialchars($entry[0]);
      $vote_sum = htmlspecialchars($entry[1]);
      $html .=
        "<tr>
        <th scope=\"row\">$nr</th>
        <td><a href=\"https://www.wykop.pl/tag/bookmeter/autor/$login/\" target=\"_blank\">$login</a></td>
        <td>$vote_sum</td>
        </tr>";
    }

    return $html;
  }

  public function get_last_joined_users()
  {
    $last_joined_users = $this->bmdb->get_last_joined_users();
    $html = '';
    $count = count($last_joined_users);
    for($i = 0; $i < $count; ++$i)
    {
      $nr = $i + 1;
      $entry = $last_joined_users[$i];
      $login = htmlspecialchars($entry[0]);
      $join_date = htmlspecialchars($entry[1]);
      $html .=
        "<tr>
        <th scope=\"row\">$nr</th>
        <td><a href=\"https://www.wykop.pl/tag/bookmeter/autor/$login/\" target=\"_blank\">$login</a></td>
        <td>$join_date</td>
        </tr>";
    }

    return $html;
  }

  public function get_top_books($worst_first = false)
  {
    $books = $worst_first ? $this->bmdb->get_worst_books() : $this->bmdb->get_top_books();
    $html = '';
    $count = count($books);
    for($i = 0; $i < $count; ++$i)
    {
      $nr = $i + 1;
      $entry = $books[$i];
      $author = htmlspecialchars($entry[0]);
      $title = htmlspecialchars($entry[1]);
      $rate = htmlspecialchars(round($entry[2], 1));
      $vote_count = htmlspecialchars($entry[3]);
      $entry_id = urlencode($entry[4]);
      $html .=
        "<tr>
        <th scope=\"row\">$nr</th>
        <td>$author</td>
        <td><a href=\"https://www.wykop.pl/wpis/$entry_id/\" target=\"_blank\">$title</a></td>
        <td>$rate</td>
        <td>$vote_count</td>
        </tr>";
    }

    return $html;
  }

  public function get_top_voted_books()
  {
    $top_voted = $this->bmdb->get_top_voted_books();
    $html = '';
    $count = count($top_voted);
    for($i = 0; $i < $count; ++$i)
    {
      $nr = $i + 1;
      $entry = $top_voted[$i];
      $author = htmlspecialchars($entry[0]);
      $title = htmlspecialchars($entry[1]);
      $vote_count = htmlspecialchars($entry[2]);
      $html .=
        "<tr>
        <th scope=\"row\">$nr</th>
        <td>$author</td>
        <td>$title</td>
        <td>$vote_count</td>
        </tr>";
    }

    return $html;
  }

  public function get_top_authors()
  {
    $top_authors = $this->bmdb->get_top_authors();
    $html = '';
    $count = count($top_authors);
    for($i = 0; $i < $count; ++$i)
    {
      $nr = $i + 1;
      $entry = $top_authors[$i];
      $author = htmlspecialchars($entry[0]);
      $book_count = htmlspecialchars($entry[1]);
      $html .=
        "<tr>
        <th scope=\"row\">$nr</th>
        <td>$author</td>
        <td>$book_count</td>
        </tr>";
    }

    return $html;
  }
  
  public function get_top_popular_books()
  {
    $top_popular_books = $this->bmdb->get_top_popular_books();
    $html = '';
    $count = count($top_popular_books);
    for($i = 0; $i < $count; ++$i)
    {
      $nr = $i + 1;
      $entry = $top_popular_books[$i];
      $author = htmlspecialchars($entry[0]);
      $title = htmlspecialchars($entry[1]);
      $book_count = htmlspecialchars($entry[2]);
      $avg_rate = htmlspecialchars(round($entry[3], 1));;
      $vote_count = htmlspecialchars($entry[4]);
      $entry_id = urlencode($entry[5]);
      
      $html .=
        "<tr>
        <th scope=\"row\">$nr</th>
        <td>$author</td>
        <td><a href=\"https://www.wykop.pl/wpis/$entry_id/\" target=\"_blank\">$title</a></td>
        <td>$book_count</td>
        <td>$avg_rate</td>
        <td>$vote_count</td>
        </tr>";
    }

    return $html;
  }

  public function get_top_genres()
  {
    $top_genres = $this->bmdb->get_top_genres();
    $html = '';
    $count = count($top_genres);
    for($i = 0; $i < $count; ++$i)
    {
      $nr = $i + 1;
      $entry = $top_genres[$i];
      $genre_name = htmlspecialchars($entry[0]);
      $genre_count = htmlspecialchars($entry[1]);
      $html .=
        "<tr>
        <th scope=\"row\">$nr</th>
        <td>$genre_name</td>
        <td>$genre_count</td>
        </tr>";
    }

    return $html;
  }

  private function get_full_percents($orig_arr)
  {
    $mid_arr = [];
    $int_sum = 0;
    $i = 0;
    $zero_count = 0;
    foreach($orig_arr as $val)
    {
      $int_sum += $ival = intval($val);
      array_push($mid_arr, [ $i, $ival, round($val - $ival, 10) ]);
      ++$i;
      if($val == 0)
        ++$zero_count;
    }

    $size = count($mid_arr);
    if($zero_count == $size)
      return $orig_arr;

    usort($mid_arr, function($a, $b) {
      $val1 = $a[2];
      $val2 = $b[2];

      if($val1 >= $val2 && $val1 <= $val2)
        return 0;

      return ($val1 < $val2) ? -1 : 1;
    });

    $diff = 100 - $int_sum;
    for($i = $size - 1; $i >= 0 && $diff > 0; --$i, --$diff)
    {
      ++$mid_arr[$i][1];
    }

    $res_arr = array_column($mid_arr, 1, 0);
    return $res_arr;
  }

  private function get_plural($singular, $n)
  {
    if($n == 1)
      return $singular;

    if($n % 10 >= 2 && $n % 10 <= 4 && ($n % 100 < 10 || $n % 100 > 20))
      return $this->plurals_map[$singular][0];

    return $this->plurals_map[$singular][1];
  }
}

?>
