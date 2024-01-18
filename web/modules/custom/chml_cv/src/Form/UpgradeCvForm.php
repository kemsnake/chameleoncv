<?php

namespace Drupal\chml_cv\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form for send request to ChatGPT.
 */
class UpgradeCvForm extends FormBase {

  /**
   * The OpenAI client.
   *
   * @var \OpenAI\Client
   */
  protected $client;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'upgrade_cv_form';
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->client = $container->get('openai.client');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $config_name = '') {

    /*$form['cv'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Your CV'),
    ];
    $form['cv']['file'] = [
      '#type' => 'file',
      '#title' => '',
    ];*/
    $form['#theme'] = 'upgrade_cv_form';
    $form['cv'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Your resume'),
      '#placeholder' => $this->t('add a Resume description'),
    ];

    /*$form['job'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Your future job'),
    ];
    $form['job']['url'] = [
      '#type' => 'textfield',
      '#title' => '',
      '#placeholder' => $this->t('Copy the link'),
    ];*/
    $form['job'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Your future job'),
      //'#title' => '',
      '#placeholder' => $this->t('add a Job description'),
    ];

    $form['actions'] = ['#type' => 'actions'];
    $form['response'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Response'),
      /*'#attributes' =>
        [
          'readonly' => 'readonly',
        ],*/
      '#prefix' => '<div id="openai-chatgpt-response">',
      '#suffix' => '</div>',
      '#description' => $this->t('The response from OpenAI will appear in the textbox above.')
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Check'),
/*      '#submit' => [
        [$this, 'submitForm'],
      ],*/
      '#ajax' => [
        'callback' => '::getResponse',
        'wrapper' => 'openai-chatgpt-response',
        'progress' => [
          'type' => 'throbber',
        ],
      ],
    ];
    return $form;
  }

  /**
   * Render the last response out to the user.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return array
   *   The modified form element.
   */
  public function getResponse(array &$form, FormStateInterface $form_state) {
    $errors = $form_state->getErrors();
    if (empty($errors)) {
      $storage = $form_state->getStorage();
      $last_response = end($storage['messages']);
      $form['response']['#value'] = trim($last_response['content']) ?? $this->t('No answer was provided.');
    }
    else {
      $form['response']['#value'] = 'errors';
    }
    return $form['response'];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $cv = $form_state->getValue('cv_description');
    $vacancy = $form_state->getValue('job_description');
    $request_text = \Drupal::config('chml_cv.settings')->get('request_text');
    $request_text = str_replace('{resume}', $cv, $request_text);
    $request_text = str_replace('{vacancy}', $vacancy, $request_text);
    $system = 'You are a friendly helpful assistant inside of a Drupal website. Be encouraging and polite and ask follow up questions of the user after giving the answer.';
    $model = 'gpt-3.5-turbo';
    $temperature = '0.4';
    $max_tokens = '1000';

    $messages = [
      ['role' => 'system', 'content' => trim($request_text)],
      ['role' => 'user', 'content' => 'continue'],
    ];

    $response = $this->client->chat()->create(
      [
        'model' => $model,
        'messages' => $messages,
        'temperature' => (int) $temperature,
        'max_tokens' => (int) $max_tokens,
      ],
    );

    $result = $response->toArray();

    $messages[] = [
      'role' => 'assistant',
      'content' => trim($result["choices"][0]["message"]["content"]),
    ];
    $form_state->setStorage(['messages' => $messages]);
    $form_state->setRebuild(TRUE);
    /*
    $this->messenger()->addStatus(trim($result["choices"][0]["message"]["content"]));
    $this->logger()->warning(trim($result["choices"][0]["message"]["content"]));*/
    //$form_state->setRebuild(TRUE);
//who is a best actor?
  }

}
