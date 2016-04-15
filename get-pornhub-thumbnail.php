<?php
/* 動画URLからサムネイルを取得 */

// ライブラリの読み込み
require_once('libraries/simple_html_dom.php');

/**
 * 指定されたURLのページのDOMオブジェクトを生成 (file_get_htmlの代わり)
 * @param string $url 取得するページのURL
 * @return $dom 生成したDOMオブジェクト
 */
function dlPage($url) {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_REFERER, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US) AppleWebKit/533.4 (KHTML, like Gecko) Chrome/5.0.375.125 Safari/533.4");
    $str = curl_exec($curl);
    curl_close($curl);

    // Create a DOM object
    $dom = new simple_html_dom();
    // Load HTML from a string
    $dom->load($str);

    return $dom;
}

/**
 * 動画URLからサムネイルを取得 (simple_html_dom.php を使用)
 * @param string $url 動画URL
 * @return array $thumbnail_urls 取得したサムネイルURL
 */
function getThumbnailUrls($url) {
    // 取得したサムネイルURLを格納する配列
    $thumbnail_urls = array();

    // HTMLを取得
    try {
        $html = dlPage($url);
    } catch (Exception $e) {
        echo 'HTMLの取得に失敗: ', $e->getMessage();
    }

    // 動画プレイヤーのスクリプト部分を取得
    $player_script = $html->find('#player', 0)->children(0);

    // '"image_url"'以降の文字列を取得
    $str = strstr($player_script->innertext, '"image_url"');

    // '\'を除去
    $str = str_replace('\\', '', $str);

    // 先頭のURLを取得
    if (preg_match('(https?://[-_.!~*\'()a-zA-Z0-9;/?:@&=+$,%#]+)', $str, $image_url) == false) {
        echo '画像URLを取得できませんでした';
    }

    // 配列を除去
    $image_url = $image_url[0];

    // '/original/'より前のURLを取得
    $image_url = strstr($image_url, '/original/', true);

    // 1から16までの全てのサムネイルURLを配列に格納
    for ($i = 1; $i <= 16; $i++) {
        $thumbnail_urls[$i - 1] = $image_url . '/original/' . $i . '.jpg';
    }

    return $thumbnail_urls;
}
