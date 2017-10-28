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
ini_set('max_execution_time', 1000);

class Crawler extends FNR_Controller
{
  private $TAG = 'Crawler';

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
    $this->load->model('announcements_model');
  }

  /**
   * Crawl the data
   * Path = crawler/go
   * Method = POST
   * Data = crawler_toke (required)
   */
  public function go_post()
  {
    $news = $this->crawl_news_list();
    $this->crawl_news_detail();
    $announcement = $this->crawl_announcement();
    $this->build_notification($news, $announcement);

    // Shortening link
    $this->shortening_link_news();
    $this->shortening_link_announcement();
  }

  /**
   * Crawl News
   *
   * @return array|null Array of inserted id_web
   */
  private function crawl_news_list()
  :?array
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
      function (\Symfony\Component\DomCrawler\Crawler $node) use (&$news_list, &$exist, &$last) {
        if ( ! $exist) {
          $title = $node->filter('.title-article');
          $date = $node->filter('time');
          $link = $title->attr('href');
          $desc = $node->filter('.post-content.text-left');
          $desc = trim(preg_replace('/\t+/', '', str_replace('more..', '', $desc->text())));
          $image = $node->filter('img.media-object');
          $image = str_replace(' ', '_', $image->attr('src'));
          $date = urlencode(html_entity_decode(htmlentities($date->html())));
          $date = urldecode(str_replace('%C2%A0', '', $date));//.'<br>';
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
                $date_given->format('Y-m-d H:i:s'));
            }
          }
        }
      });
    $result_insert = [];
    if ( ! empty($news_list)) $result_insert = $this->news_model->insert_batch($news_list);

    return $result_insert;
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
      foreach ($news_list as $value) {
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
            function (\Symfony\Component\DomCrawler\Crawler $node, $index) use ($limit, $id, &$parsed_data, &$position) {
              if ($index < ($limit - 1)) {
                $data = $node->html();
                // Extract image from data
                try {
                  $image_node = $node->filter('a.fancybox img');
                  $src = $image_node->attr('src');
                  $parsed_data[] = $this->details_model->build_details($id, 3, $src, $position);
                  $position++;
                  $image_parent_node = $image_node->parents()->getNode(0);
                  $removed_string = $image_parent_node->ownerDocument->saveHTML($image_parent_node);
                  $data = str_replace($removed_string, '', $data);
                } catch (Exception $exception) {
                  $this->log->write_log(
                    'info',
                    $this->TAG . ': crawl_news_detail: image not detected at node: ' . $index);
                }

                $type = ($node->nodeName() == 'blockquote') ? 2 : 0;
                $type = ($node->nodeName() == 'p') ? 1 : $type;
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

  /**
   * Crawl announcement
   *
   * @return array|null Array of inserted id_web
   */
  private function crawl_announcement()
  :?array
  {
    $client = new GuzzleHttp\Client();
    $announcement = [];
    $exist = FALSE;
    $result = $client->get(
      $this->config->item('crawler_target_announcement'),
      [
        'headers' => ['Cookie' => ' lang-switch=in']
      ]);
    $last = $this->announcements_model->get_last_id_web();
    $crawler = new \Symfony\Component\DomCrawler\Crawler(
      (string)$result->getBody(),
      $this->config->item('crawler_target_announcement'));
    $body = $crawler->filter('section[itemprop="articleBody"] .table.web-page-filkom tbody');
    $body->children()->each(
      function (\Symfony\Component\DomCrawler\Crawler $node) use (&$announcement, &$exist, &$last) {
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
                $date_given->format('Y-m-d H:i:s'));
            }
          }
        }
      });
    $result_print = [];
    if ( ! empty($announcement)) $result_print = $this->announcements_model->insert_batch($announcement);

    return $result_print;
  }

  private function build_notification(?array $id_web_news, ?array $id_web_announcement)
  {
    $this->log->write_log(
      'debug',
      $this->TAG . ': news: ' . json_encode($id_web_news) . ', announcement: ' .
      json_encode($id_web_announcement));
    $fcm_key = $this->config->item('fcm_server_key');
    $topics_news = $this->config->item('fcm_topic_news');
    $topic_announcement = $this->config->item('fcm_topic_announcement');
    if ( ! empty($fcm_key)) {
      $send_news = [];
      $send_announcement = [];
      if ( ! empty($id_web_news)) {
        $data_news = $this->news_model->get_id_web($id_web_news);
        if ( ! empty($data_news)) {
          $send_news['type'] = 10;
          $send_news['total'] = count($data_news);
          for ($i = 0; $i < count($data_news) && $i < 5; $i++) {
            $send_news['id_' . $i] = $data_news[$i][0];
            $send_news['title_' . $i] = $data_news[$i][1];
          }
        }
      }
      if ( ! empty($id_web_announcement)) {
        $data_announcement = $this->announcements_model->get_id_web($id_web_announcement);
        if ( ! empty($data_announcement)) {
          $send_announcement['type'] = 11;
          $send_announcement['total'] = count($data_announcement);
          for ($i = 0; $i < count($data_announcement) && $i < 5; $i++) {
            $send_announcement['title_' . $i] = $data_announcement[$i];
          }
        }
      }
      if (is_array($fcm_key)) {
        foreach ($fcm_key as $key) {
          $this->send_notification($key, $topics_news, $send_news);
          $this->send_notification($key, $topic_announcement, $send_announcement);
        }
      } else {
        $this->send_notification($fcm_key, $topics_news, $send_news);
        $this->send_notification($fcm_key, $topic_announcement, $send_announcement);
      }
    }
  }

  private function send_notification(string $fcm_key, string $topic, ?array $payload)
  {
    $fcm = new GuzzleHttp\Client();
    if ( ! empty($payload)) {
      $fcm->request(
        'POST',
        'https://fcm.googleapis.com/fcm/send',
        [
          'json' => [
            'to' => '/topics/' . $topic,
            'data' => $payload
          ],
          'headers' => [
            'Authorization' => 'key=' . $fcm_key
          ]
        ]
      );
    }
  }

  /**
   * Shortening news link
   */
  private function shortening_link_news()
  {
    $google_short_key = $this->config->item('google_short_url_key');
    if ( ! empty($google_short_key)) {
      // Check not shortened link in news
      $data = $this->news_model->get_not_shortened();
      if ( ! empty($data)) {
        $shortened = [];
        foreach ($data as $item) {
          $link_short = $this->google_shortening($item['link'], $google_short_key);
          if ( ! empty($link_short)) {
            $shortened[] = ['id_web' => $item['id_web'], 'link_short' => $link_short];
          }
        }
        $this->news_model->update_short_link_batch($shortened);
      }
    }
  }

  /**
   * Shortening announcement link
   */
  private function shortening_link_announcement()
  {
    $google_short_key = $this->config->item('google_short_url_key');
    if ( ! empty($google_short_key)) {
      // Check not shortened link in news
      $data = $this->announcements_model->get_not_shortened();
      if ( ! empty($data)) {
        $shortened = [];
        foreach ($data as $item) {
          $link_short = $this->google_shortening($item['link'], $google_short_key);
          if ( ! empty($link_short)) {
            $shortened[] = ['id_web' => $item['id_web'], 'link_short' => $link_short];
          }
        }
        $this->announcements_model->update_short_link_batch($shortened);
      }
    }
  }

  /**
   * Use google service to shortening the url
   *
   * @param string $link Long link to shortened
   * @param string $key  Google API Key
   *
   * @return null|string Short link
   */
  private function google_shortening(string $link, string $key)
  :?string
  {
    $result = NULL;

    $google_shortening = new GuzzleHttp\Client();
    $response = $google_shortening->request(
      'POST',
      'https://www.googleapis.com/urlshortener/v1/url',
      [
        'json' => [
          'longUrl' => $link
        ],
        'query' => ['key' => $key]
      ]);
    $response_body = $response->getBody();
    if ( ! empty($response_body)) {
      $json_response = json_decode($response_body);
      if ( ! empty($json_response->id)) {
        $result = $json_response->id;
      }
    }

    return $result;
  }

}