<?php
namespace Src\Helpers;
use Exception;
class ImageHandler {
    private $sourcePath;
    private $image;
    private $width;
    private $height;
    private $mime;
    private $hasTransparency = false;

    /**
     * Constructeur de la classe Image
     * 
     * @param string $sourcePath Chemin vers l'image source
     * @throws Exception Si le fichier n'existe pas ou n'est pas une image valide
     */
    public function __construct($sourcePath) {
        if (!file_exists($sourcePath)) {
            throw new Exception("Le fichier n'existe pas : $sourcePath");
        }

        $info = getimagesize($sourcePath);
        if ($info === false) {
            throw new Exception("Le fichier n'est pas une image valide : $sourcePath");
        }

        $this->sourcePath = $sourcePath;
        $this->mime = $info['mime'];
        $this->width = $info[0];
        $this->height = $info[1];
        $this->loadImage();
    }

    /**
     * Charge l'image en mémoire selon son type
     * 
     * @return bool Succès de l'opération
     */
    private function loadImage() {
        switch ($this->mime) {
            case 'image/jpeg':
                $this->image = imagecreatefromjpeg($this->sourcePath);
                break;
            case 'image/png':
                $this->image = imagecreatefrompng($this->sourcePath);
                $this->hasTransparency = $this->checkTransparency();
                $this->prepareForTransparency();
                break;
            case 'image/gif':
                $this->image = imagecreatefromgif($this->sourcePath);
                $this->hasTransparency = $this->checkTransparency();
                $this->image = $this->convertToTrueColor();
                break;
            case 'image/webp':
                $this->image = imagecreatefromwebp($this->sourcePath);
                $this->hasTransparency = $this->checkTransparency();
                $this->prepareForTransparency();
                break;
            default:
                throw new Exception("Format d'image non supporté : {$this->mime}");
        }

        if (!$this->image) {
            throw new Exception("Impossible de charger l'image : {$this->sourcePath}");
        }

        return true;
    }

    /**
     * Vérifie si l'image contient de la transparence
     * 
     * @return bool True si l'image contient de la transparence
     */
    private function checkTransparency() {
        if ($this->mime === 'image/jpeg') {
            return false;
        }
        
        // Pour PNG et GIF
        if (!imageistruecolor($this->image)) {
            // Pour les images avec palette
            $transparentIndex = imagecolortransparent($this->image);
            return $transparentIndex !== -1;
        } else {
            // Pour les images truecolor
            for ($x = 0; $x < $this->width; $x++) {
                for ($y = 0; $y < $this->height; $y++) {
                    $color = imagecolorat($this->image, $x, $y);
                    $alpha = ($color >> 24) & 0x7F;
                    if ($alpha > 0) {
                        return true;
                    }
                }
            }
        }
        
        return false;
    }

    /**
     * Prépare l'image pour la gestion de la transparence
     */
    private function prepareForTransparency() {
        if (!imageistruecolor($this->image)) {
            imagepalettetotruecolor($this->image);
        }
        imagealphablending($this->image, false);
        imagesavealpha($this->image, true);
    }

    /**
     * Convertit l'image en TrueColor tout en préservant la transparence
     * 
     * @return resource Image convertie en TrueColor
     */
    private function convertToTrueColor() {
        if (imageistruecolor($this->image)) {
            return $this->image;
        }
        
        $w = imagesx($this->image);
        $h = imagesy($this->image);
        $trueColor = imagecreatetruecolor($w, $h);
        
        imagealphablending($trueColor, false);
        imagesavealpha($trueColor, true);
        $transparent = imagecolorallocatealpha($trueColor, 0, 0, 0, 127);
        imagefill($trueColor, 0, 0, $transparent);
        
        // Gestion de la transparence d'une image GIF
        $transparentIndex = imagecolortransparent($this->image);
        if ($transparentIndex !== -1) {
            $transparentColor = imagecolorsforindex($this->image, $transparentIndex);
            $transparentTrueColor = imagecolorallocatealpha(
                $trueColor,
                $transparentColor['red'],
                $transparentColor['green'],
                $transparentColor['blue'],
                127
            );
            imagefill($trueColor, 0, 0, $transparentTrueColor);
        }
        
        imagecopy($trueColor, $this->image, 0, 0, 0, 0, $w, $h);
        
        
        return $trueColor;
    }

