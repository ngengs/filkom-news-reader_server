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

class FNR_Model extends CI_Model
{
  private $TAG = "FNR_Model";

  /**
   * FNR_Model constructor.
   */
  public function __construct()
  {
    parent::__construct();
    $this->load->database();
  }

  /**
   * Function to generate select and order for displaying news list.
   *
   * @param int $page     Page now
   * @param int $per_page News count per page
   */
  protected function news_list_builder(int $page = 1, int $per_page = 10)
  {
    $this->log->write_log('debug', $this->TAG . ': news_list_builder: $page: ' . $page . ', $per_page: ' . $per_page);

    $this->db->select(
      'HEX(news.id) as id, news.title as title, news.short_desc as short_desc,
       news.image as image, news.link as link, news.link_short as link_short, news.date as date'
    );
    $this->db->order_by('news.date', 'DESC');
    $this->db->limit($per_page, ($page - 1) * $per_page);
  }

  /**
   * @param mixed $result    Result data from the query
   * @param bool  $full_link Is we need full link?
   *
   * @return array Generated output
   */
  protected function generate_news_output($result = NULL, bool $full_link = FALSE)
  : array
  {
    $news = [];
    if ( ! empty($result)) {
      foreach ($result as $item) {
        $link = str_replace(' ', '%20', $item->link);
        if ( ! empty($item->link_short) && ! $full_link) {
          $link = $item->link_short;
        }
        $news[] = [
          'id' => $item->id,
          'title' => $item->title,
          'short_desc' => $item->short_desc,
          'image' => str_replace(' ', '%20', $item->image),
          'link' => $link,
          'date' => date("d/m/Y H:i:s", strtotime($item->date)),
        ];
      }
    }

    return $news;
  }

}