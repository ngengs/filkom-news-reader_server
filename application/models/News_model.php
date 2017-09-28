<?php
/**
 * Copyright (c) 2017 Rizky Kharisma (@ngengs)
 *
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class News_model extends FNR_Model
{
  private $TAG = "News_model";

  /**
   * News_model constructor.
   */
  public function __construct()
  {
    parent::__construct();
  }

  /**
   * Function to insert the batch data. Use build_news() for data format.
   *
   * @param array $data Build data to insert
   *
   * @return int|null Status query insert
   */
  public function insert_batch(array $data)
  {
    $this->log->write_log('debug', $this->TAG . ': insert_batch: ' . json_encode($data));
    $result = NULL;
    if ( ! empty($data)) {
      $result = $this->db->insert_batch('news', $data, FALSE);
    }

    return $result;
  }

  /**
   * Function to get last id from the web
   *
   * @return string Last id from the web
   */
  public function get_last_id_web()
  : string
  {
    $this->log->write_log('debug', $this->TAG . ': get_last_id_web: ');
    $this->db->select('id_web');
    $this->db->from('news');
    $this->db->limit(1);
    $this->db->order_by('date', 'DESC');
    $result = $this->db->get()->result();

    $return = '';
    if ( ! empty($result)) {
      $return = $result[0]->id_web;
    }

    return $return;
  }

  /**
   * Function to build news data for the batch insert.
   *
   * @param string      $id_web            News Id from the web
   * @param string      $title             News Title
   * @param string      $short_description News short description
   * @param string      $link              News original link
   * @param null|string $image             News image thumbnail
   * @param string      $date              News created date
   *
   * @return array Build news to insert
   */
  public function build_news(string $id_web, string $title, string $short_description, string $link, ?string $image, string $date)
  : array
  {
    $this->log->write_log(
      'debug',
      $this->TAG . ': build_news: $id_news: ' . $id_web . ', $title: ' . $title . ', $short_description: '
      . $short_description
      . ', $link: ' . $link . ', $image: ' . $image . ', $date: ' . $date
    );

    return [
      'id' => 'UNHEX(REPLACE(UUID(), "-", ""))',
      'id_web' => $this->db->escape($id_web),
      'title' => $this->db->escape($title),
      'short_desc' => $this->db->escape($short_description),
      'link' => $this->db->escape($link),
      'image' => $this->db->escape($image),
      'date' => $this->db->escape($date)
    ];
  }

  /**
   * Function to get news with empty detail
   *
   * @return array News list
   */
  public function get_empty_detail()
  {
    $this->log->write_log('debug', $this->TAG . ': get_empty_detail: ');
    $this->db->select('hex(news.id) as id, news.title as title, news.link as link');
    $this->db->from('news');
    $this->db->join('details', 'details.id_news = news.id', 'left');
    $this->db->where(['details.id_news' => NULL]);
    $result = $this->db->get();

    return $result->result();
  }

  /**
   * Function to get list of news per page
   *
   * @param int         $page     Page now
   * @param int|null    $per_page News count per page
   * @param null|string $id       News id. If you define the News Id this will return single data, so will return
   *                              without total_page and page_now and data is not inside `news` index
   *
   * @param null|string $key      News key or last path from the website
   *                              (eg: http://filkom.ub.ac.id/page/read/news/title-link/{key}).
   *                              If you define the News Id this will return single data, so will return without
   *                              total_page and page_now and data is not inside `news` index
   *
   * @return array News List
   */
  public function get(int $page = 1, ?int $per_page = 10, ?string $id = NULL, ?string $key = NULL)
  : array
  {
    $this->log->write_log(
      'debug',
      $this->TAG . ': get: $page: ' . $page . ', $per_page: ' . $per_page . ', $id: ' . $id . ', $key: ' . $key
    );
    $total_page = 0;
    if (empty($id)) {
      $this->db->select('count(id) as count');
      $this->db->from('news');
      $result_count = $this->db->get()->result();
      if ( ! empty($result_count)) {
        $count = $result_count[0]->count;
        $total_page = intdiv($count, $per_page);
      }
    }
    $this->news_list_builder($page, $per_page);
    $this->db->from('news');
    if ( ! empty($id)) {
      $this->db->where('hex(news.id)', $id);
    }
    if ( ! empty($key)) {
      $this->db->where('news.id_web', $key);
    }
    $result_news = $this->db->get()->result();
    $news = $this->generate_news_output($result_news);

    if (empty($id) && empty($key)) {
      return [
        'total_page' => $total_page,
        'page_now' => $page,
        'news' => $news
      ];
    } else {
      return $news;
    }
  }

  /**
   * Function to check news is exist
   *
   * @param string $id News Id
   *
   * @return bool
   */
  public function check(string $id)
  {
    $this->log->write_log('debug', $this->TAG . ': check: $id: ' . $id);
    $this->db->select('HEX(id) as id, title, image, link');
    $this->db->where('id', 'UNHEX(' . $this->db->escape($id) . ')', FALSE);
    $this->db->from('news');
    $result = $this->db->get()->result();

    return ! empty($result);
  }

}