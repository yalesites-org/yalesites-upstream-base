<?php

namespace Drupal\ys_core\Form;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\ys_core\SocialLinksManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form for managing footer-related settings.
 *
 * @package Drupal\ys_core\Form
 */
class FooterSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ys_core_footer_settings_form';
  }

  /**
   * THe Drupal backend cache renderer service.
   *
   * @var \Drupal\Core\Path\CacheBackendInterface
   */
  protected $cacheRender;

  /**
   * Social Links Manager.
   *
   * @var \Drupal\ys_core\SocialLinksManager
   */
  protected $socialLinks;

  /**
   * Settings configuration form.
   *
   * @param array $form
   *   Form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state.
   *
   * @return array
   *   Form array to render.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form = parent::buildForm($form, $form_state);
    $socialConfig = $this->config('ys_core.social_links');
    $footerConfig = $this->config('ys_core.footer_settings');

    $form['#attached']['library'][] = 'ys_core/footer_settings_form';

    $form['footer_content'] = [
      '#type' => 'details',
      '#title' => $this->t('Footer Content'),
      '#open' => TRUE,
    ];

    $form['footer_links'] = [
      '#type' => 'details',
      '#title' => $this->t('Footer Links'),
      '#attributes' => [
        'class' => [
          'ys-footer-links',
        ],
      ],
    ];

    $form['social_links'] = [
      '#type' => 'details',
      '#title' => $this->t('Social Links'),
    ];

    $form['footer_content']['footer_logos'] = [
      '#type' => 'media_library',
      '#title' => $this->t('Footer logos'),
      '#allowed_bundles' => ['image'],
      '#required' => FALSE,
      '#cardinality' => 4,
      '#default_value' => ($footerConfig->get('content.logos')) ? implode(',', $footerConfig->get('content.logos')) : NULL,
    ];

    $form['footer_content']['footer_text'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Text Content'),
      '#format' => 'restricted_html',
      '#default_value' => (isset($footerConfig->get('content.text')['value'])) ? $footerConfig->get('content.text')['value'] : NULL,
    ];

    $form['footer_links']['links_col_1_heading'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Link Column 1 Heading'),
      '#default_value' => ($footerConfig->get('links.links_col_1_heading')) ? $footerConfig->get('links.links_col_1_heading') : NULL,
    ];

    $form['footer_links']['links_col_2_heading'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Link Column 2 Heading'),
      '#default_value' => ($footerConfig->get('links.links_col_2_heading')) ? $footerConfig->get('links.links_col_2_heading') : NULL,
    ];

    $form['footer_links']['column_1_links'] = [
      '#type' => 'multivalue',
      '#title' => $this->t('Links Column 1'),
      '#cardinality' => 4,
      '#default_value' => ($footerConfig->get('links.column_1_links')) ? $footerConfig->get('links.column_1_links') : NULL,

      'link_url' => [
        '#type' => 'linkit',
        '#title' => $this->t('URL'),
        '#description' => $this->t('Type the URL or autocomplete for internal paths.'),
        '#autocomplete_route_name' => 'linkit.autocomplete',
        '#autocomplete_route_parameters' => [
          'linkit_profile_id' => 'default',
        ],
      ],
      'link_title' => [
        '#type' => 'textfield',
        '#title' => $this->t('Link Title'),
        '#default_value' => (isset($footerConfig->get('links.links_col_1')['link_title'])) ? $footerConfig->get('links.links_col_1')['link_title'] : NULL,
      ],
    ];

    $form['footer_links']['column_2_links'] = [
      '#type' => 'multivalue',
      '#title' => $this->t('Links Column 2'),
      '#cardinality' => 4,
      '#default_value' => ($footerConfig->get('links.column_2_links')) ? $footerConfig->get('links.column_2_links') : NULL,
      'link_url' => [
        '#type' => 'linkit',
        '#title' => $this->t('URL'),
        '#description' => $this->t('Type the URL or autocomplete for internal paths.'),
        '#autocomplete_route_name' => 'linkit.autocomplete',
        '#autocomplete_route_parameters' => [
          'linkit_profile_id' => 'default',
        ],
      ],
      'link_title' => [
        '#type' => 'textfield',
        '#title' => $this->t('Link Title'),
      ],
    ];

    foreach ($this->socialLinks::SITES as $id => $name) {
      $form['social_links'][$id] = [
        '#type' => 'url',
        '#title' => $this->t('@name URL', ['@name' => $name]),
        '#default_value' => $socialConfig->get($id),
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $this->validateFooterLinks($form_state, 'column_1_links');
    $this->validateFooterLinks($form_state, 'column_2_links');
  }

  /**
   * Submit form action.
   *
   * @param array $form
   *   Form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Social config.
    $socialConfig = $this->config('ys_core.social_links');
    foreach ($this->socialLinks::SITES as $id => $name) {
      $socialConfig->set($id, $form_state->getValue($id));
    }
    $socialConfig->save();

    // Footer settings config.
    $footerConfig = $this->config('ys_core.footer_settings');
    $footerConfig->set('content.logos', explode(',', $form_state->getValue('footer_logos')));
    $footerConfig->set('content.text', $form_state->getValue('footer_text'));
    $footerConfig->set('links.links_col_1_heading', $form_state->getValue('links_col_1_heading'));
    $footerConfig->set('links.links_col_2_heading', $form_state->getValue('links_col_2_heading'));
    $footerConfig->set('links.column_1_links', $form_state->getValue('column_1_links'));
    $footerConfig->set('links.column_2_links', $form_state->getValue('column_2_links'));

    $footerConfig->save();

    $this->cacheRender->invalidateAll();
    return parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'ys_core.social_links',
      'ys_core.footer_settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('cache.render'),
      $container->get('ys_core.social_links_manager')
    );
  }

  /**
   * Constructs the object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Path\CacheBackendInterface $cache_render
   *   The Cache backend interface.
   * @param \Drupal\ys_core\SocialLinksManager $social_links_manager
   *   The Yale social media links management service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, CacheBackendInterface $cache_render, SocialLinksManager $social_links_manager) {
    parent::__construct($config_factory);
    $this->cacheRender = $cache_render;
    $this->socialLinks = $social_links_manager;
  }

  /**
   * Check that footer links have both a URL and a link title.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state of the parent form.
   * @param string $field_id
   *   The id of a field on the config form.
   */
  protected function validateFooterLinks($form_state, $field_id) {
    if (($value = $form_state->getValue($field_id))) {
      foreach ($value as $link) {
        if (empty($link['link_url']) || empty($link['link_title'])) {
          $form_state->setErrorByName(
            $field_id,
            $this->t("Any link specified must have both a URL and a link title."),
          );
        }

      }
    }
  }

}
