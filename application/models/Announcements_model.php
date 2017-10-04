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

class Announcements_model extends FNR_Model
{
  private $TAG = "Announcements_model";

  /**
   * Announcements_model constructor.
   */
  public function __construct()
  {
    parent::__construct();
  }

  /**
   * Function to insert the batch data. Use build_details() for data format.
   *
   * @param array $data Build data to insert
   *
   * @return int|array Inserted id_web
   */
  public function insert_batch(array $data)
  :?array
  {
    $this->log->write_log('debug', $this->TAG . ': insert_batch: ' . json_encode($data));
    $result = [];
    if ( ! empty($data)) {
      // Check id_web before insert
      $announcement_id_web = [];
      foreach ($data as $item) {
        $announcement_id_web[] = $item['id_web'];
      }
      $this->db->select('id_web');
      $this->db->where_in('id_web', $announcement_id_web, FALSE);
      $this->db->from('announcements');
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
        $this->db->insert_batch('announcements', $data, FALSE);

        foreach ($data as $key => $item) {
          $result[] = $item['id_web'];
        }
      }
    }

    return $result;
  }

  /**
   * Function to build announcement data for the batch insert.
   *
   * @param string $id_web Announcement Id from the web
   * @param string $title  Announcement Title
   * @param string $link   Announcement Link
   * @param string $date   Announcement Date
   *
   * @return array
   */
  public function build_announcement(string $id_web, string $title, string $link, string $date)
  : array
  {
    $this->log->write_log(
      'debug',
      $this->TAG . ': build_details: $id_web: ' . $id_web . ', $title: ' . $title . ', $link: ' . $link
      . ', $date: ' . $date
    );

    return [
      'id' => 'UNHEX(REPLACE(UUID(), "-", ""))',
      'id_web' => $this->db->escape($id_web),
      'title' => $this->db->escape($title),
      'link' => $this->db->escape($link),
      'date' => $this->db->escape($date)
    ];
  }

  /**
   * Function to get last id from the web
   *
   * @return array List of last string id_web in same date
   */
  public function get_last_id_web()
  : array
  {

    $this->log->write_log('debug', $this->TAG . ': get_last_id_web: ');
    $this->db->select('date');
    $this->db->from('announcements');
    $this->db->limit(1);
    $this->db->order_by('date', 'DESC');
    $result_check_date = $this->db->get()->unbuffered_row();

    $return = [];
    if ( ! empty($result_check_date)) {
      $this->db->select('id_web');
      $this->db->from('announcements');
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
   * @param int         $page     Page now
   * @param int|null    $per_page Announcement count per page
   * @param null|string $query    Announcement query for search purpose
   *
   * @return array Announcement List
   */
  public function get(int $page = 1, ?int $per_page = 10, ?string $query = NULL)
  : array
  {
    $this->log->write_log(
      'debug',
      $this->TAG . ': get: $page: ' . $page . ', $per_page: ' . $per_page
    );
    $total_page = 0;
    if (empty($id)) {
      $this->db->select('count(id) as count');
      $this->db->from('announcements');
      if ( ! empty($query)) {
        $this->db->like('LOWER(title)', strtolower($query));
      }
      $result_count = $this->db->get()->unbuffered_row();
      if ( ! empty($result_count)) {
        $count = $result_count->count;
        $total_page = ceil($count / $per_page);
      }
    }

    $this->db->select('HEX(id) as id, title, link, date');
    $this->db->order_by('date', 'DESC');
    $this->db->limit($per_page, ($page - 1) * $per_page);
    $this->db->from('announcements');
    if ( ! empty($query)) {
      $this->db->like('LOWER(title)', strtolower($query));
    }
    $result_announcement = $this->db->get()->result();

    $announcement = [];
    if ( ! empty($result_announcement)) {
      foreach ($result_announcement as $item) {
        $announcement[] = [
          'id' => $item->id,
          'title' => $item->title,
          'link' => str_replace(' ', '%20', $item->link),
          'date' => date("d/m/Y H:i:s", strtotime($item->date)),
        ];
      }
    }

    return [
      'total_page' => $total_page,
      'page_now' => $page,
      'announcement' => $announcement
    ];
  }


  public function get_id_web(?array $id_web)
  {
    $result = [];
    if ( ! empty($id_web)) {
      $this->db->select("title");
      $this->db->from("announcements");
      $this->db->where_in("id_web", $id_web, FALSE);
      $query = $this->db->get()->result();
      if ( ! empty($query)) {
        foreach ($query as $key => $value) {
          $result[] = $value->title;
        }
      }
    }

    return $result;
  }

}