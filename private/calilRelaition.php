<?php
$calilRelaition = new calilRelaition();

header("Content-Type: text/javascript; charset=utf-8");
echo $calilRelaition->getLibraryJson($_GET["lat"], $_GET["lon"], $_GET["title"]);

exit();


class calilRelaition{
    function amazon_api($title) {
        // 書籍情報の検索
        // Access Key ID と Secret Access Key 、 AssociateTag は必須
                $access_key_id='Access Key ID';
                $secret_access_key='Secret Access Key';
                $AssociateTag='Associate Tag';
        // RFC3986 形式で URL エンコードする関数
                function urlencode_rfc3986($str) {
                    return str_replace('%7E', '~', rawurlencode($str));
                }
        // 基本的なリクエストを作成します
                $baseurl='http://ecs.amazonaws.jp/onca/xml';
            $params=array();
                $params['Service']='AWSECommerceService'; //AWSのサービス
                $params['AWSAccessKeyId']=$access_key_id; //AWSのアクセスキーID
                $params['Version']='2011-08-01'; //APIバージョン
                $params['Operation']='ItemSearch'; //商品名や著者名でキーワード検索
                $params['SearchIndex']='Books'; //本の中で検索
                $params['Keywords']=$title;//文字コードはUTF-8
                $params['AssociateTag']=$AssociateTag;//自分のアソシエイトIDを追加
                $params['ResponseGroup']='Large';//取得したい情報の種類に合わせ追加

        // Timestamp パラメータを追加します
        // - 時間の表記は ISO8601 形式、タイムゾーンは UTC(GMT)
                $params['Timestamp'] = gmdate('Y-m-d\TH:i:s\Z');

        // パラメータの順序を昇順に並び替えます
                ksort($params);

        // canonical string を作成します
                $canonical_string = '';
                foreach ($params as $k => $v) {
                    $canonical_string .= '&'.urlencode_rfc3986($k).'='.urlencode_rfc3986($v);
                }
                $canonical_string = substr($canonical_string, 1);

        // 署名を作成
                $parsed_url = parse_url($baseurl);
                $string_to_sign = "GET\n{$parsed_url['host']}\n{$parsed_url['path']}\n{$canonical_string}";
                $signature = base64_encode(hash_hmac('sha256', $string_to_sign, $secret_access_key, true));

        // XMLを取得するためのURLを作成、および署名付加
                $url = $baseurl.'?'.$canonical_string.'&Signature='.urlencode_rfc3986($signature);
        //XMLを取得、代入
                $amazon_xml=simplexml_load_string(@file_get_contents($url));
                foreach((object) $amazon_xml->Items->Item as $item_a=>$item){
                    //アダルト対策
                    $adult_dvd=$item->ItemAttributes->Format;//「アダルト」の文字
                $adult_toy=$item->ItemAttributes->IsAdultProduct;//アダルトなら「1」

                    if(strpos($adult_dvd,'アダルト')!==false){//他に文字が入っていたらイヤなのでstrposを使う
                        continue;
                    }elseif(strpos($adult_toy,'1')!==false){
                        continue;
                    }else{
                        global $title;
                        global $isbn;
                        global $bookdata;
                        $title=$item->ItemAttributes->Title; //本の名前
                        $isbn=$item->ItemAttributes->ISBN; //本のISBN
                        $bookdata = array('title' => $title,
                            'isbn' => $isbn);
                        unset($item);
                }
            }
    }
  /**
  * 
  */  
  public function getLibraryJson($lat, $lon, $title=""){
    // 設定情報取得
    $ini = parse_ini_file("config.ini");
    
    // 書籍情報を検索する
    $this->amazon_api($title);
    $book_info = $bookdata;
    
    // 付近の図書館検索
    $library = array();
    $library = $this->getNeerLibrary($ini["calil_key"], $lat, $lon);
    
    $system_id = "";
    foreach ($library["Library"] as $lib){
      $system_id .= $lib["systemid"];
      $system_id .= ",";
    }
    
    if(!empty($system_id)){
      $system_id = substr($system_id, 0, -1);
    }
    
    // 蔵書検索
    $book = array();
    $book = $this->getBookStatus($ini["calil_key"], $book_info["isbn"], $system_id);
    
    // 貸出可能図書館をピックアップ
    $library = $this->prepareLibrary($book["books"]["book"]["system"], $library["Library"]);
    
    // return用json整形
    $ret_json = $this->prepareRetJson($library,$book_info);
    
    return $ret_json;
  }
  
  public function getNeerLibrary($key, $lat, $lon){
//    $result = file_get_contents("http://api.calil.jp/library?appkey=$key&geocode=$lat,$lon&format=json");
    $result = file_get_contents("http://api.calil.jp/library?appkey=$key&geocode=136.7163027,35.390516&format=xml");
    $result = simplexml_load_string($result);
    return json_decode(json_encode($result), true);
  }
  
  public function getBookStatus($key, $isbn, $sys_id){
    $result = file_get_contents("http://api.calil.jp/check?appkey=$key&systemid=$sys_id&isbn=$isbn&format=xml");
    $result = simplexml_load_string($result);
    return json_decode(json_encode($result), true);
  }
  
  public function prepareLibrary($book_info, $library){
    $avilable_library = array();
    $avirable_book = array();
    foreach ($book_info as $book){
      if(isset($book["libkeys"]) && !empty($book["libkeys"]) && isset($book["@attributes"]["systemid"])){
        $avirable_book[]["systemid"] = $book["@attributes"]["systemid"];
      }
    }
    
    var_dump($avirable_book);
    
    foreach ($library as $lib){
      foreach ($avirable_book as $books){
        if($books["systemid"] == $lib["systemid"]){
          $avilable_library[] = $lib;
        }
      }
    }
    
    return $avilable_library;
  }
  
  public function prepareRetJson($library, $book_info){
    $ret_array = array();
    return $ret_array;
      $prepareJson_Array=array('book'=>array(
          'title'=>$bookinfo['title'],
          'isbn'=>$bookinfo['isbn']
      ),
          'library'=>array(
            'name'=>$library['systemname'],
              'address'=>$library['address'],
              'tel'=>$library['tel']
          )
      );
  }
}
?>
