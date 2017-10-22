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

class Announcement extends FNR_Controller
{
  private $TAG = "Announcement";

  /**
   * Announcement constructor.
   */
  public function __construct()
  {
    parent::__construct();
    $this->load->model('announcements_model');
  }

  /**
   * API Get list of news
   * Path = announcement/list/{$page}
   * Method = GET
   * Param = page, link(short,full)
   */
  public function list_get()
  {
    $page = $this->input->get('page') ?? 1;
    $link = $this->input->get('link') ?? 'short';
    $this->log->write_log('debug', $this->TAG . ': list: $page: ' . $page);
    if ( ! is_numeric($page) OR $page <= 0) {
      $this->response_error(VALUE_STATUS_CODE_ERROR, 'Wrong URL Parameter.', 404);
    }
    $full_link = (strtolower($link) === 'full') ? TRUE : FALSE;
    $result = $this->announcements_model->get($full_link, $page);
    $this->response($result);
  }

  /**
   * API Get list of search news
   * Path = announcement/search
   * Method = GET
   * Param = q (required), page, link(short,full)
   *
   */
  public function search_get()
  {
    $page = $this->input->get('page') ?? 1;
    $query = $this->input->get('q');
    $link = $this->input->get('link') ?? 'short';
    $this->log->write_log('debug', $this->TAG . ': list: $query: ' . $query . ',$page: ' . $page);
    if (empty($query) OR ! is_numeric($page) OR $page <= 0) {
      $this->response_error(VALUE_STATUS_CODE_ERROR, 'Wrong URL Parameter', 404);
    }
    $query = html_escape($query);
    $full_link = (strtolower($link) === 'full') ? TRUE : FALSE;
    $result = $this->announcements_model->get($full_link, $page, 10, $query);
    $this->response($result);
  }


}