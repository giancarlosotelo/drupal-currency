<?php

/**
 * @file
 * Contains \Drupal\Tests\currency\Unit\Controller\AmountFormattingFormTest.
 */

namespace Drupal\Tests\currency\Unit\Controller {

  use Drupal\Core\Config\Config;
  use Drupal\Core\Config\ConfigFactoryInterface;
  use Drupal\Core\Form\FormState;
  use Drupal\Core\Form\FormStateInterface;
  use Drupal\Core\Render\Element\Radios;
  use Drupal\currency\Controller\AmountFormattingForm;
  use Drupal\currency\Plugin\Currency\AmountFormatter\AmountFormatterManagerInterface;
  use Drupal\Tests\UnitTestCase;
  use Symfony\Component\DependencyInjection\ContainerInterface;

  /**
   * @coversDefaultClass \Drupal\currency\Controller\AmountFormattingForm
   *
   * @group Currency
   */
  class AmountFormattingFormTest extends UnitTestCase {

    /**
     * The config factory.
     *
     * @var \Drupal\Core\Config\ConfigFactoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configFactory;

    /**
     * The controller under test.
     *
     * @var \Drupal\currency\Controller\AmountFormattingForm
     */
    protected $controller;

    /**
     * The currency amount formatter manager.
     *
     * @var \Drupal\currency\Plugin\Currency\AmountFormatter\AmountFormatterManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $currencyAmountFormatterManager;

    /**
     * The string translation service.
     *
     * @var \Drupal\Core\StringTranslation\TranslationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stringTranslation;

    /**
     * {@inheritdoc}
     */
    public function setUp() {
      $this->configFactory = $this->getMock(ConfigFactoryInterface::class);

      $this->currencyAmountFormatterManager = $this->getMock(AmountFormatterManagerInterface::class);

      $this->stringTranslation = $this->getStringTranslationStub();

      $this->controller = new AmountFormattingForm($this->configFactory, $this->stringTranslation, $this->currencyAmountFormatterManager);
    }

    /**
     * @covers ::create
     * @covers ::__construct
     */
    function testCreate() {
      $container = $this->getMock(ContainerInterface::class);
      $map = array(
        array('config.factory', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->configFactory),
        array('plugin.manager.currency.amount_formatter', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->currencyAmountFormatterManager),
        array('string_translation', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->stringTranslation),
      );
      $container->expects($this->any())
        ->method('get')
        ->willReturnMap($map);

      $form = AmountFormattingForm::create($container);
      $this->assertInstanceOf(AmountFormattingForm::class, $form);
    }

    /**
     * @covers ::getFormId
     */
    public function testGetFormId() {
      $this->assertSame('currency_amount_formatting', $this->controller->getFormId());
    }

    /**
     * @covers ::buildForm
     */
    public function testBuildForm() {
      $form = array();
      $form_state = $this->getMock(FormStateInterface::class);

      $definitions = array(
        'foo' => array(
          'label' => $this->randomMachineName(),
        ),
      );

      $plugin_id = $this->randomMachineName();

      $this->currencyAmountFormatterManager->expects($this->once())
        ->method('getDefinitions')
        ->willReturn($definitions);

      $config = $this->getMockBuilder(Config::class)
        ->disableOriginalConstructor()
        ->getMock();
      $config->expects($this->once())
        ->method('get')
        ->with('plugin_id')
        ->willReturn($plugin_id);

      $this->configFactory->expects($this->once())
        ->method('getEditable')
        ->with('currency.amount_formatting')
        ->willReturn($config);

      $this->stringTranslation->expects($this->any())
        ->method('translate')
        ->will($this->returnArgument(0));

      $expected = array(
        '#default_value' => $plugin_id,
        '#options' => array(
          'foo' => $definitions['foo']['label'],
        ),
        '#process' => [[Radios::class, 'processRadios'], [$this->controller, 'processPluginOptions']],
        '#title' => 'Default amount formatter',
        '#type' => 'radios',
      );
      $build = $this->controller->buildForm($form, $form_state);
      $this->assertSame($expected, $build['default_plugin_id']);
    }

    /**
     * @covers ::processPluginOptions
     */
    public function testProcessPluginOptions() {
      $element = array();

      $definitions = array(
        'foo' => array(
          'description' => $this->randomMachineName(),
        ),
        'bar' => array(
          'description' => $this->randomMachineName(),
        ),
        // This must work without a description.
        'baz' => array(),
      );

      $this->currencyAmountFormatterManager->expects($this->once())
        ->method('getDefinitions')
        ->willReturn($definitions);

      $expected = array(
        'foo' => array(
          '#description' => $definitions['foo']['description'],
        ),
        'bar' => array(
          '#description' => $definitions['bar']['description'],
        ),
      );
      $this->assertSame($expected, $this->controller->processPluginOptions($element));
    }

    /**
     * @covers ::submitForm
     */
    public function testSubmitForm() {
      $plugin_id = $this->randomMachineName();

      $values = [
        'default_plugin_id' => $plugin_id,
      ];

      $form = [];
      $form_state = new FormState();
      $form_state->setValues($values);

      $config = $this->getMockBuilder(Config::class)
        ->disableOriginalConstructor()
        ->getMock();
      $config->expects($this->atLeastOnce())
        ->method('set')
        ->with('plugin_id', $plugin_id);
      $config->expects($this->atLeastOnce())
        ->method('save');

      $this->configFactory->expects($this->atLeastOnce())
        ->method('getEditable')
        ->with('currency.amount_formatting')
        ->willReturn($config);

      $this->controller->submitForm($form, $form_state);
    }

  }

}

namespace {

  if (!function_exists('drupal_set_message')) {
    function drupal_set_message() {}
  }

}