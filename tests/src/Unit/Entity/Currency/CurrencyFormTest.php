<?php

/**
 * @file
 * Contains \Drupal\Tests\currency\Unit\Entity\Currency\CurrencyFormTest.
 */

namespace Drupal\Tests\currency\Unit\Entity\Currency {

  use Commercie\Currency\InputInterface;
  use Drupal\Core\Entity\EntityManagerInterface;
  use Drupal\Core\Entity\EntityStorageInterface;
  use Drupal\Core\Form\FormStateInterface;
  use Drupal\Core\Language\LanguageInterface;
  use Drupal\Core\Url;
  use Drupal\Core\Utility\LinkGeneratorInterface;
  use Drupal\currency\Entity\Currency\CurrencyForm;
  use Drupal\currency\Entity\CurrencyInterface;
  use Drupal\Tests\UnitTestCase;
  use Symfony\Component\DependencyInjection\ContainerInterface;

  /**
   * @coversDefaultClass \Drupal\currency\Entity\Currency\CurrencyForm
   *
   * @group Currency
   */
  class CurrencyFormTest extends UnitTestCase {

    /**
     * The currency.
     *
     * @var \Drupal\currency\Entity\CurrencyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $currency;

    /**
     * The currency storage.
     *
     * @var \Drupal\Core\Entity\EntityStorageInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $currencyStorage;

    /**
     * The Currency input parser.
     *
     * @var \Commercie\Currency\InputInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $inputParser;

    /**
     * The link generator.
     *
     * @var \Drupal\Core\Utility\LinkGeneratorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $linkGenerator;

    /**
     * The string translator.
     *
     * @var \Drupal\Core\StringTranslation\TranslationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stringTranslation;

    /**
     * The class under test.
     *
     * @var \Drupal\currency\Entity\Currency\CurrencyForm
     */
    protected $sut;

    /**
     * {@inheritdoc}
     */
    public function setUp() {
      $this->currency = $this->getMock(CurrencyInterface::class);

      $this->currencyStorage = $this->getMock(EntityStorageInterface::class);

      $this->linkGenerator = $this->getMock(LinkGeneratorInterface::class);

      $this->inputParser = $this->getMock(InputInterface::class);

      $this->stringTranslation = $this->getStringTranslationStub();

      $this->sut = new CurrencyForm($this->stringTranslation, $this->linkGenerator, $this->currencyStorage, $this->inputParser);
      $this->sut->setEntity($this->currency);
    }

    /**
     * @covers ::create
     * @covers ::__construct
     */
    function testCreate() {
      $entity_manager = $this->getMock(EntityManagerInterface::class);
      $entity_manager->expects($this->once())
        ->method('getStorage')
        ->with('currency')
        ->willReturn($this->currencyStorage);

      $container = $this->getMock(ContainerInterface::class);

      $map = array(
        array('entity.manager', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $entity_manager),
        array('currency.input', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->inputParser),
        array('link_generator', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->linkGenerator),
        array('string_translation', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->stringTranslation),
      );
      $container->expects($this->any())
        ->method('get')
        ->willReturnMap($map);

      $sut = CurrencyForm::create($container);
      $this->assertInstanceOf(CurrencyForm::class, $sut);
    }

    /**
     * @covers ::copyFormValuesToEntity
     */
    public function testCopyFormValuesToEntity() {
      $currency_code = $this->randomMachineName();
      $currency_number = $this->randomMachineName();
      $currency_label = $this->randomMachineName();
      $currency_sign = $this->randomMachineName();
      $currency_subunits = mt_rand();
      $currency_rounding_step = mt_rand();
      $currency_status = TRUE;

      $this->currency->expects($this->once())
        ->method('setCurrencyCode')
        ->with($currency_code);
      $this->currency->expects($this->once())
        ->method('setCurrencyNumber')
        ->with($currency_number);
      $this->currency->expects($this->once())
        ->method('setLabel')
        ->with($currency_label);
      $this->currency->expects($this->once())
        ->method('setSign')
        ->with($currency_sign);
      $this->currency->expects($this->once())
        ->method('setSubunits')
        ->with($currency_subunits);
      $this->currency->expects($this->once())
        ->method('setRoundingStep')
        ->with($currency_rounding_step);
      $this->currency->expects($this->once())
        ->method('setStatus')
        ->with($currency_status);

      $form = array();
      $form_state = $this->getMock(FormStateInterface::class);
      $form_state->expects($this->atLeastOnce())
        ->method('getValues')
        ->willReturn(array(
          'currency_code' => $currency_code,
          'currency_number' => $currency_number,
          'label' => $currency_label,
          'sign' => $currency_sign,
          'subunits' => $currency_subunits,
          'rounding_step' => $currency_rounding_step,
          'status' => $currency_status,
        ));

      $method = new \ReflectionMethod($this->sut, 'copyFormValuesToEntity');
      $method->setAccessible(TRUE);

      $method->invokeArgs($this->sut, array($this->currency, $form, $form_state));
    }

