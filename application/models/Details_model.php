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

class Details_model extends FNR_Model
{
  private $TAG = "Details_model";

  /**
   * Details_model constructor.
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
      $result = $this->db->insert_batch('details', $data, FALSE);
    }

    return $result;
  }

  /**
   * Function to build search data for the batch insert.
   *
   * @param string $id_news  News id
   * @param int    $type     Type of detail. (1 = Paragraph, 2 = Block quote, 3= Image, 0 = Other)
   * @param string $data     Html / Image Url content
   * @param int    $position Position of data in the detail news
   *
   * @return array Build detail to insert
   */
  public function build_details(string $id_news, int $type, string $data, int $position)
  : array
  {
    $this->log->write_log(
      'debug',
      $this->TAG . ': build_details: $id_news: ' . $id_news . ', $type: ' . $type . ', $data: ' . $data
      . ', $position: ' . $position
    );

    return [
      'id' => 'UNHEX(REPLACE(UUID(), "-", ""))',
      'id_news' => 'UNHEX(' . $this->db->escape($id_news) . ')',
      'type' => $this->db->escape($type),
      'data' => $this->db->escape($data),
      'position' => $this->db->escape($position)
    ];
  }

  /**
   * @param string $id News Id
   *
   * @return array List of detail news
   */
  public function get(string $id)
  {
    $this->log->write_log('debug', $this->TAG . ': get: ' . $id);
    $this->db->select('type, data, position');
    $this->db->where('hex(details.id_news)', $id);
    $this->db->from('details');
    $this->db->order_by('position', 'ASC');
    $result = $this->db->get()->result();

    $content = [];
    if ( ! empty($result)) {
      foreach ($result as $item) {
        $content[] = [
          'type' => (int)$item->type,
          'position' => (int)$item->position,
          'content' => $item->data
        ];
      }
    }

    return $content;
  }


}