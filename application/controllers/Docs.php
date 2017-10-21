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

class Docs extends CI_Controller
{
  private $data = [];

  public function __construct()
  {
    parent::__construct();
    $this->load->helper('url');
    $this->data['site_name'] = 'Filkom Brawijaya News Reader';
    $this->data['menu'] = [
      [
        'id' => 1,
        'title' => 'Home',
        'link' => base_url()
      ],
      [
        'id' => 2,
        'title' => 'API',
        'link' => NULL,
        'child' => [
          [
            'id' => 20,
            'title' => 'News List',
            'link' => base_url('docs/api/news_list')
          ],
          [
            'id' => 21,
            'title' => 'News Detail',
            'link' => base_url('docs/api/news_detail')
          ],
          [
            'id' => 23,
            'title' => 'News Detail by Key',
            'link' => base_url('docs/api/news_detail_key')
          ],
          [
            'id' => 22,
            'title' => 'News Search',
            'link' => base_url('docs/api/news_search')
          ],
          [
            'id' => 24,
            'title' => 'Announcement List',
            'link' => base_url('docs/api/announcement_list')
          ],
          [
            'id' => 25,
            'title' => 'Announcement Search',
            'link' => base_url('docs/api/announcement_search')
          ],
        ]
      ],
      [
        'id' => 3,
        'title' => 'Source',
        'link' => NULL,
        'child' => [
          [
            'id' => 30,
            'title' => 'Web Server',
            'link' => 'https://github.com/ngengs/filkom-news-reader_server'
          ],
          [
            'id' => 30,
            'title' => 'Android App',
            'link' => 'https://github.com/ngengs/filkom-news-reader_application-android'
          ]
        ]
      ]
    ];
  }


  /**
   * Index Page for this controller.
   */
  public function index()
  {
    $this->data['page_title'] = 'Home';
    $this->data['selected_menu'] = 1;
    $this->load->view('docs/header', $this->data);
    $this->load->view('docs/index', $this->data);
    $this->load->view('docs/footer', $this->data);
  }

