<?php

namespace Wrk;

use HTTP\HTTPResponses;

/**
 * Class WrkAdmins
 * @package Wrk
 * @author Noé Henchoz
 * @date 2024-12
 */
class WrkAdmins {

    private const REGEX_ADMINS_USERNAME = "/^[a-zA-Z0-9._-]{1,32}$/";
    private const REGEX_ADMINS_PASSWORD = "/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,20}$/";
    private const REGEX_ADMINS_PK_ADMIN_TYPE = "/^[1-2]$/";
    private const REGEX_ADMINS_PK_ADMIN = "/^\d+$/";

    private WrkDatabase $wrkDB;

    public function __construct() {
        $this->wrkDB = WrkDatabase::getInstance();
    }

    /**
     * Read all admins
     * @return void nothing is returned
     */
    public function read(): void {
        // Get all admins from the database and send them as a response
        $admins = $this->wrkDB->select(GET_ADMINS, [], true);
        HTTPResponses::success("Liste des administrateurs récupérée", $admins);
    }

    /**
     * Get an admin
     * @param array $requestParams The request parameters
     * @return void nothing is returned
     */
    public function getAdmin(array $requestParams): void {
        // Check if the required field is set
        if ( !isset($requestParams['pk_admin']) ) {
            HTTPResponses::error(400, "L'identifiant de l'administrateur doit être spécifié");
        }
        // Validate the field
        $pkAdmin = $requestParams['pk_admin'];
        $validations = [
            'pk_admin' => [self::REGEX_ADMINS_PK_ADMIN, "L'identifiant de l'administrateur doit être un nombre entier positif"]
        ];
        foreach ( $validations as $field => $validation ) {
            if ( !preg_match($validation[0], $requestParams[$field]) ) {
                HTTPResponses::error(400, $validation[1]);
            }
        }
        // Get the admin by its id and send it as a response or send an error if the admin does not exist
        $admin = $this->getAdminById($pkAdmin);
        if ( $admin ) {
            HTTPResponses::success("Administrateur récupéré avec succès", $admin);
        } else {
            HTTPResponses::error(404, "Aucun administrateur avec cet identifiant n'a été trouvé");
        }
    }

    /**
     * Get all admin types
     * @return void nothing is returned
     */
    public function getAdminTypes(): void {
        $adminTypes = $this->wrkDB->select(GET_ADMIN_TYPES, [], true);
        HTTPResponses::success("Liste des types d'administrateurs récupérée", $adminTypes);
    }

    /**
     * Create a new admin
     * @param array $requestBody The request body
     * @return void nothing is returned
     */
    public function create(array $requestBody): void {
        // Check if the required fields are set
        if ( !isset($requestBody['username']) || !isset($requestBody['password']) || !isset($requestBody['pk_admin_type']) ) {
            HTTPResponses::error(400, "Le nom d'utilisateur, le mot de passe et le type d'administrateur doivent être spécifiés");
        }
        // Validate the fields
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
        // Check if the admin already exists
        if ( $this->getAdminByUsername($username) ) {
            HTTPResponses::error(409, "Un administrateur avec ce nom d'utilisateur existe déjà");
        }
        // Check if the admin type exists
        if ( !$this->getAdminTypeById($pkAdminType) ) {
            HTTPResponses::error(404, "Ce type d'administrateur n'existe pas");
        }
        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        // Insert the admin into the database
        $this->wrkDB->execute(INSERT_ADMIN, [$username, $hashedPassword, $pkAdminType]);
        // Get the added admin and send it as a response
        $addedAdmin = $this->getAdminById($this->wrkDB->lastInsertId());
        HTTPResponses::success("Administrateur créé avec succès", $addedAdmin);
    }