    /**
     * Convertit l'image en WebP
     * 
     * @param string $destination Chemin de destination pour l'image WebP
     * @param int $quality Qualité de la compression (0-100)
     * @return string|bool Chemin de l'image convertie ou false en cas d'échec
     */
    public function convertToWebP($destination, $quality = 100) {
        if (!$this->image) {
            return false;
        }

        // Assurer que la qualité est dans la plage acceptable
        $quality = max(0, min(100, $quality));
        
        // Convertit et enregistre en WebP
        $result = imagewebp($this->image, $destination, $quality);
        
        return $result ? $destination : false;
    }

    /**
     * Redimensionne l'image
     * 
     * @param int $newWidth Nouvelle largeur (0 pour calculer automatiquement)
     * @param int $newHeight Nouvelle hauteur (0 pour calculer automatiquement)
     * @param bool $maintainAspectRatio Maintenir le ratio d'aspect
     * @return bool Succès de l'opération
     */
    public function resize($newWidth = 0, $newHeight = 0, $maintainAspectRatio = true) {
        if (!$this->image) {
            return false;
        }

        // Si les deux dimensions sont nulles, on ne fait rien
        if ($newWidth == 0 && $newHeight == 0) {
            return true;
        }

        // Calcul des dimensions en gardant le ratio d'aspect
        if ($maintainAspectRatio) {
            if ($newWidth == 0) {
                $newWidth = $this->width * ($newHeight / $this->height);
            } elseif ($newHeight == 0) {
                $newHeight = $this->height * ($newWidth / $this->width);
            } else {
                $originalRatio = $this->width / $this->height;
                $newRatio = $newWidth / $newHeight;
                
                if ($originalRatio > $newRatio) {
                    // L'image est plus large que la cible
                    $newHeight = $newWidth / $originalRatio;
                } else {
                    // L'image est plus haute que la cible
                    $newWidth = $newHeight * $originalRatio;
                }
            }
        }

        $newWidth = (int)$newWidth;
        $newHeight = (int)$newHeight;

        // Création de la nouvelle image
        $newImage = imagecreatetruecolor($newWidth, $newHeight);
        
        // Gestion de la transparence
        if ($this->hasTransparency) {
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
            $transparent = imagecolorallocatealpha($newImage, 0, 0, 0, 127);
            imagefill($newImage, 0, 0, $transparent);
        }
        
        // Redimensionnement
        if (imagecopyresampled($newImage, $this->image, 0, 0, 0, 0, $newWidth, $newHeight, $this->width, $this->height)) {
            $this->image = $newImage;
            $this->width = $newWidth;
            $this->height = $newHeight;
            return true;
        }
        
        return false;
    }

    /**
     * Enregistre l'image
     * 
     * @param string $destination Chemin de destination
     * @param string $type Type de sortie (jpg, png, gif, webp)
     * @param int $quality Qualité pour JPEG et WebP (0-100)
     * @return bool Succès de l'opération
     */
    public function save($destination, $type = null, $quality = 80) {
        if (!$this->image) {
            return false;
        }

        // Déterminer le type si non spécifié
        if ($type === null) {
            $pathInfo = pathinfo($destination);
            $type = strtolower($pathInfo['extension']);
        } else {
            $type = strtolower($type);
        }

        // Assurer que la qualité est dans la plage acceptable
        $quality = max(0, min(100, $quality));

        switch ($type) {
            case 'jpg':
            case 'jpeg':
                return imagejpeg($this->image, $destination, $quality);
            case 'png':
                // Pour PNG, quality est sur une échelle de 0-9
                $pngQuality = (int)(9 - ($quality / 100 * 9));
                return imagepng($this->image, $destination, $pngQuality);
            case 'gif':
                return imagegif($this->image, $destination);
            case 'webp':
                return imagewebp($this->image, $destination, $quality);
            default:
                return false;
        }
    }

