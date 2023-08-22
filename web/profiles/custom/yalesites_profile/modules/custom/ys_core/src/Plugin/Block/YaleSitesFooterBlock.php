<?php

namespace Drupal\ys_core\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\ys_core\SocialLinksManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Adds a footer block with logos, text, links, and social from footer settings.
 *
 * @Block(
 *   id = "ys_footer_block",
 *   admin_label = @Translation("YaleSites Footer Block"),
 *   category = @Translation("YaleSites Core"),
 * )
 */
class YaleSitesFooterBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Social Links Manager.
   *
   * @var \Drupal\ys_core\SocialLinksManager
   */
  protected $socialLinks;

  /**
   * Footer settings.
   *
   * @var Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $footerSettings;

  /**
   * Entity type manager.
   *
   * @var Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    SocialLinksManager $social_links_manager,
    ConfigFactoryInterface $config_factory,
    EntityTypeManager $entity_type_manager,
    ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->socialLinks = $social_links_manager;
    $this->footerSettings = $config_factory->get('ys_core.footer_settings');
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('ys_core.social_links_manager'),
      $container->get('config.factory'),
      $container->get('entity_type.manager'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $footerLogos = [];

    foreach ($this->footerSettings->get('content.logos') as $key => $logoData) {
      $footerLogoMedia = $this->entityTypeManager->getStorage('media')->load($logoData['logo']);
      $footerLogos[$key]['logo'] = $this->entityTypeManager->getViewBuilder('media')->view($footerLogoMedia, 'profile_directory_card_1_1_');
      $footerLogos[$key]['url'] = $logoData['logo_url'];
    }

    $schoolLogoId = $this->footerSettings->get('content.school_logo');
    $schoolLogo = [];

    if ($schoolLogoId) {
      $schoolLogoMedia = $this->entityTypeManager->getStorage('media')->load($schoolLogoId);
      $schoolLogo = $this->entityTypeManager->getViewBuilder('media')->view($schoolLogoMedia, 'profile_directory_card_1_1_');
    }

    return [
      '#theme' => 'ys_footer_block',
      '#footer_logos' => $footerLogos,
      '#school_logo' => $schoolLogo,
      '#footer_text' => [
        '#type' => 'processed_text',
        '#text' => $this->footerSettings->get('content.text')['value'],
        '#format' => 'restricted_html',
      ],
      '#footer_links_col_1_heading' => $this->footerSettings->get('links.links_col_1_heading'),
      '#footer_links_col_2_heading' => $this->footerSettings->get('links.links_col_2_heading'),
      '#footer_links_col_1' => $this->footerSettings->get('links.links_col_1'),
      '#footer_links_col_2' => $this->footerSettings->get('links.links_col_2'),
    ];
  }

}
