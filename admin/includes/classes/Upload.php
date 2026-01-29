<?php
/*
  $Id: Ppload.php,v 1.2i  2003/07/22 04:04:04 hpdl Exp $
  (milestone 2 + image_resize  henri, ingo)

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2026 xPrioS
  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

define ('THUMB_DEBUG', 'false'); // set to 'true' for debugging is enabled

class Upload
{
    public 
        $file, 
        $filename, 
        $destination, 
        $permissions, 
        $extensions, 
        $product, 
        $image_subdir, 
        $big_destination, 
        $debug, 
        $tmp_filename, 
        $message_location;

    public function __construct($file = '', $destination = '', $permissions = '777', $extensions = '', $product = '', $image_subdir = 'big/', $debug = THUMB_DEBUG)
    {
        $this->set_file($file);
        $this->set_destination($destination);
        $this->set_permissions($permissions);
        $this->set_extensions($extensions);
        $this->set_product($product);
        $this->set_image_subdir($image_subdir);

        $this->set_output_messages('direct');

    }

    public function parse()
    {
        global $messageStack;
        if (isset($_FILES[$this->file])) {
            $file = array(
                'name' => $_FILES[$this->file]['name'],
                'type' => $_FILES[$this->file]['type'],
                'size' => $_FILES[$this->file]['size'],
                'tmp_name' => $_FILES[$this->file]['tmp_name']
            );
        }

        if (tep_not_null($file['tmp_name']) && $file['tmp_name'] != 'none' && is_uploaded_file($file['tmp_name'])) {
            if (sizeof($this->extensions) > 0) {
                if (!in_array(strtolower(substr($file['name'], strrpos($file['name'], '.')+1)), $this->extensions)) {
                    if ($this->message_location == 'direct') {
                        $messageStack->add(ERROR_FILETYPE_NOT_ALLOWED, 'error');
                    } else {
                        $messageStack->add_session(ERROR_FILETYPE_NOT_ALLOWED, 'error');
                    }
                    return false;
                }
            }
            $this->set_file($file);
            $this->set_filename($file['name']);
            $this->set_tmp_filename($file['tmp_name']);

            return $this->check_destination();
        } else {
            if ($this->message_location == 'direct') {
                $messageStack->add(WARNING_NO_FILE_UPLOADED, 'warning');
            } else {
                $messageStack->add_session(WARNING_NO_FILE_UPLOADED, 'warning');
            }
            return false;
        }
    }

    public function save()
    {
        global $messageStack, $debug;

        if (substr($this->destination, -1) != '/') $this->destination .= '/';

        if ($this->product == true) { // Product image upload; lets create the small & big image
            if (substr($this->image_subdir, -1) != '/') $this->image_subdir .= '/';
            $this->set_big_destination($this->destination . $this->image_subdir);

            // Create Thumbnail
            if ($debug=="true") echo "<br>target_small: " . $this->destination . $this->filename . "<br> Resizing small image ";
            if ($this->create_thumbnail($this->file['tmp_name'],$this->destination . $this->filename, SMALL_IMAGE_WIDTH,SMALL_IMAGE_HEIGHT,SMALL_IMAGE_RESIZE)) {
                chmod($this->destination . $this->filename, $this->permissions);
            }

            // If !path make it, if you have problems remove the line
            if (!is_dir($this->big_destination)) {
                if ($debug=="true") echo "<br>Mkdir: '" . $this->big_destination . "'";
                mkdir($this->big_destination, $this->permissions);
            }

            // Resize Big image or Upload as is
            if ($debug=="true") echo "<br><br>target_big: ".$this->big_destination;
            if (BIG_IMAGE_WIDTH || BIG_IMAGE_HEIGHT ) {
                if ($debug=="true") echo "<br> Resizing big image ";
                if ($this->create_thumbnail($this->file['tmp_name'], $this->big_destination . $this->filename, BIG_IMAGE_WIDTH, BIG_IMAGE_HEIGHT, BIG_IMAGE_RESIZE)) {
                    chmod($this->big_destination . $this->filename, $this->permissions);
                    if ($this->message_location == 'direct') {
                        $messageStack->add(SUCCESS_FILE_SAVED_SUCCESSFULLY, 'success');
                    } else {
                        $messageStack->add_session(SUCCESS_FILE_SAVED_SUCCESSFULLY, 'success');
                    }
                    return true;
                } else {
                    if ($this->message_location == 'direct') {
                        $messageStack->add(ERROR_FILE_NOT_SAVED, 'error');
                    } else {
                        $messageStack->add_session(ERROR_FILE_NOT_SAVED, 'error');
                    }
                    return false;
                }
            } else {
                
                // kein resize:
                if ($debug=="true") echo "<br> Copying big image ";
                if (move_uploaded_file($this->file['tmp_name'], $this->big_destination . $this->filename)) {
                    chmod($this->big_destination . $this->filename, $this->permissions);
                    if ($this->message_location == 'direct') {
                        $messageStack->add(SUCCESS_FILE_SAVED_SUCCESSFULLY, 'success');
                    } else {
                        $messageStack->add_session(SUCCESS_FILE_SAVED_SUCCESSFULLY, 'success');
                    }
                    return true;
                } else {
                    if ($this->message_location == 'direct') {
                        $messageStack->add(ERROR_FILE_NOT_SAVED, 'error');
                    } else {
                        $messageStack->add_session(ERROR_FILE_NOT_SAVED, 'error');
                    }
                    return false;
                }
            }
        } else { // Not a product image. Better not resize it.
            if (move_uploaded_file($this->file['tmp_name'], $this->destination . $this->filename)) {
                chmod($this->destination . $this->filename, $this->permissions);
                if ($this->message_location == 'direct') {
                    $messageStack->add(SUCCESS_FILE_SAVED_SUCCESSFULLY, 'success');
                } else {
                    $messageStack->add_session(SUCCESS_FILE_SAVED_SUCCESSFULLY, 'success');
                }
                return true;
            } else {
                if ($this->message_location == 'direct') {
                    $messageStack->add(ERROR_FILE_NOT_SAVED, 'error');
                } else {
                    $messageStack->add_session(ERROR_FILE_NOT_SAVED, 'error');
                }
                return false;
            }
        }
    }

  public function create_thumbnail($pic,$image_new,$new_width,$new_height,$fixed=0)
  {
        global $debug;
        // resize Picture if possible
        // $fixed:
        // 0: propotional resize; new_width or new_height are max Size
        // 1: new_width, new_height are new sizes; Image is proportional resized into new Image. IN_IMAGE_BGCOLOUR are the backgroundcolors
        // 2: new_width, new_height are new sizes; Thumbnail, Pic is resized and part of it is copied to new imaget
        $dst_img='';
        $image=GetImageSize($pic);
        $width=$image[0];
        $height=$image[1];
        if ($debug=="true") echo "<br>Starting in_resize_image()<br>- temp_pic: " . $pic . "<br>- image_new: " . $image_new . "<br>- width: " . $width;
        if (($new_width && $width>=$new_width) || ( $new_height && $height>=$new_height) ) {
            // JPG-Resizen
            if ($debug=="true") echo "<br><br>Image has to be resized; checking for gd-lib";
            if (function_exists('imagecreatefromjpeg')) { // check if php with gd-lib-support is installed
                if ($debug=="true") echo "<br><br>GD-Lib found start resizing<br>Image Format:" . $image[2];
                if ($image[2]==1) {
                    if (function_exists('imagecreatefromgif'))  $src_img=imagecreatefromgif($pic);
                }
                if ($image[2]==2) {
                    if (function_exists('imagecreatefromjpeg'))  $src_img=imagecreatefromjpeg($pic);
                }
                if ($image[2]==3) {
                    if (function_exists('imagecreatefrompng'))  $src_img=imagecreatefrompng($pic);
                }
                if ($src_img) {
                    if ($debug=="true") echo "<br><br>Generated new SRC-Pic";

                    switch ($fixed) {
                        case 0:
                            // proportionaler resize; width oder height ist die maximale Größe
                            $x = $new_width/$width;
                            $y = $new_height/$height;
                            if (($y>0 && $y<$x) || $x==0) $x=$y;
                            $width_big  = $width*$x;
                            $height_big = $height*$x;
                            switch (GD_LIB_VERSION) {
                                case '2':
                                    $dst_img = imagecreatetruecolor($width_big,$height_big);
                                    imagecopyresampled($dst_img,$src_img,0,0,0,0,$width_big,$height_big,imagesx($src_img),imagesy($src_img));
                                    if ($debug=="true") echo "<br> GD-LIB 2 - Generated new Pic";
                                    break;
                                default:
                                    $dst_img = imagecreate($width_big,$height_big);
                                    imagecopyresized($dst_img,$src_img,0,0,0,0,$width_big,$height_big,imagesx($src_img),imagesy($src_img));
                                    if ($debug=="true") echo "<br> GD-LIB 1 - Generated new Pic";
                            }
                            break;

                        case 1:
                            // Bild wird proportional verkleinert in das neue Bild kopiert
                            if ($new_width > 0) $x = $new_width / $width;
                            if ($new_height > 0) $y = $new_height / $height;
                            if (($y > 0 && $y < $x) || $x == 0) $x = $y;
                            $width_big = $width * $x;
                            $height_big = $height * $x;
                            if ($new_width > 0 && $new_width > $width_big) $dst_width = $new_width;
                            else $dst_width = $width_big;
                            if ($new_height > 0 && $new_height > $height_big) $dst_height = $new_height;
                            else $dst_height = $height_big;

                            // copy new picture into center of $dst_img
                            if ($dst_width > $width_big) $dstX = ($dst_width - $width_big)/2;
                            else $dstX = 0;
                            if ($dst_height > $height_big) $dstY=($dst_height - $height_big)/2;
                            else $dstY = 0;
                            switch (GD_LIB_VERSION) {
                                case '2':
                                    $dst_img = imagecreatetruecolor($dst_width,$dst_height);
                                    $colorallocate = ImageColorAllocate ($dst_img, IMAGE_BGCOLOUR_R, IMAGE_BGCOLOUR_G, IMAGE_BGCOLOUR_B);
                                    imagefilledrectangle($dst_img,0,0,$dst_width,$dst_height,$colorallocate);
                                    imagecopyresampled($dst_img,$src_img,$dstX,$dstY,0,0,$width_big,$height_big,imagesx($src_img),imagesy($src_img));
                                    if ($debug=="true") echo "<br> GD-LIB 2 - Generated new Pic";
                                    break;
                                default:
                                    $dst_img = imagecreate($dst_width,$dst_height);
                                    ImageColorAllocate ($dst_img, IMAGE_BGCOLOUR_R, IMAGE_BGCOLOUR_G, IMAGE_BGCOLOUR_B);
                                    imagecopyresized($dst_img,$src_img,$dstX,$dstY,0,0,$width_big,$height_big,imagesx($src_img),imagesy($src_img));
                                    if ($debug=="true") echo "<br> GD-LIB 1 - Generated new Pic";
                            }
                            break;

                        case 2:
                            // Thumbnail, Bild wird verkleinert und ein Ausschnitt wird ins neue kopiert
                            if ($new_width > 0) $x = $new_width / $width;
                            if ($new_height > 0) $y = $new_height / $height;
                            if (($x > 0 && $y > $x) || $x==0) $x = $y;
                            $width_big = $width * $x;
                            $height_big = $height * $x;
                            // Bild verkleinern
                            switch (GD_LIB_VERSION) {
                                case '2':
                                    $dst_img = imagecreatetruecolor($new_width,$new_height);
                                    $tmp_img = imagecreatetruecolor($width_big,$height_big);
                                    imagecopyresampled($tmp_img,$src_img,0,0,0,0,$width_big,$height_big,imagesx($src_img),imagesy($src_img));
                                    imagecopy($dst_img,$tmp_img,0,0,0,0,$new_width,$new_height);
                                    if ($debug=="true") echo "<br> GD-LIB 2 - Generated new Pic";
                                    break;
                                default:
                                    $dst_img = imagecreate($new_width,$new_height);
                                    $tmp_img = imagecreate($width_big,$height_big);
                                    imagecopyresized($tmp_img,$src_img,0,0,0,0,$width_big,$height_big,imagesx($src_img),imagesy($src_img));
                                    imagecopy($dst_img,$tmp_img,0,0,0,0,$new_width,$new_height);
                                    if ($debug=="true") echo "<br> GD-LIB 1 - Generated new Pic";
                            }
                            break;
                    }
          // Copy Picture
                    if ($image[2]==1) imagegif($dst_img,$image_new);
                    if ($image[2]==2) imagejpeg($dst_img,$image_new);
                    if ($image[2]==3) imagepng($dst_img,$image_new);
                    return true;
                } elseif ($debug=="true") {
                    echo "<br>GD-Lib Image Format not supportet";
                }
            } elseif ($debug=="true") {
                echo "<br>NO GD-Lib found";
            }
        }
        // pic couldn't be resized, so copy original
        copy ($pic,$image_new);
        return false;
  }


    public function set_file($file)
    {
        $this->file = $file;
    }

    public function set_destination($destination)
    {
        $this->destination = $destination;
    }

    public function set_big_destination($big_destination)
    {
        $this->big_destination = $big_destination;
    }

    public function set_permissions($permissions)
    {
        $this->permissions = octdec($permissions);
    }

    public function set_product($product)
    {
        $this->product = $product;
    }

    public function set_image_subdir($image_subdir)
    {
        $this->image_subdir = $image_subdir;
    }

    public function set_filename($filename)
    {
        $this->filename = $filename;
    }

    public function set_tmp_filename($filename)
    {
        $this->tmp_filename = $filename;
    }

    public function set_extensions($extensions)
    {
        if (tep_not_null($extensions)) {
            if (is_array($extensions)) {
                $this->extensions = $extensions;
            } else {
                $this->extensions = array($extensions);
            }
        } else {
            $this->extensions = [];
        }
    }

  public function check_destination()
  {
        global $messageStack;

        if (!is_writeable($this->destination)) {
            if (is_dir($this->destination)) {
                if ($this->message_location == 'direct') {
                    $messageStack->add(sprintf(ERROR_DESTINATION_NOT_WRITEABLE, $this->destination), 'error');
                } else {
                    $messageStack->add_session(sprintf(ERROR_DESTINATION_NOT_WRITEABLE, $this->destination), 'error');
                }
            } else {
                if ($this->message_location == 'direct') {
                    $messageStack->add(sprintf(ERROR_DESTINATION_DOES_NOT_EXIST, $this->destination), 'error');
                } else {
                    $messageStack->add_session(sprintf(ERROR_DESTINATION_DOES_NOT_EXIST, $this->destination), 'error');
                }
            }

            return false;
        } else {
            return true;
        }
  }

    public function set_output_messages($location)
    {
        switch ($location) {
            case 'session':
                $this->message_location = 'session';
                break;
            case 'direct':
            default:
                $this->message_location = 'direct';
                break;
        }
    }

}
