<?php

namespace Drupal\vchess\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\gamer\GamerController;
use Drupal\user\Entity\User;
use Drupal\vchess\Entity\Game;

/**
 * Form to start a chess game against a random opponent.
 */
class RandomGameForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'vchess_random_game_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['description'] = [
      '#type' => 'item',
      '#title' => $this->t('Simply click on the button below and we will create
          a game for you against a random opponent.'),
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Create Game'),
    ];

    return $form;
  }


  /**
   * Form submission handler.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $user = User::load($this->currentUser()->id());
//    $opponent_uid = $this->randomUid();

    // Get the uid load a random opponent.
    $possible_opponents = array_keys(\Drupal::entityQuery('user')
      ->condition('uid', 0, '!=')
      ->condition('uid', $user->id(), '!=')
      ->execute());

    $random_uid = $possible_opponents[rand(0, count($possible_opponents) - 1)];
    $opponent = User::load($random_uid);

    if (rand(0, 1)) {
      // Current user plays white and opponent plays black
      $white_user = $user;
      $black_user = $opponent;
    }
    else {
      // Current user plays black and opponent plays white
      $black_user = $user;
      $white_user = $opponent;
    }

    $game = Game::create()
      ->setBlackUser($black_user)
      ->setWhiteUser($white_user);
    $game->save();

    // @todo: Check out this method.
    GamerController::startGame($white_user, $black_user);

    drupal_set_message(t('Game has been created.'));
    $form_state->setRedirect('vchess.game', ['game' => $game->id()]);
  }

  /**
   * Get a random user id (uid)
   *
   * @return
   *   A user id (uid)
   */
  protected function randomUid() {
    $record = \Drupal::database()->select('users', 'u')
      ->fields('u', 'uid')
      ->condition('uid', '0', '!=')
      ->execute()
      ->limit(0, 1)
      ->fetchAssoc();

//      "SELECT uid FROM {users} WHERE uid <> 0 ORDER BY RAND()";

    return $record['uid'];
  }

}