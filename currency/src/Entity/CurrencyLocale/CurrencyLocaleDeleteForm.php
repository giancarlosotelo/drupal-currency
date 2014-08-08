<?php

/**
 * @file
 * Definition of Drupal\currency\Entity\CurrencyLocale\CurrencyLocaleDeleteForm.
 */

namespace Drupal\currency\Entity\CurrencyLocale;

use Drupal\Core\Entity\EntityConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a currency currency locale delete form.
 */
class CurrencyLocaleDeleteForm extends EntityConfirmFormBase {

  /**
   * Constructs a new class instance.
   *
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translator.
   */
  public function __construct(TranslationInterface $string_translation) {
    $this->stringTranslation = $string_translation;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('string_translation'));
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Do you really want to delete %label?', array(
      '%label' => $this->getEntity()->label(),
    ));
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('currency.currency_locale.list');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function submit(array $form, FormStateInterface $form_state) {
    $currency_locale = $this->getEntity();
    $currency_locale->delete();
    drupal_set_message($this->t('The currency locale %label has been deleted.', array(
      '%label' => $currency_locale->label(),
    )));
    $form_state->setRedirectUrl($this->getCancelUrl());
  }
}
