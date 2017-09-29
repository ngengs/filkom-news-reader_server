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

/**
 * Class FNR_Controller
 *
 * @property News_model          $news_model
 * @property Details_model       $details_model
 * @property Search_model        $search_model
 * @property Announcements_model $announcements_model
 */
class FNR_Controller extends CI_Controller
{
  private $TAG = "FNR_Controller";

  /**
   * FNR_Controller constructor.
   *
   * @param string $TAG
   */
  public function __construct()
  {
    parent::__construct();
  }


  /**
   * Function to generate JSON response
   *
   * @param string      $status  Status of response, OK for success and ERROR for error
   * @param int|null    $code    Status code of response, null if success and int if error
   * @param string|null $message Status message of response, null if success and string ig error
   * @param mixed       $data    Data to send
   *
   * @return array Generated response from parameter
   */
  private function generate_response(string $status, ?int $code, ?string $message, $data)
  : array
  {
    $this->log->write_log('debug', $this->TAG . ': generate_response: ');
    if ($message instanceof Exception) $message = $message->getMessage();

    return [
      KEY_STATUS => $status,
      KEY_STATUS_CODE => $code,
      KEY_STATUS_MESSAGE => $message,
      KEY_DATA => $data
    ];
  }

  /**
   * Function to send JSON to client
   *
   * @param int        $status_code   HTTP status code usually 200 for OK, 500 ERROR, 404 NOT FOUND
   * @param array      $response      Array response which will convert to json
   * @param array|null $custom_header Custom HTTP header to send
   */
  private function send(int $status_code, array $response, ?array $custom_header = [])
  {
    $this->log->write_log('debug', $this->TAG . ': send: ' . $status_code . ', response_status: ');
    $this->output->set_status_header($status_code);
    $this->output->set_content_type('application/json');
    if ( ! empty($custom_header)) {
      foreach ($custom_header as $name => $header) {
        $this->output->set_header((sprintf('%s: %s', $name, $header)), TRUE);
      }
    }
    $this->output->set_output(json_encode($response));
    $this->output->_display();
    die;
  }

  /**
   * Function to send JSON response to client.
   *
   * @param mixed       $data    Data to send
   * @param string      $status  Status of response, OK for success and ERROR for error
   * @param int|null    $code    Status code of response, null if success and int if error
   * @param string|null $message Status message of response, null if success and string if error
   */
  protected function response($data, string $status = VALUE_STATUS_OK, int $code = VALUE_STATUS_CODE_OK,
    ?string $message = VALUE_STATUS_MESSAGE_DEFAULT)
  {
    $this->log->write_log('debug', $this->TAG . ': response: ');
    $response = $this->generate_response($status, $code, $message, $data);
    $this->send(200, $response);
  }

  /**
   * Function to send 404
   */
  protected function response_404()
  {
    $this->log->write_log('debug', $this->TAG . ': response_404: ');
    $this->response_error(VALUE_STATUS_CODE_ERROR, 'Page not found / wrong method', 404);
  }

  /**
   * Function to send error response in API or 404 in web
   *
   * @param int    $status_code    Status code
   * @param string $status_message Status message
   * @param int    $header_code    Status code in header
   */
  protected function response_error(int $status_code, string $status_message, int $header_code = 200)
  {
    $this->log->write_log('debug', $this->TAG . ': response_error: ');
    $response =
      $this->generate_response(
        VALUE_STATUS_ERROR,
        $status_code,
        $status_message,
        VALUE_DATA_ERROR);
    $this->send($header_code, $response);
  }

  /**
   * Function to remap the router
   *
   * @param string $object_called function name
   * @param array  $params        parameter of function
   *
   * @return mixed function if exist or die if not exist
   */
  public function _remap($object_called, $params = [])
  {
    $object_called = $object_called . '_' . $this->input->method(FALSE);
    if (method_exists($this, $object_called)) {
      return call_user_func_array([$this, $object_called], $params);
    }
    $this->response_404();

    return NULL;
  }

}