    /**
     * Recadre l'image
     * 
     * @param int $x Position X du point de départ
     * @param int $y Position Y du point de départ
     * @param int $cropWidth Largeur du recadrage
     * @param int $cropHeight Hauteur du recadrage
     * @return bool Succès de l'opération
     */
    public function crop($x, $y, $cropWidth, $cropHeight) {
        if (!$this->image) {
            return false;
        }

        // Vérifier que les coordonnées sont valides
        if ($x < 0 || $y < 0 || $cropWidth <= 0 || $cropHeight <= 0) {
            return false;
        }
        
        // Création de la nouvelle image
        $newImage = imagecreatetruecolor($cropWidth, $cropHeight);
        
        // Gestion de la transparence
        if ($this->hasTransparency) {
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
            $transparent = imagecolorallocatealpha($newImage, 0, 0, 0, 127);
            imagefill($newImage, 0, 0, $transparent);
        }
        
        // Recadrage
        if (imagecopy($newImage, $this->image, 0, 0, $x, $y, $cropWidth, $cropHeight)) {
            $this->image = $newImage;
            $this->width = $cropWidth;
            $this->height = $cropHeight;
            return true;
        }
        
        return false;
    }

    /**
     * Applique un filtre de flou à l'image
     * 
     * @param int $amount Niveau de flou (1-10)
     * @return bool Succès de l'opération
     */
    public function blur($amount = 1) {
        if (!$this->image) {
            return false;
        }
        
        $amount = max(1, min(10, $amount));
        
        for ($i = 0; $i < $amount; $i++) {
            imagefilter($this->image, IMG_FILTER_GAUSSIAN_BLUR);
        }
        
        return true;
    }

    /**
     * Ajuste la luminosité de l'image
     * 
     * @param int $level Niveau de luminosité (-255 à 255)
     * @return bool Succès de l'opération
     */
    public function brightness($level) {
        if (!$this->image) {
            return false;
        }
        
        $level = max(-255, min(255, $level));
        
        return imagefilter($this->image, IMG_FILTER_BRIGHTNESS, $level);
    }

    /**
     * Ajuste le contraste de l'image
     * 
     * @param int $level Niveau de contraste (-100 à 100)
     * @return bool Succès de l'opération
     */
    public function contrast($level) {
        if (!$this->image) {
            return false;
        }
        
        $level = max(-100, min(100, $level));
        
        return imagefilter($this->image, IMG_FILTER_CONTRAST, $level);
    }

    /**
     * Applique un filtre noir et blanc à l'image
     * 
     * @return bool Succès de l'opération
     */
    public function grayscale() {
        if (!$this->image) {
            return false;
        }
        
        return imagefilter($this->image, IMG_FILTER_GRAYSCALE);
    }

