<?php

// 投票選択肢
$alcohols = ['🥃', '🍷', '🍸', '🍺'];

// 送信有無を判定する関数
$is_posted = fn (array $post): bool => count($post) > 0;

// バリデーションする関数
$is_validated = fn (array $post, array $dataArray): bool =>
isset($post['type'])
  && in_array($post['type'], $dataArray, true)
  && $post['post_date'] !== "";

// ファイル書き込みする関数
function write_data_to_file(string $file_name, array $data): bool
{
  $file = fopen($file_name, 'a');
  flock($file, LOCK_EX);
  fwrite($file, "{$data['post_date']} {$data['type']}\n");
  flock($file, LOCK_UN);
  return fclose($file);
}

// ファイルあれば中身取得して配列に入れる関数
$get_raw_data = fn (string $file_path): array =>
file_exists($file_path)
  ? file(__DIR__ . '/' . $file_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)
  : [];

// 生データをかっこいい配列にする関数
$generate_fantastic_array = fn (array $raw_data): array =>
array_map(
  fn ($x) =>
  [
    'post_date' => explode(' ', $x)[0],
    'type' => str_replace("\n", '', explode(' ', $x)[1]),
  ],
  $raw_data
);

// 配列中のtypeで集計する関数
$get_type_count = fn (string $type, array $array): int => count(array_filter($array, fn ($x) => $x['type'] === $type));
$get_type_percent = fn (string $type, array $array): float => (count(array_filter($array, fn ($x) => $x['type'] === $type)) * 100 / (count($array) !== 0 ? count($array) : 1));

// 集計した配列を作成する関数
$get_result = fn (array $type_array, array $data_array): array => array_map(
  fn ($x) => [
    'type' => $x,
    'count' => $get_type_count($x, $data_array),
    'percent' => $get_type_percent($x, $data_array)
  ],
  $type_array
);

// データ送信時にデータ追加
if ($is_posted($_POST) && $is_validated($_POST, $alcohols)) {
  write_data_to_file('data/data.txt', $_POST);
  header('Location:index.php');
  exit();
}

// データ読み込み時にデータ取得して集計
$result_data = $get_result($alcohols, $generate_fantastic_array($get_raw_data('data/data.txt')));

?>

<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Alcohol投票所</title>
</head>

<body>
  <form action="" method="post">
    <fieldset>
      <legend>投票一体型アンケートシステム</legend>
      <div id="select"></div>
      <div>
        <label for="📆">
          📆: <input type="date" name="post_date" id="📆">
        </label>
      </div>
      <div>
        <button>submit</button>
      </div>
    </fieldset>
  </form>

  <fieldset>
    <legend>結果（%）</legend>
    <div id="result"></div>
  </fieldset>

  <script>
    const selectTags = <?= json_encode($alcohols) ?>.map(x => `<div><label for="${x}">${x}: <input type="radio" name="type" id="${x}" value="${x}"></label></div>`).join('');
    const tagArray = <?= json_encode($result_data) ?>.map(x => `<p>${[...new Array(x.percent|0)].fill(x.type).join('')} ${ !isNaN(x.percent) ? (Math.round(x.percent * 100) / 100) : 0 } (${ !isNaN(x.count) ? (Math.round(x.count * 100) / 100) : 0 })</p>`);
    document.getElementById('select').innerHTML = selectTags;
    document.getElementById('result').innerHTML = tagArray.join('');
  </script>
</body>

</html>