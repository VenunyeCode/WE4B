<?php

class ImgFileUploader extends DBConnection
{

    private $savePath = "uploads/";
    //private $savePath = BASE_APP . "uploads/";
    public $hasAdequateFile = false;
    public $errorText = "";

    private $settings;
    public function __construct()
    {
        global $_settings;
        $this->settings = $_settings;
        //$this->savePath = BASE_APP . "uploads/";
        $this->hasAdequateFile = $this->IsBufferFileAdequate();
        parent::__construct();
    }
    public function __destruct()
    {
        parent::__destruct();
    }

    // Method to identify if an adequate image file is buffered in $_FILES
    //----------------------------------------------------------
    function IsBufferFileAdequate()
    {
        if ($_FILES['img']['size'] != 0) {
            if ($_FILES['img']['size'] > 5242880) {
                $this->errorText = "Fichier trop grand! Respectez la limite de 5Mo.";
                return false;
            } elseif ($_FILES['img']['type'] == "image/jpeg" || $_FILES['img']['type'] == "image/png") {
                return true;
            } else {
                $this->errorText = "Type de fichier non accepté! Images JPG et PNG seulement.";
                return false;
            }
        } else {
            $this->errorText = "No file or file size = 0";
            return false;
        }
    }

    // Attempt to save the buffered file in the server. If successful, updates post to add image
    //----------------------------------------------------------
    function SaveFileAsNew($postID)
    {

        //Cette fonction ne fait quelque chose que si hasAdequateFile = true
        if ($this->hasAdequateFile) {

            $file = $_FILES['img']['name'];
            $path = pathinfo($file); //permet d'analyser le fichier et d'obtenir des choses comme son extension
            $ext = $path['extension'];

            //Get the temp name of the file and build the destination path
            $temp_name = $_FILES['img']['tmp_name'];
            $new_filename = $this->settings->userdata('id_user') . "_" . date("mdyHis");
            $path_filename_ext = $this->savePath . $new_filename . "." . $ext;

            // Check if file already exists
            if (file_exists($path_filename_ext)) {
                $this->errorText = "Error, somehow the file already exists";
            } else {

                //If we reach here, upload is successful. so we update the post matching the ID
                $this->UpdateImageInPost($postID, $path_filename_ext);

                //Generate thumbnail version of image
                $this->Generate_Thumbnail($new_filename, $ext);

                //On sort le fichier du dossier temporaire pour le mettre dans son stockage permanent
                move_uploaded_file($temp_name, BASE_APP . $path_filename_ext);

                $this->errorText = "Congratulations! File Uploaded Successfully.";
            }
            //echo $this->errorText;
        }
        //Si une sauvegarde a étée demandée sans fichier adéquat, on considère que ce post
        //n'a alors plus d'image associée. Il faut alors mettre à jour la BDD
        else {
            $this->UpdateImageInPost($postID, "");
        }
    }

    // Constructor for the class. Needs reference to database connection
    //----------------------------------------------------------
    function OverrideOldFile($postID)
    {

        //The difference with saving a new file : we have to delete the old
        //Get path of old through query
        $this->DeleteFile($postID);

        //After that, save new file as if it were new
        $this->SaveFileAsNew($postID);
    }

    // Fonction pour effcaer le fichier lié à un post donné
    //----------------------------------------------------------
    function DeleteFile($postID)
    {

        //Requête pour trouver "image_url" du post
        $query = "SELECT `media_post` FROM `post` WHERE `id_post`= $postID";
        $result = $this->conn->query($query);

        //Si on a une réponse, on vérifie si le fichier existe. Si oui, efface
        while ($row = $result->fetch_assoc()) {
            if (file_exists($row["media_post"])) {
                unlink($row["media_post"]);
            }

            //Construire chemin du thumbnail
            //La fonction pathinfo peut découper un chemin de fichier en "morceaux"
            $pathFragments = pathinfo($row["media_post"]);
            $thumbpath = $pathFragments['dirname'] . "/" . $pathFragments['filename'];
            $thumbpath .= "_thumb.png";

            //Si un fichier correspond bien à ce chemin, efface
            if (file_exists($thumbpath)) {
                unlink($thumbpath);
            }

        }

    }

    // Méthode pour mettre à jour l'URL d'image dans la BDD, via SQLconn
    //----------------------------------------------------------
    function UpdateImageInPost($postID, $path_filename_ext)
    {
        $query = "UPDATE `post` SET `media_post`='$path_filename_ext' WHERE `id_post`= $postID";
        $this->conn->query($query);
    }

    // Méthode pour générer une version thumbnail d'un fichier image 
    //----------------------------------------------------------
    function Generate_Thumbnail($image_name)
    {

        $file_name = $_FILES['img']['tmp_name'];

        //On récupère les informations de getimagesize dans "nos varibales à nous"
        //avec cette astuce : on attribue le retour de la fonction(tableau) à une liste avec nos variables
        list($width, $height, $type, $attr) = getimagesize($file_name);

        //On construit le nom du fichier de destination
        $target_filename = $image_name . "_thumb.png";

        //On fait des maths pour calculer la hauteur de la nouvelle image.
        //La nouvelle image conserve le ration mais a une largeur "goalwidth"
        $goalWidth = 200;
        $ratio = $goalWidth / $width; //on calcule le redimentionnement
        $newHeight = $height * $ratio;

        //Pour redimentionner l'image, il nous faut un objet image PHP.
        //On en crée donc un à partir du fichier
        $src = imagecreatefromstring(file_get_contents($file_name));

        //On crée un objet image vide aux bonnes dimensions
        $dst = imagecreatetruecolor(intval($goalWidth), intval($newHeight));

        //Fonction de redimentionnement. Indique la destination (dst) la source (src), les marges,
        //puis les tailles d'arrivée et d'origine
        imagecopyresampled($dst, $src, 0, 0, 0, 0, intval($goalWidth), intval($newHeight), intval($width), intval($height));

        //Plus besoin de l'objet image src. On le purge de la mémoire
        imagedestroy($src);

        //Enregistrement du fichier de thimbnail en png
        imagepng($dst, BASE_APP . $this->savePath . $target_filename);

        //Plus besoin de l'objet image dst. On le purge
        imagedestroy($dst);
    }
}

?>