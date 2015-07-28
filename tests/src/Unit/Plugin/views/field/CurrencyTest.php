<?php

/**
 * @file
 * Contains \Drupal\Tests\currency\Unit\Plugin\views\field\CurrencyTest.
 */

namespace Drupal\Tests\currency\Unit\Plugin\views\field;

use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\currency\Entity\CurrencyInterface;
use Drupal\currency\Plugin\views\field\Currency;
use Drupal\Tests\UnitTestCase;
use Drupal\views\ResultRow;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\currency\Plugin\views\field\Currency
 *
 * @group Currency
 */
class CurrencyTest extends UnitTestCase {

  /**
   * The currency storage used for testing.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $currencyStorage;

  /**
   * The plugin configuration.
   *
   * @var mixed[]
   */
  protected $pluginConfiguration;

  /**
   * The plugin definiton.
   *
   * @var mixed[]
   */
  protected $pluginDefinition;

  /**
   * The string translator.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface
   */
  protected $stringTranslation;

  /**
   * The class under test.
   *
   * @var \Drupal\currency\Plugin\views\field\Currency
   */
  protected $sut;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $this->pluginConfiguration = [
      'currency_method' => 'label',
    ];
    $plugin_id = $this->randomMachineName();
    $this->pluginDefinition = [];

    $this->currencyStorage = $this->getMock(EntityStorageInterface::class);

    $this->stringTranslation = $this->getStringTranslationStub();

    $this->sut = new Currency($this->pluginConfiguration, $plugin_id, $this->pluginDefinition, $this->stringTranslation, $this->currencyStorage);
  }

  /**
   * @covers ::__construct
   */
  public function testConstruct() {
    $plugin_id = $this->randomMachineName();
    $this->sut = new Currency($this->pluginConfiguration, $plugin_id, $this->pluginDefinition, $this->stringTranslation, $this->currencyStorage);
  }

  /**
   * @covers ::__construct
   *
   * @expectedException \InvalidArgumentException
   */
  public function testConstructWithoutMethod() {
    $plugin_id = $this->randomMachineName();

    $this->sut = new Currency([], $plugin_id, $this->pluginDefinition, $this->stringTranslation, $this->currencyStorage);
  }

  /**
   * @covers ::__construct
   *
   * @expectedException \InvalidArgumentException
   */
  public function testConstructWithNonExistentMethod() {
    $configuration = [
      'currency_method' => $this->randomMachineName(),
    ];
    $plugin_id = $this->randomMachineName();

    $this->sut = new Currency($configuration, $plugin_id, $this->pluginDefinition, $this->stringTranslation, $this->currencyStorage);
  }

  /**
   * @covers ::create
   */
  function testCreate() {
    $entity_manager = $this->getMock(EntityManagerInterface::class);
    $entity_manager->expects($this->atLeastOnce())
      ->method('getStorage')
      ->with('currency')
      ->willReturn($this->currencyStorage);

    $container = $this->getMock(ContainerInterface::class);
    $map = [
      ['entity.manager', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $entity_manager],
      ['string_translation', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->stringTranslation],
    ];
    $container->expects($this->any())
      ->method('get')
      ->willReturnMap($map);

    $sut = Currency::create($container, $this->pluginConfiguration, '', $this->pluginDefinition);
    $this->assertInstanceOf(Currency::class, $sut);
  }

  /**
   * @covers ::render
   */
  function testRenderWithExistingCurrency() {
    $currency_code = $this->randomMachineName();

    $currency_method_return_value = $this->randomMachineName();

    $field_alias = $this->randomMachineName();

    $this->sut->field_alias = $field_alias;

    $result_row = new ResultRow([
      $field_alias => $currency_code,
    ]);

    $currency = $this->getMock(CurrencyInterface::class);
    $currency->expects($this->atLeastOnce())
      ->method($this->pluginConfiguration['currency_method'])
      ->willReturn($currency_method_return_value);

    $this->currencyStorage->expects($this->atLeastOnce())
      ->method('load')
      ->with($currency_code)
      ->willReturn($currency);

    $this->assertSame($currency_method_return_value, $this->sut->render($result_row));
  }

  /**
   * @covers ::render
   */
  function testRenderWithNonExistingCurrency() {
    $currency_code = $this->randomMachineName();

    $field_alias = $this->randomMachineName();

    $this->sut->field_alias = $field_alias;

    $result_row = new ResultRow([
      $field_alias => $currency_code,
    ]);

    $this->currencyStorage->expects($this->atLeastOnce())
      ->method('load')
      ->with($currency_code)
      ->willReturn(NULL);

    $this->assertInternalType('string', $this->sut->render($result_row));
  }

}
