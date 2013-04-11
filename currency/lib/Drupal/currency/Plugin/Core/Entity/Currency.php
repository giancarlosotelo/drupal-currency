<?php

/**
 * @file
 * Definition of Drupal\currency\Plugin\Core\Entity\Currency.
 */

namespace Drupal\currency\Plugin\Core\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Component\Annotation\Plugin;
use Drupal\Core\Annotation\Translation;

/**
 * Defines a currency entity class.
 *
 * @Plugin(
 *   access_controller_class = "Drupal\currency\CurrencyAccessController",
 *   config_prefix = "currency.currency",
 *   controller_class = "Drupal\currency\CurrencyStorageController",
 *   entity_keys = {
 *     "id" = "currencyCode",
 *     "label" = "label",
 *     "uuid" = "uuid",
 *     "status" = "status"
 *   },
 *   fieldable = FALSE,
 *   form_controller_class = {
 *     "default" = "Drupal\currency\CurrencyFormController"
 *   },
 *   id = "currency",
 *   label = @Translation("Currency"),
 *   list_controller_class = "Drupal\currency\CurrencyListController",
 *   module = "currency"
 * )
 */
class Currency extends ConfigEntityBase {

  /**
   * Alternative (non-official) currency signs.
   *
   * @var array
   *   An array of strings that are similar to self::sign.
   */
  public $alternativeSigns = array();

  /**
   * ISO 4217 currency code.
   *
   * @var string
   */
  public $currencyCode = NULL;

  /**
   * ISO 4217 currency number.
   *
   * @var string
   */
  public $currencyNumber = NULL;

  /**
   * Exchange rates to other currencies.
   *
   * @var array
   *   Keys are ISO 4217 codes, values are numeric strings.
   */
  public $exchangeRates = array();

  /**
   * The human-readable name.
   *
   * @var string
   */
  public $label = NULL;

  /**
   * The module implementing this currency.
   *
   * @var string
   */
  protected $module = 'currency';

  /**
   * The number of subunits to round amounts in this currency to.
   *
   * @see Currency::getRoundingStep()
   *
   * @var integer
   */
  public $roundingStep = NULL;

  /**
   * The currency's official sign, such as '€' or '$'.
   *
   * @var string
   */
  public $sign = '¤';

  /**
   * The number of subunits this currency has.
   *
   * @var integer|null
   */
  public $subunits = NULL;

  /**
   * This currency's usage.
   *
   * @var array
   *   An array of \Drupal\currency\Usage objects.
   */
  public $usage = array();

  /**
   * The UUID for this entity.
   *
   * @var string
   */
  public $uuid = NULL;

  /**
   * Overrides parent::id().
   */
  public function id() {
    return isset($this->currencyCode) ? $this->currencyCode : NULL;
  }

  /**
   * {@inheritdoc}
   */
  function uri() {
    $uri = array(
      'options' => array(
        'entity' => $this,
        'entity_type' => $this->entityType,
      ),
      'path' => 'admin/config/regional/currency/' . $this->id(),
    );

    return $uri;
  }

  /**
   * Returns the number of decimals.
   *
   * @todo Port this to the Entity Field API.
   *
   * @return int
   */
  public function getDecimals() {
    $decimals = 0;
    if ($this->subunits > 0) {
      $decimals = 1;
      while (pow(10, $decimals) < $this->subunits) {
        $decimals++;
      }
    }

    return $decimals;
  }

  /**
   * Returns an options list of all currencies.
   *
   * @todo Inject the entity manager service when
   * http://drupal.org/node/1863816 is fixed.
   *
   * @return array
   *   Keys are currency codes. Values are human-readable currency labels.
   */
  public static function options() {
    $options = array();
    foreach (entity_load_multiple('currency') as $currency) {
      $options[$currency->currencyCode] = t('@currency_title (@currency_code)', array(
        '@currency_title' => $currency->label(),
        '@currency_code' => $currency->currencyCode,
      ));
    }
    natcasesort($options);

    return $options;
  }

  /**
   * Format an amount using this currency and the environment's default locale
   * pattern.
   *
   * @param string $amount
   *   A numeric string.
   *
   * @return string
   */
  function format($amount) {
    return \Drupal::service('currency.locale_delegator')->getLocalePattern()->format($this, $amount);
  }

  /**
   * Returns the rounding step.
   *
   * @return string|false
   *   The rounding step as a numeric string, or FALSE if unavailable.
   */
  function getRoundingStep() {
    if (is_numeric($this->roundingStep)) {
      return $this->roundingStep;
    }
    // If a rounding step was not set explicitely, the rounding step is equal
    // to one subunit.
    elseif (is_numeric($this->subunits)) {
      return $this->subunits > 0 ? bcdiv(1, $this->subunits, CURRENCY_BCMATH_SCALE) : 1;
    }
    else {
      return FALSE;
    }
  }

  /**
   * Rounds an amount.
   *
   * @param string $amount
   *   A numeric string.
   *
   * @return string
   *   A numeric string.
   */
  function roundAmount($amount) {
    $rounding_step = $this->getRoundingStep();
    $decimals = $this->getDecimals();

    return bcmul(round(bcdiv($amount, $rounding_step, CURRENCY_BCMATH_SCALE)), $rounding_step, $decimals);
  }

  /**
   * Checks if the currency is no longer used in the world.
   *
   * @param int $reference
   *   A Unix timestamp to check the currency's usage for. Defaults to now.
   *
   * @return bool|null
   */
  function isObsolete($reference = NULL) {
    // Without usage information, we cannot know if the currency is obsolete.
    if (!$this->usage) {
      return FALSE;
    }

    // Default to the current date and time.
    if (is_null($reference)) {
      $reference = time();
    }

    // Mark the currency obsolete if all usages have an end date before that
    // comes before $reference.
    $obsolete = 0;
    foreach ($this->usage as $usage) {
      if ($usage->usageTo) {
        $to = strtotime($usage->usageTo);
        if ($to !== FALSE && $to < $reference) {
          $obsolete++;
        }
      }
    }
    return $obsolete == count($this->usage);
  }
}
