<?php

namespace app\models;

use app\models\enums\TipologiaLogin;

class User extends \yii\base\BaseObject implements \yii\web\IdentityInterface
{
    public $id;
    public $username;
    public $password;
    public $authKey;
    public $accessToken;

    public static $utentiDefault = [
        "adi" => "adi",
    ];


    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        return new static([
            "id" => $id,
            "username" => $id,
            "password" => $id,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        /*       foreach (self::$users as $user) {
                   if ($user['accessToken'] === $token) {
                       return new static($user);
                   }
               }*/

        return null;
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        /*        foreach (self::$users as $user) {
                    if (strcasecmp($user['username'], $username) === 0) {
                        return new static($user);
                    }
                }*/

        return null;
    }

    public static function findByUsernameAndPassword($username, $password, $tipo)
    {
        switch ($tipo) {
            case TipologiaLogin::DOMINIO:
                if (!str_contains($username, "@asp.messina.it"))
                    $username .= "@asp.messina.it";
                $ldap = ldap_connect("asp.messina.it");
                print_r($ldap);
                if ($ldap) {
                    ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);  // Imposta la versione del protocollo LDAP
                    ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);

                    $binding = @ldap_bind($ldap, $username, $password);  // sostituisci con le credenziali dell'utente
                    if (!$binding) {
                        return null;
                    }
                    ldap_unbind($ldap); // disconnetti dal server LDAP
                } else {
                    return null;
                }

                return new User([
                    "id" => $username,
                    "username" => str_replace("@asp.messina.it", "", $username),
                    "password" => $password
                ]) ;
            case TipologiaLogin::STATICO:
                if (array_key_exists($username, self::$utentiDefault) && self::$utentiDefault[$username] == $password)
                    return new User([
                        "id" => $username,
                        "username" => $username,
                        "password" => $password
                    ]) ;
                else
                    return null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
        return $this->authKey;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        return $this->authKey === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return $this->password === $password;
    }
}
