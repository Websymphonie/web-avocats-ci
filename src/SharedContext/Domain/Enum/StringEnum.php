<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Domain\Enum;

enum StringEnum: string
{
    case AUTHENTICATION_SUCCESS_TEXT = "Vous êtes ) présent connecté";
    case CREATE_ACCOUNT_SUCCESS_TEXT = "Votre compte a bien été crée";
    case CREATE_ACCOUNT_ERROR_TEXT = "Une erreur s'est produite lors de la création du compte";
    case GLOBAL_ERROR_TEXT = "Une erreur s'est produite, veuillez réessayer";
    case CGU_ERROR_TEXT = "Vous devez accepter les CGU pour continuer.";
    case AMOUNT_INVALID_ERROR_TEXT = "Le montant saisi est invalide";
    case EMAIL_EXIST_ERROR_TEXT = "L'adresse email %s est déjà utilisé.";
    case INVALID_CURRENT_PASSWORD_ERROR_TEXT = "Le mot de passe actuel n'est pas valide.";
    case NO_DATA_FOUND_TEXT = "Aucune donnée trouvé";
    case USER_NOT_FOUND_ERROR_TEXT = "Aucun utilisateur ne correspond à cet identifiant: %s";
    case INVOICE_NOT_FOUND_ERROR_TEXT = "Aucune facture ne correspond à cet identifiant: %s";
    case EXPIRED_TOKEN_ERROR_TEXT = "Ce token %s à expiré";
    case TOKEN_INVALID_ERROR_TEXT = "Ce token %s n'existe pas.";
    case STRING_NOT_FOUND_ERROR_TEXT = "Aucun utilisateur ne correspond à cette chaine: %s";
    case PASSWORD_NOT_IDENTICAL_ERROR_TEXT = "Les mots de passe renseignés ne sont pas identiques.";
    case ADD_ADDRESS_SUCCESS_TEXT = "Votre adresse a bien été ajoutée";
    case DELETE_ADDRESS_SUCCESS_TEXT = "Votre adresse a bien été supprimée";
    case EDIT_ADDRESS_SUCCESS_TEXT = "Votre adresse a bien été modifiée";
    case ADD_ADDRESS_ERROR_TEXT = "Erreur de l'ajout de l'adresse";
    case EDIT_ADDRESS_ERROR_TEXT = "Erreur de modification de l'adresse";
    case DELETE_ADDRESS_ERROR_TEXT = "Erreur de la suppression de l'adresse";
    case PASSWORD_CHANGE_SUCCESS_TEXT = "Votre mot de passe a bien été modifié";
    case USER_PROFILE_EDIT_SUCCESS_TEXT = "Votre profil a bien été modifié";
    case USER_EMAIL_EDIT_SUCCESS_TEXT = "Votre email a bien été modifié";
    case USER_PHONE_EDIT_SUCCESS_TEXT = "Votre téléphone a bien été modifié";
    case USER_PICTURE_EDIT_SUCCESS_TEXT = "Votre photo a bien été modifiée";
    case PASSWORD_RESET_SUCCESS_TEXT = "Votre mot de passe a bien été réinitialisé";
    case OTP_VERIFY_SUCCESS_TEXT = "Code OTP vérifié avec succès";
    case EMAIL_VERIFY_SUCCESS_TEXT = "Nous vous avons envoyé un code de réinitialisation à l'email indiqué ou dans vos spam.";
    case LOGIN_REQUIRED_TEXT = "Veuillez vous connecter svp!";

    public function with(int $id): string
    {
        return sprintf($this->value, (string)$id);
    }

    public function withString(string $value): string
    {
        return sprintf($this->value, $value);
    }
}
