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

class News extends FNR_Controller
{
  private $TAG = "News";

  /**
   * News constructor.
   */
  public function __construct()
  {
    parent::__construct();
    $this->load->model('news_model');
  }


  /**
   * API Get list of news
   * Path = news/list/
   * Method = GET
   * Param = page
   */
  public function list_get()
  {
    $page = $this->input->get('page') ?? 1;
    $this->log->write_log('debug', $this->TAG . ': list: $page: ' . $page);
    if ( ! is_numeric($page) OR $page <= 0) {
      $this->response_error(VALUE_STATUS_CODE_ERROR, 'Wrong URL Parameter.', 404);
    }
    $result = $this->news_model->get($page);
    $this->response($result);
  }

  /**
   * API Get detail of news
   * Path = news/id/{$id}
   * Method = GET
   *
   * @param string $id News Id
   */
  public function id_get($id = NULL)
  {
    $this->log->write_log('debug', $this->TAG . ': id_get: $id: ' . $id);
    if (empty($id)) $this->response_404();
    $news = $this->news_model->get(1, 1, $id);
    if (empty($news)) {
      $this->response_error(VALUE_STATUS_CODE_ERROR, 'News ID not found', 404);
    }
    $this->load->model('details_model');
    $content = $this->details_model->get($id);
    // Build output
    $news = $news[0];
    $output = [
      'id' => $news['id'],
      'title' => $news['title'],
      'image' => $news['image'],
      'link' => $news['link'],
      'date' => $news['date'],
      'content' => $content
    ];
    $this->response($output);
  }

  /**
   * API Get detail of news by the web key
   * Path = news/key/{$key}
   * Method = GET
   *
   * @param string $key News Key
   */
  public function key_get($key = NULL)
  {
    $this->log->write_log('debug', $this->TAG . ': key_get: $key: ' . $key);
    if (empty($key)) $this->response_404();
    $news = $this->news_model->get(1, 1, NULL, $key);
    if (empty($news)) {
      $this->response_error(VALUE_STATUS_CODE_ERROR, 'News Key not found', 404);
    }
    $news = $news[0];
    $this->load->model('details_model');
    $content = $this->details_model->get($news['id']);
    // Build output
    $output = [
      'id' => $news['id'],
      'title' => $news['title'],
      'image' => $news['image'],
      'link' => $news['link'],
      'date' => $news['date'],
      'content' => $content
    ];
    $this->response($output);
  }

  /**
   * API Get list of search news
   * Path = news/search
   * Method = GET
   * Param = q (required), page
   *
   */
  public function search_get()
  {
    $page = $this->input->get('page') ?? 1;
    $query = $this->input->get('q');
    $this->log->write_log('debug', $this->TAG . ': list: $query: ' . $query . ',$page: ' . $page);
    if (empty($query) OR ! is_numeric($page) OR $page <= 0) {
      $this->response_error(VALUE_STATUS_CODE_ERROR, 'Wrong URL Parameter', 404);
    }
    $this->load->model('search_model');
    $query = html_escape($query);
    $result = $this->search_model->get($query, $page);
    $this->response($result);
  }
}