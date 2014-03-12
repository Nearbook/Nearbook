<?php
/**
 * Created by PhpStorm.
 * User: tea_hugutaku
 * Date: 2014/03/08
 * Time: 15:08
 */
require_once('../private/calilRelaition.php');
?>

<!DOCTYPE html>
<html lang="ja">
  <head>
    <title>Nearbook - 近所の貸し出し可能な図書館さがせます</title>
    <meta content='近所の貸し出し可能な図書館さがせます' name='description'>
    <meta content='https://www.facebook.com/groups/hackathon.beginner/' name='author'>
    <link href='/css/normalize.css' media='screen' rel='stylesheet'>
    <link href='/css/topcoat-mobile-light.min.css' media='screen' rel='stylesheet'>
    <link href='/css/app.css' media='screen' rel='stylesheet'>
    <script src='js/vendor/jquery.min.js'></script>
    <script src='js/vendor/underscore.js'></script>
    <script src='js/vendor/backbone.js'></script>
    <script src='js/app.js'></script>
  </head>
  <body>

    <header>
      <div class="topcoat-navigation-bar">
        <div class="topcoat-navigation-bar__item center full">
          <h1 class="topcoat-navigation-bar__title">近所の貸し出し可能な図書館さがせます</h1>
        </div>
      </div>
    </header>

    <section>
      <div class="search-form">
        <form id="search-input" action="#">
          <input type="search" name="q" value="" autocomplete="off" placeholder="本の名前、ISBN" class="topcoat-search-input--large hide"> 
        </form>
      </div>

      <article>
        <div id="search-results" class="topcoat-list"></div>
      </article>
    </section>

    <script type="text/template" id="results-item-template">
      <h1 class="topcoat-list__header">
        検索結果: 
        <span class="topcoat-notification"><%= +(library.length) %></span>
        <%= book.title %> <%= book.isbn %>
      </h1>
      <ul class="topcoat-list__container"></ul>
        <% _.each(library, function(item) { %>
          <li class="topcoat-list__item">
            <dl>
              <dt>図書館名</dt>
              <dd><%= item.name %></dd>
              <dt>住所</dt>
              <dd><%= item.address %></dd>
              <dt>電話番号</dt>
              <dd><%= item.tel %></dd>
            </dl>
          </li>
        <% }); %>
      </ul>
    </script>

  </body>
</html>