  public function api($type = NULL)
  {
    $selected = -1;
    $api_title = '';
    $api_description = '';
    $api_target = '';
    $api_method = '';
    $api_try = '';
    $api_response = [
      "status" => "OK",
      "code" => 200,
      "message" => NULL,
      "data" => NULL
    ];
    switch ($type) {
      case 'news_list':
        $selected = 20;
        $api_title = 'News List';
        $api_description =
          "This API will give list of News with latest news first and with param 
<code class='highlight'><span class='s'>{page}</span></code> for pagination the news list";
        $api_target = "news/list?page={page}";
        $api_try = base_url('api/news/list?page=1');
        $api_method = 'GET';
        $list = [];
        for ($i = 0; $i < 2; $i++) {
          $list[] = [
            "id" => "NEWS-ID-{$i}",
            "title" => "News Title {$i}",
            "short_desc" => "News Description Short {$i}",
            "image" => "http://example.com/image-{$i}.jpg",
            "link" => "http://example.com/news/original-link-{$i}",
            "date" => "31/01/2011 23:23:59"
          ];
        }
        $api_response["data"] = [
          "total_page" => 4,
          "page_now" => 1,
          "news" => $list
        ];
        break;
      case 'news_detail':
        $selected = 21;
        $api_title = 'News Detail';
        $api_description = "This API will give detail of News with specific id 
<code class='highlight'><span class='s'>{id}</span></code> from the list.
<br>It will give list of content with specific type:
<br>Type: <b>1</b> for default paragraph.
<br>Type: <b>2</b> for blockquote.
<br>Type: <b>3</b> for images.";
        $api_target = "news/id/{id}";
        $api_try = base_url('api/news/id/CBF43261A4A911E79F376C92BF0C2105');
        $api_method = 'GET';
        $list = [];
        $list[] = [
          "type" => 1,
          "position" => 0,
          "content" => "News Paragraph"
        ];
        $list[] = [
          "type" => 3,
          "position" => 1,
          "content" => "http://example.com/image.jpg"
        ];
        $list[] = [
          "type" => 2,
          "position" => 2,
          "content" => "News Quotes"
        ];
        $api_response["data"] = [
          "id" => "NEWS-ID",
          "title" => "News Title",
          "image" => "http://example.com/image.jpg",
          "link" => "http://example.com/news/original-link",
          "date" => "31/01/2011 23:23:59",
          "content" => $list
        ];
        break;
      case 'news_detail_key':
        $selected = 23;
        $api_title = 'News Detail';
        $api_description = "This API will give detail of News with specific key 
<code class='highlight'><span class='s'>{key}</span></code> from the filkom web.
<br>You can get filkom news key from the last segment (eg: http://filkom.ub.ac.id/page/read/news/title-url/f14f1fe), 
that last <code class='highlight'><span class='s'>f14f1fe</span></code> is the <b>web key</b>.
<br>It will give list of content with specific type:
<br>Type: <b>1</b> for default paragraph.
<br>Type: <b>2</b> for blockquote.
<br>Type: <b>3</b> for images.";
        $api_target = "news/key/{key}";
        $api_try = base_url('api/news/key/f14f1fe');
        $api_method = 'GET';
        $list = [];
        $list[] = [
          "type" => 1,
          "position" => 0,
          "content" => "News Paragraph"
        ];
        $list[] = [
          "type" => 3,
          "position" => 1,
          "content" => "http://example.com/image.jpg"
        ];
        $list[] = [
          "type" => 2,
          "position" => 2,
          "content" => "News Quotes"
        ];
        $api_response["data"] = [
          "id" => "NEWS-ID",
          "title" => "News Title",
          "image" => "http://example.com/image.jpg",
          "link" => "http://example.com/news/original-link",
          "date" => "31/01/2011 23:23:59",
          "content" => $list
        ];
        break;
      case 'news_search':
        $selected = 22;
        $api_title = 'News Search';
        $api_description =
          "This API will give list of Search News with latest news first and with param 
<code class='highlight'><span class='s'>{text}</span></code> for searched text and 
<code class='highlight'><span class='s'>{page}</span></code> for pagination purpose";
        $api_target = "news/search?q={text}&page={page}";
        $api_try = base_url('api/news/search?q=kuliah&page=1');
        $api_method = 'GET';
        $list = [];
        for ($i = 0; $i < 2; $i++) {
          $list[] = [
            "id" => "NEWS-ID-{$i}",
            "title" => "News Title {$i}",
            "short_desc" => "News Description Short {$i}",
            "image" => "http://example.com/image-{$i}.jpg",
            "link" => "http://example.com/news/original-link-{$i}",
            "date" => "31/01/2011 23:23:59"
          ];
        }
        $api_response["data"] = [
          "total_page" => 4,
          "page_now" => 1,
          "news" => $list
        ];
        break;
      case 'announcement_list':
        $selected = 24;
        $api_title = 'Announcement List';
        $api_description =
          "This API will give list of Announcement with latest news first and with param 
<code class='highlight'><span class='s'>{page}</span></code> for pagination the announcement list";
        $api_target = "announcement/list?page={page}";
        $api_try = base_url('api/announcement/list?page=1');
        $api_method = 'GET';
        $list = [];
        for ($i = 0; $i < 2; $i++) {
          $list[] = [
            "id" => "ANNOUNCEMENT-ID-{$i}",
            "title" => "Announcement Title {$i}",
            "link" => "http://example.com/news/original-link-{$i}",
            "date" => "31/01/2011 23:23:59"
          ];
        }
        $api_response["data"] = [
          "total_page" => 4,
          "page_now" => 1,
          "announcement" => $list
        ];
        break;
      case 'announcement_search':
        $selected = 25;
        $api_title = 'Announcement Search';
        $api_description =
          "This API will give list of Search Announcement with latest announcement first and with param 
<code class='highlight'><span class='s'>{text}</span></code> for searched text and 
<code class='highlight'><span class='s'>{page}</span></code> for pagination purpose";
        $api_target = "announcement/search?q={text}&page={page}";
        $api_try = base_url('api/announcement/search?q=jadwal&page=1');
        $api_method = 'GET';
        $list = [];
        for ($i = 0; $i < 2; $i++) {
          $list[] = [
            "id" => "ANNOUNCEMENT-ID-{$i}",
            "title" => "Announcement Title {$i}",
            "link" => "http://example.com/news/original-link-{$i}",
            "date" => "31/01/2011 23:23:59"
          ];
        }
        $api_response["data"] = [
          "total_page" => 4,
          "page_now" => 1,
          "announcement" => $list
        ];
        break;

    }
    if ($selected === -1) {
      show_404();
      exit(1);
    }
    $this->data['page_title'] = "API - {$api_title}";
    $this->data['api_title'] = $api_title;
    $this->data['api_description'] = $api_description;
    $this->data['api_target'] = $api_target;
    $this->data['api_method'] = $api_method;
    $this->data['api_try'] = $api_try;
    $this->data['api_response'] = $api_response;
    $this->data['selected_menu'] = $selected;
    $this->load->view('docs/header', $this->data);
    $this->load->view('docs/api', $this->data);
    $this->load->view('docs/footer', $this->data);
  }
}
