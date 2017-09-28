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

?>

<h4><?php echo $api_title; ?></h4>

<p><?php echo $api_description; ?></p>


<div class="mdl-grid ">
  <div class="mdl-cell mdl-cell--6-col">
    <div class="mdl-grid mdl-grid--nesting">
      <div class="mdl-cell mdl-cell--4-col">
        <div>Base API URL</div>
      </div>
      <div class="mdl-cell mdl-cell--8-col">
        <code class="highlight">
          <span class="s"><?php echo base_url('api'); ?></span>
        </code>
      </div>
    </div>
    <div class="mdl-grid mdl-grid--nesting">
      <div class="mdl-cell mdl-cell--4-col">
        <div>End Point</div>
      </div>
      <div class="mdl-cell mdl-cell--8-col">
        <code class="highlight">
          <span class="s"><?php echo $api_target; ?></span>
        </code>
      </div>
    </div>
    <div class="mdl-grid mdl-grid--nesting">
      <div class="mdl-cell mdl-cell--4-col">
        <div>Method</div>
      </div>
      <div class="mdl-cell mdl-cell--8-col">
        <strong><?php echo $api_method; ?></strong>
        <div>
          <?php if ($api_method === 'GET') { ?>
            <a href="<?php echo $api_try; ?>" target="_blank"
               class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--accent">
              Try Data
            </a>
          <?php } ?>
        </div>
      </div>
    </div>
  </div>
  <div class="mdl-cell mdl-cell--6-col">
    <div><strong>Response</strong></div>
    <pre id="response"><?php //echo json_encode($api_response); ?></pre>

    <script>
      document.getElementById("response").appendChild(renderjson(<?php echo json_encode($api_response);?>));
    </script>
  </div>
</div>
