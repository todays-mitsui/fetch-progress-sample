<?php
require_once("./vendor/autoload.php");

$app = new \Slim\Slim();

$app->get("/", function() use ($app) {
  include("./templates/index.tpl");
});

// "/inquire"へのPOSTリクエストに対する処理を記述
$app->post("/inquire", function() use ($app) {
  // リクエストパラメーターを取得
  $query = $app->request()->params("q");

  // execコマンドで子プロセスを生成
  // 時間の掛かる処理を heavy_task.php に記述しておき、別個に呼び出す
  // exec() の結果は即座に返り、処理はバックグラウンドで続けられる
  //
  // コマンドライン引数を渡すときはXSS対策のためescapeshellcmd()でエスケープすること
  exec("nohup php heavy_task.php ".escapeshellcmd($query)." > /dev/null 2>&1 &");

  // ステータスコードを上書きして 202 を返す
  // PHP5.4以上であれば header() の代わりに
  // http_response_code() を使うこともできそうです
  header("HTTP/ 202 Accepted");


  // リクエストに固有の名札として
  // hash digestとかを発行しておくと便利じゃないでしょうか
  $id = hash("sha256", time().$query);

  // クライアントにとりあえず返す補足情報を組み立てる
  $res = array(
    // idは次回以降のリクエストにも使って欲しいので
    // クライアントにも共有
    "id" => $id,

    // 処理中の場合は"Accepted"などを、
    // リクエストが不正な場合などは"Error"などを返す
    "status" => "Accepted",

    // 途中経過を問い合わせるためのURIや
    // 処理結果を取りに行くURIを教えてあげる
    "location" => "/progress/${id}",

    // タイムスタンプなども返す
    "time" => time(),
  );

  // JSONにエンコードしてレスポンスを返す
  // Content-Type の設定を忘れずに
  header("Content-Type: application/json");
  echo json_encode($res);

  exit;
});

$app->get("/progress/:id", function($id) use ($app) {
  // 子プロセスが保存したJSONを指定
  $log_location = "./log/${id}.json";

  if (file_exists($log_location)) {
    // JSONを読み込み
    $json = file_get_contents($log_location);

    // JSONにエンコードしてレスポンスを返す
    // Content-Type の設定を忘れずに
    header("Content-Type: application/json");
    echo $json;

    exit;
  } else {
    // JSONが見つからない場合は適当に404を返す
    header("HTTP/ 404 Not Found");

    exit;
  }
});

$app->run();