    /**
     * @covers ::form
     */
    public function testForm() {
      $currency_code = $this->randomMachineName();
      $currency_number = $this->randomMachineName();
      $currency_label = $this->randomMachineName();
      $currency_sign = $this->randomMachineName();
      $currency_subunits = mt_rand();
      $currency_rounding_step = mt_rand();
      $currency_status = TRUE;

      $this->currency->expects($this->any())
        ->method('getCurrencyCode')
        ->willReturn($currency_code);
      $this->currency->expects($this->any())
        ->method('getCurrencyNumber')
        ->willReturn($currency_number);
      $this->currency->expects($this->any())
        ->method('label')
        ->willReturn($currency_label);
      $this->currency->expects($this->any())
        ->method('getSign')
        ->willReturn($currency_sign);
      $this->currency->expects($this->any())
        ->method('getSubunits')
        ->willReturn($currency_subunits);
      $this->currency->expects($this->any())
        ->method('getRoundingStep')
        ->willReturn($currency_rounding_step);
      $this->currency->expects($this->any())
        ->method('status')
        ->willReturn($currency_status);

      $language = $this->getMock(LanguageInterface::class);

      $this->currency->expects($this->any())
        ->method('language')
        ->willReturn($language);

      $form = array();
      $form_state = $this->getMock(FormStateInterface::class);

      $expected = array(
        'currency_code' => array(
          '#default_value' => $currency_code,
          '#disabled' => TRUE,
          '#element_validate' => array(array($this->sut, 'validateCurrencyCode')),
          '#maxlength' => 3,
          '#pattern' => '[a-zA-Z]{3}',
          '#placeholder' => 'XXX',
          '#required' => TRUE,
          '#size' => 3,
          '#title' => 'ISO 4217 code',
          '#type' => 'textfield',
        ),
        'currency_number' => array(
          '#default_value' => $currency_number,
          '#element_validate' => array(array($this->sut, 'validateCurrencyNumber')),
          '#maxlength' => 3,
          '#pattern' => '[\d]{3}',
          '#placeholder' => '999',
          '#size' => 3,
          '#title' => 'ISO 4217 number',
          '#type' => 'textfield',
        ),
        'status' => array(
          '#default_value' => $currency_status,
          '#title' => 'Enabled',
          '#type' => 'checkbox',
        ),
        'label' => array(
          '#default_value' => $currency_label,
          '#maxlength' => 255,
          '#required' => TRUE,
          '#title' => 'Name',
          '#type' => 'textfield',
        ),
        'sign' => array(
          '#currency_code' => $currency_code,
          '#default_value' => $currency_sign,
          '#title' => 'Sign',
          '#type' => 'currency_sign',
        ),
        'subunits' => array(
          '#default_value' => $currency_subunits,
          '#min' => 0,
          '#required' => TRUE,
          '#title' => 'Number of subunits',
          '#type' => 'number',
        ),
        'rounding_step' => array(
          '#default_value' => $currency_rounding_step,
          '#element_validate' => array(array($this->sut, 'validateRoundingStep')),
          '#required' => TRUE,
          '#title' => 'Rounding step',
          '#type' => 'textfield',
        ),
        '#after_build' => ['::afterBuild'],
      );
      $build = $this->sut->form($form, $form_state);
      unset($build['langcode']);
      unset($build['#process']);
      $this->assertSame($expected, $build);
    }

    /**
     * @covers ::save
     */
    public function testSave() {
      $this->currency->expects($this->once())
        ->method('save');

      $form = array();
      $form_state = $this->getMock(FormStateInterface::class);
      $form_state->expects($this->once())
        ->method('setRedirect')
        ->with('entity.currency.collection');

      $this->sut->save($form, $form_state);
    }

    /**
     * @covers ::validateCurrencyCode
     * @dataProvider providerTestValidateCurrencyCode
     */
    public function testValidateCurrencyCode($valid, $currency_code, $currency_is_new, $currency_code_exists = FALSE) {
      $element = array(
        '#value' => $currency_code,
      );
      $form = array();
      $form_state = $this->getMock(FormStateInterface::class);

      $this->currency->expects($this->any())
        ->method('isNew')
        ->willReturn($currency_is_new);

      if (!$valid) {
        $form_state->expects($this->once())
          ->method('setError')
          ->with($element, 'The currency code must be three letters.');
      }
      elseif ($currency_code_exists) {
        $loaded_currency_label = $this->randomMachineName();
        $loaded_currency_url = new Url($this->randomMachineName());

        $loaded_currency = $this->getMock(CurrencyInterface::class);
        $loaded_currency->expects($this->any())
          ->method('label')
          ->willReturn($loaded_currency_label);
        $loaded_currency->expects($this->atLeastOnce())
          ->method('urlInfo')
          ->willReturn($loaded_currency_url);

        $this->currencyStorage->expects($this->once())
          ->method('load')
          ->with($currency_code)
          ->willReturn($loaded_currency);

        $form_state->expects($this->once())
          ->method('setError');

        $this->linkGenerator->expects($this->once())
          ->method('generate')
          ->with($loaded_currency_label, $loaded_currency_url);
      }
      else {
        $this->currencyStorage->expects($this->once())
          ->method('load')
          ->with($currency_code)
          ->willReturn(FALSE);
        $form_state->expects($this->never())
          ->method('setError');
        $form_state->expects($this->never())
          ->method('setErrorByName');
      }

      $this->sut->validateCurrencyCode($element, $form_state, $form);
    }