    /**
     * Ajoute un filigrane à l'image
     * 
     * @param string $watermarkPath Chemin vers l'image du filigrane
     * @param string $position Position (top-left, top-right, bottom-left, bottom-right, center)
     * @param int $opacity Opacité du filigrane (0-100)
     * @return bool Succès de l'opération
     */
    public function addWatermark($watermarkPath, $position = 'bottom-right', $opacity = 50) {
        if (!$this->image || !file_exists($watermarkPath)) {
            return false;
        }
        
        // Charger le filigrane
        $watermarkInfo = getimagesize($watermarkPath);
        $watermarkMime = $watermarkInfo['mime'];
        
        switch ($watermarkMime) {
            case 'image/jpeg':
                $watermark = imagecreatefromjpeg($watermarkPath);
                break;
            case 'image/png':
                $watermark = imagecreatefrompng($watermarkPath);
                break;
            case 'image/gif':
                $watermark = imagecreatefromgif($watermarkPath);
                break;
            case 'image/webp':
                $watermark = imagecreatefromwebp($watermarkPath);
                break;
            default:
                return false;
        }
        
        if (!$watermark) {
            return false;
        }

        // Préparer le filigrane pour la transparence
        if ($watermarkMime === 'image/png') {
            imagealphablending($watermark, false);
            imagesavealpha($watermark, true);
        }
        
        // Dimensions du filigrane
        $watermarkWidth = imagesx($watermark);
        $watermarkHeight = imagesy($watermark);
        
        // Déterminer la position
        switch ($position) {
            case 'top-left':
                $x = 10;
                $y = 10;
                break;
            case 'top-right':
                $x = $this->width - $watermarkWidth - 10;
                $y = 10;
                break;
            case 'bottom-left':
                $x = 10;
                $y = $this->height - $watermarkHeight - 10;
                break;
            case 'bottom-right':
                $x = $this->width - $watermarkWidth - 10;
                $y = $this->height - $watermarkHeight - 10;
                break;
            case 'center':
                $x = ($this->width - $watermarkWidth) / 2;
                $y = ($this->height - $watermarkHeight) / 2;
                break;
            default:
                $x = 10;
                $y = 10;
        }
        
        // Appliquer le filigrane avec l'opacité spécifiée
        if ($opacity < 100) {
            // Créer un nouveau filigrane avec opacité
            $opacity = max(0, min(100, $opacity));
            $this->imageCopyMergeAlpha($this->image, $watermark, $x, $y, 0, 0, $watermarkWidth, $watermarkHeight, $opacity);
        } else {
            // Appliquer le filigrane sans opacité
            imagecopy($this->image, $watermark, $x, $y, 0, 0, $watermarkWidth, $watermarkHeight);
        }
        
        
        return true;
    }
    
    /**
     * Copie une image avec prise en charge de l'alpha (pour les filigranes)
     * 
     * @param resource $dst_im Image de destination
     * @param resource $src_im Image source
     * @param int $dst_x Position X de destination
     * @param int $dst_y Position Y de destination
     * @param int $src_x Position X de source
     * @param int $src_y Position Y de source
     * @param int $src_w Largeur de la source
     * @param int $src_h Hauteur de la source
     * @param int $pct Pourcentage d'opacité
     * @return bool Succès de l'opération
     */
    private function imageCopyMergeAlpha($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct) {
        // Créer une copie de l'image source
        $cut = imagecreatetruecolor($src_w, $src_h);
        imagealphablending($cut, false);
        imagesavealpha($cut, true);
        imagecopy($cut, $dst_im, 0, 0, $dst_x, $dst_y, $src_w, $src_h);
        
        // Appliquer la source avec opacité
        imagecopy($cut, $src_im, 0, 0, $src_x, $src_y, $src_w, $src_h);
        imagecopymerge($dst_im, $cut, $dst_x, $dst_y, 0, 0, $src_w, $src_h, $pct);
        
        
        return true;
    }
    
    /**
     * Rotation de l'image
     * 
     * @param float $angle Angle de rotation en degrés
     * @param int $bgColor Couleur de l'arrière-plan (par défaut transparent)
     * @return bool Succès de l'opération
     */
    public function rotate($angle, $bgColor = null) {
        if (!$this->image) {
            return false;
        }
        
        // Couleur de l'arrière-plan par défaut
        if ($bgColor === null) {
            if ($this->hasTransparency) {
                $bgColor = imagecolorallocatealpha($this->image, 255, 255, 255, 127);
            } else {
                $bgColor = imagecolorallocate($this->image, 255, 255, 255);
            }
        }
        
        // Rotation de l'image
        $rotated = imagerotate($this->image, $angle, $bgColor);
        
        if (!$rotated) {
            return false;
        }
        
        // Gérer la transparence
        if ($this->hasTransparency) {
            imagealphablending($rotated, false);
            imagesavealpha($rotated, true);
        }
        
        
        $this->image = $rotated;
        $this->width = imagesx($rotated);
        $this->height = imagesy($rotated);
        
        return true;
    }
    
