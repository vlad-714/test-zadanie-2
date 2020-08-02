<?php

namespace Auth;

class User
{
    private $id;
    private $login;
    private $db;
    private $file = 'db.xml';

    public function __construct($login = null, $password = null)
    {
        $this->login = $login;
        $this->connectDb();
    }

    public function __destruct()
    {
        $this->db = null;
    }

    public static function isAuthorized()
    {
        return !empty($_SESSION["user_id"]);
    }


    public function passwordHash($password, $salt = null, $iterations = 10)
    {
        $salt || $salt = uniqid();
        $hash = md5(md5($password . md5(sha1($salt))));

        for ($i = 0; $i < $iterations; ++$i) {
            $hash = md5(md5(sha1($hash)));
        }

        return ['hash' => $hash, 'salt' => $salt];
    }

    public function getLogin($login)
    {
        foreach ($this->db->example_table->row as $user) {
            if ($user->login == $login) {
                return reset($user->salt);
            }
        }

        return false;
    }

    public function getEmail($email)
    {
        foreach ($this->db->example_table->row as $user) {
            if ($user->email == $email) {
                return reset($user->email);
            }
        }

        return false;
    }

    public function authorize($login, $password, $remember = false)
    {
        $salt = $this->getLogin($login);
        foreach ($this->db->example_table->row as $user) {
            if ($user->login == $login && $user->salt == $salt) {
                $hashData = $this->passwordHash($password, $salt);
                if ($user->password == $hashData['hash']) {
                    $this->saveSession(reset($user->id), reset($user->login), $remember);
                    return true;
                }
            }
        }

        return false;
    }

    public function logout()
    {
        if (!empty($_SESSION["user_id"])) {
            unset($_SESSION["user_id"]);
        }
    }

    public function saveSession($user_id, $login, $remember = false, $http_only = true, $days = 7)
    {
        $_SESSION["user_id"] = $user_id;
        $_SESSION["name"] = $login;

        if ($remember) {
            // Сохранение сессии в cookie
            $sid = session_id();

            $expire = time() + $days * 24 * 3600;
            $domain = ""; // локальный домен
            $secure = false;
            $path = "/";

            setcookie("sid", $sid, $expire, $path, $domain, $secure, $http_only);
        }
    }

    public function create($login, $password, $email, $username)
    {
        $checkLogin = $this->getLogin($login);
        $checkEmail = $this->getEmail($email);

        if ($checkLogin) {
            throw new \Exception("Пользователь существует: " . $login, 1);
        }

        if ($checkEmail) {
            throw new \Exception("Email уже существует " . $email, 1);
        }

        $hasheData = $this->passwordHash($password);
        $row = $this->db->example_table->addChild("row");
        $row->addChild("id", $this->db->example_table->row->count() + 1);
        $row->addChild("login", $login);
        $row->addChild("email", $email);
        $row->addChild("username", $username);
        $row->addChild("password", $hasheData['hash']);
        $row->addChild("salt", $hasheData['salt']);
        $this->db->saveXML($this->file);

        return true;
    }

    public function connectDb()
    {
        if (file_exists($this->file)) {
            $this->db = \simplexml_load_file($this->file);
        } else {
            echo "Файл $this->file не существует!";
            die();
        }
    }
}
