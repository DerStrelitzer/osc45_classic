<?php
/*
  $Id: thumbnail.php,v 1.1 2006/09/01 by Ingo @ http://forums.oscommerce.de/index.php?showuser=36

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2026 xPrioS
  Copyright (c) 2006 osCommerce

  Released under the GNU General Public License

*/

class Thumbnail
{
    private $raw_path;
    public $base_dir, $image_dir, $thumbs_dir, $src_new, $width_new, $height_new;
    public $path, $file, $ext, $calculation_mode, $source_data;
    public $thumbnail_width, $thumbnail_height;

    public function __construct($base_dir='', $image_dir='', $dir='', $mode='1', $width='', $height='')
    {
        global $messageStack;
        if ($base_dir=='') $basedir = dirname($_SERVER['SCRIPT_FILENAME']);
        if ($base_dir[strlen($base_dir)-1] != '/') $base_dir .= '/';
        if ($image_dir!='' && $image_dir[strlen($image_dir)-1] != '/') $image_dir .= '/';
        if ($dir=='') $dir = 'thumbnail';
        if ($dir[strlen($dir)-1] == '/') $dir .= substr($dir, 0, strlen($dir)-1);

        $this->base_dir  = $base_dir;
        $this->image_dir = $image_dir;
        if (!$this->thumbs_dir = $this->check_dir($dir)) {
        $messageStack->add('header', 'Error creating dir: ' . HTTP_SERVER . '/<b>' . $dir . '</b> <= please check configuration & permissions', 'error');
        }
        if (!in_array($mode, array(1,2,3))) $mode = 1;
        $this->calculation_mode = $mode;
        $this->thumbnail_width = $this->thumbnail_height = 0;
        if ($width>0) $this->thumbnail_width = $width;
        if ($height>0) $this->thumbnail_height = $height;
    }

    public function get($src='', $alt='', $width='', $height='', $parameters='')
    {
        $error = false;
        $this->src_new = $src;
        $this->width_new = $width;
        $this->height_new = $height;
        $this->raw_path = substr($src, strlen($this->image_dir));
        if (substr($src, 0, strlen($this->image_dir)) == $this->image_dir && @is_file($this->base_dir . $this->image_dir . $this->raw_path)) {
            if (@is_file($this->base_dir . $this->thumbs_dir . $this->raw_path) && @filemtime($this->base_dir . $this->thumbs_dir . $this->raw_path) > @filemtime($this->base_dir . $this->image_dir . $this->raw_path)) {
                $this->src_new = $this->thumbs_dir . $this->raw_path;
            } else {
                if (@is_file($this->base_dir . $this->image_dir . $this->raw_path) && $this->source_data = @getimagesize($this->base_dir . $this->image_dir . $this->raw_path)) {
                    $path_parts = pathinfo($this->raw_path);
                    $this->path = $path_parts['dirname'];
                    $this->file = $path_parts['basename'];
                    $this->ext  = $path_parts['extension'];
                    if ($this->check_dir($this->thumbs_dir . '/' . $this->path) && $this->calculate_new()) {
                        switch ($this->source_data[2]) {
                            case '1': 
                                $this->calculate_gif();
                                break;
                            case '2': 
                                $this->calculate_jpg();
                                break;
                            case '3': 
                                $this->calculate_png();
                                break;
                        }
                    }
                }
            }
        }
        return tep_image($this->src_new, $alt, $width, $height, $parameters);
      //return tep_image($this->src_new, $alt, $this->width_new, $this->height_new, $parameters);
    }

    private function calculate_new()
    {
        switch ($this->calculation_mode) {
            case '1': // width only (recommend!)
                if ($this->thumbnail_width>0 && $this->source_data[0]>0 && $this->source_data[0]>$this->thumbnail_width) {
                    $ratio = $this->source_data[0]/$this->thumbnail_width;
                    $this->source_data['new_width'] = $this->thumbnail_width;
                    $this->source_data['new_height'] = intval($this->source_data[1]/$ratio);
                    return true;
                } elseif ($this->width_new>0) {
                    $ratio = $this->source_data[0]/$this->width_new;
                    $this->source_data['new_width'] = intval($this->source_data[0]/$ratio);
                    $this->source_data['new_height'] = intval($this->source_data[1]/$ratio);
                    return true;
                }
                break;
            case '2': // height only
                if ($this->thumbnail_height>0 && $this->source_data[1]>0 && $this->source_data[1]>$this->thumbnail_height) {
                    $ratio = $this->source_data[1]/$this->thumbnail_height;
                    $this->source_data['new_width'] = intval($this->source_data[0]/$ratio);
                    $this->source_data['new_height'] = $this->thumbnail_height;
                    return true;
                } elseif ($this->height_new>0) {
                    $ratio = $this->source_data[1]/$this->height_new;
                    $this->source_data['new_width'] = intval($this->source_data[0]/$ratio);
                    $this->source_data['new_height'] = intval($this->source_data[1]/$ratio);
                    return true;
                }
                break;
            case '3': // width + height
                if ($this->thumbnail_width>0 && $this->thumbnail_height>0 && $this->source_data[0]>$this->thumbnail_width && $this->source_data[1]>$this->thumbnail_height) {
                    $this->source_data['new_width'] = $this->thumbnail_width;
                    $this->source_data['new_height'] = $this->thumbnail_height;
                    return true;
                }
                if ($this->width_new>0 && $this->height_new>0) {
                    $this->source_data['new_width'] = intval($this->width_new);
                    $this->source_data['new_height'] = intval($this->height_new);
                    return true;
                }
                break;
        }
        return false;
    }

    private function calculate_gif()
    {
        if (function_exists('imagecreatefromgif')) {
        // unfertig
        // $source_image = @imagecreatefromgif($this->base_dir . $this->image_dir . $this->raw_path);
        // $destination_image = @imagecreatetruecolor($this->source_data['new_width'], $this->source_data['new_height']);
        }
        return true;
    }

    private function calculate_jpg()
    {
        $source_image = imagecreatefromjpeg($this->base_dir . $this->image_dir . $this->raw_path);
        $destination_image = imagecreatetruecolor($this->source_data['new_width'], $this->source_data['new_height']);
        if (imagecopyresampled($destination_image, $source_image, 0, 0, 0, 0, $this->source_data['new_width'], $this->source_data['new_height'], $this->source_data[0], $this->source_data[1])
           && imagejpeg($destination_image, $this->base_dir . $this->thumbs_dir . $this->raw_path , '80')
        ) {
            @chmod($this->base_dir . $this->thumbs_dir . $this->raw_path, 0777);
            imagedestroy($destination_image);
            $this->src_new = $this->thumbs_dir . $this->raw_path;
            $this->width_new = $this->source_data['new_width'];
            $this->height_new = $this->source_data['new_height'];
        }
    }

    private function calculate_png()
    {
        return true;
    }

    private function check_dir($dir = '')
    {
        $base = $this->base_dir;
        if ($dir!='') {
            $path = explode("/", $dir);
            $dept = count($path);
            for ($i=0; $i<$dept; $i++) {
                if (!is_dir($base . $path[$i])) {
                    $old_mask = @umask(0);
                    if (!@mkdir($base . $path[$i], 0777)) {
                        return false;
                    }
                    @umask($old_mask);
                }
                $base .= $path[$i] . '/';
            }
        }
        return substr($base, strlen($this->base_dir));
    }

}
