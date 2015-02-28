<?php

namespace Drupal\multiversion\Entity\Query;


trait QueryTrait {

  /**
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * @var null|string
   */
  protected $workspaceId = NULL;

  /**
   * @var boolean
   */
  protected $isDeleted = FALSE;

  /**
   * @var boolean
   */
  protected $isTransacting = FALSE;

  /**
   *
   */
  public function useWorkspace($id) {
    $this->workspaceId = $id;
    return $this;
  }

  /**
   * @see \Drupal\multiversion\Entity\Query\QueryInterface::isDeleted()
   */
  public function isDeleted() {
    $this->isDeleted = TRUE;
    return $this;
  }

  /**
   * @see \Drupal\multiversion\Entity\Query\QueryInterface::isNotDeleted()
   */
  public function isNotDeleted() {
    $this->isDeleted = FALSE;
    return $this;
  }

  /**
   * @see \Drupal\multiversion\Entity\Query\QueryInterface::isTransacting()
   */
  public function isTransacting() {
    $this->isTransacting = TRUE;
    return $this;
  }

  /**
   * @see \Drupal\multiversion\Entity\Query\QueryInterface::isNotTransacting()
   */
  public function isNotTransacting() {
    $this->isTransacting = FALSE;
    return $this;
  }

  public function prepare() {
    parent::prepare();
    $entity_type = $this->entityManager->getDefinition($this->entityTypeId);
    $revision_key = $entity_type->getKey('revision');

    $revision_query = FALSE;
    foreach ($this->condition->conditions() as $condition) {
      if ($condition['field'] == $revision_key) {
        $revision_query = TRUE;
      }
    }

    // Loading a revision is explicit. So when we try to load one we should do
    // so without a condition on the deleted flag.
    if (!$revision_query) {
      $this->condition('_deleted', (int) $this->isDeleted);
    }
    $this->condition('workspace', $this->workspaceId ?: \Drupal::service('multiversion.manager')->getActiveWorkspaceId());
    return $this;
  }

}
