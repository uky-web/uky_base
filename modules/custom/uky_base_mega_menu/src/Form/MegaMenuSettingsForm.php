<?php

namespace Drupal\uky_base_mega_menu\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;



/**
 * Provides a form for Mega Menu settings.
 */
class MegaMenuSettingsForm extends ConfigFormBase {

  /**
   * The Block Repository service.
   *
   * @var \Drupal\Core\Block\BlockRepositoryInterface
   */
  protected $blockRepository;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'uky_base_mega_menu_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['uky_base_mega_menu.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function access(AccountInterface $account, $return_as_object = FALSE) {
    $access = $account->hasPermission('administer uky base mega menu settings');
    return $return_as_object ? AccessResult::allowedIf($access)->cachePerPermissions() : $access;
  }

  /**
   * The config.factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a new YourCustomClass object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config.factory service.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory')
    );
  }


/**
 * {@inheritdoc}
 */
public function buildForm(array $form, FormStateInterface $form_state) {

  // Get the active theme machine name.
  $configFactory = \Drupal::configFactory();
  $config = $configFactory->getEditable('system.theme');
  $activeTheme = $config->get('default');

  // Check if the active theme is not ukd8.
  if ($activeTheme !== 'ukd8') {
    // Add a message indicating that ukd8 is not the default theme.
    $message = $this->t('You are not using the University Web Platform base theme, ukd8.  You are using @default.  Changing the menu settings below will create a @default_uky_mega_menu block, @default_mega_menu_mobile block, and place them in the correct regions.', ['@default' => $activeTheme]);
    $form['theme_message'] = [
      '#markup' => '<div class="messages messages--warning">' . $message . '</div>',
    ];

    // Generate new ID and theme for the block duplication.
    $new_id = $activeTheme . '_uky_mega_menu';
    $new_id_mobile = $activeTheme . '_uky_mega_menu_mobile';
    $new_theme = $activeTheme;

    // Check if the mega menu block with the new ID already exists.
    if (!$this->blockExists($new_id)) {
      $this->duplicateUkyMegaMenuBlock($new_id, $new_theme);
    }

    // Check if the mega menu mobile block with the new ID already exists.
    if (!$this->blockExists($new_id_mobile)) {
      $this->duplicateUkyMegaMenuMobileBlock($new_id_mobile, $new_theme);
    }

  }

  // Check if the active theme is ukd8.
  elseif ($activeTheme == 'ukd8') {
    // Add a message indicating that ukd8 is the default theme.
    $message = $this->t('You are using the University Web Platform base theme, ukd8.');
    $form['theme_message'] = [
      '#markup' => '<div class="messages messages--status">' . $message . '</div>',
    ];
  }

  $config = $this->config('uky_base_mega_menu.settings');
  $form['menu_type'] = [
    '#type' => 'radios',
    '#title' => $this->t('Menu Type'),
    '#options' => [
      'standard_menu' => $this->t('Standard Menu'),
      'mega_menu' => $this->t('Mega Menu'),
    ],
    '#default_value' => $config->get('menu_type') ?: 'standard_menu',
  ];

  $form['submit'] = [
    '#type' => 'submit',
    '#value' => $this->t('Save configuration'),
  ];

  return parent::buildForm($form, $form_state);
}


/**
 * Helper function to check if a block with the given ID already exists.
 */
private function blockExists($block_id) {
  $block_storage = \Drupal::entityTypeManager()->getStorage('block');
  return (bool) $block_storage->load($block_id);
}


/**
 * Helper function to duplicate the uky_mega_menu block.
 */
private function duplicateUkyMegaMenuBlock($new_id, $new_theme) {
  $block_storage = \Drupal::entityTypeManager()->getStorage('block');

  // Load the original uky_mega_menu block configuration.
  $original_block_config = $block_storage->load('uky_mega_menu');

  if ($original_block_config) {
    // Clone the original block configuration.
    $duplicate_block_config = $original_block_config->createDuplicate();

    // Update properties for the new block configuration.
    $duplicate_block_config->set('id', $new_id);
    $duplicate_block_config->set('theme', $new_theme);

    // Save the new block configuration.
    $duplicate_block_config->save();

    // Load the menu block plugin configuration.
    $original_menu_block_config = $original_block_config->getPlugin()->getConfiguration();

    if ($original_menu_block_config) {
      // Clone the original menu block plugin configuration.
      $duplicate_menu_block_config = $original_menu_block_config;

      // Update the ID to match the new block configuration.
      $duplicate_menu_block_config['id'] = $new_id;

      // Explicitly set the required settings.
      $duplicate_menu_block_config['label'] = 'Main navigation (Mega Menu)';
      $duplicate_menu_block_config['label_display'] = 0;
      $duplicate_menu_block_config['level'] = 1;
      $duplicate_menu_block_config['depth'] = 3;
      $duplicate_menu_block_config['expand_all_items'] = TRUE;

      // Attach the new menu block plugin configuration to the new block configuration.
      $duplicate_block_config->set('plugin_configuration', $duplicate_menu_block_config);

      // Save the block configuration again to apply the settings.
      $duplicate_block_config->save();
    }
  }
}

/**
 * Helper function to duplicate the uky_mega_menu_mobile block.
 */
private function duplicateUkyMegaMenuMobileBlock($new_id_mobile, $new_theme) {
  $block_storage = \Drupal::entityTypeManager()->getStorage('block');

  // Load the original uky_mega_menu block configuration.
  $original_block_config = $block_storage->load('uky_mega_menu_mobile');

  if ($original_block_config) {
    // Clone the original block configuration.
    $duplicate_block_config = $original_block_config->createDuplicate();

    // Update properties for the new block configuration.
    $duplicate_block_config->set('id', $new_id_mobile);
    $duplicate_block_config->set('theme', $new_theme);

    // Save the new block configuration.
    $duplicate_block_config->save();

    // Load the menu block plugin configuration.
    $original_menu_block_config = $original_block_config->getPlugin()->getConfiguration();

    if ($original_menu_block_config) {
      // Clone the original menu block plugin configuration.
      $duplicate_menu_block_config = $original_menu_block_config;

      // Update the ID to match the new block configuration.
      $duplicate_menu_block_config['id'] = $new_id_mobile;

      // Explicitly set the required settings.
      $duplicate_menu_block_config['label'] = 'Main navigation (Mega Menu Mobile)';
      $duplicate_menu_block_config['label_display'] = 0;
      $duplicate_menu_block_config['level'] = 1;
      $duplicate_menu_block_config['depth'] = 3;
      $duplicate_menu_block_config['expand_all_items'] = TRUE;

      // Attach the new menu block plugin configuration to the new block configuration.
      $duplicate_block_config->set('plugin_configuration', $duplicate_menu_block_config);

      // Save the block configuration again to apply the settings.
      $duplicate_block_config->save();
    }
  }
}

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('uky_base_mega_menu.settings')
      ->set('menu_type', $form_state->getValue('menu_type'))
      ->save();

