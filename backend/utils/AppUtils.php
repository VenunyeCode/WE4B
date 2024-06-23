<?php

/**
 * Classe d'utilité : contenant méthodes utiles tout le long de l'application
 */
class AppUtils
{

    //Fonction pour sécuriser les données utilisateur de manière basique
    //--------------------------------------------------------------------------------
    static final function securizeStringForSQL($string)
    {
        $string = trim($string);
        $string = stripcslashes($string);
        $string = addslashes($string);
        $string = htmlspecialchars($string);
        return $string;
    }
}
