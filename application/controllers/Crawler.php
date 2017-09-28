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
    $this->crawl_announcement();
  }

  /**
   * Crawl list of news
   */
  private function crawl_news_list()
  {
    $news_list = [];
    // Get data from server
    $client = new GuzzleHttp\Client();
    $exist = FALSE;
    $result = $client->get(
      $this->config->item('crawler_target'),
      [
        'headers' => ['Cookie' => ' lang-switch=in'] // Define cookies languague to Bahasa
      ]);
    $last = $this->news_model->get_last_id_web();
    $crawler = new \Symfony\Component\DomCrawler\Crawler(
      (string)$result->getBody(),
      $this->config->item('crawler_target'));
    $body = $crawler->filter('section[itemprop="articleBody"] .table.web-page-filkom tbody');
    $body->children()->each(
      function (\Symfony\Component\DomCrawler\Crawler $node, $i) use (&$news_list, &$exist, &$last) {
        if ( ! $exist) {
          $title = $node->filter('.title-article');
          $date = $node->filter('time');
          $link = $title->attr('href');
          $desc = $node->filter('.post-content.text-left');
          $desc = trim(preg_replace('/\t+/', '', str_replace('more..', '', $desc->text())));
          $image = $node->filter('img.media-object');
          $image = str_replace(" ", "_", $image->attr('src'));
          $date = urlencode(html_entity_decode(htmlentities($date->html())));
          $date = urldecode(str_replace("%C2%A0", "", $date));//.'<br>';
          $date_given = DateTime::createFromFormat('M d, Y', $date);
          if ($date_given) {
            $date_given->setTime(0, 0, 0);
            $id_web = explode('/', $link);
            $id_web = $id_web[count($id_web) - 1];
            foreach ($last as $id_last) {
              if ($id_web === $id_last) {
                $exist = TRUE;
                break;
              }
            }
            if ( ! $exist) {
              $news_list[] = $this->news_model->build_news(
                $id_web,
                $title->text(),
                $desc,
                $link,
                $image,
                $date_given->format("Y-m-d H:i:s"));
            }
          }
        }
      });
    if(!empty($news_list)) $this->news_model->insert_batch($news_list);
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

  private function crawl_announcement()
  {
    $client = new GuzzleHttp\Client();
    $announcement = [];
    $exist = FALSE;
    $result = $client->get(
      $this->config->item('crawler_target_announcement'),
      [
        'headers' => ['Cookie' => ' lang-switch=in']
      ]);
    $this->load->model('announcements_model');
    $last = $this->announcements_model->get_last_id_web();
    $crawler = new \Symfony\Component\DomCrawler\Crawler(
      (string)$result->getBody(),
      $this->config->item('crawler_target_announcement'));
    $body = $crawler->filter('section[itemprop="articleBody"] .table.web-page-filkom tbody');
    $body->children()->each(
      function (\Symfony\Component\DomCrawler\Crawler $node, $i) use (&$announcement, &$exist, &$last) {
        if ( ! $exist) {
          $title = $node->filter('.title-article');
          $date = $node->filter('.time-post');
          $link = $title->attr('href');
          $date_given = DateTime::createFromFormat('d M Y', $date->text());
          if ($date_given) {
            $date_given->setTime(0, 0, 0);
            $id_web = explode('/', $link);
            $id_web = $id_web[count($id_web) - 1];
            foreach ($last as $id_last) {
              if ($id_web === $id_last) {
                $exist = TRUE;
                break;
              }
            }
            if ( ! $exist) {
              $announcement[] = $this->announcements_model->build_announcement(
                $id_web,
                $title->text(),
                $link,
                $date_given->format("Y-m-d H:i:s"));
            }
          }
        }
      });
    $this->announcements_model->insert_batch($announcement);
  }

}