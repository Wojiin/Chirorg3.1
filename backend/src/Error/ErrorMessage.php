<?php

namespace App\Error;

/** Catalogue unique des messages d'erreur métier, API et validation du backend. */
final class ErrorMessage
{
    public const string ACCOUNT_DISABLED = 'Ce compte est désactivé.';
    public const string ADMIN_SELF_DELETE_FORBIDDEN = 'Un administrateur ne peut pas supprimer son propre compte.';
    public const string ADMIN_SELF_UPDATE_FORBIDDEN = 'Un administrateur ne peut pas désactiver son compte ni retirer son propre rôle administrateur.';
    public const string AUTHENTICATED_USER_REQUIRED = 'Utilisateur authentifié requis.';
    public const string AUTHENTICATED_USER_WITHOUT_EMAIL = 'An authenticated utilisateur must have an email address.';
    public const string CHIRURGIE_MODELE_ALREADY_EXISTS = 'Cette chirurgie modèle existe déjà pour cette spécialité.';
    public const string CHIRURGIE_PLANIFIEE_NOT_FOUND = 'Chirurgie planifiée introuvable.';
    public const string CHIRURGIEN_NOT_FOUND = 'Chirurgien introuvable.';
    public const string CHIRURGIEN_MUST_BE_PERSISTED = 'Le chirurgien doit être persisté avant de réserver un ordre.';
    public const string CREDENTIALS_REQUIRED = 'L’email et le mot de passe sont requis.';
    public const string CURRENT_PASSWORD_INCORRECT = 'Le mot de passe actuel est incorrect.';
    public const string DATE_FORMAT_INVALID = 'La date doit respecter le format YYYY-MM-DD.';
    public const string DATE_RANGE_INVALID = 'dateFin doit être postérieure ou égale à dateDebut.';
    public const string DATE_MUST_BE_TOMORROW_OR_LATER = 'La date doit être au minimum celle de demain.';
    public const string DEFAULT_SPECIALITE_MISSING = 'La spécialité « Sans spécialité » est absente.';
    public const string DEFAULT_SPECIALITE_IMMUTABLE = 'La spécialité « Sans spécialité » ne peut pas être modifiée.';
    public const string DEFAULT_SPECIALITE_PROTECTED = 'La spécialité « Sans spécialité » ne peut pas être supprimée.';
    public const string EMAIL_ALREADY_USED = 'Cette adresse email est déjà utilisée.';
    public const string EMAIL_INVALID = 'Cette adresse email n’est pas valide.';
    public const string FINAL_VIEW_REQUIRES_VALIDATION = 'La vue finale est disponible uniquement après validation.';
    public const string IMAGE_ORIGIN_INVALID = 'L’image doit provenir du service de téléversement ChirOrg.';
    public const string IMAGE_REQUIRED = 'Une image valide est obligatoire.';
    public const string IMAGE_STORAGE_FAILED = 'L’image n’a pas pu être enregistrée.';
    public const string IMAGE_TOO_LARGE = 'L’image ne peut pas dépasser 5 Mo.';
    public const string IMAGE_TYPE_INVALID = 'Seules les images JPEG, PNG et WebP sont acceptées.';
    public const string LISTE_MATERIEL_ALREADY_EXISTS = 'Une liste existe déjà pour ce chirurgien et cette chirurgie modèle.';
    public const string LISTE_MATERIEL_NOT_FOUND = 'Aucune liste de matériel ne correspond à ce chirurgien et à cette chirurgie modèle.';
    public const string LISTE_MATERIEL_REQUIRES_ITEM = 'La liste doit contenir au moins un matériel.';
    public const string LIST_REQUIRES_ITEM = 'Cette liste doit contenir au moins {{ limit }} élément.';
    public const string LIST_TOO_LONG = 'Cette liste ne doit pas contenir plus de {{ limit }} éléments.';
    public const string MATERIEL_SPECIALITE_MISMATCH = 'Tous les matériels doivent appartenir à la spécialité du chirurgien.';
    public const string MATERIEL_STATE_CONFLICT = 'Un matériel ne peut pas être à la fois prêt et absent.';
    public const string NEW_PASSWORD_MUST_DIFFER = 'Le nouveau mot de passe doit être différent du mot de passe actuel.';
    public const string NEW_PASSWORD_REQUIRES_DIGIT = 'Le nouveau mot de passe doit contenir un chiffre.';
    public const string NEW_PASSWORD_REQUIRES_LOWERCASE = 'Le nouveau mot de passe doit contenir une minuscule.';
    public const string NEW_PASSWORD_REQUIRES_SPECIAL_CHARACTER = 'Le nouveau mot de passe doit contenir un caractère spécial.';
    public const string NEW_PASSWORD_REQUIRES_UPPERCASE = 'Le nouveau mot de passe doit contenir une majuscule.';
    public const string PASSWORD_REQUIRES_DIGIT = 'Le mot de passe doit contenir un chiffre.';
    public const string PASSWORD_REQUIRES_LOWERCASE = 'Le mot de passe doit contenir une minuscule.';
    public const string PASSWORD_REQUIRES_SPECIAL_CHARACTER = 'Le mot de passe doit contenir un caractère spécial.';
    public const string PASSWORD_REQUIRES_UPPERCASE = 'Le mot de passe doit contenir une majuscule.';
    public const string PREPARATION_LOCKED = 'La préparation d’une chirurgie validée est verrouillée.';
    public const string PREPARATION_NOT_FOUND = 'Préparation de matériel introuvable.';
    public const string PREPARATION_STATE_REQUIRED = 'Un état prêt ou absent doit être fourni.';
    public const string POSITIVE_NUMBER_REQUIRED = 'Cette valeur doit être strictement positive.';
    public const string POSITIVE_OR_ZERO_REQUIRED = 'Cette valeur doit être positive ou nulle.';
    public const string PROGRAMME_CREATED_UNREADABLE = 'Le programme créé ne peut pas être relu.';
    public const string PROGRAMME_INVALID = 'Un programme opératoire valide est attendu.';
    public const string PROGRAMME_NOT_FOUND = 'Programme opératoire introuvable.';
    public const string PROGRAMME_REFERENCE_REQUIRED = 'La salle et un identifiant de chirurgien positif sont requis.';
    public const string PROGRAMME_REORDERED_UNREADABLE = 'Le programme réordonné ne peut pas être relu.';
    public const string PROGRAMME_REORDER_INCOMPLETE = 'La permutation doit contenir exactement toutes les chirurgies du programme.';
    public const string PROGRAMME_REORDER_LOCKED = 'Un programme contenant une chirurgie validée ne peut plus être réordonné.';
    public const string REFERENCE_IN_USE = 'Cette ressource ne peut pas être supprimée car elle est utilisée.';
    public const string REQUIRED_FIELD = 'Ce champ est obligatoire.';
    public const string ROLE_INVALID = 'Ce rôle n’est pas autorisé.';
    public const string SALLE_ALREADY_EXISTS = 'Cette salle existe déjà.';
    public const string SALLE_EXPECTED = 'Une salle est attendue.';
    public const string SALLE_IN_USE = 'Cette salle ne peut pas être supprimée car elle est utilisée par un programme.';
    public const string SALLE_NOT_FOUND = 'Salle introuvable.';
    public const string SPECIALITE_ALREADY_EXISTS = 'Cette spécialité existe déjà.';
    public const string SPECIALITE_EXPECTED = 'Une spécialité est attendue.';
    public const string TECHNICAL_SHEET_CONTENT_REQUIRED = 'Une fiche technique doit contenir une description, une image ou les deux.';
    public const string TEXT_TOO_LONG = 'Ce champ ne doit pas dépasser {{ limit }} caractères.';
    public const string TEXT_TOO_SHORT = 'Ce champ doit contenir au moins {{ limit }} caractères.';
    public const string INTEGER_REQUIRED = 'Cette valeur doit être un entier.';
    public const string VALIDATED_CHIRURGIE_INITIALIZATION_FORBIDDEN = 'Le matériel d’une chirurgie validée ne peut plus être initialisé.';
    public const string VALIDATION_REQUIRES_ALL_MATERIALS = 'Tout le matériel doit être déclaré prêt ou absent avant la validation.';
    public const string VALIDATION_REQUIRES_PREPARATION = 'La chirurgie ne possède aucune préparation de matériel.';
    public const string CHIRURGIEN_AND_MODELE_REQUIRED = 'Le chirurgien et la chirurgie modèle sont requis.';
    public const string CHIRURGIE_MODELE_SPECIALITE_MISMATCH = 'La chirurgie modèle doit appartenir à la spécialité du chirurgien.';
    public const string UTILISATEUR_NOT_FOUND = 'Utilisateur introuvable.';

    private const string CHIRURGIE_MODELE_NOT_FOUND_PATTERN = 'Chirurgie modèle %d introuvable.';
    private const string UNSUPPORTED_USER_PATTERN = 'Instances of "%s" are not supported.';

    private function __construct()
    {
    }

    public static function chirurgieModeleNotFound(int $id): string
    {
        return sprintf(self::CHIRURGIE_MODELE_NOT_FOUND_PATTERN, $id);
    }

    public static function unsupportedUser(string $class): string
    {
        return sprintf(self::UNSUPPORTED_USER_PATTERN, $class);
    }
}
