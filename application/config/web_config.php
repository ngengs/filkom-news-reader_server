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

/*
 * Crawler Configuration
 */
$config['crawler_token'] = NULL;
$config['crawler_target'] = 'http://filkom.ub.ac.id/page/read/news/';
$config['crawler_target_announcement'] = 'http://filkom.ub.ac.id/page/read/pengumuman/';

/*
 * Push Notification Configuration
 */
$config['fcm_server_key'] = [];
$config['fcm_topic_news'] = 'subscribe_news';
$config['fcm_topic_announcement'] = 'subscribe_announcement';

/*
 * Google Short URL Configuration
 */
$config['google_short_url_key'] = NULL;