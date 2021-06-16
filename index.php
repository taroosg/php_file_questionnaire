<?php

if (count($_POST) > 0) {
  if (!isset($_POST['type']) || !isset($_POST['post_date']) || $_POST['post_date'] == "") {
    header('Location:index.php');
    exit();
  }
  $type = $_POST['type'];
  $post_date = $_POST['post_date'];
  $write_data = "{$post_date} {$type}\n";
  $file = fopen('data/data.txt', 'a');
  flock($file, LOCK_EX);
  fwrite($file, $write_data);
  flock($file, LOCK_UN);
  fclose($file);
  header('Location:index.php');
  exit();
}

$array = [];

if (file_exists('data/data.txt')) {
  $file = fopen('data/data.txt', 'r');
  flock($file, LOCK_EX);
  if ($file) {
    while ($line = fgets($file)) {
      array_push($array, $line);
    }
  }
  flock($file, LOCK_UN);
  fclose($file);
}

$fantastic_array = array_map(function ($x) {
  return [
    'post_date' => explode(' ', $x)[0],
    'type' => str_replace("\n", '', explode(' ', $x)[1]),
  ];
}, $array);

?>

<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>textファイル書き込み型todoリスト（入力画面）</title>
</head>

<body>
  <form action="" method="post">
    <fieldset>
      <legend>投票一体型アンケートシステム</legend>
      <div>
        <label for="🥃">
          🥃: <input type="radio" name="type" id="🥃" value="🥃">
        </label>
      </div>
      <div>
        <label for="🍷">
          🍷: <input type="radio" name="type" id="🍷" value="🍷">
        </label>
      </div>
      <div>
        <label for="🍸">
          🍸: <input type="radio" name="type" id="🍸" value="🍸">
        </label>
      </div>
      <div>
        <label for="🍺">
          🍺: <input type="radio" name="type" id="🍺" value="🍺">
        </label>
      </div>
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
    const fantasticArray = <?= json_encode($fantastic_array) ?>;

    const getTypeCountFromFantasticArray = (type) => fantasticArray.filter(x => x.type === type).length;
    const getTypePercentFromFantasticArray = (type) => fantasticArray.filter(x => x.type === type).length * 100 / fantasticArray.length;

    const percentStatus = [{
        type: '🥃',
        count: getTypeCountFromFantasticArray('🥃'),
        percent: getTypePercentFromFantasticArray('🥃'),
      },
      {
        type: '🍷',
        count: getTypeCountFromFantasticArray('🍷'),
        percent: getTypePercentFromFantasticArray('🍷'),
      },
      {
        type: '🍸',
        count: getTypeCountFromFantasticArray('🍸'),
        percent: getTypePercentFromFantasticArray('🍸'),
      },
      {
        type: '🍺',
        count: getTypeCountFromFantasticArray('🍺'),
        percent: getTypePercentFromFantasticArray('🍺'),
      },
    ];

    const tagArray = percentStatus.map(x => `<p>${[...new Array(x.percent|0)].fill(x.type).join('')} ${ !isNaN(x.percent)?(Math.round(x.percent * 100) / 100): 0 } (${ !isNaN(x.count)?(Math.round(x.count * 100) / 100): 0 })</p>`);

    document.getElementById('result').innerHTML = tagArray.join('');
  </script>
</body>

</html>