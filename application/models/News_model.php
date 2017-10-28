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
   * @return array|null Inserted id_web
   */
  public function insert_batch(array $data)
  :?array
  {
    $this->log->write_log('debug', $this->TAG . ': insert_batch: ' . json_encode($data));
    $result = [];
    if ( ! empty($data)) {
      // Check id_web before insert
      $news_id_web = [];
      foreach ($data as $item) {
        $news_id_web[] = $item['id_web'];
      }
      $this->db->select('id_web');
      $this->db->where_in('id_web', $news_id_web, FALSE);
      $this->db->from('news');
      $result_check = $this->db->get()->result();
      foreach ($result_check as $check_item) {
        foreach ($data as $key => $item) {
          // Remove data if id_web exist
          if ($item['id_web'] === $this->db->escape($check_item->id_web)) {
            unset($data[$key]);
          }
        }
      }
      // Only insert data if data not empty after clean up
      if (count($data) > 0) {
        $this->db->insert_batch('news', $data, FALSE);
        foreach ($data as $key => $item) {
          $result[] = $item['id_web'];
        }
      }
    }

    return $result;
  }

  /**
   * Function to get last id from the web
   *
   * @return array List last id from the web
   */
  public function get_last_id_web()
  : array
  {
    $this->log->write_log('debug', $this->TAG . ': get_last_id_web: ');
    $this->db->select('date');
    $this->db->from('news');
    $this->db->limit(1);
    $this->db->order_by('date', 'DESC');
    $result_check_date = $this->db->get()->unbuffered_row();

    $return = [];
    if ( ! empty($result_check_date)) {
      $this->db->select('id_web');
      $this->db->from('news');
      $this->db->where('date', $result_check_date->date);
      $this->db->order_by('date', 'DESC');
      $result = $this->db->get()->result();
      if ( ! empty($result)) {
        foreach ($result as $item) {
          $return[] = $item->id_web;
        }
      }
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
   * @param bool        $full_link Is need full link?
   * @param int         $page      Page now
   * @param int|null    $per_page  News count per page
   * @param null|string $id        News id. If you define the News Id this will return single data, so will return
   *                               without total_page and page_now and data is not inside `news` index
   *
   * @param null|string $key       News key or last path from the website
   *                               (eg: http://filkom.ub.ac.id/page/read/news/title-link/{key}).
   *                               If you define the News Id this will return single data, so will return without
   *                               total_page and page_now and data is not inside `news` index
   *
   * @return array News List
   */
  public function get(bool $full_link, int $page = 1, ?int $per_page = 10, ?string $id = NULL, ?string $key = NULL)
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
      $result_count = $this->db->get()->unbuffered_row();
      if ( ! empty($result_count)) {
        $count = $result_count->count;
        $total_page = ceil($count / $per_page);
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
    $news = $this->generate_news_output($result_news, $full_link);

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

  /**
   * Function to get news data with given id_web
   *
   * @param array|null $id_web List of id_web to get the data
   *
   * @return array List of detail news [id, title]
   */
  public function get_id_web(?array $id_web)
  {
    $result = [];
    if ( ! empty($id_web)) {
      $this->db->select("HEX(id) as id, title");
      $this->db->from("news");
      $this->db->where_in("id_web", $id_web, FALSE);
      $query = $this->db->get()->result();
      if ( ! empty($query)) {
        foreach ($query as $value) {
          $result[] = [$value->id, $value->title];
        }
      }
    }

    return $result;
  }


  /**
   * Get empty short link
   *
   * @return array News List [id_web,link]
   */
  public function get_not_shortened()
  : array
  {
    $result = [];
    $this->db->select('id_web, link');
    $this->db->where('link_short', NULL);
    $this->db->from('news');
    $query = $this->db->get()->result();

    if ( ! empty($query)) {
      foreach ($query as $item) {
        $result[] = ['id_web' => $item->id_web, 'link' => $item->link];
      }
    }

    return $result;
  }

  /**
   * Update short link
   *
   * @param array $data News list to update [id_web,link_short]
   *
   * @return int Affected row
   */
  public function update_short_link_batch(array $data)
  : int
  {
    $affected_row = 0;
    if ( ! empty($data)) {
      $affected_row = $this->db->update_batch('news', $data, 'id_web');
    }

    return $affected_row;
  }

}