<?php

if (!empty($_COOKIE['sid'])) {
    // Проверка id сессии в cookie
    session_id($_COOKIE['sid']);
}

session_start();
require_once 'classes/User.class.php';

?><!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>Тест | Авторизация</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./css/style.css">
  </head>

  <body>
<div id="border-fixed-top"> </div>
    <div class="container">
 <h2>Вход на сайт: </h2>
      <?php if (Auth\User::isAuthorized()): ?>
    
      <h1>Hello <?php echo $_SESSION["name"]; ?></h1>

      <form class="ajax" method="post" action="./ajax.php">
          <input type="hidden" name="act" value="logout">
          <div class="form-actions">
              <button class="btn btn-large btn-primary" type="submit">Выход</button>
          </div>
      </form>

      <?php else :
echo "  
	 <p class=\"sistem-message\">Пожалуйста авторизуйтесь или зарегестрируйтесь</p> ";

?>

      <form class="form-signin ajax" method="post" action="./ajax.php">
        <div class="main-error alert alert-error hide" style="color:red;"></div>

        <table>
<tr>
  <td>Логин:</td>
  <td>
    <input type="text" name="login" />
  </td>
</tr>
<tr>
  <td>Пароль:</td>
  <td>
    <input type="password" name="password" />
  </td>
</tr>
<tr>
  <td colspan="2" align="right">
  <input type="hidden" name="act" value="login">
    <button type="submit">Войти</button>
  </td>
</tr>
</table>
<div  style="margin-top:5px;">
  <a href="/register.php">Регистрация</a>
</div>
</form>
      <?php endif; ?>

    </div> <!-- /container -->

    <script src="./vendor/jquery-2.0.3.min.js"></script>
    <script src="./vendor/bootstrap/js/bootstrap.min.js"></script>
    <script src="./js/ajax-form.js"></script>

  </body>
</html>