    /**
     * Update an admin
     * @param array $requestBody The request body
     * @return void nothing is returned
     */
    public function update(array $requestBody): void {
        // Check if the required field is set
        if ( !isset($requestBody['pk_admin']) ) {
            HTTPResponses::error(400, "L'identifiant de l'administrateur doit être spécifié pour la mise à jour");
        }
        $pkAdmin = $requestBody['pk_admin'];
        // Check if the admin with id 1 is being updated
        if ( $pkAdmin == 1 ) {
            HTTPResponses::error(403, "L'administrateur avec l'identifiant 1 ne peut pas être modifié");
        }
        // Check if the admin exists
        $existingAdmin = $this->getAdminById($pkAdmin);
        if ( !$existingAdmin ) {
            HTTPResponses::error(404, "Aucun administrateur avec cet identifiant n'a été trouvé");
        }
        // Validate the fields
        $fields = [
            'username' => [self::REGEX_ADMINS_USERNAME, "Le nom d'utilisateur ne respecte pas le bon format"],
            'fk_admin_type' => [self::REGEX_ADMINS_PK_ADMIN_TYPE, "Le type d'administrateur doit être soit 1 (Admin) soit 2 (SuperAdmin)"],
            'password' => [self::REGEX_ADMINS_PASSWORD, "Le mot de passe doit contenir au moins une lettre minuscule, une lettre majuscule, et avoir une longueur comprise entre 8 et 20 caractères"],
        ];
        // Prepare the fields to be updated
        $updates = [];
        $params = [];
        foreach ( $fields as $field => $validation ) {
            if ( isset($requestBody[$field]) ) {
                if ( !preg_match($validation[0], $requestBody[$field]) ) {
                    HTTPResponses::error(400, $validation[1]);
                }
                switch ( $field ) {
                    case 'password':
                        // Hash the password
                        $updates[] = "$field = ?";
                        $params[] = password_hash($requestBody[$field], PASSWORD_DEFAULT);
                        break;

                    case 'username':
                        // Check if the username is already taken
                        if ( $this->getAdminByUsername($requestBody[$field]) && $requestBody[$field] !== $existingAdmin['username'] ) {
                            HTTPResponses::error(409, "Un administrateur avec ce nom d'utilisateur existe déjà");
                        }
                        $updates[] = "$field = ?";
                        $params[] = $requestBody[$field];
                        break;

                    case 'fk_admin_type':
                        // Check if the admin type exists
                        if ( !$this->getAdminTypeById(intval($requestBody[$field])) ) {
                            HTTPResponses::error(404, "Aucun type d'administrateur avec cet identifiant n'a été trouvé");
                        }
                        $updates[] = "$field = ?";
                        $params[] = $requestBody[$field];
                        break;

                    default:
                        // Add the field to the updates
                        $updates[] = "$field = ?";
                        $params[] = $requestBody[$field];
                        break;
                }
            }
        }
        // Check if there are fields to update
        if ( empty($updates) ) {
            HTTPResponses::error(400, "Aucun champ valide fourni pour la mise à jour");
        }
        // Add the id of the admin to update
        $params[] = $pkAdmin;
        // Update the admin in the database
        $query = "UPDATE Admins SET " . implode(', ', $updates) . " WHERE pk_admin = ?";
        $this->wrkDB->execute($query, $params);
        // Get the updated admin and send it as a response
        $updatedAdmin = $this->getAdminById($pkAdmin);
        HTTPResponses::success("Administrateur mis à jour avec succès", $updatedAdmin);
    }

    /**
     * Delete an admin
     * @param array $requestParams The request parameters
     * @return void nothing is returned
     */
    public function delete(array $requestParams): void {
        // Check if the required field is set
        if ( !isset($requestParams['pk_admin']) ) {
            HTTPResponses::error(400, "L'identifiant de l'administrateur doit être spécifié pour la suppression");
        }
        $pkAdmin = $requestParams['pk_admin'];
        // Check if the admin with id 1 is being deleted
        if ( $pkAdmin == 1 ) {
            HTTPResponses::error(403, "L'administrateur avec l'identifiant 1 ne peut pas être supprimé");
        }
        // Validate the field
        $validations = [
            'pk_admin' => [self::REGEX_ADMINS_PK_ADMIN, "L'identifiant de l'administrateur doit être un nombre entier positif"]
        ];
        foreach ( $validations as $field => $validation ) {
            if ( !preg_match($validation[0], $requestParams[$field]) ) {
                HTTPResponses::error(400, $validation[1]);
            }
        }
        // Check if the admin exists
        $existingAdmin = $this->getAdminById($pkAdmin);
        if ( $existingAdmin ) {
            // Delete the admin from the database and send the deleted admin as a response
            $this->wrkDB->execute(DELETE_ADMIN, [$pkAdmin]);
            HTTPResponses::success("Administrateur supprimé avec succès", $existingAdmin);
        } else {
            // Send an error if the admin does not exist
            HTTPResponses::error(404, "Aucun administrateur avec cet identifiant n'a été trouvé");
        }
    }

    /**
     * Get an admin by its id
     * @param int $pkAdmin The id of the admin
     * @return array|bool the admin if it exists, false otherwise
     */
    private function getAdminById(int $pkAdmin): array|bool {
        return $this->wrkDB->select(GET_ADMIN_BY_PK, [$pkAdmin]);
    }

    /**
     * Check if an admin exists by its username
     * @param string $username The username of the admin
     * @return array|bool the admin if it exists, false otherwise
     */
    private function getAdminByUsername(string $username): array|bool {
        return $this->wrkDB->select(GET_ADMIN_BY_USERNAME, [$username]);
    }

    /**
     * Check if an admin type exists by its id
     * @param int $pkAdminType The id of the admin type
     * @return array|bool the admin type if it exists, false otherwise
     */
    private function getAdminTypeById(int $pkAdminType): array|bool {
        return $this->wrkDB->select(GET_ADMIN_TYPE_BY_PK, [$pkAdminType]);
    }

}
