<?php

/**
 * @file
 * Contains \Drupal\contact_storage\MessageEditForm.
 */

namespace Drupal\contact_storage;

use Drupal\Component\Utility\String;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form controller for contact message edit forms.
 */
class MessageEditForm extends ContentEntityForm {

  /**
   * The language manager service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Constructs a MessageEditForm object.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager service.
   */
  public function __construct(EntityManagerInterface $entity_manager, LanguageManagerInterface $language_manager) {
    parent::__construct($entity_manager);
    $this->languageManager = $language_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager'),
      $container->get('language_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $user = $this->currentUser();
    /** @var \Drupal\contact\MessageInterface $message */
    $message = $this->entity;
    $form = parent::form($form, $form_state, $message);

    $language_configuration = $this->moduleHandler->invoke('language', 'get_default_configuration', array('contact_message', $message->getContactForm()->id()));
    $form['langcode'] = array(
      '#title' => $this->t('Language'),
      '#type' => 'language_select',
      '#default_value' => $message->getUntranslated()->language()->getId(),
      '#languages' => LanguageInterface::STATE_ALL,
      '#access' => isset($language_configuration['language_show']) && $language_configuration['language_show'],
    );

    $form['name'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Author name'),
      '#maxlength' => 255,
      '#default_value' => $message->getSenderName(),
    );
    $form['mail'] = array(
      '#type' => 'email',
      '#title' => $this->t('Sender email address'),
      '#default_value' => $message->getSenderMail(),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $this->entity->save();
    $this->logger('contact')->notice('The contact message %subject has been updated.', array(
      '%subject' => $this->entity->getSubject(),
      'link' => $this->l($this->t('Edit'), 'entity.contact_message.edit_form', array('contact_message' => $this->entity->id())),
    ));
  }

}