<?php

include './classes/User.class.php';
include './classes/AjaxRequest.class.php';

if (!empty($_COOKIE['sid'])) {
    // Проверка id сессии в cookie
    session_id($_COOKIE['sid']);
}
session_start();

class AuthorizationAjaxRequest extends AjaxRequest
{
    public $actions = [
        "login" => "login",
        "logout" => "logout",
        "register" => "register",
    ];

    public function login()
    {
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            // Метод не разрешен
            http_response_code(405);
            header("Allow: POST");
            $this->setFieldError("main", "Метод не разрешен");
            return;
        }
        setcookie("sid", "");

        $login = $this->getRequestParam("login");
        $password = $this->getRequestParam("password");
        $remember = !!$this->getRequestParam("remember-me");

        if (empty($login)) {
            $this->setFieldError("login", "Введите имя пользователя");
            return;
        }

        if (empty($password)) {
            $this->setFieldError("password", "Введите пароль");
            return;
        }

        $user = new Auth\User();
        $auth_result = $user->authorize($login, $password, $remember);

        if (!$auth_result) {
            $this->setFieldError("password", "Неверное имя пользователя или пароль");
            return;
        }

        $this->status = "ok";
        $this->setResponse("redirect", "/");
        $this->message = sprintf("Привет, %s! Доступ предоставлен.", $login);
    }

    public function logout()
    {
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            // Метод не разрешен
            http_response_code(405);
            header("Allow: POST");
            $this->setFieldError("main", "Метод не разрешен");
            return;
        }

        setcookie("sid", "");

        $user = new Auth\User();
        $user->logout();

        $this->setResponse("redirect", "/");
        $this->status = "ok";
    }

    public function register()
    {
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            // Метод не разрешен
            http_response_code(405);
            header("Allow: POST");
            $this->setFieldError("main", "Метод не разрешен");
            return;
        }

        setcookie("sid", "");

        $login = $this->getRequestParam("login");
        $password1 = $this->getRequestParam("password1");
        $password2 = $this->getRequestParam("password2");
        $email = $this->getRequestParam("email");
        $username = $this->getRequestParam("username");

        if (empty($login)) {
            $this->setFieldError("login", "Введите логин");
            return;
        }

        if (empty($password1)) {
            $this->setFieldError("password1", "Введите пароль");
            return;
        }

        if (empty($password2)) {
            $this->setFieldError("password2", "Подтвердите пароль");
            return;
        }

        if ($password1 !== $password2) {
            $this->setFieldError("password2", "Введенные пароли не совпадают");
            return;
        }

        if (empty($email)) {
            $this->setFieldError("email", "Введите email");
            return;
        }if (empty($username)) {
            $this->setFieldError("username", "Введите имя пользователя");
            return;
        }

        $user = new Auth\User();

        try {
            $user->create($login, $password1, $email, $username);
        } catch (\Exception $e) {
            $this->setFieldError("login", $e->getMessage());
            return;
        }
        $user->authorize($login, $password1);

        $this->message = sprintf("Привет, %s! Спасибо за регистрацию.", $login);
        $this->setResponse("redirect", "/");
        $this->status = "ok";
    }
}

$ajaxRequest = new AuthorizationAjaxRequest($_REQUEST);
$ajaxRequest->showResponse();
