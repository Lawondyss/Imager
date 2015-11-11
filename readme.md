# Imager
Imager is manager for images and thumbnails.

## Instalation
Over [Composer] `composer require lawondyss/imager`

## Examples

### Create thumbnails
```php
// Image accepts argument of image as instance of class Imager\ImageInfo (extends SplFileInfo)
$imageInfo = new Imager\ImageInfo('path/to/image.jpg');

// create Image over factory
$factory = new Imager\ImageFactory;
$image = $factory->create($imageInfo);

// create Image directly
$image = new Imager\Image($imageInfo);

/** Thumbnails **/
// resize by width
$thumb = $image->resize(100); // instance of Imager\ImageInfo with temporary image
var_dump($thumb->getPathname()); // path to thumbnail

// resize by height
$thumb = $image->resize(null, 100);

// resize with crop, croped image is centered
$thumb = $image->resize(100, 100);
```

### Repository for sources images and thumbnails
```php
$repository = new Imager\Repository('path/to/sources', 'path/to/thumbnails');

// create ImageInfo of source image
$uploadImageInfo = new Imager\ImageInfo('path/to/uploaded/image.jpg');

// source image has not source, therefore save to sources directory
// second optional argument defined new name for saved image
$sourceImageInfo = $repository->save($uploadImageInfo, 'image.jpg'); // instance of Imager\ImageInfo with saved source image

// fetch source image only by name
$imageInfo = $repository->fetch('image.jpg'); // instance of Imager\ImageInfo with source image

// created thumbnail
$factory = new Imager\ImageFactory;
$thumb = $factory->create($imageInfo)->resize(100); // instance of Imager\ImageInfo with temporary thumbnail of image

// thumbnail has source, therefore save to thumbnails directory
$thumbImageInfo = $repository->save($thumb); // instance of Imager\ImageInfo with saved thumbnail
```

[Composer]:https://getcomposer.org/
