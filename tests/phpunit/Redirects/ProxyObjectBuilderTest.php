<?php

use PP\SMW\Redirects\ProxyObjectBuilder;

/**
 * @group PPRedirects
 * @covers PP\SMW\Redirects\ProxyObjectBuilder
 */
class ProxyObjectBuilderTest extends MediaWikiTestCase {

  private $info;

  protected function setUp() {
    parent::setUp();

    $this->propvals = array(
      'property1' => array('value1'),
      'property2' => array('value2.1', 'value2.2'),
      'property3' => array(),
    );

    $this->infoStub = $this->getInfoStub($this->propvals);
  }

  private function getInfoStub(array $propvals) {
    $infoStub = $this->createMock(\PP\SMW\Helpers\PageHelper::class);

    $infoStub->method('getName')
             ->willReturn('Page Name');
    $infoStub->method('getCategories')
             ->willReturn(array('category1', 'category2', 'category3', 'Redirect'));

    $infoStub->method('getPropertyValues')
             ->willReturn($propvals);
        
    $infoStub->method('getSortKey')
             ->willReturn('sortkey1');
    return $infoStub;
  }

  protected function tearDown() {
    unset( $this->propvals );
    unset( $this->infoStub );
    parent::tearDown();
  }

  public function testDeclarationOpeningValid() {
    $builder = new ProxyObjectBuilder($this->infoStub); 
    self::assertStringStartsWith( 
      '{{#subobject:',
      $builder->getDeclaration()
    );
  }

  public function testDeclarationClosingValid() {
    $builder = new ProxyObjectBuilder($this->infoStub);
    self::assertStringEndsWith(
      "}}", 
      $builder->getDeclaration()
    );
  }

  public function testDeclarationOpeningWithName() {
    $builder = new ProxyObjectBuilder($this->infoStub); 
    self::assertStringStartsWith( 
      '{{#subobject:sortkey1',
      $builder->getDeclaration()
    );
  }

  public function testCategories() {
    $builder = new ProxyObjectBuilder($this->infoStub); 
    self::assertContains(
      "|@category=category1;;category2;;category3|+sep=;;",
      $builder->getDeclaration()
    );
  }

  public function testSortkey() {
    $builder = new ProxyObjectBuilder($this->infoStub); 
    self::assertContains(
      "|@sortkey=sortkey1",
      $builder->getDeclaration()
    );
  }

  public function testPropertySingleValue() {
    $builder = new ProxyObjectBuilder($this->infoStub); 
    self::assertContains(
      "|property1=value1\n",
      $builder->getDeclaration()
    );
  }

  public function testPropertyMultipleValues() {
    $builder = new ProxyObjectBuilder($this->infoStub); 
    self::assertContains(
      "|property2=value2.1;;value2.2|+sep=;;\n",
      $builder->getDeclaration()
    );
  }

  public function testIsDefaultDisplayAsIsInserted() {
    $builder = new ProxyObjectBuilder($this->infoStub); 
    self::assertContains(
      "|" . ProxyObjectBuilder::DISPLAY_AS_PROPERTY . "=[[Page Name|{{KlammernEntfernen|Page Name}}]]",
       $builder->getDeclaration()
    );
  }

  public function testIsDefaultOverridden() {
    $this->propvals[ProxyObjectBuilder::DISPLAY_AS_PROPERTY] = array("OverrideValue");
    $infoStub = $this->getInfoStub($this->propvals);
    $builder = new ProxyObjectBuilder($infoStub); 
    self::assertContains(
       "|" . ProxyObjectBuilder::DISPLAY_AS_PROPERTY . "=" . "OverrideValue",
       $builder->getDeclaration()
    );
  }

}