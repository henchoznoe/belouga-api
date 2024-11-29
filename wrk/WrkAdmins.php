<?php

namespace Wrk;

use HTTP\HTTPResponses;

class WrkAdmins {

    private const REGEX_ADMINS_USERNAME = "/^[a-zA-Z0-9._-]{1,32}$/";
    private const REGEX_ADMINS_PASSWORD = "/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,20}$/";
    private const REGEX_ADMINS_PK_ADMIN_TYPE = "/^[1-2]$/";
    private const REGEX_ADMINS_PK_ADMIN = "/^\d+$/";

    private WrkDatabase $wrkDB;

    public function __construct() {
        $this->wrkDB = new WrkDatabase();
    }

    public function create(array $requestBody): void {
        if ( !isset($requestBody['username']) || !isset($requestBody['password']) || !isset($requestBody['pk_admin_type']) ) {
            HTTPResponses::error(400, "Le nom d'utilisateur, le mot de passe et le type d'administrateur doivent être spécifiés");
        }
        $username = $requestBody['username'];
        $password = $requestBody['password'];
        $pkAdminType = $requestBody['pk_admin_type'];
        $validations = [
            'username' => [self::REGEX_ADMINS_USERNAME, "Le nom d'utilisateur ne respecte pas le bon format"],
            'password' => [self::REGEX_ADMINS_PASSWORD, "Le mot de passe doit contenir au moins une lettre minuscule, une lettre majuscule, et avoir une longueur comprise entre 8 et 20 caractères"],
            'pk_admin_type' => [self::REGEX_ADMINS_PK_ADMIN_TYPE, "Le type d'administrateur doit être soit 1 (Admin) soit 2 (SuperAdmin)"]
        ];
        foreach ( $validations as $field => $validation ) {
            if ( !preg_match($validation[0], $requestBody[$field]) ) {
                HTTPResponses::error(400, $validation[1]);
            }
        }
        $existingAdmin = $this->checkAdminExistence($username);
        if ( $existingAdmin ) HTTPResponses::error(409, "Un administrateur avec ce nom d'utilisateur existe déjà");
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $this->wrkDB->execute(INSERT_ADMIN, [$username, $hashedPassword, $pkAdminType]);
        $addedAdmin = $this->getAdminById($this->wrkDB->lastInsertId());
        HTTPResponses::success("Administrateur créé avec succès", $addedAdmin);
    }

    public function read(): void {
        $admins = $this->wrkDB->select(GET_ADMINS, [], true);
        HTTPResponses::success("Liste des administrateurs récupérée", $admins);
    }

    public function update(array $requestBody): void {
        if ( !isset($requestBody['pk_admin']) || !isset($requestBody['username']) || !isset($requestBody['password']) || !isset($requestBody['pk_admin_type']) ) {
            HTTPResponses::error(400, "L'identifiant de l'administrateur, le nom d'utilisateur, le mot de passe et le type d'administrateur doivent être spécifiés");
        }
        $pkAdmin = $requestBody['pk_admin'];
        $username = $requestBody['username'];
        $password = $requestBody['password'];
        $pkAdminType = $requestBody['pk_admin_type'];
        $validations = [
            'pk_admin' => [self::REGEX_ADMINS_PK_ADMIN, "L'identifiant de l'administrateur doit être un nombre entier positif"],
            'username' => [self::REGEX_ADMINS_USERNAME, "Le nom d'utilisateur ne respecte pas le bon format"],
            'password' => [self::REGEX_ADMINS_PASSWORD, "Le mot de passe doit contenir au moins une lettre minuscule, une lettre majuscule, et avoir une longueur comprise entre 8 et 20 caractères"],
            'pk_admin_type' => [self::REGEX_ADMINS_PK_ADMIN_TYPE, "Le type d'administrateur doit être soit 1 (Admin) soit 2 (SuperAdmin)"]
        ];
        foreach ( $validations as $field => $validation ) {
            if ( !preg_match($validation[0], $requestBody[$field]) ) {
                HTTPResponses::error(400, $validation[1]);
            }
        }
        $existingAdmin = $this->getAdminById($pkAdmin);
        if ( !$existingAdmin ) HTTPResponses::error(404, "Aucun administrateur avec cet identifiant n'a été trouvé");
        $existingAdmin = $this->checkAdminExistence($username);
        if ( $existingAdmin && $existingAdmin['pk_admin'] !== $pkAdmin ) HTTPResponses::error(409, "Un administrateur avec ce nom d'utilisateur existe déjà");
        $existingAdminType = $this->checkAdminTypeExistence($pkAdminType);
        if ( !$existingAdminType ) HTTPResponses::error(404, "Aucun type d'administrateur avec cet identifiant n'a été trouvé");
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $this->wrkDB->execute(UPDATE_ADMIN, [$username, $hashedPassword, $pkAdminType, $pkAdmin]);
        $updatedAdmin = $this->getAdminById($pkAdmin);
        HTTPResponses::success("Administrateur mis à jour avec succès", $updatedAdmin);
    }

    public function delete(array $requestParams): void {
        if ( !isset($requestParams['pk_admin']) ) {
            HTTPResponses::error(400, "L'identifiant de l'administrateur doit être spécifié");
        }
        $pkAdmin = $requestParams['pk_admin'];
        $validations = [
            'pk_admin' => [self::REGEX_ADMINS_PK_ADMIN, "L'identifiant de l'administrateur doit être un nombre entier positif"]
        ];
        foreach ( $validations as $field => $validation ) {
            if ( !preg_match($validation[0], $requestParams[$field]) ) {
                HTTPResponses::error(400, $validation[1]);
            }
        }
        $existingAdmin = $this->getAdminById($pkAdmin);
        if ( !$existingAdmin ) HTTPResponses::error(404, "Aucun administrateur avec cet identifiant n'a été trouvé");
        if ( $existingAdmin['pk_admin'] == 1 ) HTTPResponses::error(403, "L'administrateur avec l'identifiant 1 ne peut pas être supprimé");
        $this->wrkDB->execute(DELETE_ADMIN, [$pkAdmin]);
        HTTPResponses::success("Administrateur supprimé avec succès", $existingAdmin);
    }

    private function checkAdminExistence(string $username): array|bool {
        return $this->wrkDB->select(GET_ADMIN_BY_USERNAME, [$username]);
    }

    private function checkAdminTypeExistence(int $pkAdminType): array|bool {
        return $this->wrkDB->select(GET_ADMIN_TYPE_BY_PK, [$pkAdminType]);
    }

    private function getAdminById(int $pkAdmin): array|bool {
        return $this->wrkDB->select(GET_ADMIN_BY_PK, [$pkAdmin]);
    }

    public function getAdminTypes(): void {
        $adminTypes = $this->wrkDB->select(GET_ADMIN_TYPES, [], true);
        HTTPResponses::success("Liste des types d'administrateurs récupérée", $adminTypes);
    }

}
