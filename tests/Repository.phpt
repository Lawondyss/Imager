<?php
/**
 * @author Ladislav Vondráček <lad.von@gmail.com>
 * @package Tests
 */

namespace Tests;

require_once __DIR__ . '/bootstrap.php';

use Imager\Repository;
use Nette;
use Tester;
use Tester\Assert;

class RepositoryTest extends Tester\TestCase
{

  /** @var \Imager\Repository */
  private $repository;


  protected function setUp()
  {
    // copy original because Repository::save remove original
    Nette\Utils\FileSystem::copy(ASSETS . IMAGE, TEMP_DIR . '/av/' . IMAGE);
  }


  protected function teardown()
  {
    Tester\Helpers::purge(TEMP_DIR);
  }


  public function testCreate()
  {
    Assert::exception(function() {
      new Repository('non/exists/path');
    }, \Imager\NotExistsException::class);

    Assert::exception(function() {
      new Repository(TEMP_DIR, 'non/exists/path');
    }, \Imager\NotExistsException::class);

    Assert::type(Repository::class, new Repository(TEMP_DIR));
    Assert::type(Repository::class, new Repository(TEMP_DIR, TEMP_DIR));
  }


  public function testFetchExceptions()
  {
    Assert::exception(function() {
      $this->getRepository()->fetch(null);
    }, \Imager\InvalidArgumentException::class);

    Assert::exception(function() {
      $this->getRepository()->fetch('');
    }, \Imager\InvalidArgumentException::class);

    Assert::exception(function() {
      $this->getRepository()->fetch('not-exists-file.dot');
    }, \Imager\NotExistsException::class);
  }


  public function testFetch()
  {
    $result = $this->getRepository()
        ->fetch(IMAGE);

    Assert::type(\Imager\ImageInfo::class, $result);

    Assert::same(960, $result->getWidth());
    Assert::same(1280, $result->getHeight());
    Assert::same(2, $result->getType());
    Assert::same('image/jpeg', $result->getMime());
    Assert::null($result->getSource());
  }


  public function testSaveWithoutName()
  {
    $imageInfo = $this->getRepository()
        ->fetch(IMAGE);

    $result = $this->getRepository()
        ->save($imageInfo);

    Assert::type(\Imager\ImageInfo::class, $result);

    Assert::same(960, $result->getWidth());
    Assert::same(1280, $result->getHeight());
    Assert::same(2, $result->getType());
    Assert::same('image/jpeg', $result->getMime());
    Assert::contains('_960x1280.jpg', $result->getFilename());
    Assert::null($result->getSource());
  }


  public function testSaveWithName()
  {
    $imageInfo = $this->getRepository()
        ->fetch(IMAGE);

    $name = 'lipsum.jpg';
    $result = $this->getRepository()
        ->save($imageInfo, $name);

    Assert::type(\Imager\ImageInfo::class, $result);

    Assert::same(960, $result->getWidth());
    Assert::same(1280, $result->getHeight());
    Assert::same(2, $result->getType());
    Assert::same('image/jpeg', $result->getMime());
    Assert::same($name, $result->getFilename());
    Assert::null($result->getSource());
  }


  /**
   * Returns instance of Repository
   * Initiate in setUp its unwanted because initialization must be tested.
   *
   * @return \Imager\Repository
   */
  private function getRepository()
  {
    if (!isset($this->repository)) {
      $this->repository = new Repository(TEMP_DIR);
    }

    return $this->repository;
  }

}

(new RepositoryTest())->run();