    /**
     * Retourne l'image horizontalement
     * 
     * @return bool Succès de l'opération
     */
    public function flipHorizontal() {
        if (!$this->image) {
            return false;
        }
        
        $flipped = imagecreatetruecolor($this->width, $this->height);
        
        // Gérer la transparence
        if ($this->hasTransparency) {
            imagealphablending($flipped, false);
            imagesavealpha($flipped, true);
            $transparent = imagecolorallocatealpha($flipped, 0, 0, 0, 127);
            imagefill($flipped, 0, 0, $transparent);
        }
        
        // Retourner l'image
        for ($x = 0; $x < $this->width; $x++) {
            imagecopy($flipped, $this->image, $this->width - $x - 1, 0, $x, 0, 1, $this->height);
        }
        
        
        $this->image = $flipped;
        
        return true;
    }
    
    /**
     * Retourne l'image verticalement
     * 
     * @return bool Succès de l'opération
     */
    public function flipVertical() {
        if (!$this->image) {
            return false;
        }
        
        $flipped = imagecreatetruecolor($this->width, $this->height);
        
        // Gérer la transparence
        if ($this->hasTransparency) {
            imagealphablending($flipped, false);
            imagesavealpha($flipped, true);
            $transparent = imagecolorallocatealpha($flipped, 0, 0, 0, 127);
            imagefill($flipped, 0, 0, $transparent);
        }
        
        // Retourner l'image
        for ($y = 0; $y < $this->height; $y++) {
            imagecopy($flipped, $this->image, 0, $this->height - $y - 1, 0, $y, $this->width, 1);
        }
        
        
        $this->image = $flipped;
        
        return true;
    }
    
    /**
     * Redimensionne l'image pour qu'elle tienne dans un cadre spécifié tout en conservant les proportions
     * 
     * @param int $maxWidth Largeur maximale
     * @param int $maxHeight Hauteur maximale
     * @return bool Succès de l'opération
     */
    public function fitInBox($maxWidth, $maxHeight) {
        if (!$this->image) {
            return false;
        }
        
        // Si l'image est déjà plus petite que le cadre, ne rien faire
        if ($this->width <= $maxWidth && $this->height <= $maxHeight) {
            return true;
        }
        
        // Calculer les dimensions proportionnelles
        $ratio = min($maxWidth / $this->width, $maxHeight / $this->height);
        $newWidth = (int)($this->width * $ratio);
        $newHeight = (int)($this->height * $ratio);
        
        // Redimensionner l'image
        return $this->resize($newWidth, $newHeight);
    }
    
    /**
     * Applique un filtre sépia à l'image
     * 
     * @return bool Succès de l'opération
     */
    public function sepia() {
        if (!$this->image) {
            return false;
        }
        
        // Convertir en niveaux de gris
        imagefilter($this->image, IMG_FILTER_GRAYSCALE);
        
        // Appliquer le filtre sépia
        return imagefilter($this->image, IMG_FILTER_COLORIZE, 100, 50, 0);
    }
    
    /**
     * Récupère les informations sur l'image
     * 
     * @return array Informations sur l'image
     */
    public function getInfo() {
        return [
            'width' => $this->width,
            'height' => $this->height,
            'mime' => $this->mime,
            'hasTransparency' => $this->hasTransparency,
            'aspectRatio' => $this->width / $this->height,
            'sourcePath' => $this->sourcePath
        ];
    }
    
    /**
     * Optimise l'image JPEG
     * 
     * @param int $quality Qualité de la compression (0-100)
     * @return bool Succès de l'opération
     */
    public function optimizeJpeg($quality = 85) {
        if (!$this->image || $this->mime !== 'image/jpeg') {
            return false;
        }
        
        // Créer un fichier temporaire
        $tempFile = tempnam(sys_get_temp_dir(), 'img');
        
        // Enregistrer l'image avec la qualité spécifiée
        if (!imagejpeg($this->image, $tempFile, $quality)) {
            unlink($tempFile);
            return false;
        }
        
        // Recharger l'image
        $newImage = imagecreatefromjpeg($tempFile);
        if (!$newImage) {
            unlink($tempFile);
            return false;
        }
        
        // Supprimer le fichier temporaire
        unlink($tempFile);
        
        // Remplacer l'image
        
        $this->image = $newImage;
        
        return true;
    }
    
