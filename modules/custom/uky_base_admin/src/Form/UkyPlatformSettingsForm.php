<?php

namespace Drupal\uky_base_admin\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Menu\MenuLinkTreeInterface;
use Drupal\Core\Menu\MenuTreeParameters;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Render\Element;

/**
 * Provides the UK Platform settings page with auto-generated submenu items.
 */
class UkyPlatformSettingsForm extends FormBase {

  /**
   * The menu link tree service.
   *
   * @var \Drupal\Core\Menu\MenuLinkTreeInterface
   */
  protected $menuLinkTree;

  /**
   * Constructs a new UkyPlatformSettingsForm.
   *
   * @param \Drupal\Core\Menu\MenuLinkTreeInterface $menu_link_tree
   *   The menu link tree service.
   */
  public function __construct(MenuLinkTreeInterface $menu_link_tree) {
    $this->menuLinkTree = $menu_link_tree;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('menu.link_tree')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'uky_base_admin_platform_settings';
  }

  /**
   * Build the form with dynamically generated submenu links.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $menu_name = 'admin';
    $parent_menu_link = 'uky_base_admin.uky_platform';

    // Create proper MenuTreeParameters object to avoid argument errors.
    $parameters = new MenuTreeParameters();
    $parameters->setRoot($parent_menu_link)->onlyEnabledLinks();

    // Load the menu tree with correct parameters.
    $tree = $this->menuLinkTree->load($menu_name, $parameters);

    // Get menu manipulators from Drupal service.
    $manipulators = [
      ['callable' => 'menu.default_tree_manipulators:checkAccess'],
      ['callable' => 'menu.default_tree_manipulators:generateIndexAndSort'],
    ];

    // Transform the menu tree with the required manipulators.
    $manipulated_tree = $this->menuLinkTree->transform($tree, $manipulators);
    $submenu = $this->menuLinkTree->build($manipulated_tree);

    $form['description'] = [
      '#markup' => '<p>This page will be updated eventually.  For now this is a placeholder for child pages.</p>',
    ];

    // Render submenu items.
    if (!empty(Element::children($submenu))) {
      $form['submenu'] = $submenu;
    }
    else {
      $form['submenu'] = [
        //'#markup' => '<p>No settings found under UK Platform.</p>',
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // No actions needed for now, just a settings page.
  }
}
