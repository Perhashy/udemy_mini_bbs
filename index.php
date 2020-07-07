<?php
session_start();
require('dbconnect.php');

if (isset($_SESSION['id']) && $_SESSION['time'] + 3600 > time()) {
  $_SESSION['time'] = time();

  $users = $db->prepare('SELECT * FROM users WHERE id=?');
  $users->execute(array($_SESSION['id']));
  $user = $users->fetch();
} else {
  header('Location: login.php');
  exit();
}

if (!empty($_POST)) {
  if ($_POST['message'] !== '') {
    $message = $db->prepare('INSERT INTO posts SET user_id=?, message=?, reply_message_id=?, created=NOW()');
    $message->execute(array(
      $user['id'],
      $_POST['message'],
      $_POST['reply_post_id']
    ));

    header('Location: index.php');
    exit();
  }
}

$posts = $db->query('SELECT u.name, u.image, p.* FROM users u, posts p WHERE u.id=p.user_id ORDER BY p.created DESC');

if (isset($_REQUEST['res'])) {
  $response = $db->prepare('SELECT u.name, u.image, p.* FROM users u, posts p WHERE u.id=p.user_id AND p.id=?');
  $response->execute(array($_REQUEST['res']));
  $table = $response->fetch();
  $message = '@' . $table['name'] . ' ' . $table['message'];
}
?>


<!DOCTYPE html>
<html lang="ja">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>ひとこと掲示板</title>

	<link rel="stylesheet" href="style.css" />
</head>

<body>
<div id="wrap">
  <div id="head">
    <h1>ひとこと掲示板</h1>
  </div>
  <div id="content">
  	<div style="text-align: right"><a href="logout.php">ログアウト</a></div>
    <form action="" method="post">
      <dl>
        <dt><?php print(htmlspecialchars($user['name'], ENT_QUOTES)); ?>さん、メッセージをどうぞ</dt>
        <dd>
          <textarea name="message" cols="50" rows="5"><?php print(htmlspecialchars($message, ENT_QUOTES)); ?></textarea>
          <input type="hidden" name="reply_post_id" value="<?php print(htmlspecialchars($_REQUEST['res'], ENT_QUOTES)); ?>" />
        </dd>
      </dl>
      <div>
        <p>
          <input type="submit" value="投稿する" />
        </p>
      </div>
    </form>


  <?php foreach ($posts as $post): ?>
    <div class="msg">
    <img src="user_image/<?php print(htmlspecialchars($post['image'], ENT_QUOTES))?>" width="48" height="48" alt="<?php print(htmlspecialchars($post['name'], ENT_QUOTES))?>" />
    <p><?php print(htmlspecialchars($post['message'], ENT_QUOTES))?><span class="name">（<?php print(htmlspecialchars($post['name'], ENT_QUOTES))?>）</span>[<a href="index.php?res=<?php print(htmlspecialchars($post['id'], ENT_QUOTES)); ?>">Re</a>]</p>
    <p class="day"><a href="view.php?id=<?php print(htmlspecialchars($post['id'], ENT_QUOTES)); ?>"><?php print(htmlspecialchars($post['created'], ENT_QUOTES))?> </a>
    <?php if ($post['reply_message_id'] > 0): ?>
      <a href="view.php?id=<?php print(htmlspecialchars($post['reply_message_id'], ENT_QUOTES)); ?>">
      返信元のメッセージ</a>
    <?php endif; ?>
[<a href="delete.php?id="
style="color: #F33;">削除</a>]
    </p>
    </div>
  <?php endforeach;?>

<ul class="paging">
<li><a href="index.php?page=">前のページへ</a></li>
<li><a href="index.php?page=">次のページへ</a></li>
</ul>
  </div>
</div>
</body>
</html>
