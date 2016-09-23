<?php

namespace Drupal\adminrss\Form;

use Drupal\adminrss\AdminRss;
use Drupal\adminrss\ViewsManager;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class AdminRssSettingsForm is the AdminRSS configuration form.
 */
class AdminRssSettingsForm extends ConfigFormBase {
  /**
   * The adminrss.views_manager service.
   *
   * @var \Drupal\adminrss\ViewsManager
   */
  protected $viewsManager;

  /**
   * AdminRssSettingsForm constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config.factory service.
   * @param \Drupal\adminrss\ViewsManager $viewsManager
   *   The adminrss.views_manager service.
   */
  public function __construct(ConfigFactoryInterface $configFactory, ViewsManager $viewsManager) {
    parent::__construct($configFactory);
    $this->viewsManager = $viewsManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $configFactory = $container->get('config.factory');
    $viewsManager = $container->get('adminrss.views_manager');
    return new static($configFactory, $viewsManager);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      AdminRss::CONFIG,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'admin_rss_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(AdminRss::CONFIG);
    $token = $config->get(AdminRss::TOKEN);
    $feedLinks = $this->viewsManager->getFeedLinks($token);

    $form[AdminRss::TOKEN] = array(
      '#default_value' => $token,
      '#description' => t('This is the token that will be required in order to get access to the AdminRSS feeds.'),
      '#maxlength' => 255,
      '#required' => TRUE,
      '#size' => 50,
      '#title' => $this->t('Admin RSS Token'),
      '#type' => 'textfield',
      '#weight' => -5,
    );

    if (!empty($feedLinks)) {
      $form['feeds'] = array(
        '#type' => 'details',
        '#title' => $this->t('Admin RSS Feeds locations'),
        '#description' => $this->t('Copy and paste these links to your RSS aggregator.'),
        '#open' => TRUE,
      );

      $form['feeds']['links'] = array(
        '#theme' => 'item_list',
        '#items' => $feedLinks,
      );
    }

    $form = parent::buildForm($form, $form_state);
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->configFactory()
      ->getEditable(AdminRss::CONFIG)
      ->set(AdminRss::TOKEN, $form_state->getValue(AdminRss::TOKEN))
      ->save();
  }

}
