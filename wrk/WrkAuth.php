<?php

namespace Wrk;

use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use HTTP\HTTPResponses;

class WrkAuth {

    private const REGEX_LOGIN_USERNAME = "/^[a-zA-Z0-9._-]{1,32}$/";
    private const REGEX_LOGIN_PASSWORD = "/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,20}$/";

    private WrkDatabase $wrkDB;

    public function __construct() {
        $this->wrkDB = new WrkDatabase();
    }

    public function login(array $requestBody): void {
        if ( !isset($requestBody['username']) || !isset($requestBody['password']) ) {
            HTTPResponses::error(400, "Le nom d'utilisateur et le mot de passe doivent être spécifiés");
        }
        $username = $requestBody['username'];
        $password = $requestBody['password'];
        $validations = [
            'username' => [self::REGEX_LOGIN_USERNAME, "Le nom d'utilisateur ne respecte pas le bon format"],
            'password' => [self::REGEX_LOGIN_PASSWORD, "Le mot de passe doit contenir au moins une lettre minuscule, une lettre majuscule, et avoir une longueur comprise entre 8 et 20 caractères"]
        ];
        foreach ( $validations as $field => $validation ) {
            if ( !preg_match($validation[0], $requestBody[$field]) ) {
                HTTPResponses::error(400, $validation[1]);
            }
        }
        $existingAdmin = $this->checkAdminExistence($username);
        if ( !$existingAdmin || !password_verify($password, $existingAdmin['password']) ) {
            HTTPResponses::error(403, "Les identifiants d'authentification sont invalides");
        }
        $payload = [
            'iss' => $_ENV["JWT_ISSUER"],
            'aud' => $_ENV["JWT_AUD"],
            'iat' => time(),
            'exp' => time() + $_ENV["JWT_EXPIRES_IN"],
            'data' => [
                'pk_admin' => $existingAdmin['pk_admin'],
                'username' => $existingAdmin['username'],
                'permission' => $existingAdmin['permission'],
            ]
        ];
        $token = JWT::encode($payload, $_ENV["JWT_SECRET"], $_ENV["JWT_ALG"]);
        $data = array('username' => $existingAdmin['username'], 'token' => $token, 'expiresAt' => $payload['exp']);
        HTTPResponses::success("Connexion réussie", $data);
    }

    private function checkAdminExistence(string $username): array|bool {
        return $this->wrkDB->select(GET_ADMIN_BY_USERNAME, [$username]);
    }

    public function authorize(int $requiredLevel): void {
        $headers = apache_request_headers();
        if ( isset($headers['Authorization']) ) {
            $authHeader = $headers['Authorization'];
            if ( preg_match('/Bearer\s(\S+)/', $authHeader, $matches) ) {
                $token = $matches[1];
                try {
                    $decoded = JWT::decode($token, new Key($_ENV["JWT_SECRET"], $_ENV["JWT_ALG"]));
                    $adminLevel = $decoded->data->permission ?? 0;
                    if ( $adminLevel < $requiredLevel ) {
                        HTTPResponses::error(403, "Accès refusé : droits insuffisants");
                    }
                } catch ( Exception $ex ) {
                    HTTPResponses::error(401, "Token invalide : " . $ex->getMessage());
                }
            } else {
                HTTPResponses::error(401, "Format de token invalide");
            }
        } else {
            HTTPResponses::error(401, "Token non fourni");
        }
    }

}
