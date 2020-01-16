<?php

namespace Drupal\transaction;

use Drupal\Core\Entity\ContentEntityStorageInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\transaction\Entity\TransactionInterface;

/**
 * Defines the storage handler class for Transaction entities.
 *
 * This extends the base storage class, adding required special handling for
 * Transaction entities.
 *
 * @ingroup transaction
 */
interface TransactionStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of Transaction revision IDs for a specific Transaction.
   *
   * @param \Drupal\transaction\Entity\TransactionInterface $entity
   *   The Transaction entity.
   *
   * @return int[]
   *   Transaction revision IDs (in ascending order).
   */
  public function revisionIds(TransactionInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as Transaction author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   Transaction revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

}
