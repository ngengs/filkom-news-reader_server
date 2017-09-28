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
ini_set('max_execution_time', 500);

class Crawler extends FNR_Controller
{
  private $TAG = "Crawler";

  /**
   * Crawler constructor.
   */
  public function __construct()
  {
    parent::__construct();
    $this->config->load('web_config');
    $token_reserved = $this->config->item('crawler_token');
    $token_request = $this->input->post('crawler_token');
    if (empty($token_reserved) && empty($token_request)) $this->response_404();
    else if ($token_request !== $token_reserved) $this->response_404();
    $this->load->model('news_model');
    $this->load->model('details_model');
    $this->load->model('search_model');
  }

  /**
   * Crawl the data
   * Path = crawler/go
   * Method = POST
   * Data = crawler_toke (required)
   */
  public function go_post()
  {
    $this->crawl_news_list();
    $this->crawl_news_detail();
  }

  /**
   * Crawl list of news
   *
   * @return int|null News list count saved to db
   */
  private function crawl_news_list()
  {
    $news_list = [];
    // Get data from server
    $client = new GuzzleHttp\Client();
    $base_url = $this->config->item('crawler_target');
    $response = $client->get($base_url);
    $body = (string)$response->getBody();
    $result_insert_news = 0;
    if ( ! empty($body)) {
      $body_object = json_decode($body);
      if ( ! empty($body_object)) {
        $last_id = $this->news_model->get_last_id_web();
        foreach ($body_object as $key => $value) {
          if ( ! empty($last_id)) {
            if ($last_id == $value->id) {
              $this->log->write_log('info', $this->TAG . ': crawl_news_list: id exist: ' . $last_id);
              break;
            }
          }
          $date_given = DateTime::createFromFormat('M d, Y - H:i', $value->tgl . ' - ' . $value->jam);
          if ($date_given) {
            $date = $date_given->format("Y-m-d H:i:s");
            $news_list[] = $this->news_model->build_news(
              $value->id,
              $value->judul,
              $value->isi,
              $value->url_web,
              $value->img,
              $date);
          }
        }
        // Insert data if not empty
        if ( ! empty($news_list)) $result_insert_news = $this->news_model->insert_batch($news_list);

        // If something wrong when inserting data, reset the return data to empty
        if (empty($result_insert_news)) $news_list = [];
      }
    }

    return $result_insert_news;
  }

  /**
   * Crwal detail of each news
   */
  private function crawl_news_detail()
  {
    $news_list = $this->news_model->get_empty_detail();
    if ( ! empty($news_list)) {
      $client = new GuzzleHttp\Client();
      $search_data = [];
      foreach ($news_list as $key => $value) {
        if (key_exists('link', $value) && ! empty($value->link)) {
          // Crawl the data
          $result = $client->get($value->link);
          $id = $value->id;

          // Start formatting result of crawler data
          $crawler = new \Symfony\Component\DomCrawler\Crawler((string)$result->getBody(), $value->link);
          $body = $crawler->filter('section[itemprop="articleBody"]');
          $limit = count($body->children());
          $parsed_data = [];
          $position = 0;
          $this->log->write_log('debug', $this->TAG . ': link: ' . $value->link);
          $this->log->write_log('debug', $this->TAG . ': limit: ' . $limit);
          $search_data[] = $this->search_model->build_search($id, $value->title, $body->text());
          // Split the data per type
          $body->children()->each(
            function (\Symfony\Component\DomCrawler\Crawler $node, $i) use ($limit, $id, &$parsed_data, &$position) {
              if ($i < ($limit - 1)) {
                $data = $node->html();
                // Extract image from data
                try {
                  $image_node = $node->filter('a.fancybox img');
                  $src = $image_node->attr("src");
                  $parsed_data[] = $this->details_model->build_details($id, 3, $src, $position);
                  $position++;
                  $image_parent_node = $image_node->parents()->getNode(0);
                  $removed_string = $image_parent_node->ownerDocument->saveHTML($image_parent_node);
                  $data = str_replace($removed_string, "", $data);
                } catch (Exception $exception) {
                  $this->log->write_log(
                    'info',
                    $this->TAG . ': crawl_news_detail: image not detected at node: ' . $i);
                }

                $type = ($node->nodeName() == "blockquote") ? 2 : 0;
                $type = ($node->nodeName() == "p") ? 1 : $type;
                $parsed_data[] = $this->details_model->build_details($id, $type, $data, $position);
                $position++;
              }
            });
          // Insert the detail
          $this->details_model->insert_batch($parsed_data);
          $crawler->clear();
          // End formatting result of crawler data
        }
      }
      // Insert the search data
      $this->search_model->insert_batch($search_data);
    }
  }

}