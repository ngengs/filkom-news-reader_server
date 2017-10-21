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


function generate_menu_item(string $title, string $type, bool $selected, ?string $url)
: string
{
  $print_url = ( ! is_null($url)) ? $url : '#';
  $print_selected = $selected?'is-active':'';

  return "<a class='mdl-navigation__link {$type} {$print_selected}' href='{$print_url}'>{$title}</a>";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title><?php echo $page_title; ?> | <?php echo $site_name; ?> Server</title>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script defer src="https://code.getmdl.io/1.3.0/material.min.js"></script>
  <?php if(!empty($api_response)){
    echo "<script src='https://cdn.rawgit.com/caldwell/renderjson/master/renderjson.js'></script>";
  }?>
  <link rel="apple-touch-icon" sizes="180x180" href="<?php echo base_url('assets/apple-touch-icon.png');?>">
  <link rel="icon" type="image/png" sizes="32x32" href="<?php echo base_url('assets/favicon-32x32.png');?>">
  <link rel="icon" type="image/png" sizes="16x16" href="<?php echo base_url('assets/favicon-16x16.png');?>">
  <link rel="manifest" href="<?php echo base_url('assets/manifest.json');?>">
  <link rel="mask-icon" href="<?php echo base_url('assets/safari-pinned-tab.svg');?>" color="#2196f3">
  <link rel="shortcut icon" href="<?php echo base_url('assets/favicon.ico');?>">
  <meta name="msapplication-config" content="<?php echo base_url('assets/browserconfig.xml');?>">
  <meta name="apple-mobile-web-app-title" content="<?php echo $site_name; ?> Server">
  <meta name="application-name" content="<?php echo $site_name; ?> Server">
  <meta name="theme-color" content="#2196f3">
  <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons|Roboto:300,400,500,700">
  <link rel="stylesheet" href="https://code.getmdl.io/1.3.0/material.blue-pink.min.css"/>
  <style>
    html, body {
      font-family: "Roboto", "Helvetica", "Arial", sans-serif;
    }

    #site-title {
      color: inherit;
      text-decoration: none;
    }

    .mdl-layout__drawer > .mdl-layout-title {
      padding-left: 16px;
      padding-right: 16px;
      text-align: center;
      line-height: 32px;
    }

    .content {
      max-width: 800px;
      padding: 40px 40px 40px 40px;
      margin-left: auto;
      margin-right: auto;
    }

    .parent {
      font-weight: bold;
    }

    .child {
      padding: 5px 40px 5px 60px !important;
    }

    .is-active {
      background-color: #e0e0e0;
    }

    .content img {
      max-width: 100%;
      padding: 10px;
      border: 1px whitesmoke solid;
      border-radius: 10px;
    }

    .lineno {
      padding-right: 8px;
    }

    .mdl-grid .mdl-grid.mdl-grid--nesting {
      padding: 0;
      margin: 0 -8px;
    }

    /* Copied from Syntax.css (https://github.com/mojombo/tpw/blob/master/css/syntax.css) */
    .highlight {
      background: #ffffff;
    }

    .highlight .c {
      color: #999988;
      font-style: italic
    }

    /* Comment */
    .highlight .err {
      color: #a61717;
      background-color: #e3d2d2
    }

    /* Error */
    .highlight .k {
      font-weight: bold
    }

    /* Keyword */
    .highlight .o {
      font-weight: bold
    }

    /* Operator */
    .highlight .cm {
      color: #999988;
      font-style: italic
    }

    /* Comment.Multiline */
    .highlight .cp {
      color: #999999;
      font-weight: bold
    }

    /* Comment.Preproc */
    .highlight .c1 {
      color: #999988;
      font-style: italic
    }

    /* Comment.Single */
    .highlight .cs {
      color: #999999;
      font-weight: bold;
      font-style: italic
    }

    /* Comment.Special */
    .highlight .gd {
      color: #000000;
      background-color: #ffdddd
    }

    /* Generic.Deleted */
    .highlight .gd .x {
      color: #000000;
      background-color: #ffaaaa
    }

    /* Generic.Deleted.Specific */
    .highlight .ge {
      font-style: italic
    }

    /* Generic.Emph */
    .highlight .gr {
      color: #aa0000
    }

    /* Generic.Error */
    .highlight .gh {
      color: #999999
    }

    /* Generic.Heading */
    .highlight .gi {
      color: #000000;
      background-color: #ddffdd
    }

    /* Generic.Inserted */
    .highlight .gi .x {
      color: #000000;
      background-color: #aaffaa
    }

    /* Generic.Inserted.Specific */
    .highlight .go {
      color: #888888
    }

    /* Generic.Output */
    .highlight .gp {
      color: #555555
    }

    /* Generic.Prompt */
    .highlight .gs {
      font-weight: bold
    }

    /* Generic.Strong */
    .highlight .gu {
      color: #aaaaaa
    }

    /* Generic.Subheading */
    .highlight .gt {
      color: #aa0000
    }

    /* Generic.Traceback */
    .highlight .kc {
      font-weight: bold
    }

    /* Keyword.Constant */
    .highlight .kd {
      font-weight: bold
    }

    /* Keyword.Declaration */
    .highlight .kp {
      font-weight: bold
    }

    /* Keyword.Pseudo */
    .highlight .kr {
      font-weight: bold
    }

    /* Keyword.Reserved */
    .highlight .kt {
      color: #445588;
      font-weight: bold
    }

    /* Keyword.Type */
    .highlight .m {
      color: #009999
    }

    /* Literal.Number */
    .highlight .s {
      color: #d14
    }

    /* Literal.String */
    .highlight .na {
      color: #008080
    }

    /* Name.Attribute */
    .highlight .nb {
      color: #0086B3
    }

    /* Name.Builtin */
    .highlight .nc {
      color: #445588;
      font-weight: bold
    }

    /* Name.Class */
    .highlight .no {
      color: #008080
    }

    /* Name.Constant */
    .highlight .ni {
      color: #800080
    }

    /* Name.Entity */
    .highlight .ne {
      color: #990000;
      font-weight: bold
    }

    /* Name.Exception */
    .highlight .nf {
      color: #990000;
      font-weight: bold
    }

    /* Name.Function */
    .highlight .nn {
      color: #555555
    }

    /* Name.Namespace */
    .highlight .nt {
      color: #000080
    }

    /* Name.Tag */
    .highlight .nv {
      color: #008080
    }

    /* Name.Variable */
    .highlight .ow {
      font-weight: bold
    }

    /* Operator.Word */
    .highlight .w {
      color: #bbbbbb
    }

    /* Text.Whitespace */
    .highlight .mf {
      color: #009999
    }

    /* Literal.Number.Float */
    .highlight .mh {
      color: #009999
    }

    /* Literal.Number.Hex */
    .highlight .mi {
      color: #009999
    }

    /* Literal.Number.Integer */
    .highlight .mo {
      color: #009999
    }

    /* Literal.Number.Oct */
    .highlight .sb {
      color: #d14
    }

    /* Literal.String.Backtick */
    .highlight .sc {
      color: #d14
    }

    /* Literal.String.Char */
    .highlight .sd {
      color: #d14
    }

    /* Literal.String.Doc */
    .highlight .s2 {
      color: #d14
    }

    /* Literal.String.Double */
    .highlight .se {
      color: #d14
    }

    /* Literal.String.Escape */
    .highlight .sh {
      color: #d14
    }

    /* Literal.String.Heredoc */
    .highlight .si {
      color: #d14
    }

    /* Literal.String.Interpol */
    .highlight .sx {
      color: #d14
    }

    /* Literal.String.Other */
    .highlight .sr {
      color: #009926
    }

    /* Literal.String.Regex */
    .highlight .s1 {
      color: #d14
    }

    /* Literal.String.Single */
    .highlight .ss {
      color: #990073
    }

    /* Literal.String.Symbol */
    .highlight .bp {
      color: #999999
    }

    /* Name.Builtin.Pseudo */
    .highlight .vc {
      color: #008080
    }

    /* Name.Variable.Class */
    .highlight .vg {
      color: #008080
    }

    /* Name.Variable.Global */
    .highlight .vi {
      color: #008080
    }

    /* Name.Variable.Instance */
    .highlight .il {
      color: #009999
    }

  </style>
