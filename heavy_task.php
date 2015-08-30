<?php

// 親プロセスからコマンドライン引数経由でリクエストのIDを受け取る
$id = $argv[1];

// 途中経過の保存先を設定
// 形式はJSONにします
$log_location = "./log/${id}.json";

// ログを貯めていく変数
$log = array();

// バックグラウンドで実行するタスクの個数
// 今回は100個の処理をすると仮定
$task_count = 100;

foreach(range(1, $task_count) as $i) {
  sleep(2); // ダミーの重い処理、完了までに2秒かかる。

  // $i 番目の処理が完了したので $log に記録
  array_push($log, "task ${i} done.");

  // 親プロセスに伝える途中経過を組み立て
  // 親プロセスのレスポンスと形を揃えてあげると
  // やりやすいんじゃないでしょうか
  $progress = array(
    "id"       => $id,
    // 処理中なので status は "Processing" で
    "status"   => "Processing",
    // 処理が何%完了したか
    "progress" => $i / $task_count,
    "log"      => $log,
    "time"     => time(),
  );

  // JSONにエンコードしてファイルとして保存
  file_put_contents($log_location, json_encode($progress));
}

// 全ての処理が完了したらその旨を報告
$progress = array(
  "id"       => $id,
  // 完了したら status を "Done" に
  "status"   => "Done",
  "progress" => 1,
  "log"      => $log,
  "time"     => time(),
);

// JSONにエンコードしてファイルとして保存
file_put_contents($log_location, json_encode($progress));

exit;
