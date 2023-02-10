<?php

namespace Alexconesap\Commons;

use Intervention\Image\ImageManagerStatic as Image;

class ImageLib
{

    /**
     * Resizes a given image centering it into the destination canvas of size $width x $height. It saves the resulting
     * image into the $dest_file (that must include the full path to the file to be written).
     *
     * No file permissions are checked.
     *
     * @param string $source_file
     * @param int $width
     * @param int $height
     * @param string $dest_file
     * @return void
     */
    public static function resize(string $source_file, int $width, int $height, string $dest_file): void
    {
        $image = Image::make($source_file);

        $originalWidth = $image->width();
        $originalHeight = $image->height();
        $originalRatio = $originalWidth / $originalHeight;
        $newRatio = $width / $height;

        if ($originalRatio > $newRatio) {
            // Original image is wider than the new canvas
            $image->resize(null, $height, function ($constraint) {
                $constraint->aspectRatio();
            });

            $x = round(($image->width() - $width) / 2, 0);
            $image->crop($width, $height, $x, 0);
        } else {
            // Original image is taller than the new canvas
            $image->resize($width, null, function ($constraint) {
                $constraint->aspectRatio();
            });

            $y = round(($image->height() - $height) / 2, 0);
            $image->crop($width, $height, 0, $y);
        }

        $image->save($dest_file);
    }

}
