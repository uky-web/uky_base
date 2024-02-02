<?php

namespace Drupal\uky_base_mega_menu\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Block\BlockRepositoryInterface;
use Drupal\Core\Messenger\MessengerInterface;
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
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
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
