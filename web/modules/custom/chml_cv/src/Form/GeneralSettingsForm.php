<?php

namespace Drupal\chml_cv\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class GeneralSettingsForm. The config form for the chml_cv module.
 *
 * @package Drupal\chml_cv\Form
 */
class GeneralSettingsForm extends ConfigFormBase {

  /**
   * {@inheritDoc}
   */
  protected function getEditableConfigNames() {
    return [
      'chml_cv.settings',
    ];
  }

  /**
   * {@inheritDoc}
   */
  public function getFormId() {
    return 'chml_cv_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('chml_cv.settings');
    $form['request_text'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Request text'),
      '#description' => $this->t('You can use snippets {resume} and {vacancy}.'),
      '#default_value' => $config->get('request_text'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('chml_cv.settings')
      ->set('request_text', $form_state->getValue('request_text'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
