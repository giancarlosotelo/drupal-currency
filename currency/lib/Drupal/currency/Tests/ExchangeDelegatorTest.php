<?php

/**
 * @file
 * Contains class \Drupal\currency\Tests\ExchangeDelegatorTest.
 */

namespace Drupal\currency\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Tests \Drupal\currency\ExchangeDelegator.
 */
class ExchangeDelegatorTest extends WebTestBase {

  public static $modules = array('currency');

  /**
   * Overrides parent::getInfo().
   */
  static function getInfo() {
    return array(
      'name' => 'Drupal\currency\ExchangeDelegator',
      'group' => 'Currency',
    );
  }

  /**
   * Tests saveConfiguration() and loadConfiguration().
   */
  public function testSaveConfiguration() {
    $exchangeDelegator = drupal_container()->get('currency.exchange_delegator');
    $configuration = array(
      'currency_bartfeenstra_currency' => TRUE,
      'currency_fixed_rates' => TRUE,
      'foo' => FALSE,
    );
    $exchangeDelegator->saveConfiguration($configuration);
    $this->assertEqual($exchangeDelegator->loadConfiguration(), $configuration);
  }

  /**
   * Tests load().
   */
  function testLoad() {
    $exchangeDelegator = drupal_container()->get('currency.exchange_delegator');

    // Test an available exchange rate.
    $this->assertIdentical($exchangeDelegator->load('EUR', 'NLG'), '2.20371');

    // Test an unavailable exchange rate for which the reverse rate is
    // available.
    $this->assertIdentical($exchangeDelegator->load('NLG', 'EUR'), '0.453780216');
  }

  /**
   * Tests loadMultiple().
   */
  function testLoadMultiple() {
    $exchangeDelegator = drupal_container()->get('currency.exchange_delegator');

    // Test an available exchange rate.
    $rates = $exchangeDelegator->loadMultiple(array(
      'EUR' => array('NLG'),
    ));
    $this->assertTrue(isset($rates['EUR']));
    $this->assertTrue(isset($rates['EUR']['NLG']));
    $this->assertIdentical($rates['EUR']['NLG'], '2.20371');

    // Test an unavailable exchange rate for which the reverse rate is
    // available.
    $rates = $exchangeDelegator->loadMultiple(array(
      'NLG' => array('EUR'),
    ));
    $this->assertTrue(isset($rates['NLG']));
    $this->assertTrue(isset($rates['NLG']['EUR']));
    $this->assertIdentical($rates['NLG']['EUR'], '0.453780216');
  }
}