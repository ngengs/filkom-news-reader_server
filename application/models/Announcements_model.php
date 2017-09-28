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
   * @return int|null Status query insert
   */
  public function insert_batch(array $data)
  {
    $this->log->write_log('debug', $this->TAG . ': insert_batch: ' . json_encode($data));
    $result = NULL;
    if ( ! empty($data)) {
      $result = $this->db->insert_batch('announcements', $data, FALSE);
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
    $result_check_date = $this->db->get()->result();

    $return = [];
    if ( ! empty($result_check_date)) {
      $this->db->select('id_web');
      $this->db->from('announcements');
      $this->db->where('date', $result_check_date[0]->date);
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


}