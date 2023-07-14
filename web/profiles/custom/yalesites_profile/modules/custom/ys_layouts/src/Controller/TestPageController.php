<?php

namespace Drupal\ys_layouts\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;

/**
 * Provides route responses for the Example module.
 */
class TestPageController extends ControllerBase {

  /**
   * Returns a simple page.
   *
   * @return array
   *   A simple renderable array.
   */
  public function testPage() {

    // Gets the main event meta section so we can clone it to nodes that don't have it yet
    $entityTypeManager = \Drupal::service('entity_type.manager');
    if ($eventViewDisplay = $entityTypeManager->getStorage('entity_view_display')->load('node.event.default')) {
      if ($eventViewDisplay->isLayoutBuilderEnabled()) {
        $eventSections = $eventViewDisplay->getSections();
        foreach ($eventSections as $section) {
          if ($section->getLayoutSettings()['label'] == 'Event Meta') {
            $eventMetaSection = $section;
          }
        }
      }
    }

    // Find all event nodes to update existing.
    $nids = \Drupal::entityQuery('node')->condition('type','event')->execute();

    foreach ($nids as $nid) {
      $node = Node::load($nid);
      $layout = $node->get('layout_builder__layout');
      $sections = $layout->getSections();

      foreach ($sections as $section) {
        // If an overridden layout already contains an Event Meta section, remove it from the update list.
        if ($section->getLayoutSettings()['label'] == 'Event Meta') {
          unset($nids[array_search($nid, $nids)]);
        }
      }
    }

    foreach ($nids as $nid) {
      $node = Node::load($nid);
      $layout = $node->get('layout_builder__layout');
      $layout->insertSection(0, $eventMetaSection);
      $node->save();
    }

    return [
      '#markup' => 'Hello, world',
    ];
  }

}