    /**
     * Ajoute un texte à l'image
     * 
     * @param string $text Texte à ajouter
     * @param int $size Taille du texte (1-5)
     * @param string $position Position du texte (top-left, top-right, bottom-left, bottom-right, center)
     * @param string $color Couleur du texte (hex ou nom)
     * @return bool Succès de l'opération
     */
    public function addText($text, $size = 3, $position = 'bottom-right', $color = '#FFFFFF') {
        if (!$this->image) {
            return false;
        }
        
        // Convertir la couleur hex en RGB
        if (substr($color, 0, 1) === '#') {
            $hex = substr($color, 1);
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
        } else {
            // Couleur par défaut si format invalide
            $r = 255;
            $g = 255;
            $b = 255;
        }
        
        // Allouer la couleur
        $textColor = imagecolorallocate($this->image, $r, $g, $b);
        
        // Limiter la taille
        $size = max(1, min(5, $size));
        
        // Déterminer la position
        $margin = 10;
        switch ($position) {
            case 'top-left':
                $x = $margin;
                $y = $margin + ($size * 10);
                break;
            case 'top-right':
                $x = $this->width - $margin - (strlen($text) * $size * 6);
                $y = $margin + ($size * 10);
                break;
            case 'bottom-left':
                $x = $margin;
                $y = $this->height - $margin;
                break;
            case 'bottom-right':
                $x = $this->width - $margin - (strlen($text) * $size * 6);
                $y = $this->height - $margin;
                break;
            case 'center':
                $x = ($this->width - (strlen($text) * $size * 6)) / 2;
                $y = ($this->height + ($size * 10)) / 2;
                break;
            default:
                $x = $margin;
                $y = $this->height - $margin;
        }
        
        // Ajouter le texte
        return imagestring($this->image, $size, $x, $y, $text, $textColor);
    }
    
    /**
     * Recadre l'image pour qu'elle ait les dimensions spécifiées (centre l'image)
     * 
     * @param int $width Largeur cible
     * @param int $height Hauteur cible
     * @return bool Succès de l'opération
     */
    public function cropToSize($width, $height) {
        if (!$this->image) {
            return false;
        }
    
        // Calcul des nouvelles dimensions pour garder le bon ratio
        $originalRatio = $this->width / $this->height;
        $targetRatio = $width / $height;
    
        if ($originalRatio > $targetRatio) {
            // Image plus large que la cible, redimensionner en hauteur
            $newWidth = $this->height * $targetRatio;
            $newHeight = $this->height;
            $x = ($this->width - $newWidth) / 2;
            $y = 0;
        } else {
            // Image plus haute que la cible, redimensionner en largeur
            $newWidth = $this->width;
            $newHeight = $this->width / $targetRatio;
            $x = 0;
            $y = ($this->height - $newHeight) / 2;
        }
    
        // Créer une nouvelle image recadrée
        $cropped = imagecreatetruecolor($width, $height);
    
        // Gérer la transparence pour PNG et GIF
        if ($this->hasTransparency) {
            imagealphablending($cropped, false);
            imagesavealpha($cropped, true);
            $transparent = imagecolorallocatealpha($cropped, 0, 0, 0, 127);
            imagefill($cropped, 0, 0, $transparent);
        }
    
        // Effectuer le recadrage et le redimensionnement
        imagecopyresampled(
            $cropped, $this->image, 
            0, 0,   // Destination x, y
            $x, $y, // Source x, y
            $width, $height,   // Destination largeur/hauteur
            $newWidth, $newHeight // Source largeur/hauteur
        );
    
        // Mettre à jour l'image de l'objet
        $this->image = $cropped;
        $this->width = $width;
        $this->height = $height;
    
        return true;
    }
}    