    /**
     * Provides data to self::testValidateCurrencyCode()
     */
    public function providerTestValidateCurrencyCode() {
      return array(
        // All valid values and no re-use of the currency code.
        array(TRUE, 'EUR', TRUE),
        // An invalid currency code.
        array(FALSE, 'AB1', TRUE),
        // A valid currency code, but it is being re-used.
        array(TRUE, 'NLG', TRUE, TRUE),
      );
    }

    /**
     * @covers ::validateCurrencyNumber
     * @dataProvider providerTestValidateCurrencyNumber
     */
    public function testValidateCurrencyNumber($valid, $currency_number, $currency_is_new, $currency_number_exists = FALSE) {
      $element = array(
        '#value' => $currency_number,
      );
      $form = array();
      $form_state = $this->getMock(FormStateInterface::class);

      $this->currency->expects($this->any())
        ->method('isNew')
        ->willReturn($currency_is_new);

      if (!$valid) {
        $form_state->expects($this->once())
          ->method('setError')
          ->with($element, 'The currency number must be three digits.');
      }
      elseif ($currency_number_exists) {
        $loaded_currency_code = $this->randomMachineName();
        $loaded_currency_label = $this->randomMachineName();
        $loaded_currency_url = new Url($this->randomMachineName());

        $loaded_currency = $this->getMock(CurrencyInterface::class);
        $loaded_currency->expects($this->any())
          ->method('id')
          ->willReturn($loaded_currency_code);
        $loaded_currency->expects($this->any())
          ->method('label')
          ->willReturn($loaded_currency_label);
        $loaded_currency->expects($this->atLeastOnce())
          ->method('urlInfo')
          ->willReturn($loaded_currency_url);

        $this->currencyStorage->expects($this->once())
          ->method('loadByProperties')
          ->with(array(
            'currencyNumber' => $currency_number,
          ))
          ->willReturn(array($loaded_currency));

        $form_state->expects($this->once())
          ->method('setError');

        $this->linkGenerator->expects($this->once())
          ->method('generate')
          ->with($loaded_currency_label, $loaded_currency_url);
      }
      else {
        $this->currencyStorage->expects($this->once())
          ->method('loadByProperties')
          ->with(array(
            'currencyNumber' => $currency_number,
          ))
          ->willReturn(FALSE);
        $form_state->expects($this->never())
          ->method('setError');
        $form_state->expects($this->never())
          ->method('setErrorByName');
      }

      $this->sut->validateCurrencyNumber($element, $form_state, $form);
    }

    /**
     * Provides data to self::testValidateCurrencyNumber()
     */
    public function providerTestValidateCurrencyNumber() {
      return array(
        // All valid values and no re-use of the currency code.
        array(TRUE, '123', TRUE),
        // An invalid currency code.
        array(FALSE, '12A', TRUE),
        // A valid currency code, but it is being re-used.
        array(TRUE, '123', TRUE, TRUE),
      );
    }

    /**
     * @covers ::validateRoundingStep
     * @dataProvider providerTestValidateRoundingStep
     */
    public function testValidateRoundingStep($valid, $input_value, $parsed_value) {
      $element = array(
        '#value' => $input_value,
      );
      $form = array();
      $form_state = $this->getMock(FormStateInterface::class);

      $this->inputParser->expects($this->once())
        ->method('parseAmount')
        ->with($input_value)
        ->willReturn($parsed_value);

      if (!$valid) {
        $form_state->expects($this->once())
          ->method('setError')
          ->with($element, 'The rounding step is not numeric.');
      }
      else {
        $form_state->expects($this->never())
          ->method('setError');
        $form_state->expects($this->never())
          ->method('setErrorByName');
      }
      $form_state->expects($this->once())
        ->method('setValueForElement')
        ->with($element, $parsed_value);

      $this->sut->validateRoundingStep($element, $form_state, $form);
    }

    /**
     * Provides data to self::testValidateRoundingStep()
     */
    public function providerTestValidateRoundingStep() {
      return array(
        array(TRUE, '0.05', 0.05),
        array(FALSE, '0.0g', FALSE),
      );
    }

  }

}

namespace {

if (!function_exists('drupal_set_message')) {
  function drupal_set_message() {}
}

}
