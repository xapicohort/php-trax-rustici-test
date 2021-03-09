<?php

namespace Drupal\skilling_xapi\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\skilling_xapi\SkillingXapiConstants;

/**
 * Configure the submissions settings.
 */
class XapiSettingsForm extends ConfigFormBase {

  /**
   * Gets the configuration names that will be editable.
   *
   * @return array
   *   An array of configuration object names that are editable if called in
   *   conjunction with the trait's config() method.
   */
  protected function getEditableConfigNames() {
    return [SkillingXapiConstants::SETTINGS_MAIN_KEY];
  }

  /**
   * Returns a unique string identifying the form.
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'skilling_xapi_config';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(SkillingXapiConstants::SETTINGS_MAIN_KEY);
    $form['instructions'] = [
      '#markup' => t('Configure xAPI. Set the end point data will be sent to. '),
    ];
    $form['endpoint_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Endpoint URL'),
      '#description' => $this->t(
        "The URL of the endpoint xAPI data will be sent to. Created by an LRS."
      ),
      '#default_value' => $config->get(SkillingXapiConstants::SETTING_ENDPOINT_URL),
    ];
    $form['endpoint_user_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Endpoint user name'),
      '#description' => $this->t(
        "An LRS user name."
      ),
      '#default_value' => $config->get(SkillingXapiConstants::SETTING_USER_NAME),
    ];
    $form['endpoint_password'] = [
      '#type' => 'password',
      '#title' => $this->t('Endpoint password'),
      '#description' => $this->t(
        "Password for the LRS user."
      ),
      '#default_value' => $config->get(SkillingXapiConstants::SETTING_PASSWORD),
    ];
    $form['endpoint_password_confirm'] = [
      '#type' => 'password',
      '#title' => $this->t('Confirm endpoint password'),
      '#description' => $this->t(
        "Password for the LRS user."
      ),
      '#default_value' => $config->get(SkillingXapiConstants::SETTING_PASSWORD),
    ];
    $form['platform'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Platform'),
      '#description' => $this->t(
        "For xAPI statement context. Usually the website home page URL."
      ),
      '#default_value' => $config->get(SkillingXapiConstants::SETTING_PLATFORM),
    ];
    $form['xapi_version'] = [
      '#type' => 'textfield',
      '#title' => $this->t('xAPI version'),
      '#description' => $this->t(
        "xAPI version, e.g., '1.0.3'."
      ),
      '#default_value' => $config->get(SkillingXapiConstants::SETTING_XAPI_VERSION),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config(SkillingXapiConstants::SETTINGS_MAIN_KEY);
    $endpointUrl = trim($form_state->getValue('endpoint_url'));
    if ($endpointUrl == '' || is_null($endpointUrl)) {
      $form_state->setErrorByName(
        'endpoint_url',
        $this->t('Sorry, an endpoint URL is required.'));
    }
    $endpointUserName = trim($form_state->getValue('endpoint_user_name'));
    if ($endpointUserName == '' || is_null($endpointUserName)) {
      $form_state->setErrorByName(
        'endpoint_user_name',
        $this->t('Sorry, an endpoint user name is required.'));
    }
    $password1 = trim($form_state->getValue('endpoint_password'));
    $password2 = trim($form_state->getValue('endpoint_password_confirm'));
    if ($password1 == '' && $password1 == '') {
      // Both fields are MT - OK, if a password is already stored.
      // Has a password been set before?
      $currentPassword = $config->get(SkillingXapiConstants::SETTING_PASSWORD);
      if ($currentPassword == '' || $currentPassword == null) {
        $form_state->setErrorByName(
          'endpoint_password',
          $this->t('Sorry, a password is required.'));
      }
    }
    else {
      if ($password1 != $password2) {
        $form_state->setErrorByName(
          'endpoint_password',
          $this->t('Sorry, the passwords are not the same.'));
      }
      if ($password1 == '' || is_null($password1)) {
        $form_state->setErrorByName(
          'endpoint_password',
          $this->t('Sorry, an endpoint password is required.'));
      }
    }
    $platform = trim($form_state->getValue('platform'));
    if ($platform == '' || is_null($platform)) {
      $form_state->setErrorByName(
        'platform',
        $this->t('Sorry, a platform is required.'));
    }
    $version = trim($form_state->getValue('xapi_version'));
    if ($version == '' || is_null($version)) {
      $form_state->setErrorByName(
        'xapi_version',
        $this->t('Sorry, xAPI version is required.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\Core\Config\Config $config */
    $config = $this->config(SkillingXapiConstants::SETTINGS_MAIN_KEY);
    $endpointUrl = trim($form_state->getValue('endpoint_url'));
    $endpointUserName = trim($form_state->getValue('endpoint_user_name'));
    $endpointPassword = trim($form_state->getValue('endpoint_password'));
    $platform = trim($form_state->getValue('platform'));
    $version = trim($form_state->getValue('xapi_version'));
    $config->set(SkillingXapiConstants::SETTING_ENDPOINT_URL, $endpointUrl);
    $config->set(SkillingXapiConstants::SETTING_USER_NAME, $endpointUserName);
    if ($endpointPassword != '' & ! is_null($endpointPassword)) {
      $config->set(SkillingXapiConstants::SETTING_PASSWORD, $endpointPassword);
    }
    $config->set(SkillingXapiConstants::SETTING_PLATFORM, $platform);
    $config->set(SkillingXapiConstants::SETTING_XAPI_VERSION, $version);
    $config->save();
    parent::submitForm($form, $form_state);
  }

}
