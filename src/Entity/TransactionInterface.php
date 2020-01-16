<?php

namespace Drupal\transaction\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Transaction entities.
 *
 * @ingroup transaction
 */
interface TransactionInterface extends ContentEntityInterface, RevisionLogInterface, EntityChangedInterface, EntityPublishedInterface, EntityOwnerInterface {

  /**
   * Add get/set methods for your configuration properties here.
   */

  /**
   * Gets the Transaction name.
   *
   * @return string
   *   Name of the Transaction.
   */
  public function getName();

  /**
   * Sets the Transaction name.
   *
   * @param string $name
   *   The Transaction name.
   *
   * @return \Drupal\transaction\Entity\TransactionInterface
   *   The called Transaction entity.
   */
  public function setName($name);

  /**
   * Gets the Transaction creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Transaction.
   */
  public function getCreatedTime();

  /**
   * Sets the Transaction creation timestamp.
   *
   * @param int $timestamp
   *   The Transaction creation timestamp.
   *
   * @return \Drupal\transaction\Entity\TransactionInterface
   *   The called Transaction entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Gets the Transaction revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the Transaction revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\transaction\Entity\TransactionInterface
   *   The called Transaction entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the Transaction revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionUser();

  /**
   * Sets the Transaction revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\transaction\Entity\TransactionInterface
   *   The called Transaction entity.
   */
  public function setRevisionUserId($uid);

  /*Returns the summ of the transaction*/
  public function getSumm();

  public function setSumm($summ);

  /*Returns the sender name of the transaction*/
  public function getSender();

  public function setSender($sender);

  /*Returns the recipient name of the transation*/
  public function getReceiver();

  public function setReceiver($receiver);

}