    // Perform block visibility settings based on the selected menu type.
    $this->updateBlockVisibility($form_state->getValue('menu_type'));

    // Redirect after submitting the form.
    $form_state->setRedirect('uky_base_mega_menu.admin_settings');

    // Add a success message.
    $this->messenger()->addStatus($this->t('Menu settings have been updated.'));
  }



  /**
   * Helper function to update block visibility based on menu type.
   */
  private function updateBlockVisibility($menu_type) {

    // Get the active theme machine name.
    $configFactory = \Drupal::configFactory();
    $config = $configFactory->getEditable('system.theme');
    $activeTheme = $config->get('default');

    // Check if the active theme is not ukd8.
    if ($activeTheme !== 'ukd8') {
      // Disable standard menu blocks by default.
      $this->setBlockVisibility('uky_main_menu', FALSE);
      $this->setBlockVisibility('mainnavigation_3', FALSE);
      $this->setBlockVisibility('mainnavigation_2', FALSE);
      $this->setBlockVisibility($activeTheme . '_uky_main_menu', FALSE);
      $this->setBlockVisibility($activeTheme . '_mainnavigation_3', FALSE);
      $this->setBlockVisibility($activeTheme . '_mainnavigation_2', FALSE);

      // Disable mega menu blocks by default.
      $this->setBlockVisibility('uky_mega_menu', FALSE);
      $this->setBlockVisibility('uky_mega_menu_mobile', FALSE);
      $this->setBlockVisibility($activeTheme . '_uky_mega_menu', FALSE);
      $this->setBlockVisibility($activeTheme . '_uky_mega_menu_mobile', FALSE);

      // Enable blocks based on the selected menu type.
      if ($menu_type == 'standard_menu') {
        $this->setBlockVisibility('uky_main_menu', TRUE);
        $this->setBlockVisibility('mainnavigation_3', TRUE);
        $this->setBlockVisibility('mainnavigation_2', TRUE);
        $this->setBlockVisibility($activeTheme . '_uky_main_menu', TRUE);
        $this->setBlockVisibility($activeTheme . '_mainnavigation_3', TRUE);
        $this->setBlockVisibility($activeTheme . '_mainnavigation_2', TRUE);
      } elseif ($menu_type == 'mega_menu') {
        $this->setBlockVisibility('uky_mega_menu', TRUE);
        $this->setBlockVisibility('uky_mega_menu_mobile', TRUE);
        $this->setBlockVisibility($activeTheme . '_uky_mega_menu', TRUE);
        $this->setBlockVisibility($activeTheme . '_uky_mega_menu_mobile', TRUE);
      }
    }

    // Check if the active theme is ukd8.
    elseif ($activeTheme == 'ukd8') {
      // Disable standard menu blocks by default.
      $this->setBlockVisibility('uky_main_menu', FALSE);
      $this->setBlockVisibility('mainnavigation_3', FALSE);
      $this->setBlockVisibility('mainnavigation_2', FALSE);

      // Disable mega menu blocks by default.
      $this->setBlockVisibility('uky_mega_menu', FALSE);
      $this->setBlockVisibility('uky_mega_menu_mobile', FALSE);

      // Enable blocks based on the selected menu type.
      if ($menu_type == 'standard_menu') {
        $this->setBlockVisibility('uky_main_menu', TRUE);
        $this->setBlockVisibility('mainnavigation_3', TRUE);
        $this->setBlockVisibility('mainnavigation_2', TRUE);
      } elseif ($menu_type == 'mega_menu') {
        $this->setBlockVisibility('uky_mega_menu', TRUE);
        $this->setBlockVisibility('uky_mega_menu_mobile', TRUE);
      }
    }
  }

/**
 * Helper function to set block visibility.
 *
 * @param string $plugin_id
 *   The block plugin ID.
 * @param bool $status
 *   The visibility status (TRUE or FALSE).
 */
private function setBlockVisibility($plugin_id, $status) {
    // Get the block repository service.
    $block_repository = \Drupal::service('entity_type.manager')->getStorage('block');
  
    // Load the block configuration.
    $block_config = $block_repository->load($plugin_id);
  
    if ($block_config) {
      // Modify the status in the block configuration.
      $block_config->set('status', $status);
  
      // Save the modified block configuration.
      $block_config->save();
    }
  }

  /**
   * Gets the block repository service.
   *
   * @return \Drupal\Core\Block\BlockRepositoryInterface
   *   The block repository service.
   */
  protected function getBlockRepository() {
    if (!$this->blockRepository) {
      $this->blockRepository = \Drupal::service('block.repository');
    }
    return $this->blockRepository;
  }

}
