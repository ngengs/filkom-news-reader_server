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

class Search_model extends FNR_Model
{
  private $TAG = "Search_model";

  public function __construct()
  {
    parent::__construct();
  }

  /**
   * Function to insert the batch data. Use build_search() for data format.
   *
   * @param array $data Build data to insert
   */
  public function insert_batch(array $data)
  {
    $this->log->write_log('debug', $this->TAG . ': insert_batch: ' . json_encode($data));
    if ( ! empty($data)) {
      $this->db->insert_batch('search', $data, FALSE);
    }
  }

  /**
   * Function to build search data for the batch insert.
   *
   * @param string $id_news News Id
   * @param string $title   News Title
   * @param string $data    News RAW / Text data
   *
   * @return array Build search to insert
   */
  public function build_search(string $id_news, string $title, string $data)
  : array
  {
    $this->log->write_log(
      'debug',
      $this->TAG . ': build_search: $id_news: ' . $id_news . ', $title: ' . $title . ', $data: ' . $data
    );

    return [
      'id' => 'UNHEX(REPLACE(UUID(), "-", ""))',
      'id_news' => 'UNHEX(' . $this->db->escape($id_news) . ')',
      'title' => $this->db->escape($title),
      'data' => $this->db->escape($data)
    ];
  }

  /**
   * Function to get list of search news per page
   *
   * @param string   $query    Search query text
   * @param int      $page     Page now
   * @param int|null $per_page News count per page
   *
   * @return array News List
   */
  public function get(string $query, int $page = 1, ?int $per_page = 10)
  {
    $this->log->write_log(
      'debug',
      $this->TAG . ': get: $query: ' . $query . ', $page: ' . $page . ', $per_page: ' . $per_page
    );

    $total_page = 0;
    $this->db->select('count(search.id) as count');
    $this->db->from('search');
    $this->db->like('LOWER(search.data)', strtolower($query));
    $this->db->or_like('LOWER(search.title)', strtolower($query));
    $result_count = $this->db->get()->unbuffered_row();
    if ( ! empty($result_count)) {
      $count = $result_count->count;
      $total_page = intdiv($count, $per_page);
    }
    $this->news_list_builder($page, $per_page);
    $this->db->from('search');
    $this->db->like('search.data', $query);
    $this->db->like('search.title', $query);
    $this->db->join('news', 'news.id=search.id_news');
    $result_news = $this->db->get()->result();
    $news = $this->generate_news_output($result_news);

    return [
      'total_page' => $total_page,
      'page_now' => $page,
      'news' => $news
    ];
  }


}