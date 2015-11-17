# Imager
Imager is manager for images and thumbnails.

## Installation
Installation performed by [Composer]. Write to command line `composer require lawondyss/imager`.

## CAUTION
Library used [ImageMagick] over command line. Is necessary have ImageMagick installed in system.

## Examples

### Create thumbnails
```php
// Image accepts argument of image as instance of class Imager\ImageInfo (extends SplFileInfo)
$imageInfo = new Imager\ImageInfo('path/to/image.jpg');
$image = new Imager\Image($imageInfo);

// create over factory
// ImageFactory::create() accepts image name in string or instance of ImageInfo
$factory = new Imager\ImageFactory;
$image = $factory->create('path/to/image.jpg');

/** Thumbnails **/
// resize by width
$thumb = $image->resize(100); // instance of Imager\ImageInfo with temporary image
var_dump($thumb->getPathname()); // path to thumbnail

// resize by height
$thumb = $image->resize(null, 100);

// resize with crop, cropped image is centered
$thumb = $image->resize(100, 100);

// origin dimensions
$thumb = $image->resize(0, 0);

/** Send image to output **/
header('Content-Type: ' . $thumb->getMime());
header('Content-Length: ' . $thumb->getSize());
echo $thumb->getContent();
```

### Repository for sources images and thumbnails
```php
$factory = new Imager\ImageFactory;

// first argument is required; is directory with sources
// second argument is optional; is directory for thumbnails; if not set, then is same as directory for sources; autocreated 
$repository = new Imager\Repository('path/to/sources', 'path/to/thumbnails');

// create ImageInfo of source image
$uploadImageInfo = $factory->createInfo('path/to/uploaded/image.jpg');

// source image has not source, therefore save to sources directory
// second optional argument defined new name for saved image
$sourceImageInfo = $repository->save($uploadImageInfo, 'image.jpg'); // instance of Imager\ImageInfo with saved source image

// fetch source image only by name
$imageInfo = $repository->fetch('image.jpg'); // instance of Imager\ImageInfo with source image

// created thumbnail
$thumb = $factory->create($imageInfo)->resize(100); // instance of Imager\ImageInfo with temporary thumbnail of image

// thumbnail has source, therefore save to thumbnails directory
$thumbImageInfo = $repository->save($thumb); // instance of Imager\ImageInfo with saved thumbnail
```

## Nette extension
For registration Imager as Nette extension is required add this configuration.
```yaml
extensions:
    imager: Imager\DI\Extension
```
Extension has this configuration:
```yaml
imager:
    sourcesDir: %appDir%/../cdn/assets # required
    thumbsDir: %wwwDir%/images/thumbs
    baseUrl: http://cdn.example.com # if is your images in another URL 
    basePath: images/thumbs/ # required; adds this path to URL
    errorImage: on # default on; displays error image if when generating an error occurred
    debugger: on # default as debugMode; display information in debug bar; WARNING! For every image send new HEAD request!
```

## Example with extension
Presenter for upload and show images
```php
class ImagerPresenter extends BasePresenter
{
    /** @var \Imager\Repository @inject */
    public $repository;

    /** @var \Imager\ImageFactory @inject */
    public $factory;

    public function renderDefault($id)
    {
        if (isset($id)) {
            $this->template->imageFromString = $id;
            $this->template->imageFromRepository = $this->repository->fetch($id);
        }
    }


    protected function createComponentUploadForm()
    {
        $control = new Nette\Application\UI\Form;

        $control->addUpload('photo')
            ->setRequired();
        $control->addSubmit('load', 'load image');
        $control->onSuccess[] = $this->uploadFormSucceed;

        return $control;
    }

    public function uploadFormSucceed(Nette\Application\UI\Form $form, $values)
    {
        $upload = $this->factory->createInfo($values->photo->getTemporaryFile());
        $source = $this->repository->save($upload);

        $this->redirect('default', $source->getFilename());
    }
}
```
Latte template
```
{block content}
{control uploadForm}
{ifset $image}
    <img n:src="$imageFromRepository, 200, 0"> {* set width, origin height *}
    <img n:src="$imageFromRepository, 200, 300"> {* set width and height *}
    <img n:src="$imageFromRepository, null, 300"> {* resize by height *}
    <img n:src="$imageFromRepository, 200"> {* resize by width *}
    <img n:src="$imageFromRepository"> {* origin width and height *}
    {* same parameters as for $imageFromRepository *}
    <img n:src="$imageFromString">
    
{/ifset}
```

### Link to image
If you required create link to image (example to e-mail), then is here method `ImageFactory::createLink()`.
Create a link is only possible when integrating into the Nette.
```php
class EmailPresenter extends BasePresenter
{
    /** @var \Imager\ImageFactory @inject */
    public $factory;

    public function actionSendEmail($email)
    {
        // ... defines $images as array with Imager\ImageInfo objects or names of images

        $linksHtml = [];
        foreach ($images as $image) {
          $linksHtml = $this->createImg($this, $image);
        }

        // ... functionality for send e-mail
    }


    private function createImg($image, $width = null, $height = null)
    {
        $link = $this->factory->createLink($this, $image, $width, $height);
        
        $img = Nette\Utils\Html::el('img', ['src' => $link]);

        return $img->render();
    }
}
```

[Composer]:https://getcomposer.org/
[ImageMagick]:http://www.imagemagick.org/