</head>
<body>
<div class="mdl-layout mdl-js-layout mdl-layout--fixed-drawer mdl-layout--fixed-header">
  <header class="mdl-layout__header">
    <div class="mdl-layout__header-row">
      <!--Title-->
      <span class="mdl-layout-title">
            <h1 id="page-title" class="mdl-typography--title"><?php echo $page_title; ?></h1>
      </span>
      <!-- Add spacer, to align navigation to the right -->
      <div class="mdl-layout-spacer"></div>
      <!-- Navigation -->
      <nav class="mdl-navigation">
        <a class="mdl-navigation__link" href="https://github.com/ngengs/filkom-news-reader_server">Source</a>
      </nav>
    </div>
  </header>
  <div class="mdl-layout__drawer">
    <span class="mdl-layout-title"><a id="site-title" class="" href="<?php echo base_url(); ?>">
        <?php echo $site_name; ?>
      </a></span>
    <nav class="mdl-navigation">
      <!--Menu-->
      <?php
      foreach ($menu as $item) {
        $child = "";
        $is_selected = FALSE;
        $print_menu = "";
        if ( ! empty($item['child'])) {
          foreach ($item['child'] as $item_child) {
            $child_selected = FALSE;
            if ( ! empty($selected_menu) && $selected_menu === $item_child['id']) {
              $child_selected = TRUE;
              $is_selected = TRUE;
            }
            $child .= generate_menu_item($item_child['title'], 'child', $child_selected, $item_child['link']);
          }
        } else {
          if ( ! empty($selected_menu) && $selected_menu=== $item['id']) {
            $is_selected = TRUE;
          }
        }
        $print_menu = generate_menu_item($item['title'], 'parent', $is_selected, $item['link']);
        $print_menu .= $child;
        echo $print_menu;
      }
      ?>
    </nav>
  </div>
  <main class="mdl-layout__content">
    <div class="page-content">
      <div class="content">
